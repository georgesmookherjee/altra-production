/**
 * Altra Theme JavaScript
 */

(function() {
    'use strict';
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {

        // Hero scroll animation
        const hero = document.querySelector('.hero-section');
        const header = document.querySelector('.site-header');

        if (hero && header) {
            let scrollThreshold = 100; // Pixels de scroll avant transition

            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset || document.documentElement.scrollTop;

                if (scrolled > scrollThreshold) {
                    // Cacher le hero
                    hero.classList.add('scrolled');
                    // Montrer le header
                    header.classList.add('visible');
                } else {
                    // Montrer le hero
                    hero.classList.remove('scrolled');
                    // Cacher le header
                    header.classList.remove('visible');
                }
            });
        }

        // Mobile menu toggle (if needed in future)
        const menuToggle = document.querySelector('.menu-toggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                document.body.classList.toggle('menu-open');
            });
        }

        // Smooth scroll for anchor links
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        anchorLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        })
        
        // Image lazy loading observer (for future use)
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            observer.unobserve(img);
                        }
                    }
                });
            });
            
            const lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(img => imageObserver.observe(img));
        }
        
    });
    
})();
