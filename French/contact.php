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

    <!-- CONTACT -->
    <section class="py-16 md:py-10 bg-white">

        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.9999999999995!2d2.2922926156746343!3d48.85837307928771!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66fdfd6a8b8b5%3A0x40b82c3688c9460!2sEiffel%20Tower%2C%20Champ%20de%20Mars%2C%205%20Avenue%20Anatole%20France%2C%2075015%20Paris%2C%20France!5e0!3m2!1sen!2sus!4v1616581234567!5m2!1sen!2sus" width="100%" height="450" style="border:0;" allowfullscreen=""></iframe>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-3">
            <div class="text-center mb-8">
                <h2 class="text-3xl md:text-4xl font-bold">Contactez-Nous</h2>
                <p class="mt-3 text-gray-600 max-w-2xl mx-auto">Une question ? Envie de rejoindre notre mission ? Écrivez-nous !</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <!-- form -->
                <form id="contact-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium">Nom complet *</label>
                            <input required type="text" class="mt-1 block w-full border rounded-md px-3 py-2" placeholder="Votre nom">
                        </div>

                        <div>
                            <label class="text-sm font-medium">Email *</label>
                            <input required type="email" class="mt-1 block w-full border rounded-md px-3 py-2" placeholder="votre@email.com">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Téléphone</label>
                        <input type="tel" class="mt-1 block w-full border rounded-md px-3 py-2" placeholder="+257 6 12 34 56 78">
                    </div>

                    <div>
                        <label class="text-sm font-medium">Sujet *</label>
                        <input required type="text" class="mt-1 block w-full border rounded-md px-3 py-2" placeholder="Objet de votre message">
                    </div>

                    <div>
                        <label class="text-sm font-medium">Message *</label>
                        <textarea required rows="6" class="mt-1 block w-full border rounded-md px-3 py-2" placeholder="Écrivez votre message ici..."></textarea>
                    </div>

                    <div>
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-md bg-primary-gold text-white">Envoyer le Message</button>
                    </div>
                </form>

                <!-- contact info -->
                <div class="space-y-6">
                    <div class="bg-light-beige p-6 rounded-xl">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-md bg-primary-gold text-white flex items-center justify-center">📍</div>
                            <div>
                                <h4 class="font-medium">Adresse</h4>
                                <p class="text-sm text-gray-600">123 Avenue de la Mission<br>75015 Paris, France</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light-beige p-6 rounded-md">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-md bg-primary-gold text-white flex items-center justify-center">📞</div>
                            <div>
                                <h4 class="font-medium">Téléphone</h4>
                                <p class="text-sm text-gray-600">+257 1 23 45 67 89<br>Lun-Ven: 9h00 - 18h00</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light-beige p-6 rounded-md">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-md bg-primary-gold text-white flex items-center justify-center">✉️</div>
                            <div>
                                <h4 class="font-medium">Email</h4>
                                <p class="text-sm text-gray-600">contact@lumierechrist.org<br>mission@lumierechrist.org</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light-beige p-6 rounded-md">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-md bg-primary-gold text-white flex items-center justify-center">🌐</div>
                            <div>
                                <h4 class="font-medium">Suivez-nous</h4>
                                <div class="flex items-center gap-3 mt-3">
                                    <a href="#" class="p-3 rounded-full bg-primary-gold text-white"><i data-feather="facebook" class="w-4 h-4"></i></a>
                                    <a href="#" class="p-3 rounded-full bg-primary-gold text-white"><i data-feather="instagram" class="w-4 h-4"></i></a>
                                    <a href="#" class="p-3 rounded-full bg-primary-gold text-white"><i data-feather="twitter" class="w-4 h-4"></i></a>
                                    <a href="#" class="p-3 rounded-full bg-primary-gold text-white"><i data-feather="linkedin" class="w-4 h-4"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
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