<?php

function redirect($url)
{
    header("Location: " . $url);
    exit;
}

function createMetaTitle($title)
{
    $meta_title = trim($title);
    if (empty($meta_title)) {
        throw new InvalidArgumentException("Meta title cannot be empty.");
    }
    return $meta_title;
}

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