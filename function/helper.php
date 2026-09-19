<?php

if (!function_exists('redirect')) {
    function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }
}

if (!function_exists('sanitizeInternalRedirect')) {
    /**
     * Take a user-supplied "redirect back to" path (e.g. from
     * ?redirect=... on the login page) and make sure it can only ever
     * point somewhere inside this site, never off-site.
     *
     * Rejects anything that looks like an absolute URL (http://, https://,
     * or a protocol-relative //host), and backslashes (some browsers treat
     * "\evil.com" like "//evil.com"). Anything that fails the check falls
     * back to $default.
     *
     * @param string $path User-supplied redirect target.
     * @param string $default Safe internal path to fall back to.
     * @return string A path safe to append to BASE_URL.
     */
    function sanitizeInternalRedirect($path, $default = '')
    {
        $path = trim((string) $path);

        if ($path === '') {
            return $default;
        }

        if (strpos($path, '\\') !== false) {
            return $default;
        }

        // Blocks "http://...", "https://...", "//evil.com", and any other
        // "scheme:" or protocol-relative prefix.
        if (preg_match('#^([a-z][a-z0-9+.\-]*:)?//#i', $path)) {
            return $default;
        }

        return ltrim($path, '/');
    }
}

if (!function_exists('createMetaTitle')) {
    function createMetaTitle($title)
    {
        $meta_title = trim($title);
        if (empty($meta_title)) {
            throw new InvalidArgumentException("Meta title cannot be empty.");
        }
        return $meta_title;
    }
}

if (!function_exists('createMetaDescription')) {
    function createMetaDescription($description)
    {
        $meta_description = trim($description);
        if (empty($meta_description)) {
            throw new InvalidArgumentException("Meta description cannot be empty.");
        }
        if (strlen($meta_description) > 200) {
            $meta_description = substr($meta_description, 0, 200);
        }
        return $meta_description;
    }
}

if (!function_exists('createSlug')) {
    function createSlug($title)
    {
        $slug = trim($title);

        if (empty($slug)) {
            throw new InvalidArgumentException("Slug title cannot be empty.");
        }

        // Convert to lowercase
        $slug = strtolower($slug);

        // Replace anything that isn't a letter or number with a hyphen
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Remove hyphens from beginning and end
        $slug = trim($slug, '-');

        if (empty($slug)) {
            throw new InvalidArgumentException("Unable to create a valid slug.");
        }

        return $slug;
    }
}