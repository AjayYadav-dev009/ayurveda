// =========================================
// Shop All mega menu: switch the active
// category panel as the user's pointer moves
// over the sidebar (mouse), or on tap (touch).
// =========================================

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.mega-menu').forEach(function (megaMenu) {
        var sidebarItems = megaMenu.querySelectorAll('.mega-menu__sidebar-item');

        var activate = function (item) {
            var targetId = item.getAttribute('data-panel-target');
            var targetPanel = megaMenu.querySelector('#' + targetId);
            if (!targetPanel) {
                return;
            }

            sidebarItems.forEach(function (el) {
                el.classList.remove('is-active');
            });
            megaMenu.querySelectorAll('.mega-menu__panel').forEach(function (panel) {
                panel.classList.remove('is-active');
            });

            item.classList.add('is-active');
            targetPanel.classList.add('is-active');
        };

        sidebarItems.forEach(function (item) {
            item.addEventListener('mouseenter', function () {
                activate(item);
            });

            // Touch devices: first tap previews the panel instead of
            // navigating straight to the category link.
            item.addEventListener('click', function (event) {
                if (item.classList.contains('is-active')) {
                    return; // second tap on an already-active item follows the link
                }
                if (window.matchMedia('(hover: none)').matches) {
                    event.preventDefault();
                    activate(item);
                }
            });
        });
    });
});