/* Vedorishi Ayurveda admin — shared product form behavior.
   Works for both add.php and edit.php via a small config object. */

window.AdminProductForm = (function () {

    function nextIndex(container) {
        // Use the current child count as the next free index. Existing
        // server-rendered rows are 0..n-1, so this never collides.
        return container.children.length;
    }

    function addRow(container, templateSelector, indexPrefix) {
        const tpl = document.querySelector(templateSelector);
        if (!tpl) return null;
        const idx = nextIndex(container);
        const html = tpl.innerHTML.split('__INDEX__').join(String(idx));
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const node = wrapper.firstElementChild;
        container.appendChild(node);
        return node;
    }

    function ensureMinRows(container, templateSelector, min) {
        while (container.children.length < min) {
            addRow(container, templateSelector);
        }
    }

    function wireRemoveButtons(container, min) {
        container.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-remove-row]');
            if (!btn) return;
            const row = btn.closest('.repeater-item');
            if (!row) return;
            if (container.children.length <= min) {
                // Don't delete below the floor -- just clear the row's
                // text/number inputs instead, so admins can't get stuck
                // with zero rows and no way to add one back visibly.
                row.querySelectorAll('input[type="text"], input[type="number"]').forEach(function (input) {
                    if (!input.name.includes('sort_order')) input.value = '';
                    else input.value = '0';
                });
                row.querySelectorAll('input[type="checkbox"]').forEach(function (cb) { cb.checked = false; });
                row.querySelectorAll('input[type="file"]').forEach(function (f) { f.value = ''; });
                return;
            }
            row.remove();
        });
    }

    function wireExclusiveCheckbox(container, className) {
        container.addEventListener('change', function (e) {
            if (!e.target.classList.contains(className)) return;
            if (!e.target.checked) return;
            container.querySelectorAll('.' + className).forEach(function (cb) {
                if (cb !== e.target) cb.checked = false;
            });
        });
    }

    function wireSectionNav(navSelector) {
        const nav = document.querySelector(navSelector);
        if (!nav) return;
        const links = Array.from(nav.querySelectorAll('a'));
        const sections = links
            .map(function (a) { return document.querySelector(a.getAttribute('href')); })
            .filter(Boolean);

        if (!('IntersectionObserver' in window) || sections.length === 0) return;

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                const id = '#' + entry.target.id;
                links.forEach(function (a) {
                    a.classList.toggle('is-active', a.getAttribute('href') === id);
                });
            });
        }, { rootMargin: '-20% 0px -70% 0px' });

        sections.forEach(function (s) { observer.observe(s); });
    }

    function wireInventoryToggle(cfg) {
        const checkbox = document.querySelector(cfg.hasVariantsCheckbox);
        const stockBlock = document.querySelector(cfg.stockSimpleBlock);
        const variantsBlock = document.querySelector(cfg.variantsBlock);
        if (!checkbox || !stockBlock || !variantsBlock) return;

        function sync() {
            const on = checkbox.checked;
            stockBlock.style.display = on ? 'none' : '';
            variantsBlock.style.display = on ? '' : 'none';
            if (on) {
                const variantContainer = document.querySelector(cfg.variantContainer);
                ensureMinRows(variantContainer, cfg.variantTemplate, cfg.minVariantRows || 1);
            }
        }

        checkbox.addEventListener('change', sync);
        sync();
    }

    function wireCategoryPrimarySync(cfg) {
        const checkboxContainer = document.querySelector('#category-checkboxes');
        const primarySelect = document.querySelector('#primary_category_id');
        if (!checkboxContainer || !primarySelect) return;

        function sync() {
            const checked = Array.from(checkboxContainer.querySelectorAll('.category-checkbox:checked'))
                .map(function (cb) { return cb.value; });

            Array.from(primarySelect.options).forEach(function (opt) {
                if (opt.value === '') return;
                const isChecked = checked.includes(opt.value);
                opt.disabled = !isChecked;
                if (!isChecked && primarySelect.value === opt.value) {
                    primarySelect.value = '';
                }
            });

            // If exactly one category is checked, pick it automatically.
            if (checked.length === 1 && primarySelect.value === '') {
                primarySelect.value = checked[0];
            }
        }

        checkboxContainer.addEventListener('change', sync);
        sync();
    }

    function wireCountryOther(cfg) {
        const select = document.querySelector(cfg.countrySelect);
        const otherField = document.querySelector(cfg.countryOtherField);
        if (!select || !otherField) return;

        function sync() {
            otherField.style.display = select.value === 'Other' ? '' : 'none';
        }

        select.addEventListener('change', sync);
        sync();
    }

    function init(cfg) {
        document.addEventListener('DOMContentLoaded', function () {

            const variantContainer = document.querySelector(cfg.variantContainer);
            const imageContainer = document.querySelector(cfg.imageContainer);

            if (variantContainer) {
                wireRemoveButtons(variantContainer, 0);
                wireExclusiveCheckbox(variantContainer, 'default-variant-checkbox');
            }
            if (imageContainer) {
                wireRemoveButtons(imageContainer, 0);
                wireExclusiveCheckbox(imageContainer, 'primary-image-checkbox');
                ensureMinRows(imageContainer, cfg.imageTemplate, cfg.minImageRows || 1);
            }

            const addVariantBtn = document.querySelector(cfg.addVariantBtn);
            if (addVariantBtn && variantContainer) {
                addVariantBtn.addEventListener('click', function () {
                    addRow(variantContainer, cfg.variantTemplate);
                });
            }

            const addImageBtn = document.querySelector(cfg.addImageBtn);
            if (addImageBtn && imageContainer) {
                addImageBtn.addEventListener('click', function () {
                    addRow(imageContainer, cfg.imageTemplate);
                });
            }

            if (cfg.hasVariantsCheckbox) wireInventoryToggle(cfg);
            wireCategoryPrimarySync(cfg);
            if (cfg.countrySelect) wireCountryOther(cfg);
            wireSectionNav('#section-nav');
        });
    }

    return { init: init };
})();