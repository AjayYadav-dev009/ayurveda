<?php
$pageTitle = 'Dosha Test Leads';
$activeNav = 'dosha-leads';
?>
<?php include __DIR__ . '/../../function/dosha.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>
<?php include __DIR__ . '/../include/header.php'; ?>

<?php
$filters = [
    'search' => trim((string) ($_GET['search'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
    'date'   => trim((string) ($_GET['date'] ?? '')),
];
$hasFilters = $filters['search'] !== '' || $filters['status'] !== '' || $filters['date'] !== '';

try {
    $leads  = getDoshaLeads($conn, $filters);
    $counts = getDoshaLeadCounts($conn);
} catch (Throwable $e) {
    error_log('Dosha leads list error: ' . $e->getMessage());
    $leads  = [];
    $counts = ['total' => 0, 'started' => 0, 'completed' => 0];
}

$leadCount = count($leads);
$returnQs  = htmlspecialchars($_SERVER['QUERY_STRING'] ?? '', ENT_QUOTES, 'UTF-8');
$statuses  = DOSHA_LEAD_STATUSES();
?>

<style>
    .ladm { max-width: 1200px; margin: 0 auto; }
    .ladm * { box-sizing: border-box; }

    .ladm .toolbar { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 18px; flex-wrap: wrap; }
    .ladm .toolbar h1 { margin: 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.01em; color: var(--ink); }
    .ladm .toolbar .subtitle { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 4px; font-size: 0.85rem; font-weight: 600; color: var(--muted); }
    .ladm .count-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 99px; font-size: 0.76rem; font-weight: 700; background: var(--mist); color: var(--muted); }
    .ladm .count-pill.is-started { background: var(--sky-tint); color: var(--sky); }
    .ladm .count-pill.is-completed { background: var(--leaf-tint); color: var(--leaf-dark); }

    .ladm .filters {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 16px;
        padding: 14px; background: #fff; border: 1px solid var(--line); border-radius: 14px;
    }
    .ladm .filters .field { display: flex; flex-direction: column; gap: 4px; }
    .ladm .filters label { font-size: 0.72rem; font-weight: 800; letter-spacing: 0.03em; text-transform: uppercase; color: var(--muted); }
    .ladm .filters input, .ladm .filters select {
        padding: 8px 12px; font-size: 0.86rem; font-family: inherit; border: 1px solid var(--line);
        border-radius: 9px; background: #fff; color: var(--ink); min-width: 150px;
    }
    .ladm .filters input:focus, .ladm .filters select:focus { outline: none; border-color: var(--leaf); box-shadow: 0 0 0 3px var(--leaf-tint); }
    .ladm .filters .search-field input { min-width: 220px; }
    .ladm .filters .filter-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; align-self: flex-end; }

    .ladm .btn {
        display: inline-flex; align-items: center; gap: 7px; padding: 9px 16px; font-size: 0.86rem; font-weight: 700;
        text-align: center; text-decoration: none; color: #fff; border: none; border-radius: 10px; cursor: pointer;
        transition: background-color 0.15s ease, transform 0.15s ease;
    }
    .ladm .btn svg { width: 15px; height: 15px; }
    .ladm .btn:hover { transform: translateY(-1px); }
    .ladm .btn:focus-visible { outline: 2px solid currentColor; outline-offset: 2px; }

    .ladm .btn-add { background-color: var(--leaf); }
    .ladm .btn-add:hover { background-color: var(--leaf-dark); }
    .ladm .btn-view { background: var(--sky-tint); color: var(--sky); }
    .ladm .btn-view:hover { background: #dcecf7; }
    .ladm .btn-reply { background: #fdf1de; color: #93650f; }
    .ladm .btn-reply:hover { background: #fbe6bf; }
    .ladm .btn-delete { background: #fdeceb; color: #c23b32; }
    .ladm .btn-delete:hover { background: #fadedb; }
    .ladm .btn-clear { background: var(--mist); color: var(--muted); }
    .ladm .btn-clear:hover { background: #e7ece7; }
    .ladm .btn-sm { padding: 7px 12px; font-size: 0.8rem; }

    .ladm .card { background: #fff; border: 1px solid var(--line); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 2px rgba(23, 72, 61, 0.04); }

    .ladm table { border-collapse: collapse; width: 100%; font-size: 0.9rem; }
    .ladm th, .ladm td { padding: 12px 16px; text-align: left; vertical-align: top; border-bottom: 1px solid var(--line); }
    .ladm thead th { background: var(--mist); font-size: 0.74rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; color: var(--muted); }
    .ladm tbody tr:last-child td { border-bottom: none; }
    .ladm tbody tr:hover { background: #fafcf9; }
    .ladm tr.is-started { background: #f6fbff; }
    .ladm tr.is-started:hover { background: #eef8ff; }

    .ladm .lead-name { font-weight: 700; color: var(--ink); }
    .ladm .lead-email, .ladm .lead-mobile { font-size: 0.8rem; color: var(--muted); margin-top: 2px; }
    .ladm .lead-meta { font-size: 0.85rem; color: var(--ink); }
    .ladm .lead-meta span { display: block; color: var(--muted); font-size: 0.8rem; }
    .ladm .lead-goal { font-size: 0.85rem; color: var(--muted); max-width: 220px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    .ladm .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px; border-radius: 99px; font-size: 0.78rem; font-weight: 700; white-space: nowrap; }
    .ladm .badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .ladm .badge-started { background: var(--sky-tint); color: var(--sky); }
    .ladm .badge-completed { background: var(--leaf-tint); color: var(--leaf-dark); }

    .ladm .status-select {
        margin-top: 6px; padding: 5px 9px; font-size: 0.78rem; font-weight: 700; font-family: inherit;
        border: 1px solid var(--line); border-radius: 8px; background: #fff; color: var(--ink); cursor: pointer;
    }
    .ladm .status-select:focus { outline: none; border-color: var(--leaf); }

    .ladm .muted-date { color: var(--muted); font-size: 0.85rem; white-space: nowrap; }
    .ladm .actions-cell { display: flex; gap: 6px; flex-wrap: wrap; }

    .ladm .empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 60px 20px; text-align: center; color: var(--muted); }
    .ladm .empty svg { width: 40px; height: 40px; color: #c9d8cd; }
    .ladm .empty strong { color: var(--ink); font-size: 0.98rem; }

    @media (max-width: 900px) {
        .ladm td:nth-child(2), .ladm td:nth-child(3) { display: none; }
    }
    @media (max-width: 720px) {
        .ladm .filters { flex-direction: column; align-items: stretch; }
        .ladm .filters .field, .ladm .filters input, .ladm .filters select { width: 100%; min-width: 0; }
        .ladm .filters .filter-actions { margin-left: 0; align-self: stretch; justify-content: flex-end; }
        .ladm .card { overflow-x: auto; }
    }
</style>

<div class="ladm">
    <div class="toolbar">
        <div>
            <h1>Dosha Test Leads</h1>
            <span class="subtitle">
                <?= $counts['total'] ?> total
                <span class="count-pill is-started"><?= $counts['started'] ?> started</span>
                <span class="count-pill is-completed"><?= $counts['completed'] ?> completed</span>
            </span>
        </div>
    </div>

    <form class="filters" method="get" action="index.php">
        <div class="field search-field">
            <label for="f-search">Search</label>
            <input type="text" id="f-search" name="search" placeholder="Name, email, mobile or location…" value="<?= htmlspecialchars($filters['search']) ?>">
        </div>
        <div class="field">
            <label for="f-status">Status</label>
            <select id="f-status" name="status">
                <option value="">All statuses</option>
                <?php foreach ($statuses as $s) : ?>
                    <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="f-date">Date</label>
            <input type="date" id="f-date" name="date" value="<?= htmlspecialchars($filters['date']) ?>">
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-add btn-sm">Apply</button>
            <?php if ($hasFilters) : ?>
                <a href="index.php" class="btn btn-clear btn-sm">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="card">
        <?php if ($leadCount === 0) : ?>
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5" /><path d="M5 20c1.5-4 4-6 7-6s5.5 2 7 6" /></svg>
                <strong><?= $hasFilters ? 'No leads match these filters' : 'No dosha test leads yet' ?></strong>
                <span><?= $hasFilters ? 'Try clearing a filter.' : 'Submissions from the "Take the Dosha Test" modal will show up here.' ?></span>
                <?php if ($hasFilters) : ?>
                    <a href="index.php" class="btn btn-clear" style="margin-top:6px;">Clear filters</a>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>DOB / Gender</th>
                            <th>Location / Goal</th>
                            <th>Status</th>
                            <th>Received</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead) :
                            $statusClass = $lead['status'] === 'completed' ? 'badge-completed' : 'badge-started';
                            $rowClass    = $lead['status'] === 'started' ? 'is-started' : '';
                        ?>
                            <tr class="<?= $rowClass ?>">
                                <td>
                                    <div class="lead-name"><?= htmlspecialchars($lead['full_name']) ?></div>
                                    <div class="lead-email"><?= htmlspecialchars($lead['email']) ?></div>
                                    <div class="lead-mobile"><?= htmlspecialchars($lead['mobile']) ?></div>
                                </td>
                                <td class="lead-meta">
                                    <?= htmlspecialchars($lead['date_of_birth']) ?>
                                    <span><?= htmlspecialchars($lead['gender']) ?></span>
                                </td>
                                <td class="lead-meta">
                                    <?= htmlspecialchars($lead['location']) ?>
                                    <?php if (!empty($lead['wellness_goal'])) : ?>
                                        <div class="lead-goal"><?= htmlspecialchars($lead['wellness_goal']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($lead['status'])) ?></span>
                                    <br>
                                    <form method="post" action="update-status.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
                                        <input type="hidden" name="return_page" value="index.php">
                                        <input type="hidden" name="return_qs" value="<?= $returnQs ?>">
                                        <select name="status" class="status-select" onchange="this.form.submit()" aria-label="Change status">
                                            <?php foreach ($statuses as $s) : ?>
                                                <option value="<?= $s ?>" <?= $s === $lead['status'] ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td class="muted-date"><?= htmlspecialchars($lead['created_at']) ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="view.php?id=<?= (int) $lead['id'] ?>&amp;<?= $returnQs ?>" class="btn btn-view btn-sm" title="View">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>?subject=<?= rawurlencode('Your Ayurvedic Dosha Test') ?>" class="btn btn-reply btn-sm" title="Email lead">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 7 9 6 9-6"/><rect x="3" y="5" width="18" height="14" rx="2"/></svg>
                                        </a>
                                        <form method="post" action="delete.php" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
                                            <input type="hidden" name="return_qs" value="<?= $returnQs ?>">
                                            <button type="submit" class="btn btn-delete btn-sm" onclick="return confirm('Delete this lead?');" title="Delete">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
