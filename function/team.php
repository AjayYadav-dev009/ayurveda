<?php

/**
 * function/team.php
 *
 * Shared data layer for `team_members` — the "Our Team Of Ayurvedic
 * Experts" homepage slider and its admin CRUD (admin/team-management.php).
 * Mirrors function/banner.php's contract 1:1 (same status-constant
 * pattern, same uploadXImage()/getXImageUrl() pair, same
 * add/update/delete/toggle/reorder shape) so the admin page could be
 * built the same way banner-management.php was.
 */

const TEAM_STATUS_INACTIVE = 0;
const TEAM_STATUS_ACTIVE = 1;

const TEAM_STATUS_OPTIONS = [
    TEAM_STATUS_ACTIVE => 'Active',
    TEAM_STATUS_INACTIVE => 'Inactive',
];

const TEAM_UPLOAD_DIR = __DIR__ . '/../uploads/team/';
const TEAM_UPLOAD_URL_PREFIX = 'uploads/team/';
const TEAM_ALLOWED_MIME_TO_EXT = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
];
const TEAM_MAX_UPLOAD_BYTES = 4 * 1024 * 1024; // 4MB

/**
 * Handles a team-member photo upload from $_FILES['image'].
 *
 * Contract matches uploadBannerImage(): returns null when no new file was
 * submitted (the caller — updateTeamMember() — treats null as "keep the
 * existing image"), returns the stored relative path (e.g.
 * "uploads/team/671f...c2.webp") on success, and throws
 * InvalidArgumentException on a bad/oversized/wrong-type file so the
 * admin form can show a real error instead of silently failing.
 *
 * @param array|null $file A single $_FILES['image']-style entry.
 * @return string|null
 */
function uploadTeamMemberImage($file)
{
    if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('There was a problem uploading that image. Please try again.');
    }

    if ($file['size'] > TEAM_MAX_UPLOAD_BYTES) {
        throw new InvalidArgumentException('Image is too large — please upload something under 4MB.');
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!isset(TEAM_ALLOWED_MIME_TO_EXT[$mime])) {
        throw new InvalidArgumentException('Please upload a JPG, PNG, WEBP, or GIF image.');
    }

    if (!is_dir(TEAM_UPLOAD_DIR)) {
        mkdir(TEAM_UPLOAD_DIR, 0755, true);
    }

    $extension = TEAM_ALLOWED_MIME_TO_EXT[$mime];
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = TEAM_UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new InvalidArgumentException('Could not save the uploaded image. Please try again.');
    }

    return TEAM_UPLOAD_URL_PREFIX . $filename;
}

/**
 * Builds a public URL for a stored team-member image path, or null if
 * there isn't one (so callers can render a fallback instead).
 *
 * @param string|null $image
 * @return string|null
 */
function getTeamMemberImageUrl($image)
{
    if (empty($image)) {
        return null;
    }
    return BASE_URL . ltrim($image, '/');
}

/**
 * All team members, newest-first fallback aside — ordered by sort_order
 * for the admin list (matches getAllBanners()'s ordering intent).
 *
 * @param mysqli $conn
 * @return array<int, array<string, mixed>>
 * @throws Exception
 */
function getAllTeamMembers($conn)
{
    $sql = "SELECT * FROM team_members ORDER BY sort_order ASC, id ASC";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception('Error fetching team members: ' . mysqli_error($conn));
    }

    $members = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
    return $members;
}

/**
 * Active-only team members for the public homepage slider.
 *
 * @param mysqli $conn
 * @param int $limit
 * @return array<int, array<string, mixed>>
 * @throws Exception
 */
function getActiveTeamMembers($conn, $limit = 12)
{
    $limit = max(1, (int) $limit);

    $sql = "SELECT * FROM team_members
            WHERE status = ?
            ORDER BY sort_order ASC, id ASC
            LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    $status = TEAM_STATUS_ACTIVE;
    mysqli_stmt_bind_param($stmt, 'ii', $status, $limit);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error fetching active team members: ' . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt);
    $members = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
    return $members;
}

/**
 * @param mysqli $conn
 * @param int $id
 * @return array<string, mixed>|null
 * @throws Exception
 */
function getTeamMemberById($conn, $id)
{
    $id = (int) $id;
    $sql = "SELECT * FROM team_members WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error fetching team member: ' . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row ?: null;
}

/**
 * @param mysqli $conn
 * @param string $name
 * @param string $designation
 * @param string $bio
 * @param string|null $image Relative path from uploadTeamMemberImage(), or null.
 * @param int $status
 * @param int $sortOrder
 * @return int Newly inserted id.
 * @throws Exception
 */
function addTeamMember($conn, $name, $designation, $bio, $image, $status, $sortOrder)
{
    $name = trim((string) $name);
    if ($name === '') {
        throw new InvalidArgumentException('Name is required.');
    }

    $sql = "INSERT INTO team_members (name, designation, bio, image, status, sort_order)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }

    $designation = trim((string) $designation);
    $bio = trim((string) $bio);
    $status = (int) $status;
    $sortOrder = (int) $sortOrder;

    mysqli_stmt_bind_param($stmt, 'ssssii', $name, $designation, $bio, $image, $status, $sortOrder);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error adding team member: ' . mysqli_error($conn));
    }

    return mysqli_insert_id($conn);
}

/**
 * @param mysqli $conn
 * @param int $id
 * @param string $name
 * @param string $designation
 * @param string $bio
 * @param string|null $image Pass null to keep the existing image (this is
 *   what uploadTeamMemberImage() returns when no new file was chosen).
 * @param int $status
 * @param int $sortOrder
 * @throws Exception
 */
function updateTeamMember($conn, $id, $name, $designation, $bio, $image, $status, $sortOrder)
{
    $id = (int) $id;
    $name = trim((string) $name);
    if ($name === '') {
        throw new InvalidArgumentException('Name is required.');
    }

    $existing = getTeamMemberById($conn, $id);
    if ($existing === null) {
        throw new InvalidArgumentException('Team member not found.');
    }

    $designation = trim((string) $designation);
    $bio = trim((string) $bio);
    $status = (int) $status;
    $sortOrder = (int) $sortOrder;

    // No new file uploaded — keep the current image rather than clearing it.
    $finalImage = $image ?? $existing['image'];
    $oldImage = $existing['image'];

    $sql = "UPDATE team_members
            SET name = ?, designation = ?, bio = ?, image = ?, status = ?, sort_order = ?
            WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'ssssiii', $name, $designation, $bio, $finalImage, $status, $sortOrder, $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error updating team member: ' . mysqli_error($conn));
    }

    // A genuinely new image replaced an old one — clean up the orphaned file.
    if ($image !== null && $oldImage !== null && $oldImage !== $finalImage) {
        $oldPath = __DIR__ . '/../' . ltrim($oldImage, '/');
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }
}

/**
 * @param mysqli $conn
 * @param int $id
 * @throws Exception
 */
function deleteTeamMember($conn, $id)
{
    $id = (int) $id;
    $existing = getTeamMemberById($conn, $id);
    if ($existing === null) {
        return;
    }

    $sql = "DELETE FROM team_members WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error deleting team member: ' . mysqli_error($conn));
    }

    if (!empty($existing['image'])) {
        $path = __DIR__ . '/../' . ltrim($existing['image'], '/');
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

/**
 * @param mysqli $conn
 * @param int $id
 * @param int $status
 * @throws Exception
 */
function updateTeamMemberStatus($conn, $id, $status)
{
    $id = (int) $id;
    $status = (int) $status;

    $sql = "UPDATE team_members SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'ii', $status, $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error updating team member status: ' . mysqli_error($conn));
    }
}

/**
 * @param mysqli $conn
 * @param array<int, int> $order [team_member_id => sort_order]
 * @throws Exception
 */
function reorderTeamMembers($conn, array $order)
{
    if (empty($order)) {
        return;
    }

    $sql = "UPDATE team_members SET sort_order = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }

    foreach ($order as $id => $sortOrder) {
        $id = (int) $id;
        $sortOrder = (int) $sortOrder;
        mysqli_stmt_bind_param($stmt, 'ii', $sortOrder, $id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Error saving team member order: ' . mysqli_error($conn));
        }
    }
}