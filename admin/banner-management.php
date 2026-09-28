<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../function/banner.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/helper.php';

$selfFile = basename(__FILE__);

function bannerFlash($type, $message)
{
    $_SESSION['banner_flash'] = ['type' => $type, 'message' => $message];
}

function bannerRedirect($selfFile, array $params = [])
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
        bannerFlash('error', 'Your session expired, or the form was submitted twice. Please try again.');
        bannerRedirect($selfFile);
    }

    try {
        switch ($action) {
            case 'create':
                $image = uploadBannerImage($_FILES['image'] ?? null);
                if ($image === null) {
                    throw new InvalidArgumentException('Please choose a banner image.');
                }
                addBanner(
                    $conn,
                    $_POST['title'] ?? '',
                    $_POST['subtitle'] ?? '',
                    $image,
                    $_POST['button_text'] ?? '',
                    $_POST['button_url'] ?? '',
                    $_POST['position'] ?? '',
                    (int) ($_POST['status'] ?? BANNER_STATUS_ACTIVE),
                    (int) ($_POST['sort_order'] ?? 0)
                );
                bannerFlash('success', 'Banner added.');
                bannerRedirect($selfFile);

            case 'update':
                $id = (int) ($_POST['id'] ?? 0);
                // uploadBannerImage() returns null when no new file was
                // chosen, which tells updateBanner() to keep the existing image.
                $image = uploadBannerImage($_FILES['image'] ?? null);
                updateBanner(
                    $conn,
                    $id,
                    $_POST['title'] ?? '',
                    $_POST['subtitle'] ?? '',
                    $image,
                    $_POST['button_text'] ?? '',
                    $_POST['button_url'] ?? '',
                    $_POST['position'] ?? '',
                    (int) ($_POST['status'] ?? BANNER_STATUS_ACTIVE),
                    (int) ($_POST['sort_order'] ?? 0)
                );
                bannerFlash('success', 'Banner updated.');
                bannerRedirect($selfFile);

            case 'delete':
                deleteBanner($conn, (int) ($_POST['id'] ?? 0));
                bannerFlash('success', 'Banner deleted.');
                bannerRedirect($selfFile);

            case 'toggle_status':
                updateBannerStatus($conn, (int) ($_POST['id'] ?? 0), (int) ($_POST['status'] ?? BANNER_STATUS_ACTIVE));
                bannerFlash('success', 'Banner status updated.');
                bannerRedirect($selfFile);

            case 'reorder':
                $ids = $_POST['banner_id'] ?? [];
                $orders = $_POST['sort_order'] ?? [];
                $order = [];
                foreach ($ids as $i => $bannerId) {
                    $order[(int) $bannerId] = (int) ($orders[$i] ?? 0);
                }
                reorderBanners($conn, $order);
                bannerFlash('success', 'Banner order saved.');
                bannerRedirect($selfFile);

            default:
                throw new InvalidArgumentException('Unknown action.');
        }
    } catch (InvalidArgumentException $e) {
        bannerFlash('error', $e->getMessage());
        if ($action === 'update' && !empty($_POST['id'])) {
            bannerRedirect($selfFile, ['action' => 'edit', 'id' => (int) $_POST['id']]);
        }
        if ($action === 'create') {
            bannerRedirect($selfFile, ['action' => 'new']);
        }
        bannerRedirect($selfFile);
    } catch (Exception $e) {
        bannerFlash('error', 'Something went wrong: ' . $e->getMessage());
        bannerRedirect($selfFile);
    }
}

/* =============================================================================
 * Prepare data for GET (list, new form, edit form)
 * ============================================================================= */

$viewAction = $_GET['action'] ?? 'list';

$formValues = [
    'id' => null,
    'title' => '',
    'subtitle' => '',
    'button_text' => '',
    'button_url' => '',
    'position' => '',
    'status' => BANNER_STATUS_ACTIVE,
    'sort_order' => 0,
    'image' => null,
];

if ($viewAction === 'edit' && !empty($_GET['id'])) {
    $editingBanner = getBannerById($conn, (int) $_GET['id']);
    if (!$editingBanner) {
        bannerFlash('error', 'Banner not found.');
        bannerRedirect($selfFile);
    }
    $formValues = [
        'id' => (int) $editingBanner['id'],
        'title' => $editingBanner['title'] ?? '',
        'subtitle' => $editingBanner['subtitle'] ?? '',
        'button_text' => $editingBanner['button_text'] ?? '',
        'button_url' => $editingBanner['button_url'] ?? '',
        'position' => $editingBanner['position'] ?? '',
        'status' => (int) $editingBanner['status'],
        'sort_order' => (int) $editingBanner['sort_order'],
        'image' => $editingBanner['image'] ?? null,
    ];
}

try {
    $banners = getAllBanners($conn);
} catch (Exception $e) {
    $banners = [];
    bannerFlash('error', 'Could not load banners: ' . $e->getMessage());
}

$flash = $_SESSION['banner_flash'] ?? null;
unset($_SESSION['banner_flash']);

$csrfToken = generateCSRFToken();
$showForm = in_array($viewAction, ['new', 'edit'], true);

$pageTitle = 'Banner Management';
$activeNav = 'banners';

include __DIR__ . '/include/header.php';

?>
<style>
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
        height: 45px;
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
        <h1>Banner Management</h1>
        <?php if (!$showForm): ?>
            <a class="btn btn-primary" href="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>?action=new">+ Add Banner</a>
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
                <?= $viewAction === 'edit' ? 'Edit Banner' : 'Add Banner' ?>
            </h2>
            <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="<?= $viewAction === 'edit' ? 'update' : 'create' ?>">
                <?php if ($viewAction === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= (int) $formValues['id'] ?>">
                <?php endif; ?>

                <div class="field">
                    <label for="image">
                        Banner Image <?= $viewAction === 'edit' ? '(leave blank to keep current image)' : '(required)' ?>
                    </label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" <?= $viewAction === 'new' ? ' required' : '' ?>>
                    <?php if ($viewAction === 'edit' && !empty($formValues['image'])): ?>
                        <div class="current-image">
                            <img src="<?= htmlspecialchars(getBannerImageUrl($formValues['image']), ENT_QUOTES, 'UTF-8') ?>" alt="Current banner image">
                        </div>
                    <?php endif; ?>
                    <p class="muted">
                        The entire visual (heading, CTA, icons) should already be baked into this
                        image — it is displayed as-is on the homepage hero, with no text overlaid by the site.
                    </p>
                </div>

                <div class="field">
                    <label for="title">Title <span class="muted">(used only as image alt text — not shown on the page)</span></label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($formValues['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field">
                    <label for="button_url">Link URL <span class="muted">(where the banner goes when clicked — leave blank for a non-clickable banner)</span></label>
                    <input type="text" id="button_url" name="button_url" placeholder="/category/hair-care or /product/example-product or /#consultation"
                        value="<?= htmlspecialchars($formValues['button_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="position">Page <span class="muted">(which page this banner shows on)</span></label>
                        <select id="position" name="position">
                            <option value="">— Select a page —</option>
                            <?php foreach (BANNER_PAGE_OPTIONS as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= ($formValues['position'] ?? '') === $value ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach (BANNER_STATUS_OPTIONS as $value => $label): ?>
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

                <!-- Kept for schema compatibility; not rendered on the public hero. -->
                <div class="field">
                    <label for="subtitle">Subtitle <span class="muted">(not displayed — legacy field)</span></label>
                    <textarea id="subtitle" name="subtitle" rows="2"><?= htmlspecialchars($formValues['subtitle'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="field">
                    <label for="button_text">Button Text <span class="muted">(not displayed — legacy field)</span></label>
                    <input type="text" id="button_text" name="button_text" value="<?= htmlspecialchars($formValues['button_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <button type="submit" class="btn btn-primary">
                    <?= $viewAction === 'edit' ? 'Save Changes' : 'Add Banner' ?>
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="card">
            <?php if (empty($banners)): ?>
                <p class="muted">No banners yet. Click "Add Banner" to create the first one.</p>
            <?php else: ?>
                <form method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" id="reorder-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="reorder">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Page</th>
                                <th>Link URL</th>
                                <th>Status</th>
                                <th>Order</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($banners as $banner):
                                $imageUrl = getBannerImageUrl($banner['image'] ?? null);
                                $isActive = (int) $banner['status'] === BANNER_STATUS_ACTIVE;
                            ?>
                                <tr>
                                    <td>
                                        <?php if ($imageUrl): ?>
                                            <img class="thumb" src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Banner Image">
                                        <?php else: ?>
                                            <span class="muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($banner['title'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php $pageLabel = BANNER_PAGE_OPTIONS[$banner['position']] ?? ($banner['position'] ?: null); ?>
                                        <?= $pageLabel ? htmlspecialchars($pageLabel, ENT_QUOTES, 'UTF-8') : '<span class="muted">Not assigned</span>' ?>
                                    </td>
                                    <td><?= htmlspecialchars($banner['button_url'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="status-pill <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                            <?= $isActive ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="hidden" name="banner_id[]" value="<?= (int) $banner['id'] ?>">
                                        <input class="order-input" type="number" name="sort_order[]" min="0" value="<?= (int) $banner['sort_order'] ?>">
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a class="btn btn-secondary btn-sm" href="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>?action=edit&id=<?= (int) $banner['id'] ?>">Edit</a>

                                            <form class="inline" method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Toggle this banner\'s status?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?= (int) $banner['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $isActive ? BANNER_STATUS_INACTIVE : BANNER_STATUS_ACTIVE ?>">
                                                <button type="submit" class="btn btn-secondary btn-sm"><?= $isActive ? 'Deactivate' : 'Activate' ?></button>
                                            </form>

                                            <form class="inline" method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this banner? This cannot be undone.');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $banner['id'] ?>">
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