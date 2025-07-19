// =============================================================
// MiniShop – main.js : UX enhancements
// =============================================================

/*-------------
    Scroll Reveal using IntersectionObserver
-------------*/
const srElements = document.querySelectorAll('[data-sr]');
const srOptions = {
  threshold: 0.15,
};
const srObserver = new IntersectionObserver((entries, observer) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('sr-show');
      observer.unobserve(entry.target);
    }
  });
}, srOptions);

srElements.forEach(el => srObserver.observe(el));

/*-------------
    Navbar background change on scroll
-------------*/
const nav = document.querySelector('.navbar');
function handleScroll() {
  if (window.scrollY > 60) {
    nav.classList.add('bg-dark', 'navbar-shadow');
  } else {
    nav.classList.remove('bg-dark', 'navbar-shadow');
  }
}
window.addEventListener('scroll', handleScroll);
handleScroll();

/*-------------
    Back to top button
-------------*/
const backBtn = document.createElement('button');
backBtn.innerHTML = '↑';
backBtn.className = 'btn btn-primary position-fixed';
backBtn.style = 'bottom:30px; right:30px; display:none; z-index:1050;';
document.body.appendChild(backBtn);
backBtn.addEventListener('click', () => window.scrollTo({top:0,behavior:'smooth'}));
window.addEventListener('scroll', () => {
  backBtn.style.display = window.scrollY > 300 ? 'block' : 'none';
}); 