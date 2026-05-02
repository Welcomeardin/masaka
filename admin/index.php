<?php
// Admin Dashboard - index.php
$pageTitle = 'Dashboard';

// First, include config to get database connection
require_once __DIR__ . '/../API/config.php';

// Start output buffering for the content
ob_start();

// Get real-time statistics from database
$stats = [];

// Total donations
$donation_query = $conn->query("SELECT COUNT(*) as total, SUM(amount) as total_amount FROM donations WHERE status = 'completed'");
$donation_stats = $donation_query->fetch_assoc();
$stats['total_donations'] = $donation_stats['total'] ?? 0;
$stats['total_donation_amount'] = $donation_stats['total_amount'] ?? 0;

// Total contact messages
$contact_query = $conn->query("SELECT COUNT(*) as total FROM contact_messages");
$stats['total_messages'] = $contact_query->fetch_assoc()['total'] ?? 0;

// Unread messages
$unread_query = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'unread'");
$stats['unread_messages'] = $unread_query->fetch_assoc()['total'] ?? 0;

// Active newsletter subscribers
$subscribers_query = $conn->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'active'");
$stats['total_subscribers'] = $subscribers_query->fetch_assoc()['total'] ?? 0;

// Total events
$events_query = $conn->query("SELECT COUNT(*) as total FROM events WHERE status != 'completed'");
$stats['total_events'] = $events_query->fetch_assoc()['total'] ?? 0;

// Upcoming events
$upcoming_query = $conn->query("SELECT COUNT(*) as total FROM events WHERE status = 'upcoming' AND event_date >= CURDATE()");
$stats['upcoming_events'] = $upcoming_query->fetch_assoc()['total'] ?? 0;

// Total gallery items
$gallery_query = $conn->query("SELECT COUNT(*) as total FROM gallery_items WHERE status = 'active'");
$stats['total_gallery_items'] = $gallery_query->fetch_assoc()['total'] ?? 0;

// Total team members
$team_query = $conn->query("SELECT COUNT(*) as total FROM team WHERE status = 'active'");
$stats['total_team_members'] = $team_query->fetch_assoc()['total'] ?? 0;

// Total slides
$slides_query = $conn->query("SELECT COUNT(*) as total FROM slides WHERE status = 'active'");
$stats['total_slides'] = $slides_query->fetch_assoc()['total'] ?? 0;

// Get recent donations
$recent_donations = $conn->query("
    SELECT full_name, email, amount, currency, status, created_at 
    FROM donations 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Get recent contact messages
$recent_messages = $conn->query("
    SELECT id, name, email, subject, status, created_at 
    FROM contact_messages 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Get upcoming events
$upcoming_events_list = $conn->query("
    SELECT e.id, e.title, e.event_date, e.event_time, e.location, e.status, l.name as language_name
    FROM events e
    LEFT JOIN languages l ON e.language_id = l.id
    WHERE e.status IN ('upcoming', 'ongoing') AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC
    LIMIT 5
");

// Get recent activity summary
$recent_activities = [];

// Recent donations
$activity_check = $conn->query("
    SELECT 'donation' as type, full_name as name, created_at, amount as detail 
    FROM donations 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY created_at DESC 
    LIMIT 3
");
if ($activity_check) {
    while ($row = $activity_check->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}

// Recent contact messages
$activity_check2 = $conn->query("
    SELECT 'message' as type, name as name, created_at, subject as detail 
    FROM contact_messages 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY created_at DESC 
    LIMIT 3
");
if ($activity_check2) {
    while ($row = $activity_check2->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}

// Recent newsletter subscribers
$activity_check3 = $conn->query("
    SELECT 'subscriber' as type, full_name as name, subscribed_at as created_at, email as detail 
    FROM newsletter_subscribers 
    WHERE subscribed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY subscribed_at DESC 
    LIMIT 3
");
if ($activity_check3) {
    while ($row = $activity_check3->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}

// Sort activities by date
if (!empty($recent_activities)) {
    usort($recent_activities, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    $recent_activities = array_slice($recent_activities, 0, 10);
}

// Get languages for display
$languages_query = $conn->query("SELECT id, code, name FROM languages WHERE is_active = 1");
$langs = [];
if ($languages_query) {
    while ($lang = $languages_query->fetch_assoc()) {
        $langs[$lang['id']] = $lang;
    }
}

// Get language distribution for content
$lang_distribution = $conn->query("
    SELECT l.name, l.code, COUNT(s.id) as slide_count, COUNT(e.id) as event_count
    FROM languages l
    LEFT JOIN slides s ON l.id = s.language_id AND s.status = 'active'
    LEFT JOIN events e ON l.id = e.language_id AND e.status != 'completed'
    WHERE l.is_active = 1
    GROUP BY l.id
");
?>

<!-- Additional CSS for dashboard -->
<style>
    .stat-card {
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -6px rgba(0, 0, 0, 0.1);
    }

    .activity-timeline-item {
        transition: all 0.2s ease;
    }

    .activity-timeline-item:hover {
        background-color: #f9fafb;
        transform: translateX(2px);
    }

    .progress-bar {
        transition: width 0.5s ease;
    }
</style>

<!-- Dashboard Content - using space-y-6 for vertical spacing -->
<div class="space-y-4">
    <!-- Welcome Section -->
    <div class="bg-white rounded border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>!</h2>
                <p class="text-gray-500 mt-1">Here's what's happening with your website today.</p>
            </div>
            <div class="flex space-x-3">
                <span class="px-3 py-1 bg-green-100 text-green-700 text-sm rounded-full">
                    Last updated: <?php echo date('M d, Y H:i'); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Donations Card -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Donations</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($stats['total_donations']); ?></p>
                    <p class="text-sm text-gray-600 mt-1">
                        $<?php echo number_format($stats['total_donation_amount'], 2); ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i data-feather="heart" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
            <div class="mt-3">
                <a href="donations.php" class="text-sm text-primary-600 hover:text-primary-700">View all donations →</a>
            </div>
        </div>

        <!-- Contact Messages Card -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Contact Messages</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($stats['total_messages']); ?></p>
                    <?php if (($stats['unread_messages'] ?? 0) > 0): ?>
                        <p class="text-sm text-orange-600 mt-1">
                            <?php echo $stats['unread_messages']; ?> unread
                        </p>
                    <?php endif; ?>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i data-feather="message-circle" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
            <div class="mt-3">
                <a href="contact.php" class="text-sm text-primary-600 hover:text-primary-700">View messages →</a>
            </div>
        </div>

        <!-- Events Card -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active Events</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($stats['total_events']); ?></p>
                    <p class="text-sm text-gray-600 mt-1">
                        <?php echo $stats['upcoming_events']; ?> upcoming
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i data-feather="calendar" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
            <div class="mt-3">
                <a href="events.php" class="text-sm text-primary-600 hover:text-primary-700">Manage events →</a>
            </div>
        </div>

        <!-- Newsletter Card -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Newsletter Subscribers</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($stats['total_subscribers']); ?></p>
                    <p class="text-sm text-gray-600 mt-1">Active subscribers</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i data-feather="mail" class="w-6 h-6 text-yellow-600"></i>
                </div>
            </div>
            <div class="mt-3">
                <a href="newsletter.php" class="text-sm text-primary-600 hover:text-primary-700">View subscribers →</a>
            </div>
        </div>
    </div>

    <!-- Secondary Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Gallery Items -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Gallery Items</p>
                    <p class="text-xl font-bold text-gray-800"><?php echo number_format($stats['total_gallery_items']); ?></p>
                </div>
                <i data-feather="grid" class="w-5 h-5 text-gray-400"></i>
            </div>
            <a href="gallery.php" class="text-xs text-primary-600 mt-2 inline-block">Manage gallery →</a>
        </div>

        <!-- Team Members -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Team Members</p>
                    <p class="text-xl font-bold text-gray-800"><?php echo number_format($stats['total_team_members']); ?></p>
                </div>
                <i data-feather="users" class="w-5 h-5 text-gray-400"></i>
            </div>
            <a href="team.php" class="text-xs text-primary-600 mt-2 inline-block">Manage team →</a>
        </div>

        <!-- Slides -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Active Slides</p>
                    <p class="text-xl font-bold text-gray-800"><?php echo number_format($stats['total_slides']); ?></p>
                </div>
                <i data-feather="image" class="w-5 h-5 text-gray-400"></i>
            </div>
            <a href="slides.php" class="text-xs text-primary-600 mt-2 inline-block">Manage slides →</a>
        </div>

        <!-- Languages Active -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Languages</p>
                    <p class="text-xl font-bold text-gray-800"><?php echo count($langs); ?></p>
                </div>
                <i data-feather="globe" class="w-5 h-5 text-gray-400"></i>
            </div>
            <a href="settings.php" class="text-xs text-primary-600 mt-2 inline-block">Manage settings →</a>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Donations -->
        <div class="bg-white rounded border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Recent Donations</h3>
                <a href="donations.php" class="text-sm text-primary-600 hover:text-primary-700">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php if ($recent_donations && $recent_donations->num_rows > 0): ?>
                    <?php while ($donation = $recent_donations->fetch_assoc()): ?>
                        <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <div class="flex-1">
                                <p class="font-medium text-gray-800 text-sm"><?php echo htmlspecialchars($donation['full_name']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($donation['created_at'])); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($donation['currency']); ?> <?php echo number_format($donation['amount'], 2); ?>
                                </p>
                                <span class="inline-block px-2 py-0.5 text-xs rounded-full <?php echo $donation['status'] == 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                    <?php echo ucfirst($donation['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="px-5 py-8 text-center text-gray-500">
                        <i data-feather="heart" class="w-8 h-8 mx-auto text-gray-300 mb-2"></i>
                        <p class="text-sm">No donations yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Contact Messages -->
        <div class="bg-white rounded border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Recent Contact Messages</h3>
                <a href="contact.php" class="text-sm text-primary-600 hover:text-primary-700">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php if ($recent_messages && $recent_messages->num_rows > 0): ?>
                    <?php while ($message = $recent_messages->fetch_assoc()): ?>
                        <div class="px-5 py-3 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between mb-1">
                                <p class="font-medium text-gray-800 text-sm"><?php echo htmlspecialchars($message['name']); ?></p>
                                <span class="inline-block px-2 py-0.5 text-xs rounded-full <?php echo $message['status'] == 'unread' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'; ?>">
                                    <?php echo ucfirst($message['status']); ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($message['subject'] ?: 'No subject'); ?></p>
                            <p class="text-xs text-gray-400 mt-1"><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="px-5 py-8 text-center text-gray-500">
                        <i data-feather="message-circle" class="w-8 h-8 mx-auto text-gray-300 mb-2"></i>
                        <p class="text-sm">No contact messages yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Upcoming Events & Activity Timeline -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Events -->
        <div class="bg-white rounded border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Upcoming Events</h3>
                <a href="events.php" class="text-sm text-primary-600 hover:text-primary-700">Manage events</a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php if ($upcoming_events_list && $upcoming_events_list->num_rows > 0): ?>
                    <?php while ($event = $upcoming_events_list->fetch_assoc()): ?>
                        <div class="px-5 py-3 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-12 text-center">
                                    <div class="bg-primary-50 rounded-lg p-1">
                                        <p class="text-lg font-bold text-primary-600"><?php echo date('d', strtotime($event['event_date'])); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo date('M', strtotime($event['event_date'])); ?></p>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($event['title']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i data-feather="clock" class="w-3 h-3 inline"></i> <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                                        <span class="mx-1">•</span>
                                        <i data-feather="map-pin" class="w-3 h-3 inline"></i> <?php echo htmlspecialchars($event['location'] ?: 'TBD'); ?>
                                    </p>
                                    <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700 mt-2">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="px-5 py-8 text-center text-gray-500">
                        <i data-feather="calendar" class="w-8 h-8 mx-auto text-gray-300 mb-2"></i>
                        <p class="text-sm">No upcoming events</p>
                        <a href="events.php?action=add" class="text-xs text-primary-600 mt-2 inline-block">Create an event →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white rounded border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-800">Recent Activity</h3>
                <p class="text-xs text-gray-500 mt-0.5">Last 7 days</p>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <?php if (!empty($recent_activities)): ?>
                    <div class="relative">
                        <!-- Timeline line -->
                        <div class="absolute left-6 top-0 bottom-0 w-px bg-gray-200"></div>

                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="relative pl-12 pr-5 py-3 activity-timeline-item">
                                <!-- Timeline dot -->
                                <div class="absolute left-5 top-4 -translate-x-1/2 w-3 h-3 rounded-full 
                                    <?php
                                    echo $activity['type'] == 'donation' ? 'bg-green-500' : ($activity['type'] == 'message' ? 'bg-blue-500' : 'bg-yellow-500');
                                    ?> 
                                    ring-2 ring-white">
                                </div>

                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            <?php
                                            if ($activity['type'] == 'donation') {
                                                echo '<i data-feather="heart" class="w-3 h-3 inline text-green-500"></i> New donation from ';
                                            } elseif ($activity['type'] == 'message') {
                                                echo '<i data-feather="message-circle" class="w-3 h-3 inline text-blue-500"></i> New message from ';
                                            } else {
                                                echo '<i data-feather="mail" class="w-3 h-3 inline text-yellow-500"></i> New subscriber: ';
                                            }
                                            ?>
                                            <span class="font-semibold"><?php echo htmlspecialchars($activity['name'] ?? 'Unknown'); ?></span>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <?php
                                            if ($activity['type'] == 'donation') {
                                                echo 'Amount: $' . number_format($activity['detail'] ?? 0, 2);
                                            } elseif ($activity['type'] == 'message') {
                                                echo 'Subject: ' . htmlspecialchars(substr($activity['detail'] ?? '', 0, 50));
                                            } else {
                                                echo htmlspecialchars($activity['detail'] ?? '');
                                            }
                                            ?>
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-400 whitespace-nowrap ml-2">
                                        <?php
                                        $time = strtotime($activity['created_at']);
                                        $now = time();
                                        $diff = $now - $time;

                                        if ($diff < 3600) {
                                            echo floor($diff / 60) . ' min ago';
                                        } elseif ($diff < 86400) {
                                            echo floor($diff / 3600) . ' hours ago';
                                        } else {
                                            echo date('M d', $time);
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="px-5 py-8 text-center text-gray-500">
                        <i data-feather="activity" class="w-8 h-8 mx-auto text-gray-300 mb-2"></i>
                        <p class="text-sm">No recent activity</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Language Content Distribution -->
    <div class="bg-white rounded border border-gray-200 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Content Distribution by Language</h3>
        </div>
        <div class="p-5">
            <div class="space-y-4">
                <?php if ($lang_distribution && $lang_distribution->num_rows > 0): ?>
                    <?php while ($lang = $lang_distribution->fetch_assoc()): ?>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium text-gray-700"><?php echo htmlspecialchars($lang['name']); ?></span>
                                    <span class="text-xs text-gray-400">(<?php echo strtoupper($lang['code']); ?>)</span>
                                </div>
                                <span class="text-sm text-gray-600">
                                    <?php echo ($lang['slide_count'] ?? 0) + ($lang['event_count'] ?? 0); ?> total items
                                </span>
                            </div>
                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                <span>Slides: <?php echo $lang['slide_count'] ?? 0; ?></span>
                                <span>Events: <?php echo $lang['event_count'] ?? 0; ?></span>
                            </div>
                            <?php
                            $total_content = ($lang['slide_count'] ?? 0) + ($lang['event_count'] ?? 0);
                            $max_content = 20;
                            $percentage = min(100, ($total_content / $max_content) * 100);
                            ?>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-primary-500 h-1.5 rounded-full progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No language data available</p>
                <?php endif; ?>
            </div>

            <div class="mt-5 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Quick Actions</span>
                    <div class="flex space-x-3">
                        <a href="slides.php?action=add" class="text-primary-600 hover:text-primary-700">+ Add Slide</a>
                        <a href="events.php?action=add" class="text-primary-600 hover:text-primary-700">+ Create Event</a>
                        <a href="gallery.php?action=add" class="text-primary-600 hover:text-primary-700">+ Upload Media</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Any dashboard-specific JavaScript here
    });
</script>

<?php
// Get the buffered content and assign to $content variable for the layout
$content = ob_get_clean();

// Now include the layout which will display the content
require_once __DIR__ . '/layout.php';
?>