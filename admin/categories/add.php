<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php require_once __DIR__ . '/index.php'; ?>

<?php

/**
 * Insert a new category.
 *
 * @param mysqli $conn
 * @param int|null $parent_id
 * @param string $name
 * @param string $slug
 * @param string|null $description
 * @param string|null $image
 * @param string $status   Must be one of CATEGORY_STATUSES.
 * @param int $sort_order
 * @return int Newly created category id.
 * @throws Exception
 * @throws InvalidArgumentException
 */
function addCategory($conn, $parent_id, $name, $slug, $description, $image, $status, $sort_order)
{
    $name = trim($name);
    $slug = trim($slug);

    if ($name === '') {
        throw new InvalidArgumentException('Category name is required.');
    }

    if ($slug === '') {
        throw new InvalidArgumentException('Category slug is required.');
    }

    if (!in_array($status, CATEGORY_STATUSES, true)) {
        throw new InvalidArgumentException(
            'Invalid status. Allowed values: ' . implode(', ', CATEGORY_STATUSES)
        );
    }

    // Normalize optional fields.
    $parent_id = ($parent_id === '' || $parent_id === null) ? null : (int) $parent_id;
    $sort_order = (int) $sort_order;

    if ($parent_id !== null) {
        // Make sure the chosen parent actually exists.
        $parentResult = getCategoryById($conn, $parent_id);
        if (mysqli_num_rows($parentResult) === 0) {
            throw new InvalidArgumentException('Selected parent category does not exist.');
        }
    }

    if (slugExists($conn, $slug)) {
        throw new InvalidArgumentException('A category with this slug already exists.');
    }

    $sql = "INSERT INTO categories
            (parent_id, name, slug, description, image, status, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'isssssi', $parent_id, $name, $slug, $description, $image, $status, $sort_order);

    if (!mysqli_stmt_execute($stmt)) {
        // 1062 = duplicate entry (race condition guard, in addition to the
        // slugExists() pre-check above).
        if (mysqli_errno($conn) === 1062) {
            throw new InvalidArgumentException('A category with this slug already exists.');
        }
        throw new Exception('Error adding category: ' . mysqli_error($conn));
    }

    return mysqli_insert_id($conn);
}