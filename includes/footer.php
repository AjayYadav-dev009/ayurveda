<?php

if (!function_exists('getFooterCategories')) {
    require_once __DIR__ . '/../function/category.php';
}

$footerCategories = getAllCategoriesWithProducts($conn);

if (count($footerCategories) > 8) {
    $footerCategories = array_slice($footerCategories, 0, 8);
}

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

$footerSocialLinks = [
    'facebook' => '#',
    'twitter' => '#',
    'instagram' => '#',
    'youtube' => '#',
    'linkedin' => '#',
];
?>

<style>
    .ftr {
        background: var(--color-bg);
        padding: 52px 0 0;
        border-top: 1px solid var(--color-border);
    }

    .ftr__columns {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
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

    /* ---- Social (now a column) ---- */

    .ftr__social-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        list-style: none;
        margin: 0;
        padding: 0;
        flex-wrap: wrap;
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

    .ftr__social-list a {
        display: flex;
        gap: 10px;
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

            <div class="ftr__col">
                <h3 class="ftr__col-title">Connect With Us</h3>
                <ul class="ftr__social-list">
                    <li>
                        <a href="<?= htmlspecialchars($footerSocialLinks['facebook'], ENT_QUOTES, 'UTF-8') ?>" aria-label="Facebook" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M13.5 21v-8h2.7l.4-3.1h-3.1V8c0-.9.25-1.5 1.55-1.5H17V3.7c-.3-.04-1.3-.13-2.5-.13-2.5 0-4.2 1.5-4.2 4.3v2.4H7.6v3.1h2.7v8h3.2z" />
                            </svg>
                            <p>Facebook</p>
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($footerSocialLinks['twitter'], ENT_QUOTES, 'UTF-8') ?>" aria-label="Twitter" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 6.6c-.6.3-1.3.5-2 .6.7-.4 1.3-1.2 1.5-2-.7.4-1.5.7-2.3.9A3.6 3.6 0 0 0 11 9c0 .3 0 .6.1.8-3-.1-5.6-1.6-7.4-3.7-.3.5-.5 1.2-.5 1.8 0 1.2.6 2.3 1.6 2.9-.6 0-1.1-.2-1.6-.4v.1c0 1.8 1.3 3.2 2.9 3.6-.3.1-.6.1-1 .1-.2 0-.5 0-.7-.1.5 1.5 1.9 2.5 3.5 2.6-1.3 1-3 1.6-4.7 1.6H3c1.4 1 3.2 1.5 5 1.5 6.1 0 9.4-5 9.4-9.4v-.4c.6-.5 1.2-1.1 1.6-1.8z" />
                            </svg>
                            <p>Twitter</p>
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($footerSocialLinks['instagram'], ENT_QUOTES, 'UTF-8') ?>" aria-label="Instagram" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3.5" y="3.5" width="17" height="17" rx="4.5" />
                                <circle cx="12" cy="12" r="4" />
                                <circle cx="17" cy="7" r="0.8" fill="currentColor" stroke="none" />
                            </svg>
                            <p>Instagram</p>
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($footerSocialLinks['youtube'], ENT_QUOTES, 'UTF-8') ?>" aria-label="YouTube" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <rect x="2.5" y="6" width="19" height="12" rx="3" fill="none" stroke="currentColor" stroke-width="1.8" />
                                <path d="M10.5 9.5l5 2.5-5 2.5z" />
                            </svg>
                            <p>Youtube</p>
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($footerSocialLinks['linkedin'], ENT_QUOTES, 'UTF-8') ?>" aria-label="LinkedIn" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M6.9 8.6H3.9V20h3zM5.4 4c-1 0-1.7.7-1.7 1.6 0 .9.7 1.6 1.7 1.6 1 0 1.7-.7 1.7-1.6C7.1 4.7 6.4 4 5.4 4zM20 20h-3v-6c0-1.4-.5-2.3-1.7-2.3-.9 0-1.5.6-1.7 1.2-.1.2-.1.5-.1.8V20h-3s.1-10.4 0-11.4h3v1.6c.4-.6 1.1-1.5 2.8-1.5 2 0 3.5 1.3 3.5 4.2V20z" />
                            </svg>
                            <p>LindedIn</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="ftr__bottom">
        <p>&copy; 2026 AYURVEDIC Store Made by Ajay Yadav. All rights reserved.</p>
    </div>
</footer>
</body>

</html>