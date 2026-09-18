<?php


const PROMO_VIDEO_STATUS_INACTIVE = 0;
const PROMO_VIDEO_STATUS_ACTIVE = 1;

const PROMO_VIDEO_STATUS_OPTIONS = [
    PROMO_VIDEO_STATUS_ACTIVE => 'Active',
    PROMO_VIDEO_STATUS_INACTIVE => 'Inactive',
];

const PROMO_VIDEO_TYPE_OPTIONS = [
    'upload' => 'Uploaded video file',
    'youtube' => 'YouTube link',
    'vimeo' => 'Vimeo link',
];

const PROMO_VIDEO_UPLOAD_DIR = __DIR__ . '/../uploads/promo-videos/';
const PROMO_VIDEO_UPLOAD_PATH = 'uploads/promo-videos/';
const PROMO_VIDEO_THUMB_UPLOAD_DIR = __DIR__ . '/../uploads/promo-videos/thumbs/';
const PROMO_VIDEO_THUMB_UPLOAD_PATH = 'uploads/promo-videos/thumbs/';

/* =============================================================================
 * Uploads
 * ============================================================================= */

/**
 * Handles the video FILE input (only relevant when video_type = 'upload').
 * Returns the relative path to store in `video_file`, or null when no file
 * was chosen (e.g. on edit, when keeping the existing file, or when the
 * admin picked a YouTube/Vimeo link instead of uploading).
 *
 * @throws InvalidArgumentException on a present-but-invalid upload.
 */
function uploadPromoVideoFile($file)
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('The video file failed to upload. Please try again.');
    }

    $allowed = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new InvalidArgumentException('Please upload an MP4, WebM, or MOV video file.');
    }

    // 100MB ceiling — generous for a homepage promo clip, but still a cap.
    if ($file['size'] > 100 * 1024 * 1024) {
        throw new InvalidArgumentException('Video file is too large (max 100MB).');
    }

    if (!is_dir(PROMO_VIDEO_UPLOAD_DIR)) {
        mkdir(PROMO_VIDEO_UPLOAD_DIR, 0755, true);
    }

    $filename = 'video-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], PROMO_VIDEO_UPLOAD_DIR . $filename)) {
        throw new InvalidArgumentException('Could not save the uploaded video. Please try again.');
    }

    return PROMO_VIDEO_UPLOAD_PATH . $filename;
}

/**
 * Handles the thumbnail image input. Returns the relative path, or null
 * when no new thumbnail was chosen.
 *
 * @throws InvalidArgumentException on a present-but-invalid upload.
 */
function uploadPromoVideoThumbnail($file)
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('The thumbnail failed to upload. Please try again.');
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new InvalidArgumentException('Please upload a JPG, PNG, or WebP thumbnail image.');
    }

    if (!is_dir(PROMO_VIDEO_THUMB_UPLOAD_DIR)) {
        mkdir(PROMO_VIDEO_THUMB_UPLOAD_DIR, 0755, true);
    }

    $filename = 'thumb-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], PROMO_VIDEO_THUMB_UPLOAD_DIR . $filename)) {
        throw new InvalidArgumentException('Could not save the thumbnail. Please try again.');
    }

    return PROMO_VIDEO_THUMB_UPLOAD_PATH . $filename;
}

function getPromoVideoFileUrl($path)
{
    return $path ? BASE_URL . ltrim($path, '/') : null;
}

function getPromoVideoThumbnailUrl($path)
{
    return $path ? BASE_URL . ltrim($path, '/') : null;
}

/* =============================================================================
 * CRUD
 * ============================================================================= */

function addPromoVideo($conn, $title, $description, $videoType, $videoUrl, $videoFile, $thumbnail, $buttonText, $buttonUrl, $orientation, $status, $sortOrder)
{
    if ($videoType === 'upload' && !$videoFile) {
        throw new InvalidArgumentException('Please upload a video file, or switch to a YouTube/Vimeo link.');
    }
    if ($videoType !== 'upload' && trim((string) $videoUrl) === '') {
        throw new InvalidArgumentException('Please enter the YouTube or Vimeo URL.');
    }

    $orientation = in_array($orientation, ['vertical', 'horizontal'], true) ? $orientation : null;

    $stmt = $conn->prepare(
        'INSERT INTO promotional_videos
            (title, description, video_type, video_url, video_file, thumbnail, button_text, button_url, orientation, status, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'sssssssssii',
        $title,
        $description,
        $videoType,
        $videoUrl,
        $videoFile,
        $thumbnail,
        $buttonText,
        $buttonUrl,
        $orientation,
        $status,
        $sortOrder
    );
    $stmt->execute();
    $stmt->close();
}

function updatePromoVideo($conn, $id, $title, $description, $videoType, $videoUrl, $videoFile, $thumbnail, $buttonText, $buttonUrl, $orientation, $status, $sortOrder)
{
    $existing = getPromoVideoById($conn, $id);
    if (!$existing) {
        throw new InvalidArgumentException('Promotional video not found.');
    }

    // Keep the existing file/thumbnail when no new one was uploaded.
    $videoFile = $videoFile ?? $existing['video_file'];
    $thumbnail = $thumbnail ?? $existing['thumbnail'];

    if ($videoType === 'upload' && !$videoFile) {
        throw new InvalidArgumentException('Please upload a video file, or switch to a YouTube/Vimeo link.');
    }
    if ($videoType !== 'upload' && trim((string) $videoUrl) === '') {
        throw new InvalidArgumentException('Please enter the YouTube or Vimeo URL.');
    }

    $orientation = in_array($orientation, ['vertical', 'horizontal'], true) ? $orientation : null;

    $stmt = $conn->prepare(
        'UPDATE promotional_videos
         SET title = ?, description = ?, video_type = ?, video_url = ?, video_file = ?,
             thumbnail = ?, button_text = ?, button_url = ?, orientation = ?, status = ?, sort_order = ?
         WHERE id = ?'
    );
    $stmt->bind_param(
        'sssssssssiii',
        $title,
        $description,
        $videoType,
        $videoUrl,
        $videoFile,
        $thumbnail,
        $buttonText,
        $buttonUrl,
        $orientation,
        $status,
        $sortOrder,
        $id
    );
    $stmt->execute();
    $stmt->close();
}

function deletePromoVideo($conn, $id)
{
    $video = getPromoVideoById($conn, $id);

    $stmt = $conn->prepare('DELETE FROM promotional_videos WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    // Best-effort cleanup of the uploaded files; a missing file here should
    // never block the delete itself, so failures are silently ignored.
    if ($video) {
        if (!empty($video['video_file']) && $video['video_type'] === 'upload') {
            @unlink(__DIR__ . '/../' . $video['video_file']);
        }
        if (!empty($video['thumbnail'])) {
            @unlink(__DIR__ . '/../' . $video['thumbnail']);
        }
    }
}

function updatePromoVideoStatus($conn, $id, $status)
{
    $stmt = $conn->prepare('UPDATE promotional_videos SET status = ? WHERE id = ?');
    $stmt->bind_param('ii', $status, $id);
    $stmt->execute();
    $stmt->close();
}

/**
 * @param array $order [video_id => sort_order]
 */
function reorderPromoVideos($conn, array $order)
{
    $stmt = $conn->prepare('UPDATE promotional_videos SET sort_order = ? WHERE id = ?');
    foreach ($order as $id => $sortOrder) {
        $id = (int) $id;
        $sortOrder = (int) $sortOrder;
        $stmt->bind_param('ii', $sortOrder, $id);
        $stmt->execute();
    }
    $stmt->close();
}

function getAllPromoVideos($conn)
{
    $result = $conn->query('SELECT * FROM promotional_videos ORDER BY sort_order ASC, id ASC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getPromoVideoById($conn, $id)
{
    $stmt = $conn->prepare('SELECT * FROM promotional_videos WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/**
 * For the public homepage section (includes/promotion-video.php): active
 * videos only, in display order, shaped to match what that file expects
 * — 'id', 'title', 'video_url', 'thumbnail', 'orientation'.
 *
 * Note: promotion-video.php renders every row into a plain <video> tag —
 * it has no YouTube/Vimeo iframe embed support. So only videos with
 * video_type = 'upload' are included here; a YouTube/Vimeo entry added
 * via the admin panel simply won't appear in this particular section
 * (it's still stored, in case another part of the site is built to embed
 * it later).
 */
function getFeaturedPromoVideos($conn, $limit = 12)
{
    $stmt = $conn->prepare(
        "SELECT id, title, video_file, thumbnail, orientation
         FROM promotional_videos
         WHERE status = ? AND video_type = 'upload'
         ORDER BY sort_order ASC, id ASC
         LIMIT ?"
    );
    $status = PROMO_VIDEO_STATUS_ACTIVE;
    $stmt->bind_param('ii', $status, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($rows as &$row) {
        $row['video_url'] = $row['video_file'];
        unset($row['video_file']);
    }

    return $rows;
}
