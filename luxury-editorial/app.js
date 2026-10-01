
const menuBtn = document.querySelector('.menu-btn');
const menu = document.querySelector('.nav-links');
if (menuBtn && menu) {
  menuBtn.addEventListener('click', () => menu.classList.toggle('open'));
  menu.querySelectorAll('a').forEach(a => a.addEventListener('click', () => menu.classList.remove('open')));
}

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) entry.target.classList.add('visible');
  });
}, {threshold: 0.08});
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

const langCopy = {
  es: {story:'Nuestra historia', wedding:'La boda', destination:'Destino', info:'Información', rsvp:'Confirma'},
  ca: {story:'La nostra història', wedding:'El casament', destination:'Destí', info:'Informació', rsvp:'Confirma'},
  en: {story:'Our story', wedding:'The wedding', destination:'Destination', info:'Information', rsvp:'RSVP'}
};
document.querySelectorAll('[data-lang]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('[data-lang]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const l = btn.dataset.lang;
    document.querySelector('[data-nav-story]').textContent = langCopy[l].story;
    document.querySelector('[data-nav-wedding]').textContent = langCopy[l].wedding;
    document.querySelector('[data-nav-destination]').textContent = langCopy[l].destination;
    document.querySelector('[data-nav-info]').textContent = langCopy[l].info;
    document.querySelector('[data-nav-rsvp]').textContent = langCopy[l].rsvp;
  });
});

const form = document.querySelector('#rsvp-form');
if (form) {
  form.addEventListener('submit', e => {
    e.preventDefault();
    const s = document.querySelector('.form-status');
    s.textContent = 'Gracias. Hemos recibido vuestra respuesta.';
    form.reset();
  });
}
