// Header scroll

if(document.querySelector('header')) {
function fixHeaderOnScroll() {
    const header = document.querySelector('header');
  
    if (window.scrollY >= 1) {
        header.classList.add('fixed');
    } else {
        header.classList.remove('fixed');
    }
}
  
window.addEventListener('scroll', fixHeaderOnScroll);
fixHeaderOnScroll();
}

// Header menu
const headerBurger = document.querySelector('.header-burger');
const mobileMenu = document.getElementById('mobile-menu');

if (headerBurger && mobileMenu) {
    let scrollPosition = 0;
    let focusableElements = null;

    const focusableSelectors =
        'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])';

    headerBurger.addEventListener('click', toggleMenu);

    function toggleMenu() {
        const expanded = headerBurger.getAttribute('aria-expanded') === 'true';
        headerBurger.setAttribute('aria-expanded', String(!expanded));
        headerBurger.setAttribute('aria-label', expanded ? 'Menu openen' : 'Menu sluiten');

        if (expanded) {
            closeMenu();
        } else {
            openMenu();
        }
    }

    function openMenu() {
        scrollPosition = window.scrollY;
        document.body.classList.add('menu-is-open');
        mobileMenu.hidden = false;
        focusableElements = mobileMenu.querySelectorAll(focusableSelectors);
        if (focusableElements.length) {
            focusableElements[0].focus();
        }
        document.addEventListener('keydown', handleKeyDown);
    }

    function closeMenu() {
        document.body.classList.remove('menu-is-open');
        window.scrollTo({top: scrollPosition, behavior: 'auto'});
        mobileMenu.hidden = true;
        headerBurger.focus();
        document.removeEventListener('keydown', handleKeyDown);
    }

    function handleKeyDown(e) {
        if (e.key === 'Escape') {
            closeMenu();
        }

        if (e.key === 'Tab' && focusableElements) {
            const first = focusableElements[0];
            const last = focusableElements[focusableElements.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                last.focus();
                e.preventDefault();
            } else if (!e.shiftKey && document.activeElement === last) {
                first.focus();
                e.preventDefault();
            }
        }
    }

    window.addEventListener('resize', () => {
        document.body.classList.remove('menu-is-open');
    });
}

// AJAX load more
if (typeof ajax !== 'undefined') {
    const loadMoreButtons = document.querySelectorAll('.ajax-load-more');
    loadMoreButtons.forEach(button => {
        button.addEventListener('click', () => {
            const action = 'load_more';
            const post_type = button.dataset.postType;
            const posts_per_page = parseInt(button.dataset.postsPerPage);
            const offset = parseInt(button.dataset.offset ?? posts_per_page);
            button.dataset.offset = offset + posts_per_page;

            fetch(ajax.url, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action, post_type, posts_per_page, offset}).toString()
            }).then(response => response.text())
              .then(html => {
                const items = button.closest('section').querySelector('.ajax-container');
                items.insertAdjacentHTML('beforeend', html);
                if (items.querySelector('.hide-ajax-button')) {
                    button.remove();
                }
            });
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // ── Focus Point — center focal point in container ─────────────────────────
    applyFocusPoints();

    document.querySelectorAll('img[data-focus-x][data-focus-y]').forEach(img => {
        if (!img.complete) {
            img.addEventListener('load', applyFocusPoints);
        }
    });
});

// ── Focus Point ───────────────────────────────────────────────────────────────
function applyFocusPoints() {
    const images = document.querySelectorAll('img[data-focus-x][data-focus-y]');

    images.forEach(img => {
        const focalX = parseFloat(img.dataset.focusX);
        const focalY = parseFloat(img.dataset.focusY);

        const imageW = img.naturalWidth;
        const imageH = img.naturalHeight;
        if (!imageW || !imageH) return;

        const containerW = img.offsetWidth;
        const containerH = img.offsetHeight;
        if (!containerW || !containerH) return;

        // Scale applied by object-fit: cover
        const scale  = Math.max(containerW / imageW, containerH / imageH);
        const scaledW = imageW * scale;
        const scaledH = imageH * scale;

        // Focal point position inside scaled image (px)
        const focalXpx = (focalX / 100) * scaledW;
        const focalYpx = (focalY / 100) * scaledH;

        // Offset to center the focal point in the container
        const rawOffsetX = focalXpx - containerW / 2;
        const rawOffsetY = focalYpx - containerH / 2;

        // Clamp: can't scroll past image edges
        const maxOffsetX = scaledW - containerW;
        const maxOffsetY = scaledH - containerH;

        const offsetX = Math.max(0, Math.min(rawOffsetX, maxOffsetX));
        const offsetY = Math.max(0, Math.min(rawOffsetY, maxOffsetY));

        // Convert to object-position %
        const posX = maxOffsetX > 0 ? (offsetX / maxOffsetX) * 100 : 50;
        const posY = maxOffsetY > 0 ? (offsetY / maxOffsetY) * 100 : 50;

        img.style.objectPosition = `${posX.toFixed(2)}% ${posY.toFixed(2)}%`;
    });
}

// Debounced resize
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(applyFocusPoints, 100);
});