        </main>
        <!-- App Footer -->
        <footer class="app-footer">
          <div class="container-fluid">
            <div class="row align-items-center">
              <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                <span>&copy; <?= date('Y'); ?> <strong>Sistem Informasi Inventory</strong>. All rights reserved.</span>
              </div>
              <div class="col-md-6 text-center text-md-end">
                <span class="text-muted small">Device-Friendly & Responsive UI</span>
              </div>
            </div>
          </div>
        </footer>
      </div><!-- /.app-main -->
    </div><!-- /.app-layout -->

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <!-- Sidebar & Layout Responsive Interactivity -->
    <script>
      (function() {
        var sidebar = document.getElementById('appSidebar');
        var backdrop = document.getElementById('sidebarBackdrop');
        var toggleBtns = document.querySelectorAll('.sidebar-toggle-btn');

        function isDesktop() {
          return window.innerWidth >= 992;
        }

        function openSidebarMobile() {
          if (sidebar) sidebar.classList.add('show');
          if (backdrop) backdrop.classList.add('show');
          document.body.classList.add('sidebar-open');
        }

        function closeSidebarMobile() {
          if (sidebar) sidebar.classList.remove('show');
          if (backdrop) backdrop.classList.remove('show');
          document.body.classList.remove('sidebar-open');
        }

        function toggleSidebar() {
          if (isDesktop()) {
            // Toggle sidebar pada desktop / laptop
            var isCollapsed = document.body.classList.toggle('sidebar-collapsed');
            document.documentElement.classList.toggle('sidebar-collapsed', isCollapsed);
            try {
              localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
            } catch (e) {}
          } else {
            // Toggle sidebar pada mobile / tablet
            if (sidebar && sidebar.classList.contains('show')) {
              closeSidebarMobile();
            } else {
              openSidebarMobile();
            }
          }
        }

        toggleBtns.forEach(function(btn) {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSidebar();
          });
        });

        if (backdrop) {
          backdrop.addEventListener('click', function() {
            closeSidebarMobile();
          });
        }

        // Tutup sidebar mobile saat menekan tombol Escape
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
            if (!isDesktop() && sidebar && sidebar.classList.contains('show')) {
              closeSidebarMobile();
            }
          }
        });

        // Event listener saat resize window antara desktop & mobile
        window.addEventListener('resize', function() {
          if (isDesktop()) {
            closeSidebarMobile();
          }
        });

        // Auto close drawer di mobile saat item link dipilih
        var navLinks = document.querySelectorAll('.app-sidebar .sidebar-link:not([data-bs-toggle="collapse"]), .app-sidebar .sidebar-sublink');
        navLinks.forEach(function(link) {
          link.addEventListener('click', function() {
            if (!isDesktop()) {
              closeSidebarMobile();
            }
          });
        });
      })();
    </script>
  </body>
</html>