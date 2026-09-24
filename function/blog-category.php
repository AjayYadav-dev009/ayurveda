<?php require_once __DIR__ . '/../config/database.php'; ?>
<?php require_once __DIR__ . '/helper.php'; ?>
<?php

if (!defined('BLOG_CATEGORY_STATUSES')) {
    define('BLOG_CATEGORY_STATUSES', ['Active', 'Inactive']);
}

if (!function_exists('blogCategorySlugExists')) {
    /**
     * Check whether a slug is already used by another blog category.
     *
     * @param mysqli $conn
     * @param string $slug
     * @param int|null $excludeId  Category id to exclude (used when editing).
     * @return bool
     * @throws Exception
     */
    function blogCategorySlugExists($conn, $slug, $excludeId = null)
    {
        if ($excludeId !== null) {
            $sql = "SELECT id FROM blog_categories WHERE slug = ? AND id != ? LIMIT 1";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, 'si', $slug, $excludeId);
        } else {
            $sql = "SELECT id FROM blog_categories WHERE slug = ? LIMIT 1";
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
}

if (!function_exists('addBlogCategory')) {
    /**
     * Insert a new blog category.
     *
     * @param mysqli $conn
     * @param string $name
     * @param string $status   Must be one of BLOG_CATEGORY_STATUSES.
     * @param int $sort_order
     * @return int Newly created category id.
     * @throws Exception
     * @throws InvalidArgumentException
     */
    function addBlogCategory($conn, $name, $status, $sort_order = 0)
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException('Blog category name is required.');
        }

        $slug = createSlug($name);

        if (!in_array($status, BLOG_CATEGORY_STATUSES, true)) {
            throw new InvalidArgumentException(
                'Invalid status. Allowed values: ' . implode(', ', BLOG_CATEGORY_STATUSES)
            );
        }

        if (blogCategorySlugExists($conn, $slug)) {
            throw new InvalidArgumentException('A blog category with this slug already exists.');
        }

        $sort_order = (int) $sort_order;

        $sql = "INSERT INTO blog_categories (name, slug, status, sort_order, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'sssi', $name, $slug, $status, $sort_order);

        if (!mysqli_stmt_execute($stmt)) {
            if (mysqli_errno($conn) === 1062) {
                throw new InvalidArgumentException('A blog category with this slug already exists.');
            }
            throw new Exception('Error adding blog category: ' . mysqli_error($conn));
        }

        $result = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return $result;
    }
}

if (!function_exists('updateBlogCategory')) {
    /**
     * Update an existing blog category.
     *
     * @param mysqli $conn
     * @param int $id
     * @param string $name
     * @param string $status   Must be one of BLOG_CATEGORY_STATUSES.
     * @param int $sort_order
     * @return bool
     * @throws Exception
     * @throws InvalidArgumentException
     */
    function updateBlogCategory($conn, $id, $name, $status, $sort_order = 0)
    {
        $id = (int) $id;
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException('Blog category name is required.');
        }

        $slug = createSlug($name);

        if (!in_array($status, BLOG_CATEGORY_STATUSES, true)) {
            throw new InvalidArgumentException(
                'Invalid status. Allowed values: ' . implode(', ', BLOG_CATEGORY_STATUSES)
            );
        }

        if (blogCategorySlugExists($conn, $slug, $id)) {
            throw new InvalidArgumentException('A blog category with this slug already exists.');
        }

        $sort_order = (int) $sort_order;

        $sql = "UPDATE blog_categories SET name = ?, slug = ?, status = ?, sort_order = ?, updated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'sssii', $name, $slug, $status, $sort_order, $id);

        if (!mysqli_stmt_execute($stmt)) {
            if (mysqli_errno($conn) === 1062) {
                throw new InvalidArgumentException('A blog category with this slug already exists.');
            }
            throw new Exception('Error updating blog category: ' . mysqli_error($conn));
        }

        return true;
    }
}

if (!function_exists('deleteBlogCategory')) {
    /**
     * Delete a blog category. blog_posts.category_id has ON DELETE SET NULL,
     * so any posts using this category are simply uncategorized — nothing
     * else needs to happen here.
     *
     * @param mysqli $conn
     * @param int $id
     * @return bool
     * @throws Exception
     * @throws InvalidArgumentException
     */
    function deleteBlogCategory($conn, $id)
    {
        $id = (int) $id;

        $existing = mysqli_fetch_assoc(getBlogCategoryById($conn, $id));
        if (!$existing) {
            throw new InvalidArgumentException('Blog category not found.');
        }

        $sql = "DELETE FROM blog_categories WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Error deleting blog category: ' . mysqli_error($conn));
        }

        return true;
    }
}

if (!function_exists('countPostsInBlogCategory')) {
    /**
     * How many blog posts currently reference this category — shown on the
     * delete-confirmation page so the admin knows they'll be uncategorized.
     *
     * @param mysqli $conn
     * @param int $id
     * @return int
     * @throws Exception
     */
    function countPostsInBlogCategory($conn, $id)
    {
        $id = (int) $id;
        $sql = "SELECT COUNT(*) AS total FROM blog_posts WHERE category_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error counting posts in category: " . mysqli_error($conn));
        }
        $result = mysqli_stmt_get_result($stmt);
        return (int) mysqli_fetch_assoc($result)['total'];
    }
}

if (!function_exists('getBlogCategories')) {
    /**
     * Fetch all blog categories.
     *
     * @param mysqli $conn
     * @return mysqli_result
     * @throws Exception
     */
    function getBlogCategories($conn)
    {
        $sql = "SELECT * FROM blog_categories ORDER BY sort_order ASC, id ASC";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            throw new Exception("Error fetching blog categories: " . mysqli_error($conn));
        }

        return $result;
    }
}

if (!function_exists('getBlogCategoryById')) {
    /**
     * Fetch a single blog category by id.
     *
     * @param mysqli $conn
     * @param int $id
     * @return mysqli_result
     * @throws Exception
     */
    function getBlogCategoryById($conn, $id)
    {
        $sql = "SELECT * FROM blog_categories WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching blog category by ID: " . mysqli_error($conn));
        }

        $result = mysqli_stmt_get_result($stmt);
        if ($result === false) {
            throw new Exception("Error fetching blog category by ID: " . mysqli_error($conn));
        }

        return $result;
    }
}

if (!function_exists('getBlogCategoriesWithPostCounts')) {
    /**
     * Active blog categories with a count of Active, already-published posts
     * in each — used for the storefront blog sidebar ("Categories" widget).
     * Categories with zero matching posts are still included (counts as 0)
     * so the admin can see an empty category exists; the storefront page
     * itself decides whether to hide zero-count rows.
     *
     * @param mysqli $conn
     * @return array<int, array{id:int, name:string, slug:string, post_count:int}>
     * @throws Exception
     */
    function getBlogCategoriesWithPostCounts($conn)
    {
        $sql = "SELECT bc.id, bc.name, bc.slug,
                       COUNT(bp.id) AS post_count
                FROM blog_categories bc
                LEFT JOIN blog_posts bp
                    ON bp.category_id = bc.id
                    AND bp.status = 'Active'
                    AND bp.published_at IS NOT NULL
                    AND bp.published_at <= NOW()
                WHERE bc.status = 'Active'
                GROUP BY bc.id, bc.name, bc.slug
                ORDER BY bc.sort_order ASC, bc.name ASC";

        $result = mysqli_query($conn, $sql);
        if (!$result) {
            throw new Exception("Error fetching blog categories with post counts: " . mysqli_error($conn));
        }

        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['post_count'] = (int) $row['post_count'];
            $categories[] = $row;
        }

        return $categories;
    }
}
