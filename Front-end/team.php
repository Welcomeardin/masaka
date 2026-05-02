<?php
require_once __DIR__ . '/lang_init.php';
require_once __DIR__ . '/../API/config.php';

$settings_query = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settings_query->fetch_assoc();

// Fetch team members
$team_query = $conn->query("SELECT * FROM team WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC");

$text = [
    1 => [
        'page_title' => 'Our Team',
        'hero_title' => 'Our Team',
        'hero_subtitle' => 'Meet the dedicated people behind our mission',
        'soon' => 'Team members coming soon...'
    ],
    2 => [
        'page_title' => 'Timu Yetu',
        'hero_title' => 'Timu Yetu',
        'hero_subtitle' => 'Kutana na watu wanaoijitolea nyuma ya dhamira yetu',
        'soon' => 'Wanachama watakuja hivi karibuni...'
    ],
    3 => [
        'page_title' => 'Notre Équipe',
        'hero_title' => 'Notre Équipe',
        'hero_subtitle' => 'Rencontrez les personnes dévouées derrière notre mission',
        'soon' => 'Les membres arrivent bientôt...'
    ]
];
$t = $text[$language_id] ?? $text[1];

ob_start();
?>

<style>
    .hero-section {
        position: relative;
        height: 50vh;
        min-height: 400px;
        background: linear-gradient(135deg, #2C3E50, #34495e);
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
    }
    .hero-content h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .gold-line {
        width: 60px;
        height: 4px;
        background: #C9A962;
        margin: 1rem auto;
        border-radius: 2px;
    }
    .team-section {
        padding: 6rem 0;
        background: #F8F9FA;
    }
    .team-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }
    .team-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    .team-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    }
    .team-card-image {
        height: 250px;
        overflow: hidden;
    }
    .team-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .team-card:hover .team-card-image img {
        transform: scale(1.05);
    }
    .team-card-content {
        padding: 1.5rem;
        text-align: center;
    }
    .team-card-content h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2C3E50;
        margin-bottom: 0.25rem;
    }
    .team-card-content .role {
        color: #C9A962;
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 0.75rem;
    }
    .team-card-content .bio {
        color: #6b7280;
        font-size: 0.85rem;
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    .social-links {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }
    .social-links a {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #F3F4F6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        transition: all 0.3s ease;
    }
    .social-links a:hover {
        background: #C9A962;
        color: white;
    }
    .fade-in {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease;
    }
    .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }
    @media (max-width: 1024px) {
        .team-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .hero-content h1 { font-size: 2rem; }
        .team-grid { grid-template-columns: 1fr; }
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <h1><?php echo $t['hero_title']; ?></h1>
        <div class="gold-line"></div>
        <p><?php echo $t['hero_subtitle']; ?></p>
    </div>
</section>

<!-- TEAM SECTION -->
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Team Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
            <?php if ($team_query && $team_query->num_rows > 0): ?>
                <?php while ($member = $team_query->fetch_assoc()): ?>
                    <div class="group bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-gray-100 overflow-hidden">
                        <!-- Image Container -->
                        <div class="relative aspect-[4/3] w-full overflow-hidden bg-gray-100">
                            <img 
                                src="../<?php echo htmlspecialchars($member['image_url'] ?? ''); ?>" 
                                alt="<?php echo htmlspecialchars($member['name']); ?>" 
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($member['name']); ?>&background=C9A962&color=fff&size=400&bold=true'"
                                loading="lazy"
                            >
                            <!-- Optional: Overlay gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6 lg:p-8">
                            <h3 class="text-xl lg:text-2xl font-bold text-dark-blue group-hover:text-primary-gold transition-colors duration-300 mb-1">
                                <?php echo htmlspecialchars($member['name']); ?>
                            </h3>
                            <p class="text-primary-gold font-semibold text-sm uppercase tracking-wider mb-4">
                                <?php echo htmlspecialchars($member['role']); ?>
                            </p>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                <?php 
                                    $bio = trim($member['bio'] ?? '');
                                    if (!empty($bio)) {
                                        echo htmlspecialchars(strlen($bio) > 100 ? substr($bio, 0, 100) . '...' : $bio);
                                    } else {
                                        echo '<span class="italic text-gray-400">No bio available</span>';
                                    }
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="col-span-full">
                    <div class="text-center py-16 px-6 bg-gray-50 rounded-2xl">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2"><?php echo $t['team_soon'] ?? 'Coming Soon'; ?></h3>
                        <p class="text-gray-500 max-w-sm mx-auto">Our team member profiles are being prepared. Please check back soon.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>


<script>
    document.querySelectorAll('.fade-in').forEach(el => {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('visible');
            });
        }, { threshold: 0.1 });
        observer.observe(el);
    });
</script>

<?php
$content = ob_get_clean();
$page_title = $t['page_title'] . ' - Masaka Initiative';
require_once 'layout.php';
?>
