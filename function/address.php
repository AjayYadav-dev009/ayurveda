<?php
require_once __DIR__ . '/../config/database.php';

/**
 * =============================================================================
 * Customer Address Functions
 * =============================================================================
 * Table: addresses
 *   id             bigint UNSIGNED PK
 *   user_id        bigint UNSIGNED  (FK -> users.id, ON DELETE CASCADE)
 *   full_name      varchar(150)
 *   phone          varchar(20)
 *   address_line1  varchar(255)
 *   address_line2  varchar(255)  nullable
 *   city           varchar(100)
 *   state          varchar(100)
 *   country        varchar(100)  default 'India'
 *   pincode        varchar(10)
 *   is_default     tinyint(1)    default 0
 *   created_at, updated_at
 *
 * There's no DB-level constraint enforcing "only one default address per
 * user" (no unique index on (user_id, is_default)), so that rule is
 * enforced here in application code — the same pattern already used for
 * product_images.is_primary.
 *
 * Every function that takes an $addressId also takes/checks $userId so one
 * customer can never read, edit, delete, or default another customer's
 * address by guessing an id.
 * =============================================================================
 */

/**
 * Get every address belonging to a user, default address first.
 *
 * @param mysqli $conn
 * @param int $userId
 * @return array<int, array>
 * @throws Exception
 */
function getUserAddresses($conn, $userId)
{
    $sql = "SELECT id, user_id, full_name, phone, address_line1, address_line2,
                   city, state, country, pincode, is_default, created_at, updated_at
            FROM addresses
            WHERE user_id = ?
            ORDER BY is_default DESC, id DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }

    $userId = (int) $userId;
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get a single address, scoped to the owning user so one customer can never
 * fetch another customer's address by id.
 *
 * @param mysqli $conn
 * @param int $addressId
 * @param int $userId
 * @return array|null
 * @throws Exception
 */
function getAddressById($conn, $addressId, $userId)
{
    $sql = "SELECT id, user_id, full_name, phone, address_line1, address_line2,
                   city, state, country, pincode, is_default, created_at, updated_at
            FROM addresses
            WHERE id = ? AND user_id = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }

    $addressId = (int) $addressId;
    $userId = (int) $userId;
    $stmt->bind_param('ii', $addressId, $userId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc() ?: null;
}

/**
 * Validate the shared address fields. Returns a trimmed/normalized copy of
 * $data, or throws listing every problem found (not just the first) so a
 * form can show all field errors at once.
 *
 * @param array $data
 * @return array
 * @throws InvalidArgumentException
 */
function validateAddressData(array $data)
{
    $errors = [];

    $clean = [
        'full_name' => trim($data['full_name'] ?? ''),
        'phone' => trim($data['phone'] ?? ''),
        'address_line1' => trim($data['address_line1'] ?? ''),
        'address_line2' => trim($data['address_line2'] ?? ''),
        'city' => trim($data['city'] ?? ''),
        'state' => trim($data['state'] ?? ''),
        'country' => trim($data['country'] ?? '') !== '' ? trim($data['country']) : 'India',
        'pincode' => trim($data['pincode'] ?? ''),
    ];

    if ($clean['full_name'] === '') {
        $errors[] = 'Full name is required.';
    }
    if ($clean['phone'] === '') {
        $errors[] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9+\-\s()]{6,20}$/', $clean['phone'])) {
        $errors[] = 'Enter a valid phone number.';
    }
    if ($clean['address_line1'] === '') {
        $errors[] = 'Address line 1 is required.';
    }
    if ($clean['city'] === '') {
        $errors[] = 'City is required.';
    }
    if ($clean['state'] === '') {
        $errors[] = 'State is required.';
    }
    if ($clean['pincode'] === '') {
        $errors[] = 'Pincode is required.';
    } elseif (!preg_match('/^[0-9A-Za-z\-\s]{3,10}$/', $clean['pincode'])) {
        $errors[] = 'Enter a valid pincode.';
    }

    if (!empty($errors)) {
        throw new InvalidArgumentException(implode(' ', $errors));
    }

    // Normalize the optional line back to null rather than an empty string.
    if ($clean['address_line2'] === '') {
        $clean['address_line2'] = null;
    }

    return $clean;
}

/**
 * Create a new address for a user.
 *
 * If this is the user's first address, or $isDefault is true, it's made
 * (or kept as) the default address and any existing default is cleared.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param array $data ['full_name','phone','address_line1','address_line2','city','state','country','pincode']
 * @param bool $isDefault
 * @return int New address id.
 * @throws InvalidArgumentException
 * @throws Exception
 */
function createAddress($conn, $userId, array $data, $isDefault = false)
{
    $userId = (int) $userId;
    $clean = validateAddressData($data);

    $conn->begin_transaction();

    try {
        $countStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM addresses WHERE user_id = ?");
        if (!$countStmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }
        $countStmt->bind_param('i', $userId);
        $countStmt->execute();
        $existingCount = (int) $countStmt->get_result()->fetch_assoc()['cnt'];

        // A user's very first address is always their default, regardless
        // of what was passed in — there should never be zero default
        // addresses once at least one address exists.
        if ($existingCount === 0) {
            $isDefault = true;
        }

        if ($isDefault) {
            $unsetStmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
            if (!$unsetStmt) {
                throw new Exception('Error preparing statement: ' . mysqli_error($conn));
            }
            $unsetStmt->bind_param('i', $userId);
            $unsetStmt->execute();
        }

        $isDefaultInt = $isDefault ? 1 : 0;

        $insertStmt = $conn->prepare(
            "INSERT INTO addresses
                (user_id, full_name, phone, address_line1, address_line2, city, state, country, pincode, is_default)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$insertStmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }

        $insertStmt->bind_param(
            'issssssssi',
            $userId,
            $clean['full_name'],
            $clean['phone'],
            $clean['address_line1'],
            $clean['address_line2'],
            $clean['city'],
            $clean['state'],
            $clean['country'],
            $clean['pincode'],
            $isDefaultInt
        );
        $insertStmt->execute();

        $newId = (int) $conn->insert_id;

        $conn->commit();

        return $newId;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Update an existing address. Scoped to $userId so a customer can never
 * update another customer's address.
 *
 * @param mysqli $conn
 * @param int $addressId
 * @param int $userId
 * @param array $data Same shape as createAddress().
 * @param bool $isDefault
 * @return bool
 * @throws InvalidArgumentException If the address doesn't belong to this user.
 * @throws Exception
 */
function updateAddress($conn, $addressId, $userId, array $data, $isDefault = false)
{
    $addressId = (int) $addressId;
    $userId = (int) $userId;

    $existing = getAddressById($conn, $addressId, $userId);
    if ($existing === null) {
        throw new InvalidArgumentException('Address not found.');
    }

    $clean = validateAddressData($data);

    // Never let the user's only address become non-default — there must
    // always be exactly one default once any address exists.
    if ($existing['is_default'] && !$isDefault) {
        $isDefault = true;
    }

    $conn->begin_transaction();

    try {
        if ($isDefault) {
            $unsetStmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
            if (!$unsetStmt) {
                throw new Exception('Error preparing statement: ' . mysqli_error($conn));
            }
            $unsetStmt->bind_param('i', $userId);
            $unsetStmt->execute();
        }

        $isDefaultInt = $isDefault ? 1 : 0;

        $updateStmt = $conn->prepare(
            "UPDATE addresses
             SET full_name = ?, phone = ?, address_line1 = ?, address_line2 = ?,
                 city = ?, state = ?, country = ?, pincode = ?, is_default = ?
             WHERE id = ? AND user_id = ?"
        );
        if (!$updateStmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }

        $updateStmt->bind_param(
            'ssssssssiii',
            $clean['full_name'],
            $clean['phone'],
            $clean['address_line1'],
            $clean['address_line2'],
            $clean['city'],
            $clean['state'],
            $clean['country'],
            $clean['pincode'],
            $isDefaultInt,
            $addressId,
            $userId
        );
        $updateStmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    return true;
}

/**
 * Delete an address. Scoped to $userId. If the deleted address was the
 * default, the next-most-recent remaining address (if any) is
 * automatically promoted to default so a user with addresses is never left
 * without one.
 *
 * @param mysqli $conn
 * @param int $addressId
 * @param int $userId
 * @return bool
 * @throws InvalidArgumentException If the address doesn't belong to this user.
 * @throws Exception
 */
function deleteAddress($conn, $addressId, $userId)
{
    $addressId = (int) $addressId;
    $userId = (int) $userId;

    $address = getAddressById($conn, $addressId, $userId);
    if ($address === null) {
        throw new InvalidArgumentException('Address not found.');
    }

    $conn->begin_transaction();

    try {
        $deleteStmt = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
        if (!$deleteStmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }
        $deleteStmt->bind_param('ii', $addressId, $userId);
        $deleteStmt->execute();

        if ((int) $address['is_default'] === 1) {
            $nextStmt = $conn->prepare(
                "SELECT id FROM addresses WHERE user_id = ? ORDER BY id DESC LIMIT 1"
            );
            if (!$nextStmt) {
                throw new Exception('Error preparing statement: ' . mysqli_error($conn));
            }
            $nextStmt->bind_param('i', $userId);
            $nextStmt->execute();
            $next = $nextStmt->get_result()->fetch_assoc();

            if ($next) {
                $promoteStmt = $conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ?");
                if (!$promoteStmt) {
                    throw new Exception('Error preparing statement: ' . mysqli_error($conn));
                }
                $promoteStmt->bind_param('i', $next['id']);
                $promoteStmt->execute();
            }
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    return true;
}

/**
 * Mark one address as the user's default, unsetting any other default they have.
 *
 * @param mysqli $conn
 * @param int $addressId
 * @param int $userId
 * @return bool
 * @throws InvalidArgumentException If the address doesn't belong to this user.
 * @throws Exception
 */
function setDefaultAddress($conn, $addressId, $userId)
{
    $addressId = (int) $addressId;
    $userId = (int) $userId;

    $address = getAddressById($conn, $addressId, $userId);
    if ($address === null) {
        throw new InvalidArgumentException('Address not found.');
    }

    $conn->begin_transaction();

    try {
        $unsetStmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
        if (!$unsetStmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }
        $unsetStmt->bind_param('i', $userId);
        $unsetStmt->execute();

        $setStmt = $conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
        if (!$setStmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }
        $setStmt->bind_param('ii', $addressId, $userId);
        $setStmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    return true;
}