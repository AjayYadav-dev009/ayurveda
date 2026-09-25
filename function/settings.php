<?php

/**
 * function/settings.php
 *
 * Site-wide settings helpers. Backed by the `settings` table:
 *   id, setting_key, setting_value, setting_type, setting_group,
 *   label, description, status, sort_order, created_at, updated_at
 * (see settings.sql for the table definition)
 *
 * Use getSetting($conn, 'site_name') anywhere in the storefront or admin
 * to read a single value. This file only depends on mysqli — it does not
 * start sessions, check auth, or output anything, so it is safe to include
 * from both the admin panel and the public storefront.
 */

/* -------------------------------------------------------------------------
 * Option lists (functions, not constants, so they can be guarded with
 * function_exists() like everything else in this file)
 * ---------------------------------------------------------------------- */

if (!function_exists('SETTING_TYPES')) {
    /** Allowed setting_type values => admin-facing labels. */
    function SETTING_TYPES()
    {
        return [
            'text'     => 'Text',
            'textarea' => 'Textarea',
            'email'    => 'Email',
            'url'      => 'URL',
            'number'   => 'Number',
            'phone'    => 'Phone',
            'boolean'  => 'Boolean',
            'image'    => 'Image',
        ];
    }
}

if (!function_exists('SETTING_STATUS_OPTIONS')) {
    /** Allowed status values => labels. */
    function SETTING_STATUS_OPTIONS()
    {
        return [
            'Active'   => 'Active',
            'Inactive' => 'Inactive',
        ];
    }
}

if (!function_exists('SETTING_GROUP_SUGGESTIONS')) {
    /**
     * Suggested groups for the create/edit form's <datalist>.
     * NOT an allow-list: any group is accepted, and getSettingGroups()
     * reads the real list from the database.
     */
    function SETTING_GROUP_SUGGESTIONS()
    {
        return ['General', 'Contact', 'Social Media', 'SEO', 'Ecommerce', 'Shipping', 'Footer'];
    }
}

/* -------------------------------------------------------------------------
 * Validation / normalisation
 * ---------------------------------------------------------------------- */

if (!function_exists('validateSettingKey')) {
    /** Keys: lowercase letters, numbers, underscore, hyphen. Max 100 chars. */
    function validateSettingKey($key)
    {
        // The D modifier stops "$" from matching before a trailing newline.
        return is_string($key)
            && strlen($key) <= 100
            && preg_match('/^[a-z0-9_-]+$/D', $key) === 1;
    }
}

if (!function_exists('normalizeSettingValueForStorage')) {
    /**
     * Boolean settings are always stored as '1' or '0' so getSetting()
     * callers can rely on a predictable value. Everything else is a string.
     */
    function normalizeSettingValueForStorage($type, $value)
    {
        if ($type === 'boolean') {
            $v = strtolower(trim((string) $value));
            return in_array($v, ['1', 'on', 'true', 'yes'], true) ? '1' : '0';
        }
        return (string) $value;
    }
}

if (!function_exists('escapeLikeValue')) {
    /** Escape %, _ and \ so user input is matched literally inside LIKE. */
    function escapeLikeValue($value)
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], (string) $value);
    }
}

if (!function_exists('validateSettingValue')) {
    /**
     * Check a value against its setting type. Empty values are allowed for
     * every type (a setting can be left blank).
     *
     * @throws InvalidArgumentException when the value doesn't fit the type.
     */
    function validateSettingValue($type, $value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return true;
        }

        switch ($type) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new InvalidArgumentException('Please enter a valid email address.');
                }
                break;
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $value)) {
                    throw new InvalidArgumentException('Please enter a valid URL starting with http:// or https://.');
                }
                break;
            case 'number':
                if (!is_numeric($value)) {
                    throw new InvalidArgumentException('Please enter a valid number.');
                }
                break;
            case 'phone':
                if (!preg_match('/^[0-9+\-\s().]{5,25}$/D', $value)) {
                    throw new InvalidArgumentException('Please enter a valid phone number.');
                }
                break;
        }
        return true;
    }
}

if (!function_exists('settingFormFromPost')) {
    /**
     * Normalise the create/edit form's $_POST into one array. The form has a
     * separate input per value kind (value_text, value_textarea,
     * value_boolean, value_image); this picks the right one for the chosen
     * type and puts it in 'setting_value'. Image values are filled in by the
     * calling page after uploadSettingImage().
     */
    function settingFormFromPost(array $post)
    {
        $str = function ($k, $default = '') use ($post) {
            return isset($post[$k]) && is_scalar($post[$k]) ? trim((string) $post[$k]) : $default;
        };

        $type = $str('setting_type', 'text');
        $form = [
            'setting_key'    => $str('setting_key'),
            'label'          => $str('label'),
            'setting_type'   => $type,
            'setting_group'  => $str('setting_group', 'General'),
            'description'    => $str('description'),
            'status'         => $str('status', 'Active') === 'Inactive' ? 'Inactive' : 'Active',
            'sort_order'     => (int) $str('sort_order', '0'),
            'value_text'     => $str('value_text'),
            'value_textarea' => isset($post['value_textarea']) && is_scalar($post['value_textarea'])
                ? (string) $post['value_textarea'] : '',
            'value_boolean'  => !empty($post['value_boolean']),
        ];

        switch ($type) {
            case 'textarea':
                $form['setting_value'] = $form['value_textarea'];
                break;
            case 'boolean':
                $form['setting_value'] = $form['value_boolean'] ? '1' : '0';
                break;
            case 'image':
                $form['setting_value'] = '';
                break;
            default:
                $form['setting_value'] = $form['value_text'];
        }
        return $form;
    }
}

if (!function_exists('settingFormFromRow')) {
    /** Turn a settings DB row into the array the create/edit form renders. */
    function settingFormFromRow(array $row)
    {
        $value = (string) ($row['setting_value'] ?? '');
        return [
            'setting_key'    => $row['setting_key'],
            'label'          => $row['label'],
            'setting_type'   => $row['setting_type'],
            'setting_group'  => $row['setting_group'],
            'description'    => (string) ($row['description'] ?? ''),
            'status'         => $row['status'],
            'sort_order'     => (int) $row['sort_order'],
            'value_text'     => $row['setting_type'] === 'image' ? '' : $value,
            'value_textarea' => $row['setting_type'] === 'image' ? '' : $value,
            'value_boolean'  => $value === '1',
            'setting_value'  => $value,
        ];
    }
}

/* -------------------------------------------------------------------------
 * Read helpers
 * ---------------------------------------------------------------------- */

if (!function_exists('settingKeyExists')) {
    function settingKeyExists(mysqli $conn, $key, $excludeId = null)
    {
        if ($excludeId !== null) {
            $excludeId = (int) $excludeId;
            $stmt = $conn->prepare('SELECT id FROM settings WHERE setting_key = ? AND id != ? LIMIT 1');
            $stmt->bind_param('si', $key, $excludeId);
        } else {
            $stmt = $conn->prepare('SELECT id FROM settings WHERE setting_key = ? LIMIT 1');
            $stmt->bind_param('s', $key);
        }
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}

if (!function_exists('getSetting')) {
    /**
     * Read a single setting value by key. Returns $default when the key
     * does not exist or the value is NULL, so callers never have to
     * null-check the result.
     *
     * Example: $siteName = getSetting($conn, 'site_name');
     */
    function getSetting(mysqli $conn, $key, $default = '')
    {
        $stmt = $conn->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row === null || $row['setting_value'] === null) {
            return $default;
        }
        return $row['setting_value'];
    }
}

if (!function_exists('getSettings')) {
    /**
     * Fetch settings for the admin list page, with optional search/group/
     * status filters. All filtering uses prepared statements.
     *
     * $filters: ['search' => string, 'group' => string, 'status' => string]
     *
     * @throws RuntimeException if the query cannot be prepared.
     */
    function getSettings(mysqli $conn, array $filters = [])
    {
        $where  = [];
        $types  = '';
        $params = [];

        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $like = '%' . escapeLikeValue($search) . '%';
            $where[] = '(label LIKE ? OR setting_key LIKE ? OR description LIKE ? OR setting_group LIKE ?)';
            $types .= 'ssss';
            array_push($params, $like, $like, $like, $like);
        }

        $group = trim($filters['group'] ?? '');
        if ($group !== '' && $group !== 'all') {
            $where[] = 'setting_group = ?';
            $types .= 's';
            $params[] = $group;
        }

        $status = trim($filters['status'] ?? '');
        if ($status !== '' && $status !== 'all') {
            $where[] = 'status = ?';
            $types .= 's';
            $params[] = $status;
        }

        $sql = 'SELECT * FROM settings';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY setting_group ASC, sort_order ASC, label ASC';

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Database query failed: ' . $conn->error);
        }
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}

if (!function_exists('getSettingsByGroup')) {
    /** All settings in one group, ordered for display. */
    function getSettingsByGroup(mysqli $conn, $group)
    {
        $stmt = $conn->prepare('SELECT * FROM settings WHERE setting_group = ? ORDER BY sort_order ASC, label ASC');
        $stmt->bind_param('s', $group);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}

if (!function_exists('getSettingGroups')) {
    /**
     * Distinct groups currently in use, for the filter dropdown. Comes from
     * the database so custom groups show up automatically.
     *
     * @throws RuntimeException if the query fails.
     */
    function getSettingGroups(mysqli $conn)
    {
        $result = $conn->query('SELECT DISTINCT setting_group FROM settings ORDER BY setting_group ASC');
        if ($result === false) {
            throw new RuntimeException('Database query failed: ' . $conn->error);
        }
        $groups = [];
        while ($row = $result->fetch_assoc()) {
            $groups[] = $row['setting_group'];
        }
        return $groups;
    }
}

if (!function_exists('getSettingById')) {
    /** Returns the setting row as an array, or null if not found. */
    function getSettingById(mysqli $conn, $id)
    {
        $id = (int) $id;
        $stmt = $conn->prepare('SELECT * FROM settings WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
}

/* -------------------------------------------------------------------------
 * Image helpers
 * ---------------------------------------------------------------------- */

if (!function_exists('settingsUploadDir')) {
    /**
     * Absolute filesystem path to the settings image upload directory,
     * created on first use (uploads/settings/).
     */
    function settingsUploadDir()
    {
        $dir = __DIR__ . '/../uploads/settings';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }
}

if (!function_exists('getSettingImageUrl')) {
    /**
     * Build a browsable URL from a stored relative settings image path.
     *
     * Expected database value:
     * uploads/settings/setting_xxxxxxxx.png
     */
    function getSettingImageUrl($storedPath)
    {
        $storedPath = trim((string) $storedPath);

        if ($storedPath === '') {
            return null;
        }

        // Normalize Windows-style slashes.
        $storedPath = str_replace('\\', '/', $storedPath);

        // Backward compatibility:
        // If an older database row contains only the filename,
        // automatically prepend the settings upload directory.
        if (strpos($storedPath, '/') === false) {
            $storedPath = 'uploads/settings/' . basename($storedPath);
        }

        // Prevent path traversal.
        if (strpos($storedPath, '..') !== false) {
            return null;
        }

        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';

        // Encode each path segment separately so "/" remains a path separator.
        $segments = explode('/', trim($storedPath, '/'));
        $segments = array_map('rawurlencode', $segments);

        return $base . '/' . implode('/', $segments);
    }
}

if (!function_exists('deleteSettingImageFile')) {
    /**
     * Remove a stored settings image from disk. Only ever touches files
     * inside uploads/settings/ (basename() blocks path traversal).
     */
    function deleteSettingImageFile($filename)
    {
        $filename = basename((string) $filename);
        if ($filename === '' || $filename === '.' || $filename === '..') {
            return false;
        }
        $path = settingsUploadDir() . '/' . $filename;
        return is_file($path) ? @unlink($path) : false;
    }
}

if (!function_exists('uploadSettingImage')) {
    /**
     * Handle an <input type="file"> upload for an image-type setting.
     * Returns the new filename on success, or null if no file was chosen
     * (meaning "keep the existing image" on edit).
     *
     * @throws InvalidArgumentException on invalid file type/size.
     * @throws RuntimeException if the file cannot be saved.
     */
    function uploadSettingImage($file)
    {
        if (empty($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Image upload failed. Please try again.');
        }

        $maxBytes = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxBytes) {
            throw new InvalidArgumentException('Image must be smaller than 2MB.');
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!isset($allowed[$mime])) {
            throw new InvalidArgumentException('Unsupported image type. Use JPG, PNG, WEBP, or GIF.');
        }

        $filename = 'setting_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];

        $destination = settingsUploadDir() . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Unable to save the uploaded image.');
        }

        // Store a web-relative path in the database.
        return 'uploads/settings/' . $filename;
    }
}

/* -------------------------------------------------------------------------
 * Write helpers
 * ---------------------------------------------------------------------- */

if (!function_exists('addSetting')) {
    /**
     * Insert a new setting. Returns the new row id.
     *
     * @throws InvalidArgumentException on validation failure (invalid key,
     *         duplicate key, missing label/group, invalid type).
     * @throws RuntimeException if the insert fails.
     */
    function addSetting(mysqli $conn, array $data)
    {
        $key         = trim($data['setting_key'] ?? '');
        $label       = trim($data['label'] ?? '');
        $type        = trim($data['setting_type'] ?? 'text');
        $group       = trim($data['setting_group'] ?? 'General');
        $description = trim($data['description'] ?? '');
        $status      = ($data['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';
        $sortOrder   = (int) ($data['sort_order'] ?? 0);
        $value       = is_scalar($data['setting_value'] ?? '') ? (string) ($data['setting_value'] ?? '') : '';

        if ($label === '') {
            throw new InvalidArgumentException('Label is required.');
        }
        if (!validateSettingKey($key)) {
            throw new InvalidArgumentException('Setting key may only contain lowercase letters, numbers, underscores, and hyphens.');
        }
        if (!array_key_exists($type, SETTING_TYPES())) {
            throw new InvalidArgumentException('Invalid setting type.');
        }
        if ($group === '') {
            throw new InvalidArgumentException('Group is required.');
        }
        validateSettingValue($type, $value);
        if (settingKeyExists($conn, $key)) {
            throw new InvalidArgumentException('A setting with the key "' . $key . '" already exists.');
        }

        $value = normalizeSettingValueForStorage($type, $value);

        $stmt = $conn->prepare(
            'INSERT INTO settings (setting_key, setting_value, setting_type, setting_group, label, description, status, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssssssi', $key, $value, $type, $group, $label, $description, $status, $sortOrder);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Unable to save the setting. Please try again.');
        }
        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }
}

if (!function_exists('updateSetting')) {
    /**
     * Update an existing setting. Returns true on success.
     *
     * For image settings, an empty value keeps the existing stored filename.
     * When an image is replaced (or the type is changed away from image),
     * the old file is removed from disk.
     *
     * @throws InvalidArgumentException on validation failure / not found.
     * @throws RuntimeException if the update fails.
     */
    function updateSetting(mysqli $conn, $id, array $data)
    {
        $id = (int) $id;
        $existing = getSettingById($conn, $id);
        if (!$existing) {
            throw new InvalidArgumentException('Setting not found.');
        }

        $key         = trim($data['setting_key'] ?? '');
        $label       = trim($data['label'] ?? '');
        $type        = trim($data['setting_type'] ?? 'text');
        $group       = trim($data['setting_group'] ?? 'General');
        $description = trim($data['description'] ?? '');
        $status      = ($data['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';
        $sortOrder   = (int) ($data['sort_order'] ?? 0);
        $value       = is_scalar($data['setting_value'] ?? '') ? (string) ($data['setting_value'] ?? '') : '';

        if ($label === '') {
            throw new InvalidArgumentException('Label is required.');
        }
        if (!validateSettingKey($key)) {
            throw new InvalidArgumentException('Setting key may only contain lowercase letters, numbers, underscores, and hyphens.');
        }
        if (!array_key_exists($type, SETTING_TYPES())) {
            throw new InvalidArgumentException('Invalid setting type.');
        }
        if ($group === '') {
            throw new InvalidArgumentException('Group is required.');
        }
        validateSettingValue($type, $value);
        if (settingKeyExists($conn, $key, $id)) {
            throw new InvalidArgumentException('A setting with the key "' . $key . '" already exists.');
        }

        // Image settings: keep the existing filename unless a new one was
        // supplied by the upload handler in the calling page.
        if ($type === 'image' && $value === '' && $existing['setting_type'] === 'image') {
            $value = (string) $existing['setting_value'];
        }

        $value = normalizeSettingValueForStorage($type, $value);

        $stmt = $conn->prepare(
            'UPDATE settings
             SET setting_key = ?, setting_value = ?, setting_type = ?, setting_group = ?, label = ?, description = ?, status = ?, sort_order = ?
             WHERE id = ?'
        );
        $stmt->bind_param('sssssssii', $key, $value, $type, $group, $label, $description, $status, $sortOrder, $id);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Unable to update the setting. Please try again.');
        }
        $stmt->close();

        // Clean up the old image file if it was replaced or no longer used.
        if (
            $existing['setting_type'] === 'image'
            && (string) $existing['setting_value'] !== ''
            && (string) $existing['setting_value'] !== $value
        ) {
            deleteSettingImageFile($existing['setting_value']);
        }

        return true;
    }
}

if (!function_exists('deleteSetting')) {
    /**
     * Delete a setting (and its image file, for image-type settings).
     * Returns true if a row was deleted, false if the id didn't exist.
     *
     * @throws RuntimeException if the delete query fails.
     */
    function deleteSetting(mysqli $conn, $id)
    {
        $id = (int) $id;
        $existing = getSettingById($conn, $id);
        if (!$existing) {
            return false;
        }

        $stmt = $conn->prepare('DELETE FROM settings WHERE id = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if (!$ok) {
            throw new RuntimeException('Unable to delete the setting. Please try again.');
        }

        if ($affected > 0 && $existing['setting_type'] === 'image' && (string) $existing['setting_value'] !== '') {
            deleteSettingImageFile($existing['setting_value']);
        }

        return $affected > 0;
    }
}
