<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php require_once __DIR__ . '/index.php'; ?>

<?php

/**
 * Walk up the parent chain starting at $startId and check whether
 * $targetId appears anywhere in it. Used to stop a category being
 * re-parented under one of its own descendants (which would create
 * a cycle in the tree).
 *
 * @param mysqli $conn
 * @param int $startId   The proposed new parent_id.
 * @param int $targetId  The category being edited.
 * @return bool True if $targetId is an ancestor of $startId (i.e. a cycle).
 * @throws Exception
 */
function wouldCreateCircularReference($conn, $startId, $targetId)
{
    $currentId = $startId;
    $visited = [];

    while ($currentId !== null) {
        if ($currentId === $targetId) {
            return true;
        }

        // Guard against any pre-existing bad data looping forever.
        if (isset($visited[$currentId])) {
            break;
        }
        $visited[$currentId] = true;

        $result = getCategoryById($conn, $currentId);
        $row = mysqli_fetch_assoc($result);
        if (!$row) {
            break;
        }

        $currentId = $row['parent_id'] !== null ? (int) $row['parent_id'] : null;
    }

    return false;
}

/**
 * Update an existing category.
 *
 * @param mysqli $conn
 * @param int $id
 * @param int|null $parent_id
 * @param string $name
 * @param string $slug
 * @param string|null $description
 * @param string|null $image
 * @param string $status   Must be one of CATEGORY_STATUSES.
 * @param int $sort_order
 * @return bool
 * @throws Exception
 * @throws InvalidArgumentException
 */
function updateCategory($conn, $id, $parent_id, $name, $slug, $description, $image, $status, $sort_order)
{
    $id = (int) $id;
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

    $parent_id = ($parent_id === '' || $parent_id === null) ? null : (int) $parent_id;
    $sort_order = (int) $sort_order;

    if ($parent_id !== null) {
        if ($parent_id === $id) {
            throw new InvalidArgumentException('A category cannot be its own parent.');
        }

        $parentResult = getCategoryById($conn, $parent_id);
        if (mysqli_num_rows($parentResult) === 0) {
            throw new InvalidArgumentException('Selected parent category does not exist.');
        }

        if (wouldCreateCircularReference($conn, $parent_id, $id)) {
            throw new InvalidArgumentException('Cannot set parent: this would create a circular category tree.');
        }
    }

    if (slugExists($conn, $slug, $id)) {
        throw new InvalidArgumentException('A category with this slug already exists.');
    }

    $sql = "UPDATE categories
            SET parent_id = ?, name = ?, slug = ?, description = ?, image = ?, status = ?, sort_order = ?, updated_at = NOW()
            WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'isssssii', $parent_id, $name, $slug, $description, $image, $status, $sort_order, $id);

    if (!mysqli_stmt_execute($stmt)) {
        if (mysqli_errno($conn) === 1062) {
            throw new InvalidArgumentException('A category with this slug already exists.');
        }
        throw new Exception('Error updating category: ' . mysqli_error($conn));
    }

    return true;
}