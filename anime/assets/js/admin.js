document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality for mobile view
    const sidebarToggle = document.createElement('button');
    sidebarToggle.className = 'btn btn-primary d-md-none sidebar-toggle';
    sidebarToggle.innerHTML = '<i class="bi bi-list"></i>';
    document.querySelector('.main-content').prepend(sidebarToggle);
    
    sidebarToggle.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth < 768 && sidebar.classList.contains('active') && 
            !e.target.closest('.sidebar') && !e.target.closest('.sidebar-toggle')) {
            sidebar.classList.remove('active');
        }
    });

    // Active link highlighting
    const currentPage = location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href').split('/').pop();
        if (currentPage === linkHref) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    // Confirmations for critical actions
    const confirmActions = document.querySelectorAll('[data-confirm]');
    confirmActions.forEach(action => {
        action.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Toast notifications
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    const toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 });
    });
    toastList.forEach(toast => toast.show());

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 150);
        }, 5000);
    });

    // Table row highlighting
    const tableRows = document.querySelectorAll('.recent-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            this.classList.toggle('table-active');
        });
    });

    // Responsive adjustments
    function handleResize() {
        if (window.innerWidth >= 768) {
            document.querySelector('.sidebar').classList.remove('active');
        }
    }
    
    window.addEventListener('resize', handleResize);
    handleResize();

    // CSRF token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
    }

    // Dark mode toggle (if implemented in your CSS)
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });

        // Check for saved preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    }

    // Card hover effects
    const cards = document.querySelectorAll('.card-anime');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
});