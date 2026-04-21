/**
 * About Page Scripts - Ronaldo Redesign
 */

document.addEventListener('DOMContentLoaded', function () {
    
    // ── Intersection Observer for Scroll Reveals ──
    const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    };

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add the animation class when the element comes into view
                if (entry.target.classList.contains('animate-fade-in-up-scroll')) {
                    entry.target.classList.add('active');
                }
                // Once animated, no need to observe anymore
                revealObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Initial check for elements that should reveal on scroll
    document.querySelectorAll('.animate-fade-in-up-scroll').forEach(el => {
        revealObserver.observe(el);
    });

    // ── Back Button Interactions ──
    const backBtn = document.querySelector('.about-back-btn');
    if (backBtn) {
        backBtn.addEventListener('mouseenter', () => {
            // Subtle sound or additional effect could go here
        });
    }

    // ── Profile Box Dynamic Effects ──
    document.querySelectorAll('.dev-profile-box').forEach(box => {
        box.addEventListener('mousemove', (e) => {
            // Optional: Add subtle 3D tilt effect like modern portfolios
        });
    });

    // ── Stat Counter Animation ──
    const animateValue = (id, start, end, duration) => {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            id.innerHTML = value.toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                id.innerHTML = end.toLocaleString() + (id.dataset.suffix || "");
            }
        };
        window.requestAnimationFrame(step);
    };

    const statObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                const endValue = parseInt(target.dataset.target);
                animateValue(target, 0, endValue, 2000);
                statObserver.unobserve(target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stat-number').forEach(stat => {
        statObserver.observe(stat);
    });

    console.log('About page Ronaldo-style redesign initialized.');
});
