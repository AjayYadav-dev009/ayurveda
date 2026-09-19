<?php

declare(strict_types=1);

/**
 * Shared setup for the Orders admin pages.
 * If something breaks on first load, the settings marked ADJUST are the
 * only things that should need changing.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make mysqli throw exceptions so failed queries roll back cleanly.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Project root = the folder that contains /admin and /function.
$root = dirname(__DIR__, 2);

// ---- ADJUST: where your database.php lives (first match wins) ----
foreach (['/config/database.php', '/includes/database.php', '/database.php', '/config/db.php'] as $candidate) {
    if (is_file($root . $candidate)) {
        require_once $root . $candidate;
        break;
    }
}
if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    exit('orders/_bootstrap.php: could not load database.php ($conn missing). Edit the path list at the top of this file.');
}

require_once $root . '/function/csrf.php';
require_once $root . '/function/helper.php';
require_once $root . '/function/order.php';

// ---- ADJUST: session key your admin login sets (holds admins.id) ----
$adminSessionKey = 'admin_id';
if (empty($_SESSION[$adminSessionKey])) {
    http_response_code(403);
    exit('Not logged in as admin.');
}
$currentAdminId = (int) $_SESSION[$adminSessionKey];

function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function money($n): string
{
    return '₹' . number_format((float) $n, 2);
}

function flash(?string $type = null, ?string $msg = null): ?array
{
    if ($type !== null) {
        $_SESSION['orders_flash'] = [$type, $msg];
        return null;
    }
    $f = $_SESSION['orders_flash'] ?? null;
    unset($_SESSION['orders_flash']);
    return $f;
}

function status_badge(string $s): string
{
    return '<span class="badge b-' . e($s) . '">' . e(ucfirst($s)) . '</span>';
}

function orders_styles(): void
{
    echo <<<'CSS'
<style>
.page{padding:28px 32px;max-width:1280px}
.page h1{margin:0 0 4px;font-size:24px;font-weight:800;letter-spacing:-.01em}
.page .sub{color:var(--muted);margin:0 0 20px;font-size:14px}
.card{background:var(--paper);border:1px solid var(--line);border-radius:14px;padding:18px 20px;margin-bottom:18px}
.card h2{margin:0 0 12px;font-size:15px;font-weight:700}
.filters{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.filters input,.filters select,.f input,.f select,.f textarea,.rowform select{font:inherit;font-size:14px;padding:8px 10px;border:1px solid var(--line);border-radius:9px;background:#fff;color:var(--ink)}
.btn{font:inherit;font-size:13.5px;font-weight:700;padding:8px 14px;border-radius:9px;border:0;background:var(--leaf);color:#fff;cursor:pointer;text-decoration:none;display:inline-block}
.btn:hover{background:var(--leaf-dark)}
.btn--ghost{background:var(--sky-tint);color:var(--sky)}
.btn--ghost:hover{background:#d8ebf8}
table.t{width:100%;border-collapse:collapse;font-size:14px}
.t th{text-align:left;font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);padding:10px 8px;border-bottom:1px solid var(--line)}
.t td{padding:10px 8px;border-bottom:1px solid var(--line);vertical-align:middle}
.t tr:last-child td{border-bottom:0}
.t a{color:var(--sky);font-weight:700;text-decoration:none}
.muted{color:var(--muted);font-size:13px}
.badge{display:inline-block;padding:3px 9px;border-radius:99px;font-size:12px;font-weight:700;background:#eef2f6;color:#475b6d}
.b-pending{background:#fff4dc;color:#946200}.b-confirmed,.b-processing{background:var(--sky-tint);color:var(--sky)}
.b-shipped{background:#ece9fb;color:#5a45c4}.b-delivered,.b-paid{background:var(--leaf-tint);color:var(--leaf-dark)}
.b-cancelled,.b-failed{background:#fde8e8;color:#b32626}.b-returned,.b-refunded{background:#f1e6f7;color:#8a3aa8}
.rowform{display:flex;gap:6px;align-items:center}
.rowform select{padding:5px 6px;font-size:13px}
.rowform .btn{padding:6px 10px;font-size:12.5px}
.flash{padding:11px 14px;border-radius:10px;margin-bottom:16px;font-size:14px;font-weight:600}
.flash--ok{background:var(--leaf-tint);color:var(--leaf-dark)}.flash--err{background:#fde8e8;color:#b32626}
.grid{display:grid;grid-template-columns:2fr 1fr;gap:18px}
@media(max-width:980px){.grid{grid-template-columns:1fr}.page{padding:18px}}
.f label{display:block;font-size:12.5px;font-weight:700;color:var(--muted);margin:12px 0 4px}
.f input,.f select,.f textarea{width:100%}
.kv{display:grid;grid-template-columns:130px 1fr;gap:6px 10px;font-size:14px}.kv dt{color:var(--muted)}.kv dd{margin:0}
.pager{display:flex;gap:6px;margin-top:14px;flex-wrap:wrap}
.pager a,.pager span{padding:6px 11px;border-radius:8px;border:1px solid var(--line);font-size:13px;text-decoration:none;color:var(--ink);background:#fff}
.pager .cur{background:var(--leaf);color:#fff;border-color:var(--leaf)}
.tablewrap{overflow-x:auto}
</style>
CSS;
}
