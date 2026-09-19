<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../function/promotional-video.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/helper.php';

$selfFile = basename(__FILE__);

function promoVideoFlash($type, $message)
{
    $_SESSION['promo_video_flash'] = ['type' => $type, 'message' => $message];
}

function promoVideoRedirect($selfFile, array $params = [])
{
    $query = http_build_query($params);
    redirect($selfFile . ($query ? '?' . $query : ''));
}

/* =============================================================================
 * Handle POST actions: create, update, delete, toggle_status, reorder
 * ============================================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        promoVideoFlash('error', 'Your session expired, or the form was submitted twice. Please try again.');
        promoVideoRedirect($selfFile);
    }

    try {
        switch ($action) {
            case 'create':
                $videoFile = uploadPromoVideoFile($_FILES['video_file'] ?? null);
                $thumbnail = uploadPromoVideoThumbnail($_FILES['thumbnail'] ?? null);
                addPromoVideo(
                    $conn,
                    $_POST['title'] ?? '',
                    $_POST['description'] ?? '',
                    $_POST['video_type'] ?? 'upload',
                    $_POST['video_url'] ?? '',
                    $videoFile,
                    $thumbnail,
                    $_POST['button_text'] ?? '',
                    $_POST['button_url'] ?? '',
                    $_POST['orientation'] ?? '',
                    (int) ($_POST['status'] ?? PROMO_VIDEO_STATUS_ACTIVE),
                    (int) ($_POST['sort_order'] ?? 0)
                );
                promoVideoFlash('success', 'Promotional video added.');
                promoVideoRedirect($selfFile);

            case 'update':
                $id = (int) ($_POST['id'] ?? 0);
                // upload*() return null when no new file was chosen, which
                // tells updatePromoVideo() to keep the existing file.
                $videoFile = uploadPromoVideoFile($_FILES['video_file'] ?? null);
                $thumbnail = uploadPromoVideoThumbnail($_FILES['thumbnail'] ?? null);
                updatePromoVideo(
                    $conn,
                    $id,
                    $_POST['title'] ?? '',
                    $_POST['description'] ?? '',
                    $_POST['video_type'] ?? 'upload',
                    $_POST['video_url'] ?? '',
                    $videoFile,
                    $thumbnail,
                    $_POST['button_text'] ?? '',
                    $_POST['button_url'] ?? '',
                    $_POST['orientation'] ?? '',
                    (int) ($_POST['status'] ?? PROMO_VIDEO_STATUS_ACTIVE),
                    (int) ($_POST['sort_order'] ?? 0)
                );
                promoVideoFlash('success', 'Promotional video updated.');
                promoVideoRedirect($selfFile);

            case 'delete':
                deletePromoVideo($conn, (int) ($_POST['id'] ?? 0));
                promoVideoFlash('success', 'Promotional video deleted.');
                promoVideoRedirect($selfFile);

            case 'toggle_status':
                updatePromoVideoStatus($conn, (int) ($_POST['id'] ?? 0), (int) ($_POST['status'] ?? PROMO_VIDEO_STATUS_ACTIVE));
                promoVideoFlash('success', 'Promotional video status updated.');
                promoVideoRedirect($selfFile);

            case 'reorder':
                $ids = $_POST['video_id'] ?? [];
                $orders = $_POST['sort_order'] ?? [];
                $order = [];
                foreach ($ids as $i => $videoId) {
                    $order[(int) $videoId] = (int) ($orders[$i] ?? 0);
                }
                reorderPromoVideos($conn, $order);
                promoVideoFlash('success', 'Video order saved.');
                promoVideoRedirect($selfFile);

            default:
                throw new InvalidArgumentException('Unknown action.');
        }
    } catch (InvalidArgumentException $e) {
        promoVideoFlash('error', $e->getMessage());
        if ($action === 'update' && !empty($_POST['id'])) {
            promoVideoRedirect($selfFile, ['action' => 'edit', 'id' => (int) $_POST['id']]);
        }
        if ($action === 'create') {
            promoVideoRedirect($selfFile, ['action' => 'new']);
        }
        promoVideoRedirect($selfFile);
    } catch (Exception $e) {
        promoVideoFlash('error', 'Something went wrong: ' . $e->getMessage());
        promoVideoRedirect($selfFile);
    }
}

/* =============================================================================
 * Prepare data for GET (list, new form, edit form)
 * ============================================================================= */

$viewAction = $_GET['action'] ?? 'list';

$formValues = [
    'id' => null,
    'title' => '',
    'description' => '',
    'video_type' => 'upload',
    'video_url' => '',
    'button_text' => '',
    'button_url' => '',
    'orientation' => '',
    'status' => PROMO_VIDEO_STATUS_ACTIVE,
    'sort_order' => 0,
    'video_file' => null,
    'thumbnail' => null,
];

if ($viewAction === 'edit' && !empty($_GET['id'])) {
    $editingVideo = getPromoVideoById($conn, (int) $_GET['id']);
    if (!$editingVideo) {
        promoVideoFlash('error', 'Promotional video not found.');
        promoVideoRedirect($selfFile);
    }
    $formValues = [
        'id' => (int) $editingVideo['id'],
        'title' => $editingVideo['title'] ?? '',
        'description' => $editingVideo['description'] ?? '',
        'video_type' => $editingVideo['video_type'] ?? 'upload',
        'video_url' => $editingVideo['video_url'] ?? '',
        'button_text' => $editingVideo['button_text'] ?? '',
        'button_url' => $editingVideo['button_url'] ?? '',
        'orientation' => $editingVideo['orientation'] ?? '',
        'status' => (int) $editingVideo['status'],
        'sort_order' => (int) $editingVideo['sort_order'],
        'video_file' => $editingVideo['video_file'] ?? null,
        'thumbnail' => $editingVideo['thumbnail'] ?? null,
    ];
}

try {
    $videos = getAllPromoVideos($conn);
} catch (Exception $e) {
    $videos = [];
    promoVideoFlash('error', 'Could not load promotional videos: ' . $e->getMessage());
}

$flash = $_SESSION['promo_video_flash'] ?? null;
unset($_SESSION['promo_video_flash']);

$csrfToken = generateCSRFToken();
$showForm = in_array($viewAction, ['new', 'edit'], true);

include __DIR__ . '/include/header.php';

?>
<style>
    /* Same admin design tokens/layout as banner-management.php, so both
       pages look and feel identical. */
    :root {
        --leaf: #2f9e6e;
        --leaf-dark: #22794f;
        --leaf-tint: #e7f6ee;
        --ink: #1c2b3a;
        --sky: #0f6fb0;
        --sky-tint: #eaf4fb;
        --paper: #ffffff;
        --mist: #f4f8fb;
        --line: #e1e9f0;
        --muted: #64798c;
        --danger: #c8412f;
        --danger-tint: #fbebe8;
    }

    body {
        background: var(--mist);
        margin: 0;
        color: var(--ink);
    }

    .admin-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 24px 20px 60px;
    }

    h1 {
        font-size: 34px;
        font-weight: 800;
        letter-spacing: -0.01em;
        margin: 0 0 20px;
        color: var(--ink);
    }

    .card {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 22px;
        margin-bottom: 20px;
        box-shadow: 0 1px 2px rgba(28, 43, 58, 0.04);
    }

    .flash {
        padding: 11px 15px;
        border-radius: 10px;
        margin-bottom: 16px;
        font-size: 14px;
        font-weight: 600;
        border-left: 3px solid transparent;
    }

    .flash-success {
        background: var(--leaf-tint);
        color: var(--leaf-dark);
        border-left-color: var(--leaf);
    }

    .flash-error {
        background: var(--danger-tint);
        color: var(--danger);
        border-left-color: var(--danger);
    }

    .btn {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .btn-primary {
        background: var(--leaf);
        color: #fff;
    }

    .btn-primary:hover {
        background: var(--leaf-dark);
    }

    .btn-secondary {
        background: #fff;
        color: var(--sky);
        border-color: var(--line);
    }

    .btn-secondary:hover {
        background: var(--sky-tint);
        border-color: var(--sky);
    }

    .btn-danger {
        background: #fff;
        color: var(--danger);
        border-color: #f0cdc6;
    }

    .btn-danger:hover {
        background: var(--danger-tint);
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        text-align: left;
        padding: 11px 9px;
        border-bottom: 1px solid var(--line);
        font-size: 13px;
        vertical-align: middle;
    }

    th {
        color: var(--muted);
        font-weight: 700;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    tbody tr:hover {
        background: var(--sky-tint);
    }

    .thumb {
        width: 90px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        background: var(--mist);
        border: 1px solid var(--line);
        display: block;
    }

    .status-pill {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-active {
        background: var(--leaf-tint);
        color: var(--leaf-dark);
    }

    .status-inactive {
        background: var(--mist);
        color: var(--muted);
    }

    .type-pill {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        background: var(--sky-tint);
        color: var(--sky);
    }

    .order-input {
        width: 56px;
        padding: 4px 6px;
        border: 1px solid var(--line);
        border-radius: 6px;
        font-family: inherit;
    }

    .order-input:focus,
    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        outline: none;
        border-color: var(--sky);
        box-shadow: 0 0 0 3px var(--sky-tint);
    }

    .row-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    form.inline {
        display: inline;
    }

    .field {
        margin-bottom: 14px;
    }

    .field label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 5px;
        color: var(--ink);
    }

    .field input[type=text],
    .field input[type=number],
    .field input[type=file],
    .field select,
    .field textarea {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid var(--line);
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        color: var(--ink);
        background: #fff;
        box-sizing: border-box;
    }

    .field-row {
        display: flex;
        gap: 16px;
    }

    .field-row .field {
        flex: 1;
    }

    .current-image {
        margin-top: 8px;
    }

    .current-image img {
        max-width: 220px;
        border-radius: 8px;
        border: 1px solid var(--line);
    }

    .current-file {
        margin-top: 6px;
        font-size: 12.5px;
        color: var(--muted);
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .muted {
        color: var(--muted);
        font-size: 12px;
    }
</style>

<div class="admin-wrap">
    <div class="top-bar">
        <h1>Promotional Video Management</h1>
        <?php if (!$showForm): ?>
            <a class="btn btn-primary" href="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>?action=new">+ Add Video</a>
        <?php else: ?>
            <a class="btn btn-secondary" href="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>">&larr; Back to list</a>
        <?php endif; ?>
    </div>

    <?php if ($flash): ?>
        <div class="flash flash-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <!-- ==================== END ADMIN CHROME ==================== -->

    <?php if ($showForm): ?>
        <div class="card">
            <h2 style="font-size:16px;margin-top:0;">
                <?= $viewAction === 'edit' ? 'Edit Promotional Video' : 'Add Promotional Video' ?>
            </h2>
            <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="<?= $viewAction === 'edit' ? 'update' : 'create' ?>">
                <?php if ($viewAction === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= (int) $formValues['id'] ?>">
                <?php endif; ?>

                <div class="field">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($formValues['title'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="2"><?= htmlspecialchars($formValues['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="field">
                    <label for="video_type">Video Source</label>
                    <select id="video_type" name="video_type" onchange="promoVideoToggleSource(this.value)">
                        <?php foreach (PROMO_VIDEO_TYPE_OPTIONS as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $formValues['video_type'] === $value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="muted" style="margin:6px 0 0;">Only "Uploaded video file" entries appear in the homepage carousel — it plays videos directly and doesn't embed YouTube/Vimeo. A link is still saved either way, for future use elsewhere on the site.</p>
                </div>

                <div class="field" id="field-video-file">
                    <label for="video_file">
                        Video File <?= $viewAction === 'edit' ? '(leave blank to keep current file)' : '' ?>
                    </label>
                    <input type="file" id="video_file" name="video_file" accept="video/mp4,video/webm,video/quicktime">
                    <?php if ($viewAction === 'edit' && !empty($formValues['video_file'])): ?>
                        <p class="current-file">Current file: <?= htmlspecialchars(basename($formValues['video_file']), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <div class="field" id="field-video-url">
                    <label for="video_url">Video URL <span class="muted">(YouTube or Vimeo link)</span></label>
                    <input type="text" id="video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=..."
                        value="<?= htmlspecialchars($formValues['video_url'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field">
                    <label for="thumbnail">
                        Thumbnail Image <?= $viewAction === 'edit' ? '(leave blank to keep current thumbnail)' : '' ?>
                    </label>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/webp">
                    <?php if ($viewAction === 'edit' && !empty($formValues['thumbnail'])): ?>
                        <div class="current-image">
                            <img src="<?= htmlspecialchars(getPromoVideoThumbnailUrl($formValues['thumbnail']), ENT_QUOTES, 'UTF-8') ?>" alt="Current thumbnail">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="orientation">Orientation <span class="muted">(only matters for uploaded video files — leave as Auto-detect if unsure)</span></label>
                    <select id="orientation" name="orientation">
                        <option value="" <?= $formValues['orientation'] === '' ? 'selected' : '' ?>>Auto-detect</option>
                        <option value="horizontal" <?= $formValues['orientation'] === 'horizontal' ? 'selected' : '' ?>>Horizontal (16:9)</option>
                        <option value="vertical" <?= $formValues['orientation'] === 'vertical' ? 'selected' : '' ?>>Vertical (9:16)</option>
                    </select>
                </div>

                <div class="field">
                    <label for="button_url">Button Link <span class="muted">(where the button below the video goes — leave blank to hide it)</span></label>
                    <input type="text" id="button_url" name="button_url" placeholder="/category/hair-care or /product/example-product"
                        value="<?= htmlspecialchars($formValues['button_url'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field">
                    <label for="button_text">Button Text</label>
                    <input type="text" id="button_text" name="button_text" placeholder="Shop Now" value="<?= htmlspecialchars($formValues['button_text'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach (PROMO_VIDEO_STATUS_OPTIONS as $value => $label): ?>
                                <option value="<?= (int) $value ?>" <?= (int) $formValues['status'] === (int) $value ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" min="0" value="<?= (int) $formValues['sort_order'] ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <?= $viewAction === 'edit' ? 'Save Changes' : 'Add Video' ?>
                </button>
            </form>
        </div>

        <script>
            // Show only the field relevant to the chosen video source —
            // both inputs still post either way, but the backend only
            // requires the one matching video_type (see promotional_video.php).
            function promoVideoToggleSource(type) {
                document.getElementById('field-video-file').style.display = type === 'upload' ? '' : 'none';
                document.getElementById('field-video-url').style.display = type === 'upload' ? 'none' : '';
            }
            promoVideoToggleSource(document.getElementById('video_type').value);
        </script>
    <?php else: ?>
        <div class="card">
            <?php if (empty($videos)): ?>
                <p class="muted">No promotional videos yet. Click "Add Video" to create the first one.</p>
            <?php else: ?>
                <form method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" id="reorder-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="reorder">
                    <table>
                        <thead>
                            <tr>
                                <th>Thumbnail</th>
                                <th>Title</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Order</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($videos as $video):
                                $thumbUrl = getPromoVideoThumbnailUrl($video['thumbnail'] ?? null);
                                $isActive = (int) $video['status'] === PROMO_VIDEO_STATUS_ACTIVE;
                                $typeLabel = PROMO_VIDEO_TYPE_OPTIONS[$video['video_type']] ?? $video['video_type'];
                            ?>
                                <tr>
                                    <td>
                                        <?php if ($thumbUrl): ?>
                                            <img class="thumb" src="<?= htmlspecialchars($thumbUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Video thumbnail">
                                        <?php else: ?>
                                            <span class="muted">No thumbnail</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($video['title'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="type-pill"><?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td>
                                        <span class="status-pill <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                            <?= $isActive ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="hidden" name="video_id[]" value="<?= (int) $video['id'] ?>">
                                        <input class="order-input" type="number" name="sort_order[]" min="0" value="<?= (int) $video['sort_order'] ?>">
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a class="btn btn-secondary btn-sm" href="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>?action=edit&id=<?= (int) $video['id'] ?>">Edit</a>

                                            <form class="inline" method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Toggle this video\'s status?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?= (int) $video['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $isActive ? PROMO_VIDEO_STATUS_INACTIVE : PROMO_VIDEO_STATUS_ACTIVE ?>">
                                                <button type="submit" class="btn btn-secondary btn-sm"><?= $isActive ? 'Deactivate' : 'Activate' ?></button>
                                            </form>

                                            <form class="inline" method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this video? This cannot be undone.');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $video['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="margin-top:14px;">
                        <button type="submit" class="btn btn-primary btn-sm">Save Order</button>
                        <span class="muted">Lower numbers show first on the homepage.</span>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>