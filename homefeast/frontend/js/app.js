// Minimal hash-based SPA router.
// Each route maps to an HTML template in /views. Navigation never reloads the page.

const routes = {
  '/login':     { view: 'login',     auth: false },
  '/register':  { view: 'register',  auth: false },
  '/dashboard': { view: 'dashboard', auth: true },
  '/explore':   { view: 'explore',   auth: true },
  '/pantry':    { view: 'pantry',    auth: true },
  '/recipes':   { view: 'recipes',   auth: true },
  '/favorites': { view: 'favorites', auth: true },
  '/profile':   { view: 'profile',   auth: true },
  '/admin':     { view: 'admin',     auth: true },
};

const app = document.getElementById('app');
const nav = document.getElementById('main-nav');

async function loadRoute() {
  const path = location.hash.replace('#', '') || '/login';
  const route = routes[path] || routes['/login'];

  try {
    const res = await fetch('views/' + route.view + '.html');
    if (!res.ok) throw new Error('View not found');
    app.innerHTML = await res.text();
  } catch (err) {
    app.innerHTML = '<div class="alert alert-danger">Could not load this page.</div>';
  }

  // Show the navbar only on pages after login
  nav.classList.toggle('d-none', !route.auth);
  document.querySelectorAll('#navLinks .nav-link').forEach(link => {
    link.classList.toggle('active', link.getAttribute('href') === '#' + path);
  });

  // Collapse the mobile menu after navigating
  const menu = document.getElementById('navLinks');
  if (menu.classList.contains('show')) bootstrap.Collapse.getOrCreateInstance(menu).hide();

  window.scrollTo(0, 0);
  bindStaticForms();
  if (route.view === 'pantry') initPantry();
}

// Milestone 1 only: forms just navigate. Real AJAX + JWT comes in Milestone 4.
function bindStaticForms() {
  app.querySelectorAll('form[data-goto]').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();
      location.hash = form.dataset.goto;
    });
  });
}

// "What can I cook?" page: ingredient chips (static results for now).
function initPantry() {
  const form = document.getElementById('ingredientForm');
  const input = document.getElementById('ingredientInput');
  const chips = document.getElementById('ingredientChips');
  const empty = document.getElementById('chipsEmpty');
  if (!form) return;

  function refreshEmpty() {
    empty.classList.toggle('d-none', chips.children.length > 0);
  }

  function addChip(name) {
    name = name.trim().toLowerCase();
    if (!name) return;
    const exists = [...chips.querySelectorAll('[data-name]')].some(c => c.dataset.name === name);
    if (exists) return;
    const chip = document.createElement('span');
    chip.className = 'chip';
    chip.dataset.name = name;
    chip.append(name + ' ');
    const x = document.createElement('button');
    x.type = 'button';
    x.className = 'btn-close btn-close-white btn-sm ms-1';
    x.setAttribute('aria-label', 'Remove ' + name);
    x.addEventListener('click', () => { chip.remove(); refreshEmpty(); });
    chip.append(x);
    chips.append(chip);
    refreshEmpty();
  }

  form.addEventListener('submit', e => {
    e.preventDefault();
    input.value.split(',').forEach(addChip);
    input.value = '';
    input.focus();
  });

  document.querySelectorAll('[data-quick]').forEach(btn => {
    btn.addEventListener('click', () => addChip(btn.dataset.quick));
  });

  document.getElementById('clearChips').addEventListener('click', () => {
    chips.innerHTML = '';
    refreshEmpty();
  });

  ['chicken', 'rice', 'garlic', 'onion'].forEach(addChip);
}

window.addEventListener('hashchange', loadRoute);
window.addEventListener('DOMContentLoaded', () => {
  if (!location.hash) location.hash = '#/login';
  loadRoute();
});
