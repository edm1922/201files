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

    console.log('About page Ronaldo-style redesign initialized.');
});
