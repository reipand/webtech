document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.sidebar-collapse-btn');
    const backdrop = document.querySelector('.sidebar-backdrop');
    
    // Toggle sidebar
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        backdrop.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
    });
    
    // Close sidebar when clicking backdrop
    backdrop.addEventListener('click', function() {
        sidebar.classList.remove('show');
        backdrop.style.display = 'none';
    });
    
    // Close sidebar when clicking a nav link on mobile
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('show');
                backdrop.style.display = 'none';
            }
        });
    });
});