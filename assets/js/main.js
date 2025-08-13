// assets/js/main.js
document.addEventListener('DOMContentLoaded', function(){
  const sidebar = document.querySelector('nav.sidebar');
  const toggle = document.getElementById('sidebarToggle');
  if (toggle && sidebar) {
    toggle.addEventListener('click', function(e){
      e.preventDefault();
      sidebar.classList.toggle('show');
    });
    // hide on outside click (mobile)
    document.addEventListener('click', function(ev){
      if (window.innerWidth < 992 && sidebar.classList.contains('show')) {
        if (!sidebar.contains(ev.target) && !toggle.contains(ev.target)) {
          sidebar.classList.remove('show');
        }
      }
    });
  }
});
