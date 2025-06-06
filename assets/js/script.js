// assets/js/script.js

document.addEventListener('DOMContentLoaded', function () {
    // Handle navigation to students page
    const studentsNavLink = document.querySelector('a[href="?view=students"]');

    if (studentsNavLink) {
        studentsNavLink.addEventListener('click', function (e) {
            // Prevent default link behavior if you want to handle it with JS
            // e.preventDefault();

            // You could add additional logic here if needed, like:
            // - Loading animations
            // - AJAX loading of content
            // - Tracking analytics

            console.log('Navigating to Students page');

            // The default href behavior will handle the navigation since we're using query parameters
            // If you wanted to do it programmatically:
            // window.location.href = '?view=students';
        });
    }

    // Highlight active sidebar item (redundant since PHP already does this, but good for JS-only apps)
    function highlightActiveNavItem() {
        const currentView = new URLSearchParams(window.location.search).get('view') || 'dashboard';
        const navItems = document.querySelectorAll('.nav-item');

        navItems.forEach(item => {
            item.classList.remove('active');
            const itemView = new URLSearchParams(item.getAttribute('href').split('?')[1]).get('view');
            if (itemView === currentView) {
                item.classList.add('active');
            }
        });
    }

    // Call the function on page load
    highlightActiveNavItem();

    // You might also want to listen for popstate events if using back/forward buttons
    window.addEventListener('popstate', highlightActiveNavItem);
});