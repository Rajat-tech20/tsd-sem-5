/* ---------- Icons (inline SVG, decorative) ---------- */
const ICONS = {
  web:   '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 7l-5 5 5 5M16 7l5 5-5 5M14 4l-4 16"/></svg>',
  react: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="1.8" fill="currentColor"/><ellipse cx="12" cy="12" rx="10" ry="4"/><ellipse cx="12" cy="12" rx="10" ry="4" transform="rotate(60 12 12)"/><ellipse cx="12" cy="12" rx="10" ry="4" transform="rotate(120 12 12)"/></svg>',
  arrow: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg>'
};
const LABEL = { web: "HTML / CSS / JS", react: "React" };

/* ---------- Build cards ---------- */
const cardsEl = document.getElementById('cards');
const emptyEl = document.getElementById('empty');
const resultEl = document.getElementById('result');
const searchEl = document.getElementById('search');

const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

PRACTICALS.forEach((p, i) => {
  const a = document.createElement('a');
  a.className = 'card ' + p.type;
  a.href = p.url;
  if (OPEN_IN_NEW_TAB) { a.target = '_blank'; a.rel = 'noopener'; }
  a.dataset.type = p.type;
  a.dataset.search = (p.title + ' ' + p.description + ' ' + p.tech.join(' ')).toLowerCase();
  a.style.setProperty('--i', i);
  a.innerHTML = `
    <div class="card-top">
      <span class="icon">${ICONS[p.type]}</span>
      <span class="num">${String(i + 1).padStart(2, '0')}</span>
    </div>
    <h3>${esc(p.title)}</h3>
    <p>${esc(p.description)}</p>
    <div class="card-foot">
      <span class="chips">${p.tech.map(t => `<i>${esc(t)}</i>`).join('')}</span>
      <span class="go">Open ${ICONS.arrow}</span>
    </div>
    <span class="sr-only">${LABEL[p.type]} practical</span>`;
  cardsEl.appendChild(a);
});

/* ---------- Filter + search ---------- */
let activeFilter = 'all';
function applyFilters() {
  const q = searchEl.value.trim().toLowerCase();
  let shown = 0;
  cardsEl.querySelectorAll('.card').forEach(c => {
    const ok = (activeFilter === 'all' || c.dataset.type === activeFilter) && c.dataset.search.includes(q);
    c.hidden = !ok;
    if (ok) shown++;
  });
  emptyEl.hidden = shown !== 0;
  resultEl.textContent = `Showing ${shown} of ${PRACTICALS.length} practicals`;
}
document.querySelectorAll('.filters button').forEach(btn => {
  btn.addEventListener('click', () => {
    activeFilter = btn.dataset.filter;
    document.querySelectorAll('.filters button').forEach(b => b.setAttribute('aria-pressed', String(b === btn)));
    applyFilters();
  });
});
searchEl.addEventListener('input', applyFilters);
applyFilters();

/* ---------- Hero: letters decode into the subject name ---------- */
const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
const CHARS = '01{}<>/;=+*#';
document.querySelectorAll('h1 .row').forEach((row, ri) => {
  const text = row.dataset.text;
  row.innerHTML = [...text].map(c => `<span class="ch" aria-hidden="true">&nbsp;</span>`).join('');
  row.querySelectorAll('.ch').forEach((s, i) => {
    const settle = 500 + ri * 350 + i * 70;
    const start = performance.now();
    (function tick(now) {
      if (now - start >= settle) { s.textContent = text[i]; return; }
      s.textContent = CHARS[Math.floor(Math.random() * CHARS.length)];
      requestAnimationFrame(tick);
    })(start);
  });
});

/* ---------- Hero glow follows the cursor ---------- */
const glow = document.getElementById('glow');
document.querySelector('.hero').addEventListener('pointermove', e => {
  glow.style.setProperty('--x', e.clientX + 'px');
  glow.style.setProperty('--y', e.clientY + 'px');
});

/* ---------- Cards reveal once, in sequence, as the dashboard scrolls into view ---------- */
const cardIO = new IntersectionObserver(entries => {
  entries.forEach(en => {
    if (en.isIntersecting) { en.target.classList.add('in'); cardIO.unobserve(en.target); }
  });
}, { threshold: .15 });
cardsEl.querySelectorAll('.card').forEach(c => reduceMotion ? c.classList.add('in') : cardIO.observe(c));

/* ---------- Count-up numbers ---------- */
const countIO = new IntersectionObserver(entries => {
  entries.forEach(en => {
    if (!en.isIntersecting) return;
    countIO.unobserve(en.target);
    const end = +en.target.dataset.count;
    if (reduceMotion) { en.target.textContent = end; return; }
    let n = 0;
    const t = setInterval(() => { en.target.textContent = ++n; if (n >= end) clearInterval(t); }, 110);
  });
}, { threshold: .6 });
document.querySelectorAll('[data-count]').forEach(el => countIO.observe(el));

/* ---------- About Me: scroll reveal ---------- */
const aboutCard = document.getElementById('about-card');
if (aboutCard) {
  const aboutIO = new IntersectionObserver(entries => {
    entries.forEach(en => {
      if (en.isIntersecting) { en.target.classList.add('in'); aboutIO.unobserve(en.target); }
    });
  }, { threshold: .2 });
  reduceMotion ? aboutCard.classList.add('in') : aboutIO.observe(aboutCard);
}

/* ---------- Thank You: scroll reveal + floating particles ---------- */
const tyContent = document.getElementById('ty-content');
const tyParticles = document.getElementById('ty-particles');

if (tyContent) {
  const tyIO = new IntersectionObserver(entries => {
    entries.forEach(en => {
      if (en.isIntersecting) { en.target.classList.add('in'); tyIO.unobserve(en.target); }
    });
  }, { threshold: .2 });
  reduceMotion ? tyContent.classList.add('in') : tyIO.observe(tyContent);
}

/* Generate random floating dots */
if (tyParticles && !reduceMotion) {
  for (let i = 0; i < 25; i++) {
    const dot = document.createElement('span');
    dot.className = 'ty-dot';
    dot.style.left = Math.random() * 100 + '%';
    dot.style.bottom = '-8px';
    dot.style.setProperty('--delay', (Math.random() * 4).toFixed(2) + 's');
    dot.style.width = dot.style.height = (2 + Math.random() * 3) + 'px';
    tyParticles.appendChild(dot);
  }
}
