<?php

if (!function_exists('getSubcategoriesWithProducts')) {
    require_once __DIR__ . '/../function/category.php';
}
if (!function_exists('generateFooterCaptcha')) {
    require_once __DIR__ . '/../function/newletter.php';
}

// try {
//     $footerCategories = array_slice(getSubcategoriesWithProducts($conn), 0, 8);
// } catch (Exception $e) {
//     $footerCategories = [];
// }

$footerCaptcha = generateFooterCaptcha();

$newsletterFlash = $_SESSION['newsletter_flash'] ?? null;
unset($_SESSION['newsletter_flash']);

// Used so the newsletter form can redirect back to whatever page it was
// submitted from (see footer-newsletter-subscribe.php).
$currentUrl = BASE_URL . ltrim($_SERVER['REQUEST_URI'] ?? '', '/');

// ---------------------------------------------------------------------------
// TODO: these columns are placeholder links — none of these pages exist
// in the project yet (About Us, Contact Us, FAQ, Terms of Use, Privacy
// Policy, Track Order, Blog, Career, etc.). Point them at real files as
// each page gets built.
// ---------------------------------------------------------------------------
$footerEnquireLinks = [
    'About Us' => 'about.php',
    'Gynam' => 'gynam.php',
    'Contact Us' => 'contact.php',
    'FAQ' => 'faq.php',
    'Terms of Service' => 'terms.php',
    'Track Order' => 'track-order.php',
    'Certification & Lab Reports' => 'certifications.php',
    'Career' => 'career.php',
];

$footerPolicyLinks = [
    'Terms of Use' => 'terms.php',
    'Return & Refund Policy' => 'refund-policy.php',
    'Privacy Policy' => 'privacy-policy.php',
    'Cancellation Policy' => 'cancellation-policy.php',
    'Shipping Policy' => 'shipping-policy.php',
];

$footerPartnerLinks = [
    'Blog' => 'blog.php',
];

$footerQuickLinks = [
    'Shop By Product' => 'products.php',
    'All Ingredients' => 'ingredients.php',
    'Consult A Vaidya' => 'consult.php',
    'Dosha Test' => 'dosha-test.php',
];

// TODO: point these at real social profile URLs.
$footerSocialLinks = [
    'facebook' => '#',
    'twitter' => '#',
    'instagram' => '#',
    'youtube' => '#',
    'linkedin' => '#',
];
?>

<style>
    /* ==========================================================================
       Site footer. Namespaced "ftr".
       ========================================================================== */

    .ftr {
        background: var(--color-bg);
        padding: 52px 0 0;
        border-top: 1px solid var(--color-border);
    }

    .ftr__columns {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 32px;
        margin-bottom: 40px;
    }

    .ftr__col-title {
        margin: 0 0 16px;
        font-size: 17px;
        font-weight: 700;
        color: var(--color-primary-dark);
    }

    .ftr__links {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .ftr__links a {
        font-size: 13px;
        color: var(--color-accent);
        text-decoration: none;
    }

    .ftr__links a:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }

    /* ---- Newsletter + captcha ---- */

    .ftr__newsletter {
        max-width: 480px;
        margin-bottom: 36px;
    }

    .ftr__newsletter-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--color-text);
        margin-bottom: 12px;
    }

    .ftr__newsletter-flash {
        font-size: 12.5px;
        margin: 0 0 10px;
        padding: 8px 12px;
        border-radius: var(--radius-sm);
    }

    .ftr__newsletter-flash--success {
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        border: 1px solid var(--color-primary);
    }

    .ftr__newsletter-flash--error {
        color: #8a1c14;
        background: #fbeceb;
        border: 1px solid #f2c6c2;
    }

    .ftr__email-row {
        display: flex;
        margin-bottom: 12px;
    }

    .ftr__email-row input[type="email"] {
        flex: 1;
        min-width: 0;
        padding: 12px 14px;
        font-size: 13px;
        font-family: inherit;
        color: var(--color-text);
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-right: none;
        border-radius: var(--radius-sm) 0 0 var(--radius-sm);
    }

    .ftr__email-row button {
        flex-shrink: 0;
        width: 48px;
        border: none;
        background: var(--color-primary);
        color: var(--color-white);
        border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ftr__email-row button:hover {
        background: var(--color-primary-dark);
    }

    .ftr__email-row button svg {
        width: 16px;
        height: 16px;
    }

    .ftr__captcha-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ftr__captcha-code {
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 20px;
        font-weight: 700;
        color: var(--color-text);
        letter-spacing: 2px;
        user-select: none;
        white-space: nowrap;
    }

    .ftr__captcha-code span {
        display: inline-block;
    }

    .ftr__captcha-row input[type="text"] {
        flex: 1;
        min-width: 0;
        padding: 11px 14px;
        font-size: 13px;
        font-family: inherit;
        color: var(--color-text);
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
    }

    /* ---- Social ---- */

    .ftr__social-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--color-text);
        margin-bottom: 12px;
    }

    .ftr__social-list {
        display: flex;
        gap: 10px;
        list-style: none;
        margin: 0 0 40px;
        padding: 0;
    }

    .ftr__social-list a {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid var(--color-border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-text);
        text-decoration: none;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .ftr__social-list a:hover {
        background: var(--color-primary);
        color: var(--color-white);
        border-color: var(--color-primary);
    }

    .ftr__social-list svg {
        width: 15px;
        height: 15px;
    }

    /* ---- Bottom bar ---- */

    .ftr__bottom {
        border-top: 1px solid var(--color-border);
        padding: 18px 0;
        text-align: center;
    }

    .ftr__bottom p {
        margin: 0;
        font-size: 12px;
        font-weight: 600;
        color: var(--color-text-light);
    }

    @media (max-width: 980px) {
        .ftr__columns {
            grid-template-columns: repeat(3, 1fr);
            row-gap: 32px;
        }
    }

    @media (max-width: 640px) {
        .ftr__columns {
            grid-template-columns: repeat(2, 1fr);
        }

        .ftr__captcha-row {
            flex-wrap: wrap;
        }
    }
</style>

<footer class="ftr">
    <div class="container">
        <div class="ftr__columns">
            <div class="ftr__col">
                <h3 class="ftr__col-title">Top Categories</h3>
                <?php if (!empty($footerCategories)): ?>
                    <ul class="ftr__links">
                        <?php foreach ($footerCategories as $category): ?>
                            <li><a href="<?= htmlspecialchars(BASE_URL . 'categories-product.php?category_slug=' . urlencode($category['slug']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="ftr__col">
                <h3 class="ftr__col-title">Enquire</h3>
                <ul class="ftr__links">
                    <?php foreach ($footerEnquireLinks as $label => $href): ?>
                        <li><a href="<?= htmlspecialchars(BASE_URL . $href, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="ftr__col">
                <h3 class="ftr__col-title">Policy</h3>
                <ul class="ftr__links">
                    <?php foreach ($footerPolicyLinks as $label => $href): ?>
                        <li><a href="<?= htmlspecialchars(BASE_URL . $href, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="ftr__col">
                <h3 class="ftr__col-title">Partner &amp; News</h3>
                <ul class="ftr__links">
                    <?php foreach ($footerPartnerLinks as $label => $href): ?>
                        <li><a href="<?= htmlspecialchars(BASE_URL . $href, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="ftr__col">
                <h3 class="ftr__col-title">Quick Links</h3>
                <ul class="ftr__links">
                    <?php foreach ($footerQuickLinks as $label => $href): ?>
                        <li><a href="<?= htmlspecialchars(BASE_URL . $href, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="ftr__newsletter" id="newsletter">
            <span class="ftr__newsletter-label">Sign Up For Our Newsletter</span>

            <?php if ($newsletterFlash): ?>
                <p class="ftr__newsletter-flash ftr__newsletter-flash--<?= htmlspecialchars($newsletterFlash['type'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($newsletterFlash['message'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="<?= htmlspecialchars(BASE_URL . 'footer-newsletter-subscribe.php', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8') ?>">

                <div class="ftr__email-row">
                    <input type="email" name="email" placeholder="Enter your email address" required>
                    <button type="submit" aria-label="Subscribe">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6" /></svg>
                    </button>
                </div>

                <div class="ftr__captcha-row">
                    <span class="ftr__captcha-code" aria-hidden="true">
                        <?php foreach (str_split($footerCaptcha) as $i => $char):
                            $rotate = (($i * 37 + 11) % 17) - 8; // deterministic per-request "random" look, no JS needed
                            $lift = (($i * 23 + 5) % 7) - 3;
                        ?>
                            <span style="transform: rotate(<?= $rotate ?>deg) translateY(<?= $lift ?>px);"><?= htmlspecialchars($char, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </span>
                    <input type="text" name="captcha" placeholder="Captcha" autocomplete="off" required>
                </div>
            </form>
        </div>

        <div>
            <span class="ftr__social-label">Connect With Us</span>
            <ul class="ftr__social-list">
                <li>
                    <a href="<?= htmlspecialchars($footerSocialLinks['facebook'], ENT_QUOTES, 'UTF-8') ?>" aria-label="Facebook" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V8c0-.9.25-1.5 1.55-1.5H17V3.7c-.3-.04-1.3-.13-2.5-.13-2.5 0-4.2 1.5-4.2 4.3v2.4H7.6v3.1h2.7v8h3.2z"/></svg>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($footerSocialLinks['twitter'], ENT_QUOTES, 'UTF-8') ?>" aria-label="Twitter" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 6.6c-.6.3-1.3.5-2 .6.7-.4 1.3-1.2 1.5-2-.7.4-1.5.7-2.3.9A3.6 3.6 0 0 0 11 9c0 .3 0 .6.1.8-3-.1-5.6-1.6-7.4-3.7-.3.5-.5 1.2-.5 1.8 0 1.2.6 2.3 1.6 2.9-.6 0-1.1-.2-1.6-.4v.1c0 1.8 1.3 3.2 2.9 3.6-.3.1-.6.1-1 .1-.2 0-.5 0-.7-.1.5 1.5 1.9 2.5 3.5 2.6-1.3 1-3 1.6-4.7 1.6H3c1.4 1 3.2 1.5 5 1.5 6.1 0 9.4-5 9.4-9.4v-.4c.6-.5 1.2-1.1 1.6-1.8z"/></svg>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($footerSocialLinks['instagram'], ENT_QUOTES, 'UTF-8') ?>" aria-label="Instagram" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="0.8" fill="currentColor" stroke="none"/></svg>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($footerSocialLinks['youtube'], ENT_QUOTES, 'UTF-8') ?>" aria-label="YouTube" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="currentColor"><rect x="2.5" y="6" width="19" height="12" rx="3" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M10.5 9.5l5 2.5-5 2.5z"/></svg>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($footerSocialLinks['linkedin'], ENT_QUOTES, 'UTF-8') ?>" aria-label="LinkedIn" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.9 8.6H3.9V20h3zM5.4 4c-1 0-1.7.7-1.7 1.6 0 .9.7 1.6 1.7 1.6 1 0 1.7-.7 1.7-1.6C7.1 4.7 6.4 4 5.4 4zM20 20h-3v-6c0-1.4-.5-2.3-1.7-2.3-.9 0-1.5.6-1.7 1.2-.1.2-.1.5-.1.8V20h-3s.1-10.4 0-11.4h3v1.6c.4-.6 1.1-1.5 2.8-1.5 2 0 3.5 1.3 3.5 4.2V20z"/></svg>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="ftr__bottom">
        <p>&copy; 2026 AYURVEDIC Store Made by Ajay Yadav. All rights reserved.</p>
    </div>
</footer>
</body>
</html>