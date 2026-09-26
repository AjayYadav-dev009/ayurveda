<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/dosha.php';

$id   = (int) ($_GET['id'] ?? 0);
$lead = $id > 0 ? getDoshaLeadById($conn, $id) : null;

// Preserve the inbox's filters when the person clicks "Back to leads".
$backQs = '';
foreach (['search', 'status', 'date'] as $key) {
    if (!empty($_GET[$key])) {
        $backQs .= ($backQs === '' ? '' : '&') . $key . '=' . rawurlencode((string) $_GET[$key]);
    }
}
$backUrl = 'index.php' . ($backQs !== '' ? '?' . $backQs : '');

$pageTitle = $lead ? 'Dosha Lead — ' . $lead['full_name'] : 'Lead not found';
$activeNav = 'dosha-leads';
?>
<?php include __DIR__ . '/../include/header.php'; ?>

<style>
    .ladm-view { max-width: 780px; margin: 0 auto; }
    .ladm-view * { box-sizing: border-box; }

    .ladm-view .back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--muted); text-decoration: none; margin-bottom: 16px; }
    .ladm-view .back-link:hover { color: var(--ink); }
    .ladm-view .back-link svg { width: 15px; height: 15px; }

    .ladm-view .card { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 24px; box-shadow: 0 1px 2px rgba(23, 72, 61, 0.04); }

    .ladm-view .head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding-bottom: 18px; margin-bottom: 18px; border-bottom: 1px solid var(--line); }
    .ladm-view .head h1 { margin: 0 0 4px; font-size: 1.3rem; font-weight: 800; color: var(--ink); }
    .ladm-view .head .sub { font-size: 0.9rem; color: var(--muted); font-weight: 600; }

    .ladm-view .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px; border-radius: 99px; font-size: 0.78rem; font-weight: 700; white-space: nowrap; }
    .ladm-view .badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .ladm-view .badge-started { background: var(--sky-tint); color: var(--sky); }
    .ladm-view .badge-completed { background: var(--leaf-tint); color: var(--leaf-dark); }

    .ladm-view .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 20px; }
    .ladm-view .meta-item label { display: block; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.03em; text-transform: uppercase; color: var(--muted); margin-bottom: 3px; }
    .ladm-view .meta-item div { font-size: 0.92rem; color: var(--ink); font-weight: 600; word-break: break-word; }
    .ladm-view .meta-item a { color: var(--leaf-dark); text-decoration: none; }
    .ladm-view .meta-item a:hover { text-decoration: underline; }

    .ladm-view .goal-body {
        background: var(--mist); border-radius: 12px; padding: 18px; font-size: 0.95rem;
        line-height: 1.6; color: var(--ink); white-space: pre-wrap; word-break: break-word; margin-bottom: 20px;
    }

    .ladm-view .tech-meta { font-size: 0.78rem; color: var(--muted); margin-bottom: 22px; }
    .ladm-view .tech-meta span { display: inline-block; margin-right: 16px; }

    .ladm-view .foot-actions { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; padding-top: 18px; border-top: 1px solid var(--line); }
    .ladm-view .status-form { display: flex; align-items: center; gap: 8px; }
    .ladm-view .status-form label { font-size: 0.78rem; font-weight: 700; color: var(--muted); }
    .ladm-view .status-form select {
        padding: 8px 12px; font-size: 0.86rem; font-family: inherit; border: 1px solid var(--line);
        border-radius: 9px; background: #fff; color: var(--ink);
    }

    .ladm-view .btn {
        display: inline-flex; align-items: center; gap: 7px; padding: 10px 18px; font-size: 0.86rem; font-weight: 700;
        text-decoration: none; color: #fff; border: none; border-radius: 10px; cursor: pointer;
        transition: background-color 0.15s ease, transform 0.15s ease;
    }
    .ladm-view .btn svg { width: 15px; height: 15px; }
    .ladm-view .btn:hover { transform: translateY(-1px); }
    .ladm-view .btn-reply { background: #fdf1de; color: #93650f; }
    .ladm-view .btn-reply:hover { background: #fbe6bf; }
    .ladm-view .btn-delete { background: #fdeceb; color: #c23b32; }
    .ladm-view .btn-delete:hover { background: #fadedb; }

    .ladm-view .not-found { text-align: center; padding: 60px 20px; color: var(--muted); }
</style>

<div class="ladm-view">
    <a href="<?= htmlspecialchars($backUrl) ?>" class="back-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        Back to leads
    </a>

    <div class="card">
        <?php if (!$lead) : ?>
            <div class="not-found">
                <strong>Lead not found.</strong>
                <p>It may have already been deleted.</p>
            </div>
        <?php else :
            $statusClass = $lead['status'] === 'completed' ? 'badge-completed' : 'badge-started';
        ?>
            <div class="head">
                <div>
                    <h1><?= htmlspecialchars($lead['full_name']) ?></h1>
                    <div class="sub">Dosha test lead</div>
                </div>
                <span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($lead['status'])) ?></span>
            </div>

            <div class="meta-grid">
                <div class="meta-item">
                    <label>Email</label>
                    <div><a href="mailto:<?= htmlspecialchars($lead['email']) ?>"><?= htmlspecialchars($lead['email']) ?></a></div>
                </div>
                <div class="meta-item">
                    <label>Mobile</label>
                    <div><?= htmlspecialchars($lead['mobile']) ?></div>
                </div>
                <div class="meta-item">
                    <label>Date of Birth</label>
                    <div><?= htmlspecialchars($lead['date_of_birth']) ?></div>
                </div>
                <div class="meta-item">
                    <label>Gender</label>
                    <div><?= htmlspecialchars($lead['gender']) ?></div>
                </div>
                <div class="meta-item">
                    <label>Location</label>
                    <div><?= htmlspecialchars($lead['location']) ?></div>
                </div>
                <?php if (!empty($lead['dosha_result'])) : ?>
                    <div class="meta-item">
                        <label>Dosha Result</label>
                        <div><?= htmlspecialchars($lead['dosha_result']) ?></div>
                    </div>
                <?php endif; ?>
                <div class="meta-item">
                    <label>Received</label>
                    <div><?= htmlspecialchars($lead['created_at']) ?></div>
                </div>
            </div>

            <?php if (!empty($lead['wellness_goal'])) : ?>
                <div class="meta-item" style="margin-bottom:10px;">
                    <label>Wellness Goal</label>
                </div>
                <div class="goal-body"><?= htmlspecialchars($lead['wellness_goal']) ?></div>
            <?php endif; ?>

            <div class="tech-meta">
                <span>Session token: <?= htmlspecialchars($lead['session_token']) ?></span>
                <?php if (!empty($lead['ip_address'])) : ?><span>IP: <?= htmlspecialchars($lead['ip_address']) ?></span><?php endif; ?>
                <?php if (!empty($lead['user_agent'])) : ?><span>Agent: <?= htmlspecialchars($lead['user_agent']) ?></span><?php endif; ?>
                <?php if (!empty($lead['updated_at'])) : ?><span>Last updated: <?= htmlspecialchars($lead['updated_at']) ?></span><?php endif; ?>
            </div>

            <div class="foot-actions">
                <form method="post" action="update-status.php" class="status-form">
                    <label for="v-status">Status</label>
                    <input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
                    <input type="hidden" name="return_page" value="view.php">
                    <input type="hidden" name="return_qs" value="id=<?= (int) $lead['id'] ?><?= $backQs !== '' ? '&' . htmlspecialchars($backQs) : '' ?>">
                    <select id="v-status" name="status" onchange="this.form.submit()">
                        <?php foreach (DOSHA_LEAD_STATUSES() as $s) : ?>
                            <option value="<?= $s ?>" <?= $s === $lead['status'] ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <div style="display:flex; gap:8px;">
                    <a href="mailto:<?= htmlspecialchars($lead['email']) ?>?subject=<?= rawurlencode('Your Ayurvedic Dosha Test') ?>" class="btn btn-reply">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 7 9 6 9-6"/><rect x="3" y="5" width="18" height="14" rx="2"/></svg>
                        Email lead
                    </a>
                    <form method="post" action="delete.php">
                        <input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
                        <input type="hidden" name="return_qs" value="<?= htmlspecialchars($backQs) ?>">
                        <button type="submit" class="btn btn-delete" onclick="return confirm('Delete this lead?');">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
