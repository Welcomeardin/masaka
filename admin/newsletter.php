<?php
session_start();
require_once __DIR__ . '/../auth/require_login.php';
require_once __DIR__ . '/../API/config.php';

$pageTitle = 'Newsletter Management';

// Start output buffering for the content
ob_start();

// Get all languages
$languages_query = $conn->query("SELECT id, code, name FROM languages WHERE is_active = 1");
$langs = [];
if ($languages_query) {
    while ($lang = $languages_query->fetch_assoc()) {
        $langs[$lang['id']] = $lang;
    }
}

// Handle unsubscribe
if (isset($_GET['unsub']) && is_numeric($_GET['unsub'])) {
    $id = (int)$_GET['unsub'];
    $check = $conn->query("SELECT id, email, full_name FROM newsletter_subscribers WHERE id = $id");
    if ($check->num_rows > 0) {
        $subscriber = $check->fetch_assoc();
        $updateQuery = "UPDATE newsletter_subscribers SET status='unsubscribed' WHERE id=$id";
        if ($conn->query($updateQuery)) {
            $_SESSION['swal_message'] = ['title' => 'Unsubscribed!', 'text' => htmlspecialchars($subscriber['email']) . ' has been unsubscribed.', 'icon' => 'success'];
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to unsubscribe: ' . $conn->error, 'icon' => 'error'];
        }
    }
    header("Location: newsletter.php");
    exit;
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $check = $conn->query("SELECT id, email, full_name FROM newsletter_subscribers WHERE id = $deleteId");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $deleteQuery = "DELETE FROM newsletter_subscribers WHERE id = $deleteId";
        if ($conn->query($deleteQuery)) {
            $_SESSION['swal_message'] = ['title' => 'Deleted!', 'text' => 'Subscriber "' . htmlspecialchars($row['email']) . '" has been removed.', 'icon' => 'success'];
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to delete: ' . $conn->error, 'icon' => 'error'];
        }
    }
    header("Location: newsletter.php");
    exit;
}

// Handle resubscribe
if (isset($_GET['resub']) && is_numeric($_GET['resub'])) {
    $id = (int)$_GET['resub'];
    $check = $conn->query("SELECT id, email FROM newsletter_subscribers WHERE id = $id");
    if ($check->num_rows > 0) {
        $updateQuery = "UPDATE newsletter_subscribers SET status='active' WHERE id=$id";
        if ($conn->query($updateQuery)) {
            $_SESSION['swal_message'] = ['title' => 'Resubscribed!', 'text' => 'Subscriber has been reactivated.', 'icon' => 'success'];
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to resubscribe: ' . $conn->error, 'icon' => 'error'];
        }
    }
    header("Location: newsletter.php");
    exit;
}

// Get SweetAlert message from session
$swal_message = null;
if (isset($_SESSION['swal_message'])) {
    $swal_message = $_SESSION['swal_message'];
    unset($_SESSION['swal_message']);
}

// Get filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query with filters
$where_clauses = [];
if ($status_filter && in_array($status_filter, ['active', 'unsubscribed'])) {
    $where_clauses[] = "ns.status = '$status_filter'";
}
if ($search) {
    $search = $conn->real_escape_string($search);
    $where_clauses[] = "(ns.full_name LIKE '%$search%' OR ns.email LIKE '%$search%')";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$subscribers = $conn->query("
    SELECT ns.*, l.name as language_name, l.code as language_code 
    FROM newsletter_subscribers ns
    LEFT JOIN languages l ON ns.language_id = l.id
    $where_sql
    ORDER BY 
        CASE WHEN ns.status = 'active' THEN 0 ELSE 1 END,
        ns.subscribed_at DESC
");

// Get statistics
$stats = [];
$total_query = $conn->query("SELECT COUNT(*) as count FROM newsletter_subscribers");
$stats['total'] = $total_query->fetch_assoc()['count'] ?? 0;

$active_query = $conn->query("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'active'");
$stats['active'] = $active_query->fetch_assoc()['count'] ?? 0;

$unsubscribed_query = $conn->query("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'unsubscribed'");
$stats['unsubscribed'] = $unsubscribed_query->fetch_assoc()['count'] ?? 0;

// Get language distribution
$lang_distribution = $conn->query("
    SELECT l.name, COUNT(ns.id) as count 
    FROM newsletter_subscribers ns
    LEFT JOIN languages l ON ns.language_id = l.id
    WHERE ns.status = 'active'
    GROUP BY ns.language_id
    ORDER BY count DESC
");
?>

<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-active {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-unsubscribed {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .btn-action {
        padding: 6px;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .btn-action:hover {
        transform: scale(1.1);
    }

    .stat-card {
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 14px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .data-table th {
        background-color: #f9fafb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .data-table tbody tr:hover {
        background-color: #f9fafb;
        transition: all 0.2s;
    }

    .data-table tr.unsubscribed-row {
        background-color: #fef2f2;
        opacity: 0.8;
    }

    .filter-btn {
        padding: 6px 16px;
        border-radius: 9999px;
        font-size: 13px;
        transition: all 0.2s;
    }

    .filter-btn-active {
        background-color: #6366f1;
        color: white;
    }

    .filter-btn-inactive {
        background-color: #f3f4f6;
        color: #374151;
    }

    .filter-btn-inactive:hover {
        background-color: #e5e7eb;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        overflow-y: auto;
    }

    .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-30px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .language-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        background-color: #e0e7ff;
        color: #3730a3;
    }
</style>

<div class="space-y-6">
    <!-- Header with Search -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="relative flex-1 max-w-md">
            <i data-feather="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <form method="GET" action="" id="searchForm">
                <input type="text" name="search" id="searchInput" placeholder="Search by name or email..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:ring-2">
                <?php if ($status_filter): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                <?php endif; ?>
            </form>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">Export to CSV</p>
            <a href="newsletter_export.php" class="text-primary-600 hover:text-primary-700 text-sm flex items-center gap-1">
                <i data-feather="download" class="w-4 h-4"></i> Export Subscribers
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Subscribers</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($stats['total']); ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-feather="users" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active</p>
                    <p class="text-2xl font-bold text-green-600 mt-1"><?php echo number_format($stats['active']); ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Unsubscribed</p>
                    <p class="text-2xl font-bold text-red-600 mt-1"><?php echo number_format($stats['unsubscribed']); ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i data-feather="x-circle" class="w-6 h-6 text-red-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Conversion Rate</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">
                        <?php
                        $rate = $stats['total'] > 0 ? round(($stats['active'] / $stats['total']) * 100, 1) : 0;
                        echo $rate . '%';
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i data-feather="trending-up" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="flex flex-wrap gap-2">
        <a href="?<?php echo $search ? 'search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo !$status_filter ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            All
        </a>
        <a href="?status=active<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo $status_filter == 'active' ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            Active
        </a>
        <a href="?status=unsubscribed<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo $status_filter == 'unsubscribed' ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            Unsubscribed
        </a>
    </div>

    <!-- Subscribers Table -->
    <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Language</th>
                        <th>Status</th>
                        <th>Subscribed Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($subscribers && $subscribers->num_rows > 0): ?>
                        <?php while ($sub = $subscribers->fetch_assoc()): ?>
                            <tr class="<?php echo $sub['status'] == 'unsubscribed' ? 'unsubscribed-row' : ''; ?>">
                                <td class="text-sm"><?php echo $sub['id']; ?></td>
                                <td>
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($sub['full_name'] ?: 'N/A'); ?></div>
                                </td>
                                <td>
                                    <div class="text-sm text-gray-700"><?php echo htmlspecialchars($sub['email']); ?></div>
                                </td>
                                <td>
                                    <?php if ($sub['language_name']): ?>
                                        <span class="language-badge"><?php echo htmlspecialchars($sub['language_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">Not specified</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $sub['status']; ?>">
                                        <?php echo ucfirst($sub['status']); ?>
                                    </span>
                                </td>
                                <td class="text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($sub['subscribed_at'])); ?>
                                    <div class="text-xs text-gray-400"><?php echo date('h:i A', strtotime($sub['subscribed_at'])); ?></div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <button onclick="viewSubscriber(<?php echo htmlspecialchars(json_encode($sub)); ?>)" class="btn-action text-blue-600 hover:bg-blue-50 p-2" title="View Details">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </button>
                                        <?php if ($sub['status'] == 'active'): ?>
                                            <a href="newsletter.php?unsub=<?php echo $sub['id']; ?>" class="btn-action text-yellow-600 hover:bg-yellow-50 p-2" title="Unsubscribe" onclick="return confirmUnsubscribe(event, '<?php echo htmlspecialchars($sub['email']); ?>')">
                                                <i data-feather="bell-off" class="w-4 h-4"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="newsletter.php?resub=<?php echo $sub['id']; ?>" class="btn-action text-green-600 hover:bg-green-50 p-2" title="Resubscribe" onclick="return confirmResubscribe(event, '<?php echo htmlspecialchars($sub['email']); ?>')">
                                                <i data-feather="bell" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="confirmDelete(<?php echo $sub['id']; ?>, '<?php echo htmlspecialchars($sub['email']); ?>')" class="btn-action text-red-600 hover:bg-red-50 p-2" title="Delete">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500">
                                <i data-feather="mail" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                                <p>No subscribers found.</p>
                                <?php if ($search || $status_filter): ?>
                                    <p class="text-sm mt-2">Try clearing your filters or search criteria.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Language Distribution Section -->
    <?php if ($lang_distribution && $lang_distribution->num_rows > 0): ?>
        <div class="bg-white rounded border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Subscriber Language Distribution</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php while ($lang = $lang_distribution->fetch_assoc()): ?>
                        <div class="p-4 border rounded-lg hover:shadow-md transition-all">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($lang['name'] ?: 'Not Specified'); ?></p>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo $lang['count']; ?> subscribers</p>
                                </div>
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i data-feather="globe" class="w-5 h-5 text-gray-500"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <?php
                                    $percentage = $stats['active'] > 0 ? ($lang['count'] / $stats['active']) * 100 : 0;
                                    ?>
                                    <div class="bg-primary-500 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Subscriber Details Modal -->
<div id="subscriberModal" class="modal">
    <div class="modal-content">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800">Subscriber Details</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-6">
            <div id="subscriberDetails" class="space-y-4"></div>
        </div>
        <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex justify-end">
            <button onclick="closeModal()" class="px-5 py-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-colors">Close</button>
        </div>
    </div>
</div>

<script>
    let currentSubscriberEmail = null;

    // Initialize Feather Icons
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
    });

    // Show SweetAlert if exists
    <?php if ($swal_message): ?>
        Swal.fire({
            title: '<?php echo $swal_message['title']; ?>',
            text: '<?php echo addslashes($swal_message['text']); ?>',
            icon: '<?php echo $swal_message['icon']; ?>',
            confirmButtonColor: '#6366f1',
            timer: 3000
        });
    <?php endif; ?>

    // Auto-submit search on input
    let searchTimeout;
    document.getElementById('searchInput')?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('searchForm').submit();
        }, 500);
    });

    function viewSubscriber(subscriber) {
        const detailsHtml = `
            <div class="border-b pb-3">
                <p class="text-xs text-gray-500 mb-2">Personal Information</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Full Name</p>
                        <p class="text-gray-900">${escapeHtml(subscriber.full_name || 'Not provided')}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Email Address</p>
                        <p class="text-gray-900">${escapeHtml(subscriber.email)}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Language</p>
                        <p class="text-gray-900">${escapeHtml(subscriber.language_name || 'Not specified')}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Status</p>
                        <span class="status-badge status-${subscriber.status}">${subscriber.status.charAt(0).toUpperCase() + subscriber.status.slice(1)}</span>
                    </div>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-2">Subscription Details</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Subscribed Date</p>
                        <p class="text-gray-900">${new Date(subscriber.subscribed_at).toLocaleDateString()}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Subscribed Time</p>
                        <p class="text-gray-900">${new Date(subscriber.subscribed_at).toLocaleTimeString()}</p>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('subscriberDetails').innerHTML = detailsHtml;
        document.getElementById('subscriberModal').classList.add('active');
    }

    function confirmUnsubscribe(event, email) {
        event.preventDefault();
        Swal.fire({
            title: 'Unsubscribe?',
            text: `Are you sure you want to unsubscribe ${email} from the newsletter?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, unsubscribe',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = event.target.closest('a').href;
            }
        });
        return false;
    }

    function confirmResubscribe(event, email) {
        event.preventDefault();
        Swal.fire({
            title: 'Resubscribe?',
            text: `Are you sure you want to resubscribe ${email} to the newsletter?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, resubscribe',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = event.target.closest('a').href;
            }
        });
        return false;
    }

    function confirmDelete(id, email) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to permanently delete ${email} from the subscriber list. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `newsletter.php?delete=${id}`;
            }
        });
    }

    function closeModal() {
        document.getElementById('subscriberModal').classList.remove('active');
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('subscriberModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>