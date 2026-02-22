/* Mervmaii - Animations & UI */

document.addEventListener('DOMContentLoaded', function() {
    // Login page animation
    var loginContainer = document.querySelector('.login-container');
    if (loginContainer) {
        loginContainer.classList.add('animate-fade-in');
    }

    // Mobile nav toggle
    var navToggle = document.getElementById('navToggle');
    var navMenu = document.querySelector('.nav-menu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navMenu.classList.toggle('open');
        });

        // Close menu when clicking a link on small screens
        navMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    navMenu.classList.remove('open');
                }
            });
        });
    }
});
