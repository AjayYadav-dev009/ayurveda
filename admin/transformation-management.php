<?php

/**
 * Admin — Customer Transformation Management
 *
 * Full CRUD for the homepage "Customer Transformations" section: add, edit,
 * delete, toggle active/inactive, and reorder. Built on the
 * `transformations` table via function/transformation.php — mirrors
 * banner-management.php's structure so all three admin pages behave and
 * look the same.
 *
 * INTEGRATION:
 *  - Drop this file into your admin area (e.g. admin/transformation-management.php).
 *  - Swap the placeholder auth check below for your project's real one.
 *  - Swap the plain <header>/<style> block for your existing admin
 *    layout/header/footer includes — everything here lives between the
 *    "ADMIN CHROME" markers so it's easy to lift out.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../function/transformation.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------------------------
// TODO: wire this into your existing admin auth guard, e.g.:
//   require_once __DIR__ . '/../function/auth.php';
//   requireAdminLogin();
// Left as a no-op placeholder so this file is self-contained to review.
// ---------------------------------------------------------------------------

$selfFile = basename(__FILE__);

function transformationFlash($type, $message)
{
    $_SESSION['transformation_flash'] = ['type' => $type, 'message' => $message];
}

function transformationRedirect($selfFile, array $params = [])
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
        transformationFlash('error', 'Your session expired, or the form was submitted twice. Please try again.');
        transformationRedirect($selfFile);
    }

    try {
        switch ($action) {
            case 'create':
                $beforeImage = uploadTransformationImage($_FILES['before_image'] ?? null, 'before');
                $afterImage = uploadTransformationImage($_FILES['after_image'] ?? null, 'after');
                if ($beforeImage === null || $afterImage === null) {
                    throw new InvalidArgumentException('Please choose both a before and an after image.');
                }
                $productId = ($_POST['product_id'] ?? '') !== '' ? (int) $_POST['product_id'] : null;
                addTransformation(
                    $conn,
                    $_POST['customer_name'] ?? '',
                    $beforeImage,
                    $afterImage,
                    $_POST['description'] ?? '',
                    $productId,
                    $_POST['duration'] ?? '',
                    isset($_POST['is_verified']) ? 1 : 0,
                    $_POST['status'] ?? TRANSFORMATION_STATUS_ACTIVE,
                    (int) ($_POST['sort_order'] ?? 0)
                );
                transformationFlash('success', 'Transformation added.');
                transformationRedirect($selfFile);

            case 'update':
                $id = (int) ($_POST['id'] ?? 0);
                // upload*() return null when no new file was chosen, which
                // tells updateTransformation() to keep the existing image.
                $beforeImage = uploadTransformationImage($_FILES['before_image'] ?? null, 'before');
                $afterImage = uploadTransformationImage($_FILES['after_image'] ?? null, 'after');
                $productId = ($_POST['product_id'] ?? '') !== '' ? (int) $_POST['product_id'] : null;
                updateTransformation(
                    $conn,
                    $id,
                    $_POST['customer_name'] ?? '',
                    $beforeImage,
                    $afterImage,
                    $_POST['description'] ?? '',
                    $productId,
                    $_POST['duration'] ?? '',
                    isset($_POST['is_verified']) ? 1 : 0,
                    $_POST['status'] ?? TRANSFORMATION_STATUS_ACTIVE,
                    (int) ($_POST['sort_order'] ?? 0)
                );
                transformationFlash('success', 'Transformation updated.');
                transformationRedirect($selfFile);

            case 'delete':
                deleteTransformation($conn, (int) ($_POST['id'] ?? 0));
                transformationFlash('success', 'Transformation deleted.');
                transformationRedirect($selfFile);

            case 'toggle_status':
                updateTransformationStatus($conn, (int) ($_POST['id'] ?? 0), $_POST['status'] ?? TRANSFORMATION_STATUS_ACTIVE);
                transformationFlash('success', 'Transformation status updated.');
                transformationRedirect($selfFile);

            case 'reorder':
                $ids = $_POST['transformation_id'] ?? [];
                $orders = $_POST['sort_order'] ?? [];
                $order = [];
                foreach ($ids as $i => $tId) {
                    $order[(int) $tId] = (int) ($orders[$i] ?? 0);
                }
                reorderTransformations($conn, $order);
                transformationFlash('success', 'Order saved.');
                transformationRedirect($selfFile);

            default:
                throw new InvalidArgumentException('Unknown action.');
        }
    } catch (InvalidArgumentException $e) {
        transformationFlash('error', $e->getMessage());
        if ($action === 'update' && !empty($_POST['id'])) {
            transformationRedirect($selfFile, ['action' => 'edit', 'id' => (int) $_POST['id']]);
        }
        if ($action === 'create') {
            transformationRedirect($selfFile, ['action' => 'new']);
        }
        transformationRedirect($selfFile);
    } catch (Exception $e) {
        transformationFlash('error', 'Something went wrong: ' . $e->getMessage());
        transformationRedirect($selfFile);
    }
}

/* =============================================================================
 * Prepare data for GET (list, new form, edit form)
 * ============================================================================= */

$viewAction = $_GET['action'] ?? 'list';

$formValues = [
    'id' => null,
    'customer_name' => '',
    'description' => '',
    'product_id' => '',
    'duration' => '',
    'is_verified' => false,
    'status' => TRANSFORMATION_STATUS_ACTIVE,
    'sort_order' => 0,
    'before_image' => null,
    'after_image' => null,
];

if ($viewAction === 'edit' && !empty($_GET['id'])) {
    $editingT = getTransformationById($conn, (int) $_GET['id']);
    if (!$editingT) {
        transformationFlash('error', 'Transformation not found.');
        transformationRedirect($selfFile);
    }
    $formValues = [
        'id' => (int) $editingT['id'],
        'customer_name' => $editingT['customer_name'] ?? '',
        'description' => $editingT['description'] ?? '',
        'product_id' => $editingT['product_id'] ?? '',
        'duration' => $editingT['duration'] ?? '',
        'is_verified' => (bool) $editingT['is_verified'],
        'status' => $editingT['status'] ?? TRANSFORMATION_STATUS_ACTIVE,
        'sort_order' => (int) $editingT['sort_order'],
        'before_image' => $editingT['before_image'] ?? null,
        'after_image' => $editingT['after_image'] ?? null,
    ];
}

try {
    $transformations = getAllTransformations($conn);
} catch (Exception $e) {
    $transformations = [];
    transformationFlash('error', 'Could not load transformations: ' . $e->getMessage());
}

try {
    $productOptions = getProductsForSelect($conn);
} catch (Exception $e) {
    $productOptions = [];
}

$flash = $_SESSION['transformation_flash'] ?? null;
unset($_SESSION['transformation_flash']);

$csrfToken = generateCSRFToken();
$showForm = in_array($viewAction, ['new', 'edit'], true);

include __DIR__ . '/include/header.php';

?>
<style>
    /* Same admin design tokens/layout as banner-management.php and
       promo-video-management.php, so all three pages match. */
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

    .thumb-pair {
        display: flex;
        gap: 4px;
    }

    .thumb {
        width: 54px;
        height: 54px;
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

    .verified-pill {
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

    .field-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .field-checkbox input {
        width: auto;
    }

    .field-checkbox label {
        margin: 0;
    }

    .current-image {
        margin-top: 8px;
    }

    .current-image img {
        max-width: 160px;
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
        <h1>Customer Transformation Management</h1>
        <?php if (!$showForm): ?>
            <a class="btn btn-primary" href="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>?action=new">+ Add Transformation</a>
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
                <?= $viewAction === 'edit' ? 'Edit Transformation' : 'Add Transformation' ?>
            </h2>
            <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="<?= $viewAction === 'edit' ? 'update' : 'create' ?>">
                <?php if ($viewAction === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= (int) $formValues['id'] ?>">
                <?php endif; ?>

                <div class="field">
                    <label for="customer_name">Customer Name <span class="muted">(e.g. "Rahul S.")</span></label>
                    <input type="text" id="customer_name" name="customer_name" value="<?= htmlspecialchars($formValues['customer_name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="before_image">
                            Before Image <?= $viewAction === 'edit' ? '(leave blank to keep current image)' : '(required)' ?>
                        </label>
                        <input type="file" id="before_image" name="before_image" accept="image/jpeg,image/png,image/webp" <?= $viewAction === 'new' ? ' required' : '' ?>>
                        <?php if ($viewAction === 'edit' && !empty($formValues['before_image'])): ?>
                            <div class="current-image">
                                <img src="<?= htmlspecialchars(getTransformationImageUrl($formValues['before_image']), ENT_QUOTES, 'UTF-8') ?>" alt="Current before image">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="after_image">
                            After Image <?= $viewAction === 'edit' ? '(leave blank to keep current image)' : '(required)' ?>
                        </label>
                        <input type="file" id="after_image" name="after_image" accept="image/jpeg,image/png,image/webp" <?= $viewAction === 'new' ? ' required' : '' ?>>
                        <?php if ($viewAction === 'edit' && !empty($formValues['after_image'])): ?>
                            <div class="current-image">
                                <img src="<?= htmlspecialchars(getTransformationImageUrl($formValues['after_image']), ENT_QUOTES, 'UTF-8') ?>" alt="Current after image">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="field">
                    <label for="description">Description <span class="muted">(short quote — shown on the card)</span></label>
                    <textarea id="description" name="description" rows="3"><?= htmlspecialchars($formValues['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="product_id">Product <span class="muted">(optional)</span></label>
                        <select id="product_id" name="product_id">
                            <option value="">— No product —</option>
                            <?php foreach ($productOptions as $product): ?>
                                <option value="<?= (int) $product['id'] ?>" <?= (string) $formValues['product_id'] === (string) $product['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="duration">Duration <span class="muted">(e.g. "8 Weeks")</span></label>
                        <input type="text" id="duration" name="duration" value="<?= htmlspecialchars($formValues['duration'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="field field-checkbox">
                    <input type="checkbox" id="is_verified" name="is_verified" value="1" <?= $formValues['is_verified'] ? 'checked' : '' ?>>
                    <label for="is_verified">Verified customer <span class="muted">(shows the "Verified Customer" badge — only check this when genuinely verified)</span></label>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach (TRANSFORMATION_STATUS_OPTIONS as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $formValues['status'] === $value ? 'selected' : '' ?>>
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
                    <?= $viewAction === 'edit' ? 'Save Changes' : 'Add Transformation' ?>
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="card">
            <?php if (empty($transformations)): ?>
                <p class="muted">No transformations yet. Click "Add Transformation" to create the first one.</p>
            <?php else: ?>
                <form method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" id="reorder-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="reorder">
                    <table>
                        <thead>
                            <tr>
                                <th>Before / After</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Status</th>
                                <th>Order</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transformations as $t):
                                $beforeUrl = getTransformationImageUrl($t['before_image'] ?? null);
                                $afterUrl = getTransformationImageUrl($t['after_image'] ?? null);
                                $isActive = ($t['status'] ?? '') === TRANSFORMATION_STATUS_ACTIVE;
                            ?>
                                <tr>
                                    <td>
                                        <div class="thumb-pair">
                                            <?php if ($beforeUrl): ?>
                                                <img class="thumb" src="<?= htmlspecialchars($beforeUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Before">
                                            <?php endif; ?>
                                            <?php if ($afterUrl): ?>
                                                <img class="thumb" src="<?= htmlspecialchars($afterUrl, ENT_QUOTES, 'UTF-8') ?>" alt="After">
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($t['customer_name'] ?: '—', ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (!empty($t['is_verified'])): ?>
                                            <br><span class="verified-pill">Verified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($t['product_name'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="status-pill <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                            <?= $isActive ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="hidden" name="transformation_id[]" value="<?= (int) $t['id'] ?>">
                                        <input class="order-input" type="number" name="sort_order[]" min="0" value="<?= (int) $t['sort_order'] ?>">
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a class="btn btn-secondary btn-sm" href="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>?action=edit&id=<?= (int) $t['id'] ?>">Edit</a>

                                            <form class="inline" method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Toggle this transformation\'s status?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $isActive ? TRANSFORMATION_STATUS_INACTIVE : TRANSFORMATION_STATUS_ACTIVE ?>">
                                                <button type="submit" class="btn btn-secondary btn-sm"><?= $isActive ? 'Deactivate' : 'Activate' ?></button>
                                            </form>

                                            <form class="inline" method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this transformation? This cannot be undone.');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
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