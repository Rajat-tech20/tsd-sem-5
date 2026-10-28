/* Mobile navigation toggle */
const navToggle = document.getElementById('navToggle');
const mainNav = document.getElementById('mainNav');
const navOverlay = document.getElementById('navOverlay');

function openNav() {
  navToggle.setAttribute('aria-expanded', 'true');
  navToggle.setAttribute('aria-label', 'Close menu');
  mainNav.classList.add('is-open');
  navOverlay.classList.add('is-visible');
  navOverlay.setAttribute('aria-hidden', 'false');
  document.body.style.overflow = 'hidden';
}

function closeNav() {
  navToggle.setAttribute('aria-expanded', 'false');
  navToggle.setAttribute('aria-label', 'Open menu');
  mainNav.classList.remove('is-open');
  navOverlay.classList.remove('is-visible');
  navOverlay.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';
}

navToggle.addEventListener('click', () => {
  const isOpen = navToggle.getAttribute('aria-expanded') === 'true';
  isOpen ? closeNav() : openNav();
});

navOverlay.addEventListener('click', closeNav);

window.addEventListener('resize', () => {
  if (window.innerWidth >= 900) closeNav();
});
