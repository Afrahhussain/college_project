<?php
// includes/footer.php - place at end of pages
?>
  </main>

  <!-- Bootstrap JS bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function(){
      const toggle = document.getElementById('sidebarToggle');
      const sidebar = document.getElementById('appSidebar');
      const overlay = document.getElementById('sidebarOverlay');
      const main = document.getElementById('mainWrap');

      function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        main.classList.add('shift');
      }
      function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        main.classList.remove('shift');
      }

      toggle && toggle.addEventListener('click', function(e){
        if (sidebar.classList.contains('active')) closeSidebar(); else openSidebar();
      });

      overlay && overlay.addEventListener('click', closeSidebar);
      // close on ESC
      document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') closeSidebar();
      });

      // confirmation for action links
      document.addEventListener('click', function(e){
        const el = e.target.closest('.confirm-action');
        if (!el) return;
        e.preventDefault();
        const message = el.dataset.confirm || 'Are you sure?';
        if (confirm(message)) {
          // if element is an <a>, follow it
          const href = el.getAttribute('href');
          if (href) window.location = href;
        }
      });

    })();
  </script>

</body>
</html>
