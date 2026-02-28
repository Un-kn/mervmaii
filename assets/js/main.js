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

    // Reactions (photos & love notes)
    var containers = document.querySelectorAll('.reaction-container');
    containers.forEach(function (container) {
        var mainBtn = container.querySelector('.reaction-main-btn');
        var picker = container.querySelector('.reaction-picker');
        if (!mainBtn || !picker) return;

        var togglePicker = function(show) {
            if (show) {
                container.classList.add('show-picker');
            } else {
                container.classList.remove('show-picker');
            }
        };

        // Click main button = quick like toggle
        mainBtn.addEventListener('click', function () {
            var current = mainBtn.getAttribute('data-current') || '';
            var nextType = current === 'like' ? '' : 'like';
            if (nextType === '') {
                // send same type to remove
                sendReaction(container, 'like');
            } else {
                sendReaction(container, 'like');
            }
        });

        // Hover to open picker (desktop)
        container.addEventListener('mouseenter', function () {
            togglePicker(true);
        });
        container.addEventListener('mouseleave', function () {
            togglePicker(false);
        });

        // Long press to open picker (for mobile)
        var pressTimer;
        mainBtn.addEventListener('mousedown', function () {
            pressTimer = setTimeout(function () {
                togglePicker(true);
            }, 400);
        });
        ['mouseup', 'mouseleave'].forEach(function (evt) {
            mainBtn.addEventListener(evt, function () {
                clearTimeout(pressTimer);
            });
        });

        // Tap main button quickly on touch opens picker too
        mainBtn.addEventListener('touchstart', function () {
            pressTimer = setTimeout(function () {
                togglePicker(true);
            }, 300);
        }, { passive: true });
        mainBtn.addEventListener('touchend', function () {
            clearTimeout(pressTimer);
        });

        picker.querySelectorAll('.reaction-option').forEach(function (opt) {
            opt.addEventListener('click', function (e) {
                e.stopPropagation();
                var type = opt.getAttribute('data-type');
                sendReaction(container, type);
                togglePicker(false);
            });
        });
    });

});

function sendReaction(container, type) {
    var context = container.getAttribute('data-reaction-context'); // 'photo' or 'note'
    var id = container.getAttribute('data-id');
    if (!context || !id) return;

    var url = context === 'photo' ? 'photo_reaction.php' : 'note_reaction.php';
    var formData = new FormData();
    if (context === 'photo') {
        formData.append('photo_id', id);
    } else {
        formData.append('note_id', id);
    }
    formData.append('reaction_type', type);

    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    }).then(function () {
        // Reload to reflect updated counts and names
        window.location.reload();
    }).catch(function () {});
}
