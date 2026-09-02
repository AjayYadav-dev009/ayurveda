<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php require_once __DIR__ . '/index.php'; ?>

<?php

/**
 * Thrown when a delete is blocked pending user confirmation. Carries a
 * full breakdown of what the delete will do, so the caller can show it
 * to the user before they confirm.
 */
class CategoryDeletionImpactException extends InvalidArgumentException
{
    /** @var array<int,array{id:int,name:string}> Subcategories that will be deleted (excludes the category itself). */
    public array $subcategories;

    /** @var array<int,array{id:int,title:string}> Products that will be permanently deleted (no category left). */
    public array $productsToDelete;

    /** @var array<int,array{id:int,title:string}> Products that will just be unlinked from this category/subtree but kept. */
    public array $productsToUnlink;

    public function __construct(array $subcategories, array $productsToDelete, array $productsToUnlink, string $message)
    {
        parent::__construct($message);
        $this->subcategories = $subcategories;
        $this->productsToDelete = $productsToDelete;
        $this->productsToUnlink = $productsToUnlink;
    }
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
 *            - if it has NO other category outside this subtree,
 *              the product itself is deleted (cascades to its
 *              details/images/variants/reviews/wishlist/cart rows,
 *              and nulls out any historical order_items reference).
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
 * @return array{deletedCategoryIds: int[], deletedProductIds: int[], unlinkedProductIds: int[]}
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
    $productsToDelete = $productImpact['toDelete'];
    $productsToUnlink = $productImpact['toUnlink'];

    $hasImpact = !empty($subcategories) || !empty($productsToDelete) || !empty($productsToUnlink);

    if ($hasImpact && !$force) {
        $parts = [];
        if (!empty($subcategories)) {
            $parts[] = count($subcategories) . ' subcategor' . (count($subcategories) === 1 ? 'y' : 'ies') .
                ' (' . implode(', ', array_column($subcategories, 'name')) . ')';
        }
        if (!empty($productsToDelete)) {
            $parts[] = count($productsToDelete) . ' product' . (count($productsToDelete) === 1 ? '' : 's') .
                ' that will be permanently deleted, since ' . (count($productsToDelete) === 1 ? 'it has' : 'they have') .
                ' no other category (' . implode(', ', array_column($productsToDelete, 'title')) . ')';
        }
        if (!empty($productsToUnlink)) {
            $parts[] = count($productsToUnlink) . ' product' . (count($productsToUnlink) === 1 ? '' : 's') .
                ' that will just be unlinked from this category but kept, since ' .
                (count($productsToUnlink) === 1 ? 'it still has' : 'they still have') . ' another category';
        }

        throw new CategoryDeletionImpactException(
            $subcategories,
            $productsToDelete,
            $productsToUnlink,
            'Deleting "' . $categoryRow['name'] . '" will also affect: ' . implode('; ', $parts) . '. Do you still want to delete it?'
        );
    }

    // Either nothing but the category itself is affected, or the user has
    // already confirmed (force = true). Run everything atomically.
    mysqli_begin_transaction($conn);

    try {
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

        // Delete products that no longer belong to any category. This
        // cascades to product_details/product_images/product_variants/
        // reviews/wishlist/cart, and nulls out order_items.product_id.
        $deletedProductIds = array_column($productsToDelete, 'id');
        if (!empty($deletedProductIds)) {
            $placeholders = implode(',', array_fill(0, count($deletedProductIds), '?'));
            $types = str_repeat('i', count($deletedProductIds));
            $sql = "DELETE FROM products WHERE id IN ($placeholders)";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            $bindArgs = [$types];
            foreach ($deletedProductIds as $key => $value) {
                $bindArgs[] = &$deletedProductIds[$key];
            }
            call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Error deleting orphaned products: ' . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }

    return [
        'deletedCategoryIds' => $subtreeIds,
        'deletedProductIds' => array_column($productsToDelete, 'id'),
        'unlinkedProductIds' => array_column($productsToUnlink, 'id'),
    ];
}