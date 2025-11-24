<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Masaka Initiative - Mission Mondiale</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-gold: #C9A962;
            --text-dark: #2C3E50;
            --light-beige: #F5F1E8;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
        }

        .font-playfair {
            font-family: 'Playfair Display', serif;
        }

        /* custom small utilities for this page */
        .text-primary-gold {
            color: var(--primary-gold);
        }

        .bg-primary-gold {
            background-color: var(--primary-gold);
        }

        /* HERO SLIDER - CSS-based sliding widths */
        .hero-slider {
            height: 85vh;
            min-height: 560px;
            position: relative;
            overflow: hidden;
        }

        .slides {
            display: flex;
            width: 300%;
            height: 100%;
            transition: transform 0.6s ease;
        }

        .slide {
            width: 33.3333%;
            height: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slide .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.45));
        }

        .slide .content {
            z-index: 10;
            text-align: center;
            max-width: 980px;
            padding: 0 1rem;
        }

        .text-shadow {
            text-shadow: 2px 6px 24px rgba(0, 0, 0, 0.45);
        }

        /* slider nav buttons */
        .slider-btn {
            background: rgba(102, 101, 101, 0.35);
            backdrop-filter: blur(4px);
            border: none;
        }

        .slider-btn:hover {
            background: rgba(0, 0, 0, 0.55);
        }

        /* dots */
        .dot {
            width: 12px;
            height: 12px;
            background: rgba(255, 255, 255, 0.45);
            border-radius: 9999px;
            transition: transform .25s, background .25s;
        }

        .dot.active {
            background: var(--primary-gold);
            transform: scale(1.25);
        }

        /* small fade-in helper */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: all .6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* small shadow for cards */
        .card-shadow {
            box-shadow: 0 10px 30px rgba(2, 6, 23, 0.08);
        }

        /* small responsive tweaks for very small screens */
        @media (max-width: 420px) {
            .hero-slider {
                min-height: 420px;
            }
        }
    </style>
</head>

<body class="bg-white antialiased">

    <!-- TOP BAR (hidden on mobile) -->
    <div class="hidden md:flex justify-between items-center bg-amber-400 text-white py-2 px-6">
        <div class="flex items-center gap-6 text-sm">
            <a href="mailto:contact@masakainitiative.org" class="flex items-center gap-2 hover:text-white text-gray-800">
                <i data-feather="mail" class="w-4 h-4"></i>
                <span>contact@masakainitiative.org</span>
            </a>
            <a href="tel:+1234567890" class="flex items-center gap-2 hover:text-white text-gray-800">
                <i data-feather="phone" class="w-4 h-4"></i>
                <span>+1 234 567 890</span>
            </a>
        </div>

        <div class="flex items-center gap-4">
            <!-- social -->
            <div class="flex items-center gap-3">
                <a href="#" class="hover:text-white text-gray-800"><i data-feather="facebook" class="w-4 h-4"></i></a>
                <a href="#" class="hover:text-white text-gray-800"><i data-feather="twitter" class="w-4 h-4"></i></a>
                <a href="#" class="hover:text-white text-gray-800"><i data-feather="instagram" class="w-4 h-4"></i></a>
                <a href="#" class="hover:text-white text-gray-800"><i data-feather="linkedin" class="w-4 h-4"></i></a>
            </div>

            <!-- Se connecter -->
            <a href="../auth/login.php" class="flex items-center gap-2 text-sm hover:text-white text-gray-800 border-l pl-4 border-gray-600">
                <i data-feather="user-plus" class="w-4 h-4"></i>
                <span>Se connecter</span>
            </a>
        </div>
    </div>

    <!-- HEADER / NAV -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- logo -->
                <div class="flex items-center gap-3">
                    <img src="../assert/masaka.png" alt="Masaka Logo" class="h-10 w-10 rounded-full object-cover">
                    <div>
                        <div class=" font-bold text-lg text-primary-gold">MASAKA INITIATIVE</div>
                        <div class="text-xs text-gray-500 -mt-0.5">Mission Mondiale</div>
                    </div>
                </div>

                <!-- nav (desktop) -->
                <nav id="main-nav" class="hidden md:flex items-center gap-6">
                    <a href="index.php" class="flex items-center gap-2 text-gray-700 hover:text-primary-gold transition">
                        <i data-feather="home" class="w-4 h-4"></i><span class="text-sm font-medium">Accueil</span>
                    </a>

                    <a href="about.php" class="flex items-center gap-2 text-gray-700 hover:text-primary-gold transition">
                        <i data-feather="info" class="w-4 h-4"></i><span class="text-sm font-medium">À Propos</span>
                    </a>

                    <a href="events.php" class="flex items-center gap-2 text-gray-700 hover:text-primary-gold transition">
                        <i data-feather="calendar" class="w-4 h-4"></i><span class="text-sm font-medium">Événements</span>
                    </a>

                    <a href="testimonials.php" class="flex items-center gap-2 text-gray-700 hover:text-primary-gold transition">
                        <i data-feather="message-circle" class="w-4 h-4"></i><span class="text-sm font-medium">Témoignages</span>
                    </a>

                    <a href="donations.php" class="flex items-center gap-2 text-gray-700 hover:text-primary-gold transition">
                        <i data-feather="heart" class="w-4 h-4"></i><span class="text-sm font-medium">Dons</span>
                    </a>

                    <a href="contact.php" class="flex items-center gap-2 text-gray-700 hover:text-primary-gold transition">
                        <i data-feather="phone-call" class="w-4 h-4"></i><span class="text-sm font-medium">Contact</span>
                    </a>

                    <!-- Language selector (desktop) -->
                    <div class="relative">
                        <button id="lang-btn" class="flex items-center gap-2 border border-gray-200 rounded-full px-3 py-1 text-sm hover:bg-gray-50">
                            <i data-feather="globe" class="w-4 h-4"></i>
                            <span id="lang-label" class="select-none">FR</span>
                            <i data-feather="chevron-down" class="w-4 h-4"></i>
                        </button>

                        <div id="lang-menu" class="hidden absolute right-0 mt-2 w-44 bg-white border rounded-md py-2 shadow-lg">
                            <button class="w-full text-left px-3 py-2 hover:bg-gray-100" data-lang="fr"><a href="../French/index.php">Français (FR)</a></button>
                            <button class="w-full text-left px-3 py-2 hover:bg-gray-100" data-lang="en"><a href="../English/index.php">English (EN)</a></button>
                            <button class="w-full text-left px-3 py-2 hover:bg-gray-100" data-lang="sw"><a href="../Swahili/index.php">Kiswahili (SW)</a></button>
                        </div>
                    </div>
                </nav>


                <!-- mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" aria-label="Open menu" class="p-2 rounded-md border border-gray-200">
                        <i data-feather="menu" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- mobile nav panel -->
        <div id="mobile-nav" class="hidden md:hidden bg-white border-t">
            <div class="px-4 py-4 space-y-3">
                <a href="#accueil" class="block flex items-center gap-3"><i data-feather="home" class="w-5 h-5"></i>Accueil</a>
                <a href="#about" class="block flex items-center gap-3"><i data-feather="info" class="w-5 h-5"></i>À Propos</a>
                <a href="#missions" class="block flex items-center gap-3"><i data-feather="flag" class="w-5 h-5"></i>Missions</a>
                <a href="#events" class="block flex items-center gap-3"><i data-feather="calendar" class="w-5 h-5"></i>Événements</a>
                <a href="#testimonials" class="block flex items-center gap-3"><i data-feather="message-circle" class="w-5 h-5"></i>Témoignages</a>
                <a href="#donations" class="block flex items-center gap-3"><i data-feather="heart" class="w-5 h-5"></i>Dons</a>
                <a href="#contact" class="block flex items-center gap-3"><i data-feather="phone-call" class="w-5 h-5"></i>Contact</a>

                <div class="pt-2 border-t mt-2">
                    <div class="flex items-center gap-2">
                        <i data-feather="globe" class="w-5 h-5"></i>
                        <select id="mobile-lang" class="w-full border rounded px-2 py-1">
                            <option value="fr"><a href="../French/index.php">Français (FR)</a></option>
                            <option value="en"><a href="../English/index.php">English (EN)</a></option>
                            <option value="sw"><a href="../Swahili/index.php">Kiswahili (SW)</a></option>
                        </select>
                    </div>

                    <div class="mt-3 flex items-center gap-3">
                        <a href="#" class="flex items-center gap-2 text-gray-700"><i data-feather="facebook" class="w-5 h-5"></i></a>
                        <a href="#" class="flex items-center gap-2 text-gray-700"><i data-feather="twitter" class="w-5 h-5"></i></a>
                        <a href="#" class="flex items-center gap-2 text-gray-700"><i data-feather="instagram" class="w-5 h-5"></i></a>
                        <a href="#" class="flex items-center gap-2 text-gray-700"><i data-feather="linkedin" class="w-5 h-5"></i></a>
                    </div>

                    <a href="login.php" class="mt-3 inline-flex items-center gap-2 px-3 py-2 rounded-md border text-sm">
                        <i data-feather="user-plus" class="w-4 h-4"></i>Se connecter
                    </a>
                </div>
            </div>
        </div>
    </header>



    <!-- ABOUT -->
    <section id="about" class="py-16 md:py-24 bg-light-beige">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="font-bold text-3xl md:text-4xl">Qui Sommes-Nous ?</h2>
                <p class="mt-3 text-gray-600 max-w-2xl mx-auto">Un groupe missionnaire dédié à l'évangélisation et au service humanitaire</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="fade-in">
                    <h3 class="text-2xl font-semibold text-primary-gold mb-4">Notre Mission</h3>
                    <p class="mb-4">Depuis plus de 20 ans, nous œuvrons pour répandre la Parole de Dieu et apporter un soutien concret aux communautés dans le besoin à travers le monde.</p>
                    <p class="mb-4">Notre groupe missionnaire est animé par la foi, la compassion et le désir de servir. Nous croyons que chaque personne mérite d'entendre le message d'amour du Christ et de recevoir une aide concrète dans les moments difficiles.</p>
                    <p class="mb-6">Nos valeurs fondamentales sont l'amour inconditionnel, l'intégrité, le service désintéressé et la foi inébranlable en Dieu.</p>
                    <a href="mission.php" class="inline-flex items-center gap-2 bg-primary-gold text-white px-5 py-3 rounded-full">
                        <i data-feather="arrow-right" class="w-4 h-4"></i>Voir Plus
                    </a>
                </div>

                <div class="rounded-md overflow-hidden h-80 card-shadow">
                    <img src="https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=1200" alt="Équipe" class="w-full h-full object-cover">
                </div>
            </div>

            <!-- stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-12">
                <div class="bg-gray-200 p-6 rounded card-shadow text-center">
                    <div class=" font-bold text-4xl text-primary-gold stat-number">120</div>
                    <div class="text-gray-600 mt-2">Missions Accomplies</div>
                </div>
                <div class="bg-gray-200 p-6 rounded card-shadow text-center">
                    <div class=" font-bold text-4xl text-primary-gold stat-number">45</div>
                    <div class="text-gray-600 mt-2">Pays Visités</div>
                </div>
                <div class="bg-gray-200 p-6 rounded card-shadow text-center">
                    <div class=" font-bold text-4xl text-primary-gold stat-number">10000</div>
                    <div class="text-gray-600 mt-2">Vies Touchées</div>
                </div>
                <div class="bg-gray-200 p-6 rounded card-shadow text-center">
                    <div class=" font-bold text-4xl text-primary-gold stat-number">200</div>
                    <div class="text-gray-600 mt-2">Missionnaires</div>
                </div>
            </div>
        </div>
    </section>

    <!-- MISSIONS -->
    <section id="missions" class="py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class=" text-3xl md:text-4xl font-bold">Nos Missions</h2>
                <p class="mt-3 text-gray-600 max-w-2xl mx-auto">Découvrez nos projets actuels et passés à travers le monde</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- mission card -->
                <article class="mission-card bg-white rounded-md overflow-hidden card-shadow">
                    <div class="h-56 mission-image bg-[url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=900')] bg-cover bg-center"></div>
                    <div class="p-6">
                        <span class="inline-block bg-primary-gold text-white px-3 py-1 rounded-full text-sm mb-3">En cours</span>
                        <h3 class="text-xl font-semibold mb-2">Évangélisation en Afrique</h3>
                        <p class="text-gray-600 mb-4">Programme d'évangélisation et de formation biblique dans les villages ruraux d'Afrique de l'Ouest, touchant plus de 2000 personnes.</p>
                        <a href="#" class="text-primary-gold inline-flex items-center gap-2 font-medium">
                            En savoir plus <i data-feather="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </article>

                <!-- mission card -->
                <article class="mission-card bg-white rounded-md overflow-hidden card-shadow">
                    <div class="h-56 mission-image bg-[url('https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?w=900')] bg-cover bg-center"></div>
                    <div class="p-6">
                        <span class="inline-block bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm mb-3">Terminé</span>
                        <h3 class=" text-xl font-semibold mb-2">Aide Humanitaire en Asie</h3>
                        <p class="text-gray-600 mb-4">Distribution de nourriture, médicaments et fournitures scolaires dans les régions défavorisées d'Asie du Sud-Est.</p>
                        <a href="#" class="text-primary-gold inline-flex items-center gap-2 font-medium">
                            En savoir plus <i data-feather="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </article>

                <!-- mission card -->
                <article class="mission-card bg-white rounded-md overflow-hidden card-shadow">
                    <div class="h-56 mission-image bg-[url('https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=900')] bg-cover bg-center"></div>
                    <div class="p-6">
                        <span class="inline-block bg-primary-gold text-white px-3 py-1 rounded-full text-sm mb-3">En cours</span>
                        <h3 class=" text-xl font-semibold mb-2">Construction d'Églises</h3>
                        <p class="text-gray-600 mb-4">Édification de lieux de culte et de centres communautaires dans les zones reculées d'Amérique Latine.</p>
                        <a href="#" class="text-primary-gold inline-flex items-center gap-2 font-medium">
                            En savoir plus <i data-feather="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </article>
            </div>

        </div>
    </section>






    <!-- FOOTER -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <img src="../assert/masaka.png" alt="logo" class="h-12 w-12 rounded-full object-cover">
                    <div>
                        <div class=" font-bold text-lg text-primary-gold">MASAKA INITIATIVE</div>
                        <div class="text-xs text-gray-300">Mission Mondiale</div>
                    </div>
                </div>
                <p class="text-gray-300 text-sm">Un groupe missionnaire chrétien dédié à répandre l'amour et la parole de Dieu à travers le monde depuis 2004.</p>
                <div class="flex items-center gap-3 mt-4">
                    <a href="#" class="p-2 rounded-full bg-primary-gold text-white"><i data-feather="facebook" class="w-4 h-4"></i></a>
                    <a href="#" class="p-2 rounded-full bg-primary-gold text-white"><i data-feather="instagram" class="w-4 h-4"></i></a>
                    <a href="#" class="p-2 rounded-full bg-primary-gold text-white"><i data-feather="twitter" class="w-4 h-4"></i></a>
                    <a href="#" class="p-2 rounded-full bg-primary-gold text-white"><i data-feather="linkedin" class="w-4 h-4"></i></a>
                </div>
            </div>

            <div>
                <h4 class=" font-semibold text-lg text-primary-gold mb-3">Liens Rapides</h4>
                <ul class="text-gray-300 space-y-2">
                    <li><a href="#accueil" class="hover:text-white">Accueil</a></li>
                    <li><a href="#about" class="hover:text-white">À Propos</a></li>
                    <li><a href="#missions" class="hover:text-white">Nos Missions</a></li>
                    <li><a href="#events" class="hover:text-white">Événements</a></li>
                    <li><a href="#donations" class="hover:text-white">Faire un Don</a></li>
                </ul>
            </div>

            <div>
                <h4 class=" font-semibold text-lg text-primary-gold mb-3">Ressources</h4>
                <ul class="text-gray-300 space-y-2">
                    <li><a href="#" class="hover:text-white">Blog Missionnaire</a></li>
                    <li><a href="#" class="hover:text-white">Galerie Photos</a></li>
                    <li><a href="#" class="hover:text-white">Rapports Annuels</a></li>
                    <li><a href="#" class="hover:text-white">Prières du Jour</a></li>
                </ul>
            </div>

            <div>
                <h4 class=" font-semibold text-lg text-primary-gold mb-3">Newsletter</h4>
                <p class="text-gray-300 text-sm">Recevez nos actualités et témoignages chaque mois.</p>
                <form id="newsletter" class="mt-4 flex gap-2">
                    <input type="email" placeholder="Votre email" required class="w-full px-3 py-2 rounded-md text-gray-900">
                    <button class="px-4 py-2 rounded-md bg-primary-gold text-white">→</button>
                </form>
            </div>
        </div>

        <div class="border-t border-gray-800 py-6 text-center text-gray-400 text-sm">
            &copy; <span id="current-year"></span> Masaka Initiative. Tous droits réservés. &middot; <a href="#" class="hover:text-white">Mentions Légales</a> &middot; <a href="#" class="hover:text-white">Politique de Confidentialité</a>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();

            /* ---------------------------------
                LANGUAGE MENU (DESKTOP)
            ----------------------------------*/
            const langBtn = document.getElementById('lang-btn');
            const langMenu = document.getElementById('lang-menu');
            const langLabel = document.getElementById('lang-label');

            langBtn && langBtn.addEventListener('click', () => {
                langMenu.classList.toggle('hidden');
            });

            langMenu && langMenu.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', () => {
                    const lang = btn.dataset.lang || 'fr';
                    langLabel.textContent = lang.toUpperCase();
                    langMenu.classList.add('hidden');
                });
            });

            /* ---------------------------------
                MOBILE MENU
            ----------------------------------*/
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const mobileNav = document.getElementById('mobile-nav');

            mobileBtn && mobileBtn.addEventListener('click', () => {
                mobileNav.classList.toggle('hidden');
            });

            /* ---------------------------------
                MOBILE LANGUAGE SELECT
            ----------------------------------*/
            const mobileLang = document.getElementById('mobile-lang');
            mobileLang && mobileLang.addEventListener('change', () => {
                // Handle language change here
            });

            /* ---------------------------------
                SMOOTH SCROLL
            ----------------------------------*/
            document.querySelectorAll('a[href^="#"]').forEach(a => {
                a.addEventListener('click', function(e) {
                    if (a.closest('#mobile-nav')) mobileNav.classList.add('hidden');
                    const href = this.getAttribute('href');
                    if (href && href.startsWith('#')) {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            /* ---------------------------------
                FADE-IN ANIMATION
            ----------------------------------*/
            const io = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) entry.target.classList.add('visible');
                });
            }, {
                threshold: 0.12
            });

            document.querySelectorAll('.fade-in').forEach(el => io.observe(el));

            /* ---------------------------------
                CURRENT YEAR
            ----------------------------------*/
            document.getElementById('current-year').textContent = new Date().getFullYear();

        });
    </script>

</body>

</html>