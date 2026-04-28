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
            const heroAltraEl    = heroLogo.querySelector('.logo-altra');
            const headerAltraEl  = headerLogo ? headerLogo.querySelector('.logo-altra') : null;
            const heroLogoProduction = heroLogo.querySelector('.logo-production');

            // Positions initiales — recalculées au chargement et au resize
            let scrollThreshold, heroLogoCenterX, heroLogoCenterY;
            let altraOffsetX, altraOffsetY, targetScale;
            let initNavLeftCenterY, initNavRightCenterY;

            // Mesure la baseline réelle d'un élément texte via un probe inline-block.
            // Fonctionne sur tous les navigateurs indépendamment des métriques de fonte.
            function measureBaseline(el) {
                const probe = document.createElement('span');
                probe.style.cssText = 'display:inline-block;width:0;height:0;vertical-align:baseline;';
                el.appendChild(probe);
                const y = probe.getBoundingClientRect().bottom;
                el.removeChild(probe);
                return y;
            }

            function initPositions() {
                // Reset des transforms JS et des positions inline pour mesurer les positions CSS pures
                heroLogo.style.transform = '';
                if (heroNavLeft)  { heroNavLeft.style.transform  = ''; heroNavLeft.style.top  = ''; }
                if (heroNavRight) { heroNavRight.style.transform = ''; heroNavRight.style.top = ''; }

                scrollThreshold = window.innerHeight * 0.8;

                const heroLogoRect  = heroLogo.getBoundingClientRect();
                const heroAltraRect = heroAltraEl ? heroAltraEl.getBoundingClientRect() : heroLogoRect;

                heroLogoCenterX = heroLogoRect.left + heroLogoRect.width  / 2;
                heroLogoCenterY = heroLogoRect.top  + heroLogoRect.height / 2;
                const heroAltraCenterX = heroAltraRect.left + heroAltraRect.width  / 2;
                const heroAltraCenterY = heroAltraRect.top  + heroAltraRect.height / 2;

                altraOffsetX = heroAltraCenterX - heroLogoCenterX;
                altraOffsetY = heroAltraCenterY - heroLogoCenterY;

                const heroAltraW  = heroAltraRect.width;
                const headerAltraW = headerAltraEl ? headerAltraEl.getBoundingClientRect().width : heroAltraW;
                targetScale = headerAltraW / heroAltraW;

                // Alignement baseline cross-browser : mesure les baselines réelles et positionne les navs
                if (heroNavLeft && heroNavRight && heroAltraEl) {
                    const altraBaseline = measureBaseline(heroAltraEl);

                    const navLeftAnchor  = heroNavLeft.querySelector('a')  || heroNavLeft;
                    const navRightAnchor = heroNavRight.querySelector('a') || heroNavRight;

                    const navLeftRect        = heroNavLeft.getBoundingClientRect();
                    const navLeftBaseFromTop = measureBaseline(navLeftAnchor) - navLeftRect.top;
                    // top CSS = baseline_cible + height/2 - baselineFromTop (car translateY(-50%))
                    const navTopPx = altraBaseline + navLeftRect.height / 2 - navLeftBaseFromTop;
                    heroNavLeft.style.top  = navTopPx + 'px';
                    heroNavRight.style.top = navTopPx + 'px';
                }

                initNavLeftCenterY  = heroNavLeft  ? heroNavLeft.getBoundingClientRect().top  + heroNavLeft.getBoundingClientRect().height  / 2 : 0;
                initNavRightCenterY = heroNavRight ? heroNavRight.getBoundingClientRect().top + heroNavRight.getBoundingClientRect().height / 2 : 0;
            }

            // Cacher logo et navs du header au départ
            if (headerLogo)     headerLogo.style.opacity     = '0';
            if (headerNavLeft)  headerNavLeft.style.opacity  = '0';
            if (headerNavRight) headerNavRight.style.opacity = '0';

            initPositions();

            // Re-mesure après chargement des fontes custom pour garantir des baselines exactes
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(function() {
                    initPositions();
                    updateLogoMorphing();
                });
            }

            function updateLogoMorphing() {
                const scrolled = window.pageYOffset || document.documentElement.scrollTop;
                const progress = Math.min(scrolled / scrollThreshold, 1);

                if (scrolled > scrollThreshold) hero.style.pointerEvents = 'none';

                // "Production" et hero-info disparaissent tôt (progress 0 → 0.4)
                const earlyFade = Math.max(0, 1 - progress / 0.8);
                if (heroLogoProduction) heroLogoProduction.style.opacity = earlyFade;
                if (heroInfo)           heroInfo.style.opacity = earlyFade;
                if (scrollIndicator)    scrollIndicator.style.opacity = Math.max(0, 1 - progress * 2);

                // Morphing logo : translate X+Y vers le header + scale progressif
                // moveSpeed : 0.5 = atteint la position finale à 50% du scroll (plus petit = plus rapide)
                const moveSpeed    = 0.9;
                const moveProgress = Math.min(progress / moveSpeed, 1);

                let tx = 0, ty = 0;
                if (headerAltraEl) {
                    const logoRect      = headerAltraEl.getBoundingClientRect();
                    const targetCenterX = logoRect.left + logoRect.width  / 2;
                    const targetCenterY = logoRect.top  + logoRect.height / 2;

                    // Compenser la dérive due au scale (le scale s'applique autour du centre de .hero-logo,
                    // pas de .logo-altra — donc on corrige avec altraOffsetX * targetScale)
                    tx = (targetCenterX - heroLogoCenterX - altraOffsetX * targetScale) * moveProgress;
                    ty = (targetCenterY - heroLogoCenterY - altraOffsetY * targetScale) * moveProgress;
                    const scale = 1 - (1 - targetScale) * moveProgress;

                    heroLogo.style.transform = `translate(calc(-50% + ${tx}px), calc(-50% + ${ty}px)) scale(${scale})`;
                }

                // Fade final heroLogo (80% → 100%) / apparition header
                const fadeStart     = 0.98;
                const heroOpacity   = progress < fadeStart ? 1 : Math.max(0, 1 - (progress - fadeStart) / (1 - fadeStart));
                const headerOpacity = progress < fadeStart ? 0 : Math.min(1, (progress - fadeStart) / (1 - fadeStart));

                heroLogo.style.opacity = heroOpacity;
                if (heroNavLeft)  heroNavLeft.style.opacity  = heroOpacity;
                if (heroNavRight) heroNavRight.style.opacity = heroOpacity;

                if (headerOpacity > 0) {
                    header.classList.add('visible');
                    if (headerLogo)     headerLogo.style.opacity     = headerOpacity;
                    if (headerNavLeft)  headerNavLeft.style.opacity  = headerOpacity;
                    if (headerNavRight) headerNavRight.style.opacity = headerOpacity;
                } else {
                    header.classList.remove('visible');
                    if (headerLogo)     headerLogo.style.opacity     = '0';
                    if (headerNavLeft)  headerNavLeft.style.opacity  = '0';
                    if (headerNavRight) headerNavRight.style.opacity = '0';
                }

                // INFOS/CONTACT : translation verticale depuis leur position réelle vers leur position header
                // On conserve le -50% du CSS (centrage vertical) et on ajoute le delta en px
                if (heroNavLeft && headerNavLeft) {
                    const r       = headerNavLeft.getBoundingClientRect();
                    const targetY = r.top + r.height / 2;
                    const tY      = (targetY - initNavLeftCenterY) * moveProgress;
                    heroNavLeft.style.transform = `translateY(calc(-50% + ${tY}px))`;
                }
                if (heroNavRight && headerNavRight) {
                    const r       = headerNavRight.getBoundingClientRect();
                    const targetY = r.top + r.height / 2;
                    const tY      = (targetY - initNavRightCenterY) * moveProgress;
                    heroNavRight.style.transform = `translateY(calc(-50% + ${tY}px))`;
                }

                // Background du hero disparaît progressivement
                hero.style.backgroundColor = `rgba(255, 255, 255, ${1 - progress})`;
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

            // Recalculer lors du redimensionnement — recapture les positions initiales
            window.addEventListener('resize', function() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        initPositions();
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

            // Cache Vimeo Player instances
            const vimeoPlayers = {};

            function getVimeoPlayer(index) {
                const iframe = slides[index].querySelector('iframe');
                if (!iframe) return null;
                if (!vimeoPlayers[index] && typeof Vimeo !== 'undefined') {
                    vimeoPlayers[index] = new Vimeo.Player(iframe);
                }
                return vimeoPlayers[index] || null;
            }

            function goToSlide(newIndex) {
                // Pause video on current slide if leaving a video slide
                const currentPlayer = getVimeoPlayer(currentIndex);
                if (currentPlayer) {
                    currentPlayer.pause();
                }

                slides[currentIndex].classList.remove('active');
                currentIndex = ((newIndex % totalImages) + totalImages) % totalImages;
                slides[currentIndex].classList.add('active');
                currentImageSpan.textContent = currentIndex + 1;

                // Autoplay video on new slide — fonctionne car déclenché par geste utilisateur (clic)
                const newPlayer = getVimeoPlayer(currentIndex);
                if (newPlayer) {
                    newPlayer.play().catch(function() {
                        // Browser a bloqué l'autoplay (sans interaction préalable)
                    });
                }
            }

            // Clic sur la galerie → slide suivant
            galleryViewer.addEventListener('click', function(e) {
                if (e.target.closest('.gallery-counter') || e.target.closest('.gallery-left-label')) return;
                goToSlide(currentIndex + 1);
            });

            // Navigation clavier
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowRight' || e.key === ' ') {
                    e.preventDefault();
                    goToSlide(currentIndex + 1);
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    goToSlide(currentIndex - 1);
                }
            });

            // Autoplay si la première slide est une vidéo
            const firstPlayer = getVimeoPlayer(0);
            if (firstPlayer) {
                firstPlayer.play().catch(function() {});
            }
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
