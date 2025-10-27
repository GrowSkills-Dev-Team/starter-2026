// Header scroll
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

// Header menu
const headerBurger = document.querySelector('.header-burger');
const mobileMenu = document.getElementById('mobile-menu');

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

// AJAX load more
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