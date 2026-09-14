<?php

/**
 * Admin — Team Management
 *
 * Full CRUD for "Our Team Of Ayurvedic Experts" — the homepage slider of
 * doctor/vaidya profiles: add, edit, delete, toggle active/inactive, and
 * reorder. Built on the new `team_members` table
 * (migration_add_team_members_table.sql) via function/team.php — same
 * shape as banner-management.php, so it drops in next to it.
 *
 * INTEGRATION:
 *  - Drop this file into your admin area (e.g. admin/team-management.php).
 *  - Swap the placeholder auth check below for your project's real one.
 *  - Swap the plain <header>/<style> block for your existing admin
 *    layout/header/footer includes — everything team-specific lives
 *    between the "ADMIN CHROME" markers so it's easy to lift out.
 */

require_once __DIR__ . '/../function/team.php';
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

// Computed instead of hardcoded, so every link/redirect below still works
// no matter what this file is actually named or renamed to.
$selfFile = basename(__FILE__);

function teamFlash($type, $message)
{
    $_SESSION['team_flash'] = ['type' => $type, 'message' => $message];
}

function teamRedirect($selfFile, array $params = [])
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
        teamFlash('error', 'Your session expired, or the form was submitted twice. Please try again.');
        teamRedirect($selfFile);
    }

    try {
        switch ($action) {
            case 'create':
                $image = uploadTeamMemberImage($_FILES['image'] ?? null);
                addTeamMember(
                    $conn,
                    $_POST['name'] ?? '',
                    $_POST['designation'] ?? '',
                    $_POST['bio'] ?? '',
                    $image,
                    (int) ($_POST['status'] ?? TEAM_STATUS_ACTIVE),
                    (int) ($_POST['sort_order'] ?? 0)
                );
                teamFlash('success', 'Team member added.');
                teamRedirect($selfFile);

            case 'update':
                $id = (int) ($_POST['id'] ?? 0);
                // uploadTeamMemberImage() returns null when no new file was
                // chosen, which tells updateTeamMember() to keep the existing image.
                $image = uploadTeamMemberImage($_FILES['image'] ?? null);
                updateTeamMember(
                    $conn,
                    $id,
                    $_POST['name'] ?? '',
                    $_POST['designation'] ?? '',
                    $_POST['bio'] ?? '',
                    $image,
                    (int) ($_POST['status'] ?? TEAM_STATUS_ACTIVE),
                    (int) ($_POST['sort_order'] ?? 0)
                );
                teamFlash('success', 'Team member updated.');
                teamRedirect($selfFile);

            case 'delete':
                deleteTeamMember($conn, (int) ($_POST['id'] ?? 0));
                teamFlash('success', 'Team member deleted.');
                teamRedirect($selfFile);

            case 'toggle_status':
                updateTeamMemberStatus($conn, (int) ($_POST['id'] ?? 0), (int) ($_POST['status'] ?? TEAM_STATUS_ACTIVE));
                teamFlash('success', 'Team member status updated.');
                teamRedirect($selfFile);

            case 'reorder':
                $ids = $_POST['team_member_id'] ?? [];
                $orders = $_POST['sort_order'] ?? [];
                $order = [];
                foreach ($ids as $i => $memberId) {
                    $order[(int) $memberId] = (int) ($orders[$i] ?? 0);
                }
                reorderTeamMembers($conn, $order);
                teamFlash('success', 'Team order saved.');
                teamRedirect($selfFile);

            default:
                throw new InvalidArgumentException('Unknown action.');
        }
    } catch (InvalidArgumentException $e) {
        teamFlash('error', $e->getMessage());
        if ($action === 'update' && !empty($_POST['id'])) {
            teamRedirect($selfFile, ['action' => 'edit', 'id' => (int) $_POST['id']]);
        }
        if ($action === 'create') {
            teamRedirect($selfFile, ['action' => 'new']);
        }
        teamRedirect($selfFile);
    } catch (Exception $e) {
        teamFlash('error', 'Something went wrong: ' . $e->getMessage());
        teamRedirect($selfFile);
    }
}

/* =============================================================================
 * Prepare data for GET (list, new form, edit form)
 * ============================================================================= */

$viewAction = $_GET['action'] ?? 'list';

$formValues = [
    'id' => null,
    'name' => '',
    'designation' => '',
    'bio' => '',
    'status' => TEAM_STATUS_ACTIVE,
    'sort_order' => 0,
    'image' => null,
];

if ($viewAction === 'edit' && !empty($_GET['id'])) {
    $editingMember = getTeamMemberById($conn, (int) $_GET['id']);
    if (!$editingMember) {
        teamFlash('error', 'Team member not found.');
        teamRedirect($selfFile);
    }
    $formValues = [
        'id' => (int) $editingMember['id'],
        'name' => $editingMember['name'] ?? '',
        'designation' => $editingMember['designation'] ?? '',
        'bio' => $editingMember['bio'] ?? '',
        'status' => (int) $editingMember['status'],
        'sort_order' => (int) $editingMember['sort_order'],
        'image' => $editingMember['image'] ?? null,
    ];
}

try {
    $teamMembers = getAllTeamMembers($conn);
} catch (Exception $e) {
    $teamMembers = [];
    teamFlash('error', 'Could not load team members: ' . $e->getMessage());
}

$flash = $_SESSION['team_flash'] ?? null;
unset($_SESSION['team_flash']);

$csrfToken = generateCSRFToken();
$showForm = in_array($viewAction, ['new', 'edit'], true);

?>
<style>
    body {
        font-family: system-ui, -apple-system, sans-serif;
        background: #f5f6f8;
        margin: 0;
        color: #1f2430;
    }

    .admin-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 24px 20px 60px;
    }

    h1 {
        font-size: 22px;
        margin: 0 0 20px;
    }

    .card {
        background: #fff;
        border: 1px solid #e3e5ea;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .flash {
        padding: 10px 14px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
    }

    .flash-success {
        background: #e6f6ea;
        color: #1f7a3d;
        border: 1px solid #bfe8cc;
    }

    .flash-error {
        background: #fdeceb;
        color: #b3261e;
        border: 1px solid #f6c6c2;
    }

    .btn {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 6px;
        border: 1px solid transparent;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-primary {
        background: #2f6f4e;
        color: #fff;
    }

    .btn-secondary {
        background: #fff;
        color: #2f2f2f;
        border-color: #d7d9de;
    }

    .btn-danger {
        background: #fff;
        color: #b3261e;
        border-color: #f0c6c2;
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
        padding: 10px 8px;
        border-bottom: 1px solid #edeef1;
        font-size: 13px;
        vertical-align: middle;
    }

    th {
        color: #6b7280;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .thumb {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 50%;
        background: #f0f0f0;
        display: block;
    }

    .bio-preview {
        max-width: 320px;
        color: #6b7280;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .status-pill {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-active {
        background: #e6f6ea;
        color: #1f7a3d;
    }

    .status-inactive {
        background: #f1f1f3;
        color: #6b7280;
    }

    .order-input {
        width: 56px;
        padding: 4px 6px;
        border: 1px solid #d7d9de;
        border-radius: 5px;
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
        font-weight: 600;
        margin-bottom: 5px;
    }

    .field input[type=text],
    .field input[type=number],
    .field input[type=file],
    .field select,
    .field textarea {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #d7d9de;
        border-radius: 6px;
        font-size: 14px;
        box-sizing: border-box;
        font-family: inherit;
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
        width: 90px;
        height: 90px;
        object-fit: cover;
        border-radius: 50%;
        border: 1px solid #e3e5ea;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .muted {
        color: #6b7280;
        font-size: 12px;
    }
</style>

<div class="admin-wrap">
    <div class="top-bar">
        <h1>Team Management</h1>
        <?php if (!$showForm): ?>
            <a class="btn btn-primary" href="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>?action=new">+ Add Team Member</a>
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
                <?= $viewAction === 'edit' ? 'Edit Team Member' : 'Add Team Member' ?>
            </h2>
            <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="<?= $viewAction === 'edit' ? 'update' : 'create' ?>">
                <?php if ($viewAction === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= (int) $formValues['id'] ?>">
                <?php endif; ?>

                <div class="field">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" placeholder="Dr. Priyanka Jagota"
                        value="<?= htmlspecialchars($formValues['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="field">
                    <label for="designation">Designation <span class="muted">(e.g. Maharishi Expert Vaidya)</span></label>
                    <input type="text" id="designation" name="designation"
                        value="<?= htmlspecialchars($formValues['designation'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="5" placeholder="A few sentences on their qualifications, experience, and areas of specialisation."><?= htmlspecialchars($formValues['bio'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="field">
                    <label for="image">
                        Photo <?= $viewAction === 'edit' ? '(leave blank to keep current photo)' : '(optional)' ?>
                    </label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                    <?php if ($viewAction === 'edit' && !empty($formValues['image'])): ?>
                        <div class="current-image">
                            <img src="<?= htmlspecialchars(getTeamMemberImageUrl($formValues['image']), ENT_QUOTES, 'UTF-8') ?>" alt="Current photo">
                        </div>
                    <?php endif; ?>
                    <p class="muted">Shown as a square/portrait crop on the homepage slider. If left empty, a placeholder icon is shown instead.</p>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach (TEAM_STATUS_OPTIONS as $value => $label): ?>
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
                    <?= $viewAction === 'edit' ? 'Save Changes' : 'Add Team Member' ?>
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="card">
            <?php if (empty($teamMembers)): ?>
                <p class="muted">No team members yet. Click "Add Team Member" to create the first one.</p>
            <?php else: ?>
                <form method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" id="reorder-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="reorder">
                    <table>
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Bio</th>
                                <th>Status</th>
                                <th>Order</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teamMembers as $member):
                                $imageUrl = getTeamMemberImageUrl($member['image'] ?? null);
                                $isActive = (int) $member['status'] === TEAM_STATUS_ACTIVE;
                            ?>
                                <tr>
                                    <td>
                                        <?php if ($imageUrl): ?>
                                            <img class="thumb" src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
                                        <?php else: ?>
                                            <span class="muted">No photo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($member['designation'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="bio-preview"><?= htmlspecialchars($member['bio'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="status-pill <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                            <?= $isActive ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="hidden" name="team_member_id[]" value="<?= (int) $member['id'] ?>">
                                        <input class="order-input" type="number" name="sort_order[]" min="0" value="<?= (int) $member['sort_order'] ?>">
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a class="btn btn-secondary btn-sm" href="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>?action=edit&id=<?= (int) $member['id'] ?>">Edit</a>

                                            <form class="inline" method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Toggle this team member\'s status?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $isActive ? TEAM_STATUS_INACTIVE : TEAM_STATUS_ACTIVE ?>">
                                                <button type="submit" class="btn btn-secondary btn-sm"><?= $isActive ? 'Deactivate' : 'Activate' ?></button>
                                            </form>

                                            <form class="inline" method="post" action="<?= htmlspecialchars($selfFile, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this team member? This cannot be undone.');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
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
                        <span class="muted">Lower numbers show first in the homepage slider.</span>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>