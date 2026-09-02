<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php require_once __DIR__ . '/index.php'; ?>

<?php

/** Slug used to find/create the fallback category for orphaned products. */
const UNCATEGORIZED_SLUG = 'uncategorised';
const UNCATEGORIZED_NAME = 'Uncategorised';

/**
 * Thrown when a delete is blocked pending user confirmation. Carries a
 * full breakdown of what the delete will do, so the caller can show it
 * to the user before they confirm.
 */
class CategoryDeletionImpactException extends InvalidArgumentException
{
    /** @var array<int,array{id:int,name:string}> Subcategories that will be deleted (excludes the category itself). */
    public array $subcategories;

    /** @var array<int,array{id:int,title:string}> Products that will be moved to "Uncategorised" (no other category). */
    public array $productsToUncategorize;

    /** @var array<int,array{id:int,title:string}> Products that will just be unlinked from this category/subtree but kept. */
    public array $productsToUnlink;

    public function __construct(array $subcategories, array $productsToUncategorize, array $productsToUnlink, string $message)
    {
        parent::__construct($message);
        $this->subcategories = $subcategories;
        $this->productsToUncategorize = $productsToUncategorize;
        $this->productsToUnlink = $productsToUnlink;
    }
}

/**
 * Find the "Uncategorised" category, creating it if it doesn't exist yet.
 * Used as the fallback home for products that would otherwise be left
 * with zero categories after a category (sub)tree is deleted.
 *
 * @param mysqli $conn
 * @return int Category id.
 * @throws Exception
 */
function getOrCreateUncategorizedCategory($conn)
{
    $stmt = mysqli_prepare($conn, "SELECT id FROM categories WHERE slug = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    $slug = UNCATEGORIZED_SLUG;
    mysqli_stmt_bind_param($stmt, 's', $slug);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error looking up uncategorised category: " . mysqli_error($conn));
    }
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        return (int) $row['id'];
    }

    $name = UNCATEGORIZED_NAME;
    $sql = "INSERT INTO categories (parent_id, name, slug, description, image, status, sort_order, created_at, updated_at)
            VALUES (NULL, ?, ?, 'Automatically created holding category for products left without a category.', NULL, 'Active', 0, NOW(), NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error creating uncategorised category: " . mysqli_error($conn));
    }

    return mysqli_insert_id($conn);
}

/**
 * Delete a category and its entire subtree (all descendant categories,
 * at any depth).
 *
 * Two-step confirmation flow:
 *
 *   1. Call with $force = false (default). If deleting would affect
 *      anything beyond the category itself (it has subcategories,
 *      and/or products are linked to it or its subcategories),
 *      nothing is deleted. A CategoryDeletionImpactException is thrown
 *      with a full breakdown so the caller can show the user exactly
 *      what will happen and ask for confirmation.
 *
 *   2. If the user confirms, call again with $force = true:
 *        - The category and every descendant category are deleted.
 *        - For every product linked to any of those categories:
 *            - if it has NO other category outside this subtree, it
 *              is re-linked to the "Uncategorised" category (created
 *              automatically if it doesn't exist yet) instead of
 *              being deleted, so no product data is ever lost.
 *            - if it still has at least one category outside this
 *              subtree, it is simply unlinked from the deleted
 *              categories and otherwise left untouched.
 *
 *   A category with no subcategories and no linked products is
 *   deleted immediately regardless of $force.
 *
 * The whole operation runs in a single transaction: if any step
 * fails, everything is rolled back.
 *
 * @param mysqli $conn
 * @param int $id
 * @param bool $force  Set true only after the user has explicitly
 *                      confirmed the deletion after seeing the impact.
 * @return array{deletedCategoryIds: int[], uncategorizedProductIds: int[], unlinkedProductIds: int[]}
 * @throws Exception
 * @throws InvalidArgumentException
 * @throws CategoryDeletionImpactException
 */
function deleteCategory($conn, $id, $force = false)
{
    $id = (int) $id;

    $existing = getCategoryById($conn, $id);
    $categoryRow = mysqli_fetch_assoc($existing);
    if (!$categoryRow) {
        throw new InvalidArgumentException('Category not found.');
    }

    if (strcasecmp($categoryRow['slug'], UNCATEGORIZED_SLUG) === 0) {
        throw new InvalidArgumentException('The "Uncategorised" category cannot be deleted; it is used as the fallback for orphaned products.');
    }

    $subtreeIds = getCategorySubtreeIds($conn, $id);
    $subcategoryIds = array_values(array_diff($subtreeIds, [$id]));

    $subcategories = [];
    if (!empty($subcategoryIds)) {
        $placeholders = implode(',', array_fill(0, count($subcategoryIds), '?'));
        $types = str_repeat('i', count($subcategoryIds));
        $sql = "SELECT id, name FROM categories WHERE id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        $bindArgs = [$types];
        foreach ($subcategoryIds as $key => $value) {
            $bindArgs[] = &$subcategoryIds[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching subcategories: " . mysqli_error($conn));
        }
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $subcategories[] = ['id' => (int) $row['id'], 'name' => $row['name']];
        }
    }

    $productImpact = getProductImpactForSubtree($conn, $subtreeIds);
    $productsToUncategorize = $productImpact['toDelete'];
    $productsToUnlink = $productImpact['toUnlink'];

    $hasImpact = !empty($subcategories) || !empty($productsToUncategorize) || !empty($productsToUnlink);

    if ($hasImpact && !$force) {
        $parts = [];
        if (!empty($subcategories)) {
            $parts[] = count($subcategories) . ' subcategor' . (count($subcategories) === 1 ? 'y' : 'ies') .
                ' (' . implode(', ', array_column($subcategories, 'name')) . ')';
        }
        if (!empty($productsToUncategorize)) {
            $parts[] = count($productsToUncategorize) . ' product' . (count($productsToUncategorize) === 1 ? '' : 's') .
                ' that will be moved to "' . UNCATEGORIZED_NAME . '", since ' . (count($productsToUncategorize) === 1 ? 'it has' : 'they have') .
                ' no other category (' . implode(', ', array_column($productsToUncategorize, 'title')) . ')';
        }
        if (!empty($productsToUnlink)) {
            $parts[] = count($productsToUnlink) . ' product' . (count($productsToUnlink) === 1 ? '' : 's') .
                ' that will just be unlinked from this category but kept, since ' .
                (count($productsToUnlink) === 1 ? 'it still has' : 'they still have') . ' another category';
        }

        throw new CategoryDeletionImpactException(
            $subcategories,
            $productsToUncategorize,
            $productsToUnlink,
            'Deleting "' . $categoryRow['name'] . '" will also affect: ' . implode('; ', $parts) . '. Do you still want to delete it?'
        );
    }

    // Either nothing but the category itself is affected, or the user has
    // already confirmed (force = true). Run everything atomically.
    mysqli_begin_transaction($conn);

    try {
        // Resolve (or create) the fallback category *before* deleting
        // anything, so if this fails we haven't touched real data yet.
        $uncategorizedId = null;
        if (!empty($productsToUncategorize)) {
            $uncategorizedId = getOrCreateUncategorizedCategory($conn);
        }

        // Deleting the categories cascades to product_categories rows
        // automatically (fk_pc_category ON DELETE CASCADE).
        $placeholders = implode(',', array_fill(0, count($subtreeIds), '?'));
        $types = str_repeat('i', count($subtreeIds));
        $sql = "DELETE FROM categories WHERE id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        $bindArgs = [$types];
        $idsForBind = $subtreeIds;
        foreach ($idsForBind as $key => $value) {
            $bindArgs[] = &$idsForBind[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Error deleting categories: ' . mysqli_error($conn));
        }

        // Re-link orphaned products to "Uncategorised" instead of
        // deleting them. Their old product_categories rows for the
        // deleted subtree are already gone via the cascade above.
        if (!empty($productsToUncategorize) && $uncategorizedId !== null) {
            $sql = "INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, 1)";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            foreach ($productsToUncategorize as $product) {
                $productId = (int) $product['id'];
                mysqli_stmt_bind_param($stmt, 'ii', $productId, $uncategorizedId);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception('Error moving product to Uncategorised: ' . mysqli_error($conn));
                }
            }
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }

    return [
        'deletedCategoryIds' => $subtreeIds,
        'uncategorizedProductIds' => array_column($productsToUncategorize, 'id'),
        'unlinkedProductIds' => array_column($productsToUnlink, 'id'),
    ];
}
