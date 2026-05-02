<?php
require_once __DIR__ . '/lang_init.php';
require_once __DIR__ . '/../API/config.php';

// Fetch settings
$settings_query = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settings_query->fetch_assoc();

// Fetch about content
$about = fetchContent($conn, 'about', "status = 'active' AND language_id = $language_id", '', 1)->fetch_assoc() ?? [];

// If no about content in current language, try to get fallback
if (empty($about)) {
    $about = fetchContent($conn, 'about', "status = 'active' AND language_id = 1", '', 1)->fetch_assoc() ?? [];
}

// Fetch team members
$team_query = $conn->query("SELECT * FROM team WHERE status = 'active' ORDER BY sort_order ASC LIMIT 4");

// Stats - Filter by current language where applicable
$stats = [];
$stats['missions'] = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'completed' AND language_id = $language_id")->fetch_assoc()['count'] ?? 0;
$stats['countries'] = $conn->query("SELECT COUNT(DISTINCT location) as count FROM events WHERE location IS NOT NULL AND language_id = $language_id")->fetch_assoc()['count'] ?? 0;
$stats['lives'] = $conn->query("SELECT COUNT(*) as count FROM team WHERE status = 'active'")->fetch_assoc()['count'] ?? 0;
$stats['members'] = $conn->query("SELECT COUNT(*) as count FROM team WHERE status = 'active'")->fetch_assoc()['count'] ?? 0;

// If no stats in current language, get overall stats as fallback
if ($stats['missions'] == 0) {
    $stats['missions'] = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'completed'")->fetch_assoc()['count'] ?? 45;
    $stats['countries'] = $conn->query("SELECT COUNT(DISTINCT location) as count FROM events WHERE location IS NOT NULL")->fetch_assoc()['count'] ?? 20;
}
if ($stats['lives'] == 0) {
    $stats['lives'] = 500;
}
if ($stats['members'] == 0) {
    $stats['members'] = 12;
}

// Translation array
$text = [
    1 => [
        'page_title' => 'About Us',
        'hero_title' => 'About Us',
        'hero_subtitle' => 'Learn about our mission and vision',
        'who_we_are' => 'Who We Are',
        'missionary_group' => 'We provide free tools and training to help 1,000 small makers grow sustainable businesses by 2026. Because when local creators succeed, communities thrive.',
        'our_story' => 'Our Story',
        'mission' => 'Mission',
        'our_mission' => 'Our Mission',
        'vision' => 'Vision',
        'meet_team' => 'Meet Our Team',
        'view_all' => 'View All',
        'see_more' => 'See More',
        'join_mission' => 'Join Our Mission',
        'join_text' => 'Be part of our mission to make a difference in world.',
        'donate_now' => 'Donate Now',
        'learn_more' => 'Learn More',
        'missions' => 'Missions',
        'countries' => 'Countries',
        'team_members' => 'Team Members',
        'years' => 'Years',
        'missions_completed' => 'Missions Completed',
        'countries_visited' => 'Countries Visited',
        'lives_touched' => 'Lives Touched',
        'missionaries' => 'Missionaries',
        'content_soon' => 'Content coming soon...'
    ],
    2 => [
        'page_title' => 'Kuhusu Sisi',
        'hero_title' => 'Kuhusu Sisi',
        'hero_subtitle' => 'Jifunze kuhusu dhamira na dira yetu',
        'who_we_are' => 'Tutawezaje',
        'missionary_group' => 'Tunatoa zana bure na mafunzo kusaidia wafanyabiashara wadogo 1,000 kukua kibiashara endelevu ifikapo 2026. Kwa sababu wakati waundaji wa ndani wanafanikiwa, jamii hufanikiwa.',
        'our_story' => 'Hadithi Yetu',
        'mission' => 'Dhamira',
        'our_mission' => 'Dhamira Yetu',
        'vision' => 'Dira',
        'meet_team' => 'Kutana na Timu',
        'view_all' => 'Tazama Wote',
        'see_more' => 'Ona Zaidi',
        'join_mission' => 'Jiunge na Dhamira',
        'join_text' => 'Kuwa sehemu ya dhamira yetu kufanya tofauti duniani.',
        'donate_now' => 'Changia Sasa',
        'learn_more' => 'Jifunze Zaidi',
        'missions' => 'Misheni',
        'countries' => 'Nchi',
        'team_members' => 'Wanachama wa Timu',
        'years' => 'Miaka',
        'missions_completed' => 'Misheni Zilizokamilika',
        'countries_visited' => 'Nchi Zilizotembelewa',
        'lives_touched' => 'Maisha Yaliyoathiriwa',
        'missionaries' => 'Wamisheni',
        'content_soon' => 'Maudhui yaja hivi karibuni...'
    ],
    3 => [
        'page_title' => 'À Propos',
        'hero_title' => 'À Propos',
        'hero_subtitle' => 'Apprenez à propos de notre mission et vision',
        'who_we_are' => 'Qui Nous Sommes',
        'missionary_group' => 'Nous fournissons des outils gratuits et une formation pour aider 1 000 petits créateurs à développer des entreprises durables d\'ici 2026. Parce que lorsque les créateurs locaux réussissent, les communautés prospèrent.',
        'our_story' => 'Notre Histoire',
        'mission' => 'Mission',
        'our_mission' => 'Notre Mission',
        'vision' => 'Vision',
        'meet_team' => 'Rencontrez Notre Équipe',
        'view_all' => 'Voir Tout',
        'see_more' => 'Voir Plus',
        'join_mission' => 'Rejoindre Notre Mission',
        'join_text' => 'Faites partie de notre mission pour faire une différence dans le monde.',
        'donate_now' => 'Faire un Don',
        'learn_more' => 'En Savoir Plus',
        'missions' => 'Missions',
        'countries' => 'Pays',
        'team_members' => 'Membres d\'Équipe',
        'years' => 'Années',
        'missions_completed' => 'Missions Accomplies',
        'countries_visited' => 'Pays Visités',
        'lives_touched' => 'Vies Touchées',
        'missionaries' => 'Missionnaires',
        'content_soon' => 'Contenu à venir bientôt...'
    ]
];

$t = $text[$language_id] ?? $text[1];

ob_start();
?>


<!-- Hero Section -->
<section class="relative h-96 bg-dark-blue flex items-center justify-center text-white">
    <div class="text-center z-10">
        <h1 class="text-4xl lg:text-5xl font-bold mb-4"><?php echo $t['hero_title']; ?></h1>
        <div class="w-24 h-1 bg-primary-gold mx-auto mb-6 rounded-full"></div>
        <p class="text-xl text-gray-200 max-w-2xl mx-auto"><?php echo $t['hero_subtitle']; ?></p>
    </div>
</section>

<!-- ABOUT SECTION -->
<section class="about-section py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- About Grid -->
        <div class="about-grid grid lg:grid-cols-2 gap-12 lg:gap-16 items-center mb-20">
            <!-- Left: Content -->
            <div class="about-content fade-in space-y-6">
                <h3 class="text-2xl lg:text-3xl font-bold text-dark-blue relative inline-block pb-3">
                    <?php echo htmlspecialchars($about['mission_title'] ?? $t['our_mission']); ?>
                    <span class="absolute bottom-0 left-0 w-12 h-1 bg-primary-gold rounded-full"></span>
                </h3>
                <div class="text-gray-600 leading-relaxed text-base lg:text-lg space-y-4">
                    <?php 
                        $content = trim($about['content'] ?? '');
                        if (empty($content)) {
                            $content = 'We are dedicated to making a positive impact in our communities through various initiatives and programs.';
                        }
                        echo nl2br(htmlspecialchars($content));
                    ?>
                </div>
                <div class="pt-4">
                    <a href="<?php echo htmlspecialchars(getLanguageUrl('about.php')); ?>" 
                       class="inline-flex items-center gap-2 bg-primary-gold text-white font-semibold px-6 py-3 rounded-full hover:bg-primary-gold/90 transition-all duration-300 hover:translate-x-1">
                        <span><?php echo htmlspecialchars($t['see_more']); ?></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Right: Image -->
            <div class="about-image fade-in relative group">
                <div class="relative rounded-lg overflow-hidden ">
                    <img 
                        src="../<?php echo htmlspecialchars($about['image_url'] ?? ''); ?>" 
                        alt="About us" 
                        class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-105"
                        onerror="this.src='https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800&h=600&fit=crop'"
                        loading="lazy"
                    >
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
            <div class="stat-card fade-in text-center bg-light-gray p-6 rounded-lg shadow-md">
                <div class="stat-number text-4xl lg:text-5xl font-bold text-dark-blue mb-2" data-target="<?php echo $stats['missions']; ?>">0</div>
                <div class="stat-label text-gray-600 font-medium"><?php echo htmlspecialchars($t['missions_completed']); ?></div>
            </div>
            <div class="stat-card fade-in text-center bg-light-gray p-6 rounded-lg shadow-md">
                <div class="stat-number text-4xl lg:text-5xl font-bold text-dark-blue mb-2" data-target="<?php echo $stats['countries']; ?>">0</div>
                <div class="stat-label text-gray-600 font-medium"><?php echo htmlspecialchars($t['countries_visited']); ?></div>
            </div>
            <div class="stat-card fade-in text-center bg-light-gray p-6 rounded-lg shadow-md">
                <div class="stat-number text-4xl lg:text-5xl font-bold text-dark-blue mb-2" data-target="<?php echo $stats['lives']; ?>">0</div>
                <div class="stat-label text-gray-600 font-medium"><?php echo htmlspecialchars($t['lives_touched']); ?></div>
            </div>
            <div class="stat-card fade-in text-center bg-light-gray p-6 rounded-lg shadow-md  ">
                <div class="stat-number text-4xl lg:text-5xl font-bold text-dark-blue mb-2" data-target="<?php echo $stats['members']; ?>">0</div>
                <div class="stat-label text-gray-600 font-medium"><?php echo htmlspecialchars($t['missionaries']); ?></div>
            </div>
        </div>
    </div>
</section>

<!-- MISSION & VISION SECTION -->
<section class="py-14 bg-light-gray">
    <div class="max-w-6xl mx-auto px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-4xl lg:text-5xl font-bold text-dark-blue mb-4">Our Mission & Vision</h2>
            <div class="w-24 h-1 bg-primary-gold mx-auto rounded-full"></div>
            <p class="text-gray-600 mt-6 max-w-2xl mx-auto">Guided by faith and purpose, we strive to make a lasting impact in our communities and beyond.</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
            <!-- Mission Card -->
            <div class="group bg-white rounded-lg shadow-lg hover:shadow-lg transition-all duration-300 hover:-translate-y-2 overflow-hidden">
                <!-- Mission Image -->
                <?php if (!empty($about['mission_image'])): ?>
                    <div class="relative h-56 w-full overflow-hidden bg-gray-100">
                        <img 
                            src="../<?php echo htmlspecialchars($about['mission_image']); ?>" 
                            alt="Mission illustration" 
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            onerror="this.src='https://placehold.co/800x400/f3f4f6/9ca3af?text=Mission'"
                            loading="lazy"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
                    </div>
                <?php endif; ?>
                
                <!-- Mission Content -->
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-primary-gold/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                            </svg>
                        </div>
                        <span class="text-primary-gold font-semibold text-sm uppercase tracking-wider">Our Commitment</span>
                    </div>
                    <h4 class="text-xl lg:text-xl font-bold text-dark-blue mb-4 group-hover:text-primary-gold transition-colors duration-300">
                        <?php echo htmlspecialchars($about['mission_title'] ?? $t['mission'] ?? 'Our Mission'); ?>
                    </h4>
                    <div class="w-12 h-0.5 bg-primary-gold/40 mb-5 rounded-full"></div>
                    <p class="text-gray-600 leading-relaxed text-base lg:text-lg">
                        <?php 
                            $mission_text = trim($about['mission_text'] ?? '');
                            if (empty($mission_text)) {
                                $mission_text = 'To spread the love of Christ through evangelism, humanitarian service, and community development, making a positive impact in the lives of people around the world.';
                            }
                            echo nl2br(htmlspecialchars($mission_text));
                        ?>
                    </p>
                </div>
            </div>

            <!-- Vision Card -->
            <div class="group bg-white rounded-lg shadow-lg hover:shadow-lg transition-all duration-300 hover:-translate-y-2 overflow-hidden">
                <!-- Vision Image -->
                <?php if (!empty($about['vision_image'])): ?>
                    <div class="relative h-56 w-full overflow-hidden bg-gray-100">
                        <img 
                            src="../<?php echo htmlspecialchars($about['vision_image']); ?>" 
                            alt="Vision illustration" 
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            onerror="this.src='https://placehold.co/800x400/f3f4f6/9ca3af?text=Vision'"
                            loading="lazy"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
                    </div>
                <?php endif; ?>
                
                <!-- Vision Content -->
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-primary-gold/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 4C7 4 2.73 7.11 1 11.5 2.73 15.89 7 19 12 19s9.27-3.11 11-7.5C21.27 7.11 17 4 12 4zm0 12.5c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                        </div>
                        <span class="text-primary-gold font-semibold text-sm uppercase tracking-wider">Our Aspiration</span>
                    </div>
                    <h4 class="text-xl lg:text-xl font-bold text-dark-blue mb-4 group-hover:text-primary-gold transition-colors duration-300">
                        <?php echo htmlspecialchars($about['vision_title'] ?? $t['vision'] ?? 'Our Vision'); ?>
                    </h4>
                    <div class="w-12 h-0.5 bg-primary-gold/40 mb-5 rounded-full"></div>
                    <p class="text-gray-600 leading-relaxed text-base lg:text-lg">
                        <?php 
                            $vision_text = trim($about['vision_text'] ?? '');
                            if (empty($vision_text)) {
                                $vision_text = 'To be a beacon of hope and transformation, creating communities where faith flourishes, needs are met, and people experience the unconditional love of God.';
                            }
                            echo nl2br(htmlspecialchars($vision_text));
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-24 bg-primary-gold">
    <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center">
        <div class="space-y-6">
            <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4"><?php echo $t['join_mission']; ?></h2>
            <p class="text-xl text-white/90 max-w-2xl mx-auto leading-relaxed"><?php echo $t['join_text']; ?></p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="donations.php?lang=<?php echo $lang_code; ?>" class="bg-white text-primary-gold hover:bg-gray-100 hover:text-primary-gold font-bold px-8 py-3 rounded-full transition-all duration-300">
                    <?php echo $t['donate_now']; ?>
                </a>
                <a href="contact.php?lang=<?php echo $lang_code; ?>" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-primary-gold font-bold px-8 py-3 rounded-full transition-all duration-300">
                    <?php echo $t['learn_more']; ?>
                </a>
            </div>
        </div>
    </div>
</section>

<script>
// Animated counter
function animateCounters() {
    const counters = document.querySelectorAll('[data-target]');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const increment = target / 100;
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.ceil(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        // Start animation when element is in viewport
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                }
            });
        }, { threshold: 0.5 });
        
        observer.observe(counter);
    });
}

// Fade in animation
document.querySelectorAll('.fade-in').forEach(el => {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.1 });
    observer.observe(el);
});

// Initialize animations
document.addEventListener('DOMContentLoaded', () => {
    animateCounters();
});
</script>

<?php
$content = ob_get_clean();
$page_title = $t['page_title'] . ' - Masaka Initiative';
require_once 'layout.php';
?>
