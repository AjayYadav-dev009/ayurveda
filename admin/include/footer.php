</div><!-- /.content -->
        </main>
    </div>

    <script>
        (function () {
            // Sidebar: on desktop the button collapses it to icons (remembered);
            // on phones/tablets it opens and closes the slide-in drawer.
            var root = document.documentElement;
            var body = document.body;
            var btn = document.querySelector('[data-sb-toggle]');
            var overlay = document.querySelector('[data-sb-overlay]');
            var mobile = window.matchMedia('(max-width: 900px)');

            if (!btn) {
                return;
            }

            function setDrawer(open) {
                body.classList.toggle('sb-open', open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            btn.addEventListener('click', function () {
                if (mobile.matches) {
                    setDrawer(!body.classList.contains('sb-open'));
                    return;
                }
                var collapsed = !root.classList.contains('sb-collapsed');
                root.classList.toggle('sb-collapsed', collapsed);
                btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                try { localStorage.setItem('adm-sb', collapsed ? '1' : '0'); } catch (e) {}
            });

            if (overlay) {
                overlay.addEventListener('click', function () { setDrawer(false); });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && body.classList.contains('sb-open')) {
                    setDrawer(false);
                }
            });

            function onChange() {
                setDrawer(false);
                if (!mobile.matches) {
                    btn.setAttribute('aria-expanded', root.classList.contains('sb-collapsed') ? 'false' : 'true');
                }
            }

            if (mobile.addEventListener) {
                mobile.addEventListener('change', onChange);
            } else if (mobile.addListener) {
                mobile.addListener(onChange);
            }
        })();
    </script>
</body>

</html>