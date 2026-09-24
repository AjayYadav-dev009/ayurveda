<?php require_once __DIR__ . '/../config/database.php'; ?>
<?php require_once __DIR__ . '/helper.php'; ?>
<?php

// Absolute path on disk where blog images are physically stored.
if (!defined('BLOG_IMAGE_UPLOAD_DIR')) {
    define('BLOG_IMAGE_UPLOAD_DIR', __DIR__ . '/../uploads/blog/');
}

// Public web path used to build <img src="..."> URLs.
// The uploads folder is <project>/uploads/blog/. When the project lives in a
// sub-folder of the web root (e.g. http://localhost/ayurveda/), a hard-coded
// '/uploads/blog/' points at the wrong place and images 404, so work the URL
// out from the folder's real location under DOCUMENT_ROOT.
if (!defined('BLOG_IMAGE_PUBLIC_PATH')) {
    $blogPublicPath = '/uploads/blog/';
    $blogPathDerived = false;

    $blogDocRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $blogProjectRoot = realpath(dirname(__DIR__));
    if ($blogDocRoot !== false && $blogProjectRoot !== false) {
        $blogDocRoot = rtrim(str_replace('\\', '/', $blogDocRoot), '/');
        $blogProjectRoot = rtrim(str_replace('\\', '/', $blogProjectRoot), '/');
        if ($blogDocRoot !== '' && stripos($blogProjectRoot, $blogDocRoot) === 0) {
            $blogPublicPath = substr($blogProjectRoot, strlen($blogDocRoot)) . '/uploads/blog/';
            $blogPathDerived = true;
        }
    }

    define('BLOG_IMAGE_PUBLIC_PATH', $blogPublicPath);
    define('BLOG_IMAGE_PATH_DERIVED', $blogPathDerived);
    unset($blogPublicPath, $blogPathDerived, $blogDocRoot, $blogProjectRoot);
}

if (!defined('BLOG_IMAGE_MAX_BYTES')) {
    define('BLOG_IMAGE_MAX_BYTES', 5 * 1024 * 1024); // 5 MB
}

if (!defined('BLOG_STATUSES')) {
    define('BLOG_STATUSES', ['Draft', 'Active', 'Inactive']);
}

if (!function_exists('uploadBlogImage')) {
    /**
     * Validate and store an uploaded blog image, returning the relative
     * path to save in the `blog_posts.image` column.
     *
     * Same contract as uploadCategoryImage() in function/category.php:
     * $_FILES['image'] is an array (tmp_name, error, size, ...), never
     * pass it straight into addBlogPost()/updateBlogPost(). Always run it
     * through this function first and use the returned *string* (or null).
     *
     * @param array|null $file One entry from $_FILES, e.g. $_FILES['image']
     * @return string|null Relative path to store in the DB, or null if no file was chosen
     * @throws InvalidArgumentException If a file was chosen but is invalid
     * @throws RuntimeException If a valid file can't be moved to disk
     */
    function uploadBlogImage($file)
    {
        if (!is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => 'The image exceeds the server upload_max_filesize limit.',
                UPLOAD_ERR_FORM_SIZE  => 'The image exceeds the form MAX_FILE_SIZE limit.',
                UPLOAD_ERR_PARTIAL    => 'The image was only partially uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary upload folder on the server.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write the image to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the image upload.',
            ];
            throw new InvalidArgumentException($uploadErrors[$file['error']] ?? 'Unknown upload error.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('Invalid upload.');
        }

        if ($file['size'] <= 0) {
            throw new InvalidArgumentException('The uploaded image is empty.');
        }

        if ($file['size'] > BLOG_IMAGE_MAX_BYTES) {
            throw new InvalidArgumentException('The image is larger than ' . (BLOG_IMAGE_MAX_BYTES / 1024 / 1024) . 'MB.');
        }

        $allowedMimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];

        // Detect the real MIME type from file contents; never trust the
        // client-supplied Content-Type header.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!isset($allowedMimeToExt[$mime])) {
            throw new InvalidArgumentException('Unsupported file type. Allowed types: JPG, PNG, WEBP, GIF.');
        }

        if (@getimagesize($file['tmp_name']) === false) {
            throw new InvalidArgumentException('The file does not appear to be a valid image.');
        }

        $extension = $allowedMimeToExt[$mime];

        $base = pathinfo($file['name'], PATHINFO_FILENAME);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $base), '-'));
        if ($slug === '') {
            $slug = 'blog';
        }
        $slug = substr($slug, 0, 60);

        $filename = $slug . '-' . bin2hex(random_bytes(6)) . '.' . $extension;

        if (!is_dir(BLOG_IMAGE_UPLOAD_DIR) && !mkdir(BLOG_IMAGE_UPLOAD_DIR, 0755, true) && !is_dir(BLOG_IMAGE_UPLOAD_DIR)) {
            throw new RuntimeException('Could not create the upload directory.');
        }

        $destination = rtrim(BLOG_IMAGE_UPLOAD_DIR, '/') . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Could not move the uploaded image to its destination.');
        }

        chmod($destination, 0644);

        return $filename;
    }
}

if (!function_exists('getBlogImageUrl')) {
    /**
     * Build the public URL for a stored blog image path.
     *
     * @param ?string $imagePath As stored in blog_posts.image
     * @return ?string
     */
    function getBlogImageUrl($imagePath)
    {
        if ($imagePath === null || $imagePath === '') {
            return null;
        }

        $path = rtrim(BLOG_IMAGE_PUBLIC_PATH, '/') . '/' . ltrim($imagePath, '/');

        // A derived path already includes any sub-folder, so BASE_URL must
        // not be prepended again.
        if (defined('BLOG_IMAGE_PATH_DERIVED') && BLOG_IMAGE_PATH_DERIVED) {
            return $path;
        }

        if (defined('BASE_URL') && BASE_URL !== '') {
            return rtrim(BASE_URL, '/') . $path;
        }

        return $path;
    }
}

if (!function_exists('blogSlugExists')) {
    /**
     * Check whether a slug is already used by another blog post.
     *
     * @param mysqli $conn
     * @param string $slug
     * @param int|null $excludeId  Post id to exclude (used when editing).
     * @return bool
     * @throws Exception
     */
    function blogSlugExists($conn, $slug, $excludeId = null)
    {
        if ($excludeId !== null) {
            $sql = "SELECT id FROM blog_posts WHERE slug = ? AND id != ? LIMIT 1";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, 'si', $slug, $excludeId);
        } else {
            $sql = "SELECT id FROM blog_posts WHERE slug = ? LIMIT 1";
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

if (!function_exists('normalizeBlogPublishedAt')) {
    /**
     * Normalize a raw published_at value coming from a <input type="datetime-local">
     * ("Y-m-d\TH:i") into the "Y-m-d H:i:s" format the DB column expects.
     * Returns null for an empty value.
     *
     * @param string|null $raw
     * @return string|null
     */
    function normalizeBlogPublishedAt($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $raw = str_replace('T', ' ', $raw);
        if (strlen($raw) === 16) { // "Y-m-d H:i"
            $raw .= ':00';
        }

        return $raw;
    }
}

if (!function_exists('addBlogPost')) {
    /**
     * Insert a new blog post.
     *
     * published_at behaviour: if $published_at is left empty and $status is
     * 'Active', it is auto-filled with the current datetime (first-publish
     * convenience). Otherwise the value passed in is used as-is (or left
     * null).
     *
     * @param mysqli $conn
     * @param string $title
     * @param string|null $excerpt
     * @param string|null $content
     * @param string|null $image
     * @param string $status   Must be one of BLOG_STATUSES.
     * @param string|null $published_at  Raw value from the form (datetime-local format), or null.
     * @param string|null $meta_title
     * @param string|null $meta_description
     * @param int|string|null $category_id
     * @param string|null $hero_image Filename from uploadBlogImage() for the top banner, or null.
     * @return int Newly created blog post id.
     * @throws Exception
     * @throws InvalidArgumentException
     */
    function addBlogPost($conn, $title, $excerpt, $content, $image, $status, $published_at, $meta_title, $meta_description, $category_id = null, $hero_image = null)
    {
        $title = trim($title);

        if ($title === '') {
            throw new InvalidArgumentException('Blog title is required.');
        }

        $slug = createSlug($title);

        if (!in_array($status, BLOG_STATUSES, true)) {
            throw new InvalidArgumentException(
                'Invalid status. Allowed values: ' . implode(', ', BLOG_STATUSES)
            );
        }

        if (blogSlugExists($conn, $slug)) {
            throw new InvalidArgumentException('A blog post with this slug already exists.');
        }

        $excerpt = trim((string) $excerpt) !== '' ? trim($excerpt) : null;
        $meta_title = trim((string) $meta_title) !== '' ? trim($meta_title) : $title;
        $meta_description = trim((string) $meta_description) !== '' ? substr(trim($meta_description), 0, 200) : null;

        $published_at = normalizeBlogPublishedAt($published_at);
        if ($published_at === null && $status === 'Active') {
            $published_at = date('Y-m-d H:i:s');
        }

        $category_id = ($category_id === '' || $category_id === null) ? null : (int) $category_id;

        $sql = "INSERT INTO blog_posts
                (title, slug, excerpt, content, image, hero_image, status, published_at, meta_title, meta_description, category_id, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'ssssssssssi', $title, $slug, $excerpt, $content, $image, $hero_image, $status, $published_at, $meta_title, $meta_description, $category_id);

        if (!mysqli_stmt_execute($stmt)) {
            if (mysqli_errno($conn) === 1062) {
                throw new InvalidArgumentException('A blog post with this slug already exists.');
            }
            throw new Exception('Error adding blog post: ' . mysqli_error($conn));
        }

        $result = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return $result;
    }
}

if (!function_exists('updateBlogPost')) {
    /**
     * Update an existing blog post.
     *
     * published_at behaviour: an explicit non-empty value always wins. If
     * left empty, the existing published_at is preserved UNLESS this save
     * is the first time the post becomes 'Active' (existing published_at
     * was null), in which case it is auto-filled with the current datetime.
     *
     * @param mysqli $conn
     * @param int $id
     * @param string $title
     * @param string|null $excerpt
     * @param string|null $content
     * @param string|null $image
     * @param string $status   Must be one of BLOG_STATUSES.
     * @param string|null $published_at  Raw value from the form (datetime-local format), or null.
     * @param string|null $meta_title
     * @param string|null $meta_description
     * @param int|string|null $category_id
     * @param string|null|false $hero_image New hero filename, null to clear it, or false (default) to leave it unchanged.
     * @return bool
     * @throws Exception
     * @throws InvalidArgumentException
     */
    function updateBlogPost($conn, $id, $title, $excerpt, $content, $image, $status, $published_at, $meta_title, $meta_description, $category_id = null, $hero_image = false)
    {
        $id = (int) $id;
        $title = trim($title);

        if ($title === '') {
            throw new InvalidArgumentException('Blog title is required.');
        }

        // Look up the post's current image/published_at before we overwrite
        // them. If a new file was uploaded (edit.php only calls
        // uploadBlogImage() when one was chosen), we need the old filename
        // so it can be removed from disk after the update succeeds.
        $existingResult = getBlogPostById($conn, $id);
        $existingRow = mysqli_fetch_assoc($existingResult);
        if (!$existingRow) {
            throw new InvalidArgumentException('Blog post not found.');
        }
        $oldImage = $existingRow['image'];
        $oldHeroImage = $existingRow['hero_image'] ?? null;
        if ($hero_image === false) {
            $hero_image = $oldHeroImage;
        }

        $slug = createSlug($title);

        if (!in_array($status, BLOG_STATUSES, true)) {
            throw new InvalidArgumentException(
                'Invalid status. Allowed values: ' . implode(', ', BLOG_STATUSES)
            );
        }

        if (blogSlugExists($conn, $slug, $id)) {
            throw new InvalidArgumentException('A blog post with this slug already exists.');
        }

        $excerpt = trim((string) $excerpt) !== '' ? trim($excerpt) : null;
        $meta_title = trim((string) $meta_title) !== '' ? trim($meta_title) : $title;
        $meta_description = trim((string) $meta_description) !== '' ? substr(trim($meta_description), 0, 200) : null;

        $normalizedPublishedAt = normalizeBlogPublishedAt($published_at);
        if ($normalizedPublishedAt !== null) {
            $published_at = $normalizedPublishedAt;
        } elseif ($status === 'Active' && $existingRow['published_at'] === null) {
            $published_at = date('Y-m-d H:i:s');
        } else {
            $published_at = $existingRow['published_at'];
        }

        $category_id = ($category_id === '' || $category_id === null) ? null : (int) $category_id;

        $sql = "UPDATE blog_posts
                SET title = ?, slug = ?, excerpt = ?, content = ?, image = ?, hero_image = ?, status = ?, published_at = ?, meta_title = ?, meta_description = ?, category_id = ?, updated_at = NOW()
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'ssssssssssii', $title, $slug, $excerpt, $content, $image, $hero_image, $status, $published_at, $meta_title, $meta_description, $category_id, $id);

        if (!mysqli_stmt_execute($stmt)) {
            if (mysqli_errno($conn) === 1062) {
                throw new InvalidArgumentException('A blog post with this slug already exists.');
            }
            throw new Exception('Error updating blog post: ' . mysqli_error($conn));
        }

        // Only remove the old file once the DB update has committed, and
        // only when a new image actually replaced it.
        if ($oldImage !== null && $oldImage !== '' && $oldImage !== $image) {
            $oldPath = rtrim(BLOG_IMAGE_UPLOAD_DIR, '/') . '/' . ltrim($oldImage, '/');
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }
        if ($oldHeroImage !== null && $oldHeroImage !== '' && $oldHeroImage !== $hero_image) {
            $oldHeroPath = rtrim(BLOG_IMAGE_UPLOAD_DIR, '/') . '/' . ltrim($oldHeroImage, '/');
            if (is_file($oldHeroPath)) {
                @unlink($oldHeroPath);
            }
        }

        return true;
    }
}

if (!function_exists('deleteBlogPost')) {
    /**
     * Delete a blog post and its image file (if any). Blog posts have no
     * dependent rows in other tables, so this is a plain delete — no
     * impact/confirmation tree like categories.
     *
     * @param mysqli $conn
     * @param int $id
     * @return bool
     * @throws Exception
     * @throws InvalidArgumentException
     */
    function deleteBlogPost($conn, $id)
    {
        $id = (int) $id;

        $existingRow = mysqli_fetch_assoc(getBlogPostById($conn, $id));
        if (!$existingRow) {
            throw new InvalidArgumentException('Blog post not found.');
        }

        $sql = "DELETE FROM blog_posts WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Error deleting blog post: ' . mysqli_error($conn));
        }

        foreach ([$existingRow['image'] ?? null, $existingRow['hero_image'] ?? null] as $file) {
            if ($file !== null && $file !== '') {
                $filePath = rtrim(BLOG_IMAGE_UPLOAD_DIR, '/') . '/' . ltrim($file, '/');
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        return true;
    }
}

if (!function_exists('getBlogPosts')) {
    /**
     * Fetch all blog posts, most recently created first.
     *
     * @param mysqli $conn
     * @return mysqli_result
     * @throws Exception
     */
    function getBlogPosts($conn)
    {
        $sql = "SELECT bp.*, bc.name AS category_name
                FROM blog_posts bp
                LEFT JOIN blog_categories bc ON bc.id = bp.category_id
                ORDER BY bp.created_at DESC, bp.id DESC";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            throw new Exception("Error fetching blog posts: " . mysqli_error($conn));
        }

        return $result;
    }
}

if (!function_exists('estimateBlogReadTime')) {
    /**
     * Estimate reading time in whole minutes (minimum 1) from HTML content,
     * using a 200 words-per-minute average. Tags are stripped before the
     * word count so CKEditor markup doesn't inflate the estimate.
     *
     * @param string|null $content Raw HTML from blog_posts.content
     * @return int
     */
    function estimateBlogReadTime($content)
    {
        $text = trim(strip_tags((string) $content));
        if ($text === '') {
            return 1;
        }

        $wordCount = str_word_count($text);
        $minutes = (int) ceil($wordCount / 200);

        return max(1, $minutes);
    }
}

if (!function_exists('getPublishedBlogPosts')) {
    /**
     * Fetch Active, already-published blog posts for the public storefront,
     * newest first, with optional category and search filtering and
     * pagination.
     *
     * @param mysqli $conn
     * @param int $limit
     * @param int $offset
     * @param string|null $categorySlug  Filter to one blog category, or null for all.
     * @param string|null $search        Matches against title/excerpt/content.
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    function getPublishedBlogPosts($conn, $limit = 10, $offset = 0, $categorySlug = null, $search = null)
    {
        $where = ["bp.status = 'Active'", 'bp.published_at IS NOT NULL', 'bp.published_at <= NOW()'];
        $types = '';
        $params = [];

        if ($categorySlug !== null && $categorySlug !== '') {
            $where[] = 'bc.slug = ?';
            $types .= 's';
            $params[] = $categorySlug;
        }

        if ($search !== null && trim($search) !== '') {
            $where[] = '(bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.content LIKE ?)';
            $like = '%' . trim($search) . '%';
            $types .= 'sss';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT bp.*, bc.name AS category_name, bc.slug AS category_slug
                FROM blog_posts bp
                LEFT JOIN blog_categories bc ON bc.id = bp.category_id
                WHERE $whereSql
                ORDER BY bp.published_at DESC, bp.id DESC
                LIMIT ? OFFSET ?";

        $types .= 'ii';
        $params[] = (int) $limit;
        $params[] = (int) $offset;

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        $bindArgs = [$types];
        foreach ($params as $key => $value) {
            $bindArgs[] = &$params[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching published blog posts: " . mysqli_error($conn));
        }

        $result = mysqli_stmt_get_result($stmt);
        $posts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $posts[] = $row;
        }

        return $posts;
    }
}

if (!function_exists('countPublishedBlogPosts')) {
    /**
     * Count Active, already-published blog posts, with the same optional
     * category/search filtering as getPublishedBlogPosts(). Used to build
     * pagination for the public blog listing.
     *
     * @param mysqli $conn
     * @param string|null $categorySlug
     * @param string|null $search
     * @return int
     * @throws Exception
     */
    function countPublishedBlogPosts($conn, $categorySlug = null, $search = null)
    {
        $where = ["bp.status = 'Active'", 'bp.published_at IS NOT NULL', 'bp.published_at <= NOW()'];
        $types = '';
        $params = [];

        if ($categorySlug !== null && $categorySlug !== '') {
            $where[] = 'bc.slug = ?';
            $types .= 's';
            $params[] = $categorySlug;
        }

        if ($search !== null && trim($search) !== '') {
            $where[] = '(bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.content LIKE ?)';
            $like = '%' . trim($search) . '%';
            $types .= 'sss';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT COUNT(*) AS total
                FROM blog_posts bp
                LEFT JOIN blog_categories bc ON bc.id = bp.category_id
                WHERE $whereSql";

        if ($types === '') {
            $result = mysqli_query($conn, $sql);
            if (!$result) {
                throw new Exception("Error counting published blog posts: " . mysqli_error($conn));
            }
            return (int) mysqli_fetch_assoc($result)['total'];
        }

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        $bindArgs = [$types];
        foreach ($params as $key => $value) {
            $bindArgs[] = &$params[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error counting published blog posts: " . mysqli_error($conn));
        }

        $result = mysqli_stmt_get_result($stmt);
        return (int) mysqli_fetch_assoc($result)['total'];
    }
}

if (!function_exists('getRecentPublishedBlogPosts')) {
    /**
     * Latest published posts for the "Popular Posts" sidebar. Stands in for
     * real popularity tracking (e.g. view counts), which the schema doesn't
     * have yet — this is recency-based by design, per project decision.
     *
     * @param mysqli $conn
     * @param int $limit
     * @param int|null $excludeId  Post id to leave out (e.g. the post currently being viewed).
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    function getRecentPublishedBlogPosts($conn, $limit = 4, $excludeId = null)
    {
        $where = ["status = 'Active'", 'published_at IS NOT NULL', 'published_at <= NOW()'];
        $types = '';
        $params = [];

        if ($excludeId !== null) {
            $where[] = 'id != ?';
            $types .= 'i';
            $params[] = (int) $excludeId;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT * FROM blog_posts WHERE $whereSql ORDER BY published_at DESC, id DESC LIMIT ?";
        $types .= 'i';
        $params[] = (int) $limit;

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        $bindArgs = [$types];
        foreach ($params as $key => $value) {
            $bindArgs[] = &$params[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching recent blog posts: " . mysqli_error($conn));
        }

        $result = mysqli_stmt_get_result($stmt);
        $posts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $posts[] = $row;
        }

        return $posts;
    }
}

if (!function_exists('getBlogPostById')) {
    /**
     * Fetch a single blog post by id.
     *
     * @param mysqli $conn
     * @param int $id
     * @return mysqli_result
     * @throws Exception
     */
    function getBlogPostById($conn, $id)
    {
        $sql = "SELECT * FROM blog_posts WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching blog post by ID: " . mysqli_error($conn));
        }

        $result = mysqli_stmt_get_result($stmt);
        if ($result === false) {
            throw new Exception("Error fetching blog post by ID: " . mysqli_error($conn));
        }

        return $result;
    }
}

/* ------------------------------------------------------------------
 * Storefront helpers for blog-details.php
 * ------------------------------------------------------------------ */

if (!function_exists('fetchBlogRows')) {
    /**
     * Run a prepared SELECT and return all rows as an array.
     *
     * @param mysqli $conn
     * @param string $sql
     * @param string $types  bind_param type string ('' if no params)
     * @param array  $params
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    function fetchBlogRows($conn, $sql, $types = '', array $params = [])
    {
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        if ($types !== '') {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error running blog query: " . mysqli_error($conn));
        }
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
}

if (!function_exists('getPublishedBlogPostBySlug')) {
    /**
     * Fetch one Active, already-published post by slug (with its category).
     *
     * @return array<string, mixed>|null
     */
    function getPublishedBlogPostBySlug($conn, $slug)
    {
        $sql = "SELECT bp.*, bc.name AS category_name, bc.slug AS category_slug
                FROM blog_posts bp
                LEFT JOIN blog_categories bc ON bc.id = bp.category_id
                WHERE bp.slug = ?
                  AND bp.status = 'Active'
                  AND bp.published_at IS NOT NULL
                  AND bp.published_at <= NOW()
                LIMIT 1";
        $rows = fetchBlogRows($conn, $sql, 's', [(string) $slug]);
        return $rows[0] ?? null;
    }
}

if (!function_exists('getAdjacentPublishedBlogPosts')) {
    /**
     * Previous (older) and next (newer) published posts, for the
     * "Previous Post / Next Post" links.
     *
     * @return array{prev: array|null, next: array|null}
     */
    function getAdjacentPublishedBlogPosts($conn, array $post)
    {
        $live = "status = 'Active' AND published_at IS NOT NULL AND published_at <= NOW()";
        $at = $post['published_at'];
        $id = (int) $post['id'];

        $prev = fetchBlogRows(
            $conn,
            "SELECT id, title, slug FROM blog_posts
             WHERE $live AND (published_at < ? OR (published_at = ? AND id < ?))
             ORDER BY published_at DESC, id DESC LIMIT 1",
            'ssi',
            [$at, $at, $id]
        );
        $next = fetchBlogRows(
            $conn,
            "SELECT id, title, slug FROM blog_posts
             WHERE $live AND (published_at > ? OR (published_at = ? AND id > ?))
             ORDER BY published_at ASC, id ASC LIMIT 1",
            'ssi',
            [$at, $at, $id]
        );

        return ['prev' => $prev[0] ?? null, 'next' => $next[0] ?? null];
    }
}

if (!function_exists('getRelatedPublishedBlogPosts')) {
    /**
     * Related posts: same category first, then the most recent others.
     *
     * @return array<int, array<string, mixed>>
     */
    function getRelatedPublishedBlogPosts($conn, array $post, $limit = 4)
    {
        $categoryId = (int) ($post['category_id'] ?? 0);
        return fetchBlogRows(
            $conn,
            "SELECT id, title, slug, image, content, published_at
             FROM blog_posts
             WHERE status = 'Active' AND published_at IS NOT NULL AND published_at <= NOW() AND id != ?
             ORDER BY COALESCE(category_id = ?, 0) DESC, published_at DESC, id DESC
             LIMIT ?",
            'iii',
            [(int) $post['id'], $categoryId, (int) $limit]
        );
    }
}

if (!function_exists('prepareBlogContent')) {
    /**
     * Give every <h2> in the post body an id and return a table of contents.
     *
     * @param string|null $html Raw CKEditor HTML from blog_posts.content
     * @return array{html: string, toc: array<int, array{id:string, text:string}>}
     */
    function prepareBlogContent($html)
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return ['html' => '', 'toc' => []];
        }

        $previous = libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementsByTagName('div')->item(0);
        $toc = [];
        $used = [];

        $emptyHeadings = [];
        foreach ($root->getElementsByTagName('h2') as $h2) {
            // trim() does not strip &nbsp; (U+00A0), which CKEditor leaves in blank headings.
            $text = preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', $h2->textContent);
            if ($text === '') {
                $emptyHeadings[] = $h2;
                continue;
            }
            $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-')) ?: 'section';
            $id = $base;
            for ($n = 2; isset($used[$id]); $n++) {
                $id = $base . '-' . $n;
            }
            $used[$id] = true;
            $h2->setAttribute('id', $id);
            $toc[] = ['id' => $id, 'text' => $text];
        }

        // Blank headings would otherwise render as an empty numbered circle.
        foreach ($emptyHeadings as $node) {
            $node->parentNode->removeChild($node);
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return ['html' => $out, 'toc' => $toc];
    }
}