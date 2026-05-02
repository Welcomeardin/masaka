<?php
// This layout is included by other pages, so we assume lang_init.php is already included
// But we ensure variables are set for standalone use
if (!isset($language_id)) {
    require_once __DIR__ . '/lang_init.php';
    require_once __DIR__ . '/../API/config.php';
    $settings_query = $conn->query("SELECT * FROM settings LIMIT 1");
    $settings = $settings_query->fetch_assoc();
}
$page_title = $page_title ?? ($language_id == 1 ? 'Masaka Initiative - Mission Mondiale' : ($language_id == 2 ? 'Masaka Initiative - Ulimwengu wa Kimisionari' : 'Masaka Initiative - Mission Mondiale'));
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $page_title; ?></title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- Google Fonts: Inter and Century Gothic fallback -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-gold: #C9A962;
            --dark-blue: #2C3E50;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Century Gothic', 'Segoe UI', sans-serif;
            color: var(--dark-blue);
            background-color: var(--white);
            line-height: 1.6;
        }

        .font-heading {
            font-family: 'Inter', 'Century Gothic', sans-serif;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        .text-primary-gold {
            color: var(--primary-gold);
        }

        .bg-primary-gold {
            background-color: var(--primary-gold);
        }

        .text-dark-blue {
            color: var(--dark-blue);
        }

        .bg-dark-blue {
            background-color: var(--dark-blue);
        }

        /* Top Header Bar */
        .top-header {
            background: var(--dark-blue);
            color: var(--white);
            padding: 0.5rem 0;
            font-size: 0.85rem;
        }

        .top-header-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .top-header-left a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
        }

        .top-header-left a:hover {
            color: var(--primary-gold);
        }

        .top-header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .social-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            background: var(--primary-gold);
            color: var(--white);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .top-header {
                display: none;
            }
        }

        /* Navbar Styles */
        .navbar {
            background: var(--white);
            box-shadow: 0 2px 20px rgba(44, 62, 80, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .nav-logo img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-gold);
            background: var(--white);
        }

        .nav-logo-text {
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--dark-blue);
            letter-spacing: -0.5px;
        }

        .nav-logo-subtitle {
            font-size: 0.7rem;
            color: var(--primary-gold);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: -2px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2.5rem;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark-blue);
            font-weight: 500;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-gold);
            transition: width 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--primary-gold);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .donate-btn {
            background: var(--primary-gold);
            color: var(--white);
            padding: 0.625rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(201, 169, 98, 0.3);
        }

        .donate-btn:hover {
            background: #b8963d;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(201, 169, 98, 0.4);
        }

        .lang-switcher {
            position: relative;
        }

        .lang-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 50px;
            background: var(--white);
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--dark-blue);
            transition: all 0.3s ease;
        }

        .lang-btn:hover {
            border-color: var(--primary-gold);
        }

        .lang-dropdown {
            position: absolute;
            top: 120%;
            right: 0;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(44, 62, 80, 0.15);
            min-width: 160px;
            overflow: hidden;
            border: 1px solid #f3f4f6;
        }

        .lang-dropdown a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            text-decoration: none;
            color: var(--dark-blue);
            font-size: 0.9rem;
            transition: background 0.2s ease;
        }

        .lang-dropdown a:hover {
            background: var(--light-gray);
        }

        .lang-dropdown a.active {
            background: rgba(201, 169, 98, 0.1);
            color: var(--primary-gold);
            font-weight: 600;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }

        .mobile-nav {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--white);
            border-top: 1px solid #f3f4f6;
            box-shadow: 0 10px 40px rgba(44, 62, 80, 0.1);
        }

        .mobile-nav a {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--dark-blue);
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s ease;
        }

        .mobile-nav a:hover {
            background: var(--light-gray);
            color: var(--primary-gold);
        }

        /* Footer Styles */
        .footer {
            background: var(--dark-blue);
            color: var(--white);
            padding: 3rem 0 1.5rem;
        }

        .footer-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 2.5rem;
        }

        .footer-brand p {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            margin-top: 1rem;
            line-height: 1.7;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .footer-logo img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-gold);
            background: var(--white);
        }

        .footer-logo-text {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .footer-logo-subtitle {
            font-size: 0.7rem;
            color: var(--primary-gold);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer h4 {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 1.25rem;
            color: var(--primary-gold);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary-gold);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.5);
        }

        .footer-social {
            display: flex;
            gap: 0.75rem;
        }

        .footer-social a {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .footer-social a:hover {
            background: var(--primary-gold);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            .mobile-menu-btn {
                display: block;
            }
            .mobile-nav {
                display: block;
            }
            .mobile-nav.hidden {
                display: none;
            }
            .donate-btn {
                display: none;
            }
            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .footer-logo {
                justify-content: center;
            }
            .footer-bottom {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
    
    <?php if (isset($custom_css)) echo $custom_css; ?>
</head>

<body>

    <!-- TOP HEADER -->
    <div class="top-header">
        <div class="top-header-container">
            <!-- Left: Contact Info -->
            <div class="top-header-left">
                <?php if (!empty($settings['email'])): ?>
                    <a href="mailto:<?php echo htmlspecialchars($settings['email']); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <span><?php echo htmlspecialchars($settings['email']); ?></span>
                    </a>
                <?php endif; ?>
                <?php if (!empty($settings['phone'])): ?>
                    <a href="tel:<?php echo htmlspecialchars($settings['phone']); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <span><?php echo htmlspecialchars($settings['phone']); ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Right: Social Icons -->
            <div class="top-header-right">
                <?php if (!empty($settings['facebook_link'])): ?>
                    <a href="<?php echo htmlspecialchars($settings['facebook_link']); ?>" target="_blank" class="social-icon" title="Facebook">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if (!empty($settings['instagram_link'])): ?>
                    <a href="<?php echo htmlspecialchars($settings['instagram_link']); ?>" target="_blank" class="social-icon" title="Instagram">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                <?php endif; ?>
                <?php if (!empty($settings['twitter_link'])): ?>
                    <a href="<?php echo htmlspecialchars($settings['twitter_link']); ?>" target="_blank" class="social-icon" title="Twitter">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if (!empty($settings['youtube_link'])): ?>
                    <a href="<?php echo htmlspecialchars($settings['youtube_link']); ?>" target="_blank" class="social-icon" title="YouTube">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></svg>
                    </a>
                <?php endif; ?>
                <?php if (!empty($settings['linkedin_link'])): ?>
                    <a href="<?php echo htmlspecialchars($settings['linkedin_link']); ?>" target="_blank" class="social-icon" title="LinkedIn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <!-- Logo -->
            <a href="<?php echo getLanguageUrl('index.php'); ?>" class="nav-logo">
                <img src="<?php echo !empty($settings['logo_url']) ? '../' . $settings['logo_url'] : '../uploads/settings/default_logo.png'; ?>" alt="Masaka Logo" onerror="this.src='../uploads/settings/default_logo.png'">
                <div>
                    <div class="nav-logo-text">MASAKA INITIATIVE</div>
                    <div class="nav-logo-subtitle">Mission Mondiale</div>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <div class="nav-links">
                <a href="<?php echo getLanguageUrl('index.php'); ?>"><?php echo $language_id == 1 ? 'Home' : ($language_id == 2 ? 'Nyumbani' : 'Accueil'); ?></a>
                <a href="<?php echo getLanguageUrl('about.php'); ?>"><?php echo $language_id == 1 ? 'About' : ($language_id == 2 ? 'Kuhusu' : 'À Propos'); ?></a>
                <a href="<?php echo getLanguageUrl('events.php'); ?>"><?php echo $language_id == 1 ? 'Events' : ($language_id == 2 ? 'Matukio' : 'Événements'); ?></a>
                <a href="<?php echo getLanguageUrl('team.php'); ?>"><?php echo $language_id == 1 ? 'Team' : ($language_id == 2 ? 'Timu' : 'Équipe'); ?></a>
                <a href="<?php echo getLanguageUrl('contact.php'); ?>"><?php echo $language_id == 1 ? 'Contact' : ($language_id == 2 ? 'Mawasiliano' : 'Contact'); ?></a>
            </div>

            <!-- Right Actions -->
            <div class="nav-actions">
                <!-- Donate Button -->
                <a href="<?php echo getLanguageUrl('donations.php'); ?>" class="donate-btn">
                    <?php echo $language_id == 1 ? 'Donate' : ($language_id == 2 ? 'Changia' : 'Don'); ?>
                </a>

                <!-- Language Switcher -->
                <div class="lang-switcher">
                    <button class="lang-btn" id="lang-btn">
                        <span><?php echo strtoupper($lang_code); ?></span>
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div class="lang-dropdown hidden" id="lang-dropdown">
                        <a href="?lang=1" class="<?php echo $language_id == 1 ? 'active' : ''; ?>"><span>🇬🇧</span> English</a>
                        <a href="?lang=2" class="<?php echo $language_id == 2 ? 'active' : ''; ?>"><span>🇰🇪</span> Kiswahili</a>
                        <a href="?lang=3" class="<?php echo $language_id == 3 ? 'active' : ''; ?>"><span>🇫🇷</span> Français</a>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn" id="mobile-menu-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="mobile-nav hidden" id="mobile-nav">
            <a href="<?php echo getLanguageUrl('index.php'); ?>"><?php echo $language_id == 1 ? 'Home' : ($language_id == 2 ? 'Nyumbani' : 'Accueil'); ?></a>
            <a href="<?php echo getLanguageUrl('about.php'); ?>"><?php echo $language_id == 1 ? 'About' : ($language_id == 2 ? 'Kuhusu' : 'À Propos'); ?></a>
            <a href="<?php echo getLanguageUrl('events.php'); ?>"><?php echo $language_id == 1 ? 'Events' : ($language_id == 2 ? 'Matukio' : 'Événements'); ?></a>
            <a href="<?php echo getLanguageUrl('team.php'); ?>"><?php echo $language_id == 1 ? 'Team' : ($language_id == 2 ? 'Timu' : 'Équipe'); ?></a>
            <a href="<?php echo getLanguageUrl('contact.php'); ?>"><?php echo $language_id == 1 ? 'Contact' : ($language_id == 2 ? 'Mawasiliano' : 'Contact'); ?></a>
            <a href="<?php echo getLanguageUrl('donations.php'); ?>" style="background: var(--primary-gold); color: white; margin: 0.5rem 1rem; border-radius: 50px; text-align: center;">
                <?php echo $language_id == 1 ? 'Donate Now' : ($language_id == 2 ? 'Changia Sasa' : 'Faire un Don'); ?>
            </a>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main>
        <?php echo $content ?? ''; ?>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Brand Column -->
                <div class="footer-brand">
                    <div class="footer-logo">
                        <img src="<?php echo !empty($settings['logo_url']) ? '../' . $settings['logo_url'] : '../uploads/settings/default_logo.png'; ?>" alt="Masaka Logo" onerror="this.src='../uploads/settings/default_logo.png'">
                        <div>
                            <div class="footer-logo-text">MASAKA INITIATIVE</div>
                            <div class="footer-logo-subtitle">Mission Mondiale</div>
                        </div>
                    </div>
                    <p><?php echo $language_id == 1 ? 'A Christian missionary group dedicated to spreading love and the word of God around the world since 2004.' : ($language_id == 2 ? 'Kikundi cha kimisionari cha Kikristo kinachojitolea kusambaza upendo na neno la Mungu duniani tangu 2004.' : 'Un groupe missionnaire chrétien dédié à répandre l\'amour et la parole de Dieu dans le monde depuis 2004.'); ?></p>
                    <div class="footer-social">
                        <?php if (!empty($settings['facebook_link'])): ?>
                            <a href="<?php echo htmlspecialchars($settings['facebook_link']); ?>" target="_blank"><i data-feather="facebook" class="w-4 h-4"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['instagram_link'])): ?>
                            <a href="<?php echo htmlspecialchars($settings['instagram_link']); ?>" target="_blank"><i data-feather="instagram" class="w-4 h-4"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['twitter_link'])): ?>
                            <a href="<?php echo htmlspecialchars($settings['twitter_link']); ?>" target="_blank"><i data-feather="twitter" class="w-4 h-4"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['linkedin_link'])): ?>
                            <a href="<?php echo htmlspecialchars($settings['linkedin_link']); ?>" target="_blank"><i data-feather="linkedin" class="w-4 h-4"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4><?php echo $language_id == 1 ? 'Quick Links' : ($language_id == 2 ? 'Viungo Muhimu' : 'Liens Rapides'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo getLanguageUrl('index.php'); ?>"><?php echo $language_id == 1 ? 'Home' : ($language_id == 2 ? 'Nyumbani' : 'Accueil'); ?></a></li>
                        <li><a href="<?php echo getLanguageUrl('about.php'); ?>"><?php echo $language_id == 1 ? 'About' : ($language_id == 2 ? 'Kuhusu' : 'À Propos'); ?></a></li>
                        <li><a href="<?php echo getLanguageUrl('events.php'); ?>"><?php echo $language_id == 1 ? 'Events' : ($language_id == 2 ? 'Matukio' : 'Événements'); ?></a></li>
                        <li><a href="<?php echo getLanguageUrl('team.php'); ?>"><?php echo $language_id == 1 ? 'Team' : ($language_id == 2 ? 'Timu' : 'Équipe'); ?></a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4><?php echo $language_id == 1 ? 'Contact' : ($language_id == 2 ? 'Mawasiliano' : 'Contact'); ?></h4>
                    <ul class="footer-links">
                        <?php if (!empty($settings['email'])): ?>
                            <li><a href="mailto:<?php echo htmlspecialchars($settings['email']); ?>"><?php echo htmlspecialchars($settings['email']); ?></a></li>
                        <?php endif; ?>
                        <?php if (!empty($settings['phone'])): ?>
                            <li><a href="tel:<?php echo htmlspecialchars($settings['phone']); ?>"><?php echo htmlspecialchars($settings['phone']); ?></a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo getLanguageUrl('contact.php'); ?>"><?php echo $language_id == 1 ? 'Contact Form' : ($language_id == 2 ? 'Fomu ya Mawasiliano' : 'Formulaire de Contact'); ?></a></li>
                    </ul>
                </div>

                <!-- Donate CTA -->
                <div>
                    <h4><?php echo $language_id == 1 ? 'Support Us' : ($language_id == 2 ? 'Tusaidie' : 'Soutenez-Nous'); ?></h4>
                    <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem; margin-bottom: 1rem;">
                        <?php echo $language_id == 1 ? 'Your contribution helps us continue our mission.' : ($language_id == 2 ? 'Mchango wako unatusaidia kuendelea na dhamira yetu.' : 'Votre contribution nous aide à poursuivre notre mission.'); ?>
                    </p>
                    <a href="<?php echo getLanguageUrl('donations.php'); ?>" style="display: inline-block; background: var(--primary-gold); color: white; padding: 0.75rem 1.5rem; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 0.875rem; transition: all 0.3s ease;">
                        <?php echo $language_id == 1 ? 'Donate Now' : ($language_id == 2 ? 'Changia Sasa' : 'Faire un Don'); ?>
                    </a>
                </div>
            </div>

            <div class="footer-bottom">
                <div>&copy; <?php echo date('Y'); ?> Masaka Initiative. <?php echo $language_id == 1 ? 'All rights reserved.' : ($language_id == 2 ? 'Haki zote zimehifadhiwa.' : 'Tous droits réservés.'); ?></div>
                <div style="display: flex; gap: 1.5rem;">
                    <a href="#" style="color: rgba(255,255,255,0.5); text-decoration: none; transition: color 0.3s;"><?php echo $language_id == 1 ? 'Privacy Policy' : ($language_id == 2 ? 'Sera ya Faragha' : 'Politique de Confidentialité'); ?></a>
                    <a href="#" style="color: rgba(255,255,255,0.5); text-decoration: none; transition: color 0.3s;"><?php echo $language_id == 1 ? 'Terms of Use' : ($language_id == 2 ? 'Masharti ya Matumizi' : 'Conditions d\'Utilisation'); ?></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();

            // Language switcher - Desktop
            const langBtn = document.getElementById('lang-btn');
            const langMenu = document.getElementById('lang-dropdown');

            if (langBtn && langMenu) {
                // Toggle dropdown
                langBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    langMenu.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!langBtn.contains(e.target) && !langMenu.contains(e.target)) {
                        langMenu.classList.add('hidden');
                    }
                });

                // Close menu after clicking a language link
                langMenu.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        langMenu.classList.add('hidden');
                    });
                });
            }

            // Mobile menu toggle
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const mobileNav = document.getElementById('mobile-nav');
            if (mobileBtn && mobileNav) {
                mobileBtn.addEventListener('click', () => {
                    mobileNav.classList.toggle('hidden');
                });
            }

            // Smooth scrolling
            document.querySelectorAll('a[href^="#"]').forEach(a => {
                a.addEventListener('click', function(e) {
                    if (a.closest('#mobile-nav') && mobileNav) mobileNav.classList.add('hidden');
                    const href = this.getAttribute('href');
                    if (href && href.startsWith('#')) {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            // Fade-in on scroll
            const io = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) entry.target.classList.add('visible');
                });
            }, { threshold: 0.12 });
            document.querySelectorAll('.fade-in').forEach(el => io.observe(el));

            // Newsletter form
            const newsletter = document.getElementById('newsletter');
            if (newsletter) {
                newsletter.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(newsletter);
                    try {
                        const response = await fetch('subscribe.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();
                        alert(result.message || 'Merci pour votre abonnement à la newsletter !');
                        if (result.success) newsletter.reset();
                    } catch (error) {
                        alert('Une erreur est survenue. Veuillez réessayer.');
                    }
                });
            }

            // Current year
            document.getElementById('current-year').textContent = new Date().getFullYear();
        });
    </script>
    
    <?php if (isset($custom_js)) echo $custom_js; ?>
</body>

</html>