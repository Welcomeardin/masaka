<?php
require_once __DIR__ . '/lang_init.php';
require_once __DIR__ . '/../API/config.php';

// Fetch settings
$settings_query = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settings_query->fetch_assoc();

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query conditions
$conditions = ["e.language_id = $language_id", "e.status != 'cancelled'"];
if ($status_filter !== 'all') {
    $conditions[] = "e.status = '" . $conn->real_escape_string($status_filter) . "'";
}
if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $conditions[] = "(e.title LIKE '%$search_term%' OR e.description LIKE '%$search_term%' OR e.location LIKE '%$search_term%')";
}
$where_clause = implode(" AND ", $conditions);

// Fetch events
$events_query = $conn->query("
    SELECT e.*, 
           CASE 
               WHEN e.event_date > CURDATE() THEN 'upcoming'
               WHEN e.event_date = CURDATE() THEN 'ongoing'
               ELSE 'completed'
           END as current_status
    FROM events e
    WHERE $where_clause
    ORDER BY 
        CASE 
            WHEN e.event_date >= CURDATE() THEN 0 
            ELSE 1 
        END,
        e.event_date ASC,
        e.event_time ASC
");

// Get counts
$upcoming_count = $conn->query("
    SELECT COUNT(*) as count FROM events 
    WHERE language_id = $language_id AND event_date >= CURDATE() AND status != 'cancelled'
")->fetch_assoc()['count'] ?? 0;

$past_count = $conn->query("
    SELECT COUNT(*) as count FROM events 
    WHERE language_id = $language_id AND event_date < CURDATE() AND status != 'cancelled'
")->fetch_assoc()['count'] ?? 0;

// Translations
$t = [
    1 => ['Events', 'Our Events', 'Discover our upcoming and past events', 'All', 'Upcoming', 'Past', 'Upcoming Events', 'Past Events', 'Search events...', 'Register', 'Share', 'Contact Us', 'No events found', 'View all events'],
    2 => ['Matukio', 'Matukio Yetu', 'Gundua matukio yetu yajayo na yaliyopita', 'Yote', 'Yajayo', 'Yaliyopita', 'Matukio Yajayo', 'Matukio Yaliyopita', 'Tafuta matukio...', 'Jisajili', 'Sambaza', 'Wasiliana Nasi', 'Hakuna matukio', 'Tazama yote'],
    3 => ['Événements', 'Nos Événements', 'Découvrez nos événements à venir et passés', 'Tous', 'À Venir', 'Passés', 'Événements à Venir', 'Événements Passés', 'Rechercher...', 'Je m\'inscris', 'Partager', 'Nous contacter', 'Aucun événement', 'Voir tous']
];
$l = $t[$language_id] ?? $t[3];

ob_start();
?>

<!-- Hero Section -->
<section class="relative h-96 bg-dark-blue flex items-center justify-center text-white">
    <div class="text-center z-10">
        <h1 class="text-4xl lg:text-5xl font-bold mb-4"><?php echo $l[1]; ?></h1>
        <div class="w-24 h-1 bg-primary-gold mx-auto mb-6 rounded-full"></div>
        <p class="text-xl text-gray-200 max-w-2xl mx-auto"><?php echo $l[2]; ?></p>
    </div>
</section>

<section class="py-8 bg-white border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex flex-wrap gap-3">
                <a href="?status=all&lang=<?php echo $lang_code; ?>" class="px-5 py-2 rounded-full transition <?php echo $status_filter == 'all' ? 'bg-primary-gold text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                    <?php echo $l[3]; ?> (<?php echo ($upcoming_count + $past_count); ?>)
                </a>
                <a href="?status=upcoming&lang=<?php echo $lang_code; ?>" class="px-5 py-2 rounded-full transition <?php echo $status_filter == 'upcoming' ? 'bg-primary-gold text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                    <?php echo $l[4]; ?> (<?php echo $upcoming_count; ?>)
                </a>
                <a href="?status=completed&lang=<?php echo $lang_code; ?>" class="px-5 py-2 rounded-full transition <?php echo $status_filter == 'completed' ? 'bg-primary-gold text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                    <?php echo $l[5]; ?> (<?php echo $past_count; ?>)
                </a>
            </div>
            <form method="GET" class="relative w-full md:w-80">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                <input type="hidden" name="lang" value="<?php echo $lang_code; ?>">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="<?php echo $l[8]; ?>" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-primary-gold">
                <i data-feather="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            </form>
        </div>
    </div>
</section>

<section class="py-16 md:py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($events_query && $events_query->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while ($event = $events_query->fetch_assoc()): ?>
                <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition group">
                    <div class="h-48 relative overflow-hidden">
                        <?php if (!empty($event['image_url'])): ?>
                            <img src="../<?php echo $event['image_url']; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-primary-gold to-amber-600 flex items-center justify-center">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="absolute top-4 right-4">
                            <?php if ($event['event_date'] > date('Y-m-d')): ?>
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold"><?php echo $l[4]; ?></span>
                            <?php elseif ($event['event_date'] == date('Y-m-d')): ?>
                                <span class="bg-orange-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Live</span>
                            <?php else: ?>
                                <span class="bg-gray-500 text-white px-3 py-1 rounded-full text-xs font-semibold"><?php echo $l[5]; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 text-gray-500 text-sm mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span><?php echo date('d M Y', strtotime($event['event_date'])); ?></span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo nl2br(htmlspecialchars($event['description'] ?? '')); ?></p>
                        <div class="flex gap-3">
                            <?php if ($event['event_date'] >= date('Y-m-d')): ?>
                                <a href="contact.php?lang=<?php echo $lang_code; ?>" class="flex-1 bg-primary-gold text-white text-center py-2 rounded-full hover:opacity-90 transition text-sm font-medium">
                                    <?php echo $l[9]; ?>
                                </a>
                            <?php endif; ?>
                            <a href="events.php?lang=<?php echo $lang_code; ?>" class="flex-1 border border-primary-gold text-primary-gold text-center py-2 rounded-full hover:bg-primary-gold hover:text-white transition text-sm font-medium">
                                <?php echo $l[13]; ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <svg class="w-20 h-20 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <h3 class="text-2xl font-bold text-gray-600 mb-4"><?php echo $l[12]; ?></h3>
                <div class="flex justify-center gap-4">
                    <a href="contact.php?lang=<?php echo $lang_code; ?>" class="inline-flex items-center gap-2 bg-primary-gold text-white px-6 py-3 rounded-lg hover:bg-primary-gold/90 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <?php echo $l[11]; ?>
                    </a>
                    <a href="events.php?lang=<?php echo $lang_code; ?>" class="inline-flex items-center gap-2 border border-primary-gold text-primary-gold px-6 py-3 rounded-lg hover:bg-primary-gold hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16a1 1 0 001 1h16a1 1 0 001-1V4a1 1 0 00-1-1H4a1 1 0 00-1 1z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17H6v-2h3v2H9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7H6v2h3V7z"></path>
                        </svg>
                        <?php echo $l[13]; ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Fade in animation
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
$page_title = $l[0] . ' - Masaka Initiative';
require_once 'layout.php';
?>
