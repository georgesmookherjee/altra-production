/**
 * Altra Theme JavaScript
 */

(function() {
    'use strict';
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {

        // Hero scroll animation avec morphing du logo
        const hero = document.querySelector('.hero-section');
        const heroLogo = document.querySelector('.hero-logo');
        const heroInfo = document.querySelector('.hero-info');
        const heroNavLeft = document.querySelector('.hero-nav-left');
        const heroNavRight = document.querySelector('.hero-nav-right');
        const scrollIndicator = document.querySelector('.scroll-indicator');
        const header = document.querySelector('.site-header');
        const headerLogo = document.querySelector('.site-logo');
        const headerNavLeft = document.querySelector('.nav-left');
        const headerNavRight = document.querySelector('.nav-right');

        if (hero && heroLogo && header) {
            // Distance de scroll pour la transition complète
            let scrollThreshold = window.innerHeight * 0.8;

            // PARAMÈTRES DE TIMING pour "Altra Production"
            // À quel moment (en %) le logo hero disparaît et le logo header apparaît
            const switchPoint = 0.99; // À 95% de la transition, switch instantané

            // Cacher le logo du header au début (on utilise le hero logo qui se déplace)
            if (headerLogo) {
                headerLogo.style.opacity = '0';
            }

            // Cacher les nav du header au début (ils apparaîtront avec le header)
            if (headerNavLeft && headerNavRight) {
                headerNavLeft.style.opacity = '0';
                headerNavRight.style.opacity = '0';
            }

            function updateLogoMorphing() {
                const scrolled = window.pageYOffset || document.documentElement.scrollTop;
                const progress = Math.min(scrolled / scrollThreshold, 1); // 0 à 1

                // Gérer le hero logo et pointer events
                if (scrolled > scrollThreshold) {
                    hero.style.pointerEvents = 'none';
                }

                // Switch instantané entre hero et header au switchPoint
                if (progress < switchPoint) {
                    // Avant le switch: hero visible, header caché
                    heroLogo.style.opacity = '1';
                    if (heroNavLeft) heroNavLeft.style.opacity = '1';
                    if (heroNavRight) heroNavRight.style.opacity = '1';

                    header.classList.remove('visible');
                    if (headerLogo) headerLogo.style.opacity = '0';
                    if (headerNavLeft) headerNavLeft.style.opacity = '0';
                    if (headerNavRight) headerNavRight.style.opacity = '0';
                } else {
                    // Après le switch: hero caché, header visible
                    heroLogo.style.opacity = '0';
                    if (heroNavLeft) heroNavLeft.style.opacity = '0';
                    if (heroNavRight) heroNavRight.style.opacity = '0';

                    header.classList.add('visible');
                    if (headerLogo) headerLogo.style.opacity = '1';
                    if (headerNavLeft) headerNavLeft.style.opacity = '1';
                    if (headerNavRight) headerNavRight.style.opacity = '1';
                }

                // Calculer la position cible (position du logo dans le header)
                let translateY = 0;

                if (headerLogo) {
                    const logoRect = headerLogo.getBoundingClientRect();
                    const targetY = logoRect.top + logoRect.height / 2;

                    // Position initiale (centre de l'écran)
                    const startY = window.innerHeight / 2;

                    // Calculer le déplacement vertical uniquement
                    const deltaY = targetY - startY;

                    // Appliquer les transformations progressives au logo (seulement vertical)
                    translateY = deltaY * progress;

                    // Calculer le scale pour matcher la taille du logo header
                    const heroLogoSize = heroLogo.offsetWidth;
                    const headerLogoSize = logoRect.width;
                    const targetScale = headerLogoSize / heroLogoSize;
                    const scale = 1 - (progress * (1 - targetScale));

                    // Garder centré horizontalement (-50%), bouger seulement verticalement
                    heroLogo.style.transform = `translate(-50%, calc(-50% + ${translateY}px)) scale(${scale})`;
                }

                // Transition de couleur du logo de blanc vers noir
                const colorProgress = progress;
                const white = 255;
                const black = 0;
                const currentColor = Math.round(white - (white - black) * colorProgress);
                heroLogo.style.color = `rgb(${currentColor}, ${currentColor}, ${currentColor})`;

                // Transition pour INFOS (nav-left) - seulement vertical
                if (heroNavLeft && headerNavLeft) {
                    const navLeftRect = headerNavLeft.getBoundingClientRect();
                    const targetY = navLeftRect.top + navLeftRect.height / 2;

                    const startY = window.innerHeight / 2;
                    const deltaY = targetY - startY;
                    const translateY = deltaY * progress;

                    heroNavLeft.style.transform = `translateY(calc(-50% + ${translateY}px))`;
                    heroNavLeft.style.color = `rgb(${currentColor}, ${currentColor}, ${currentColor})`;
                }

                // Transition pour CONTACT (nav-right) - seulement vertical
                if (heroNavRight && headerNavRight) {
                    const navRightRect = headerNavRight.getBoundingClientRect();
                    const targetY = navRightRect.top + navRightRect.height / 2;

                    const startY = window.innerHeight / 2;
                    const deltaY = targetY - startY;
                    const translateY = deltaY * progress;

                    heroNavRight.style.transform = `translateY(calc(-50% + ${translateY}px))`;
                    heroNavRight.style.color = `rgb(${currentColor}, ${currentColor}, ${currentColor})`;
                }

                // Faire suivre les infos de contact avec le logo
                if (heroInfo) {
                    heroInfo.style.opacity = 1 - progress;
                    // Les infos suivent le même déplacement vertical que le logo
                    heroInfo.style.transform = `translate(-50%, calc(-50% + 120px + ${translateY}px))`;
                    // Transition de couleur pour les infos aussi
                    heroInfo.style.color = `rgb(${currentColor}, ${currentColor}, ${currentColor})`;
                }
                if (scrollIndicator) {
                    scrollIndicator.style.opacity = 1 - (progress * 2); // Disparaît plus vite
                }

                // Ajuster l'opacité du background du hero
                hero.style.backgroundColor = `rgba(0, 0, 0, ${1 - progress})`;
            }

            // Initialiser l'état au chargement
            updateLogoMorphing();

            // Mettre à jour au scroll (avec throttle pour meilleures performances)
            let ticking = false;
            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        updateLogoMorphing();
                        ticking = false;
                    });
                    ticking = true;
                }
            });

            // Recalculer lors du redimensionnement
            window.addEventListener('resize', function() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        updateLogoMorphing();
                        ticking = false;
                    });
                    ticking = true;
                }
            });
        } else if (header) {
            // Si pas de hero section, afficher le header directement
            header.classList.add('visible');
        }

        // Project Gallery Click Navigation
        const galleryViewer = document.querySelector('.project-gallery-viewer');
        if (galleryViewer) {
            const slides = galleryViewer.querySelectorAll('.gallery-slide');
            const currentImageSpan = galleryViewer.querySelector('.current-image');
            const totalImages = slides.length;
            let currentIndex = 0;

            // Click on gallery to go to next image
            galleryViewer.addEventListener('click', function(e) {
                // Don't navigate if clicking on the counter
                if (e.target.closest('.gallery-counter')) {
                    return;
                }

                // Hide current slide
                slides[currentIndex].classList.remove('active');

                // Go to next slide (loop back to 0 if at end)
                currentIndex = (currentIndex + 1) % totalImages;

                // Show next slide
                slides[currentIndex].classList.add('active');

                // Update counter
                currentImageSpan.textContent = currentIndex + 1;
            });

            // Optional: Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (!galleryViewer) return;

                if (e.key === 'ArrowRight' || e.key === ' ') {
                    e.preventDefault();
                    galleryViewer.click();
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    // Go to previous image
                    slides[currentIndex].classList.remove('active');
                    currentIndex = (currentIndex - 1 + totalImages) % totalImages;
                    slides[currentIndex].classList.add('active');
                    currentImageSpan.textContent = currentIndex + 1;
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
