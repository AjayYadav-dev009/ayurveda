<?php require_once __DIR__ . '/../../config/database.php'; ?>

<?php

const CATEGORY_STATUSES = ['Active', 'Inactive'];

/**
 * Fetch all categories.
 *
 * @param mysqli $conn
 * @return mysqli_result
 * @throws Exception
 */
function getCategories($conn)
{
    $sql = "SELECT * FROM categories ORDER BY sort_order ASC, id ASC";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception("Error fetching categories: " . mysqli_error($conn));
    }

    return $result;
}

/**
 * Fetch a single category by id.
 *
 * @param mysqli $conn
 * @param int $id
 * @return mysqli_result
 * @throws Exception
 */
function getCategoryById($conn, $id)
{
    $sql = "SELECT * FROM categories WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching category by ID: " . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        throw new Exception("Error fetching category by ID: " . mysqli_error($conn));
    }

    return $result;
}

/**
 * Fetch all direct children of a given category (used for delete guards
 * and building a tree view).
 *
 * @param mysqli $conn
 * @param int $parentId
 * @return mysqli_result
 * @throws Exception
 */
function getChildCategories($conn, $parentId)
{
    $sql = "SELECT id, name FROM categories WHERE parent_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $parentId);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching child categories: " . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        throw new Exception("Error fetching child categories: " . mysqli_error($conn));
    }

    return $result;
}

/**
 * Check whether a slug is already used by another category.
 *
 * @param mysqli $conn
 * @param string $slug
 * @param int|null $excludeId  Category id to exclude (used when editing).
 * @return bool
 * @throws Exception
 */
function slugExists($conn, $slug, $excludeId = null)
{
    if ($excludeId !== null) {
        $sql = "SELECT id FROM categories WHERE slug = ? AND id != ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'si', $slug, $excludeId);
    } else {
        $sql = "SELECT id FROM categories WHERE slug = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 's', $slug);
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error checking slug: " . mysqli_error($conn));
    }

    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

/**
 * Get every descendant category id of $rootId, at any depth, including
 * $rootId itself. Used when cascade-deleting a whole category subtree.
 *
 * @param mysqli $conn
 * @param int $rootId
 * @return int[]
 * @throws Exception
 */
function getCategorySubtreeIds($conn, $rootId)
{
    $ids = [(int) $rootId];
    $levelIds = [(int) $rootId];

    while (!empty($levelIds)) {
        $placeholders = implode(',', array_fill(0, count($levelIds), '?'));
        $types = str_repeat('i', count($levelIds));

        $sql = "SELECT id FROM categories WHERE parent_id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        $bindArgs = [];
        $bindArgs[] = $types;
        foreach ($levelIds as $key => $value) {
            $bindArgs[] = &$levelIds[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching category subtree: " . mysqli_error($conn));
        }

        $result = mysqli_stmt_get_result($stmt);
        $nextLevel = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $nextLevel[] = (int) $row['id'];
        }

        $ids = array_merge($ids, $nextLevel);
        $levelIds = $nextLevel;
    }

    return $ids;
}

/**
 * Work out exactly what deleting a category subtree will do to products:
 * which ones will be fully deleted (no category left outside the subtree)
 * vs. which ones will simply be unlinked from these categories but survive
 * (because they're still linked to at least one category outside the subtree).
 *
 * @param mysqli $conn
 * @param int[] $subtreeIds
 * @return array{toDelete: array<int,array{id:int,title:string}>, toUnlink: array<int,array{id:int,title:string}>}
 * @throws Exception
 */
function getProductImpactForSubtree($conn, array $subtreeIds)
{
    $placeholders = implode(',', array_fill(0, count($subtreeIds), '?'));
    $types = str_repeat('i', count($subtreeIds));

    // Every distinct product linked to any category in the subtree, plus
    // how many category links that product has OUTSIDE the subtree.
    $sql = "SELECT p.id, p.title,
                   (
                       SELECT COUNT(*) FROM product_categories pc_out
                       WHERE pc_out.product_id = p.id
                         AND pc_out.category_id NOT IN ($placeholders)
                   ) AS other_category_count
            FROM products p
            INNER JOIN (
                SELECT DISTINCT product_id FROM product_categories WHERE category_id IN ($placeholders)
            ) affected ON affected.product_id = p.id";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    // Same subtree ids are needed twice (once per IN clause).
    $allIds = array_merge($subtreeIds, $subtreeIds);
    $allTypes = $types . $types;

    $bindArgs = [$allTypes];
    foreach ($allIds as $key => $value) {
        $bindArgs[] = &$allIds[$key];
    }
    call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error checking product impact: " . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt);

    $toDelete = [];
    $toUnlink = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $entry = ['id' => (int) $row['id'], 'title' => $row['title']];
        if ((int) $row['other_category_count'] === 0) {
            $toDelete[] = $entry;
        } else {
            $toUnlink[] = $entry;
        }
    }

    return ['toDelete' => $toDelete, 'toUnlink' => $toUnlink];
}