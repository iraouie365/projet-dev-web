// Custom JS for theme toggle and UI helpers
(function() {
  // Theme toggle
  function setTheme(dark) {
    document.body.classList.toggle('dark-mode', dark);
    localStorage.setItem('theme', dark ? 'dark' : 'light');
  }
  window.setTheme = setTheme;
  // On load, apply saved theme
  if (localStorage.getItem('theme') === 'dark') setTheme(true);

  // Add theme toggle button to navbar
  document.addEventListener('DOMContentLoaded', function() {
    var nav = document.querySelector('.navbar .container-fluid');
    if (nav) {
      var btn = document.createElement('button');
      btn.className = 'btn btn-sm btn-outline-light ms-2';
      btn.innerHTML = '<span id="theme-icon">🌙</span>';
      btn.title = 'Basculer le thème';
      btn.onclick = function() {
        var dark = !document.body.classList.contains('dark-mode');
        setTheme(dark);
        document.getElementById('theme-icon').textContent = dark ? '☀️' : '🌙';
      };
      nav.appendChild(btn);
      // Set correct icon
      document.getElementById('theme-icon').textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
    }
  });
})();
