
// Sidebar toggle mobile
const btnToggle = document.getElementById('btnToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

btnToggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
});

overlay.addEventListener('click', () => {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
});

// Close sidebar when clicking menu item on mobile
document.querySelectorAll('.menu-item').forEach(it => {
    it.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });
});

// Ticket card click effect
document.querySelectorAll('.ticket-card').forEach(card => {
    card.addEventListener('click', function (e) {
        if (!e.target.closest('button')) {
            // Aquí puedes añadir la navegación al detalle del ticket
            console.log('Ver ticket:', this.querySelector('.ticket-id').textContent);
        }
    });
});

// Count-up animation for metrics (supports ints and floats)
function animateCount(el, target, duration = 1200) {
    const isFloat = (String(target).indexOf('.') !== -1);
    const decimals = isFloat ? (String(target).split('.')[1].length) : 0;
    const start = 0;
    const startTime = performance.now();

    function tick(now) {
        const progress = Math.min((now - startTime) / duration, 1);
        const value = start + (target - start) * easeOutCubic(progress);
        el.textContent = isFloat ? value.toFixed(decimals) : Math.round(value);
        if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);

    function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }
}

// Trigger count-up when visible
const metrics = document.querySelectorAll('.metric-value');
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const el = entry.target;
            const target = parseFloat(el.getAttribute('data-target')) || 0;
            animateCount(el, target, 1000 + Math.random() * 600);
            observer.unobserve(el);
        }
    });
}, { threshold: 0.4 });

metrics.forEach(m => observer.observe(m));

