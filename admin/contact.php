<?php
session_start();
require_once __DIR__ . '/../auth/require_login.php';
require_once __DIR__ . '/../API/config.php';

$pageTitle = 'Contact Messages';

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

// Handle mark as read/replied/unread
if (isset($_GET['mark']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $mark = $_GET['mark'];
    if (in_array($mark, ['read', 'replied', 'unread'])) {
        if ($conn->query("UPDATE contact_messages SET status='$mark' WHERE id=$id")) {
            $_SESSION['swal_message'] = ['title' => 'Updated!', 'text' => 'Message status updated successfully!', 'icon' => 'success'];
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to update status.', 'icon' => 'error'];
        }
    }
    header("Location: contact.php" . (isset($_GET['filter']) ? "?filter=" . $_GET['filter'] : ""));
    exit;
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $check = $conn->query("SELECT id, name FROM contact_messages WHERE id = $deleteId");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $deleteQuery = "DELETE FROM contact_messages WHERE id = $deleteId";
        if ($conn->query($deleteQuery)) {
            $_SESSION['swal_message'] = ['title' => 'Deleted!', 'text' => 'Message from "' . htmlspecialchars($row['name']) . '" has been deleted.', 'icon' => 'success'];
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to delete: ' . $conn->error, 'icon' => 'error'];
        }
    }
    header("Location: contact.php" . (isset($_GET['filter']) ? "?filter=" . $_GET['filter'] : ""));
    exit;
}

// Handle reply action (just opens email client)
if (isset($_GET['reply']) && isset($_GET['id'])) {
    $replyId = (int)$_GET['reply'];
    $replyQuery = $conn->query("SELECT email, name, subject FROM contact_messages WHERE id = $replyId");
    if ($replyQuery && $replyQuery->num_rows > 0) {
        $replyData = $replyQuery->fetch_assoc();
        $email = $replyData['email'];
        $subject = "Re: " . $replyData['subject'];
        // Open email client
        echo "<script>window.location.href = 'mailto:$email?subject=" . urlencode($subject) . "';</script>";
        exit;
    }
}

// Get SweetAlert message from session
$swal_message = null;
if (isset($_SESSION['swal_message'])) {
    $swal_message = $_SESSION['swal_message'];
    unset($_SESSION['swal_message']);
}

// Get filters
$status_filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query with filters
$where_clauses = [];
if ($status_filter && in_array($status_filter, ['unread', 'read', 'replied'])) {
    $where_clauses[] = "status = '$status_filter'";
}
if ($search) {
    $search = $conn->real_escape_string($search);
    $where_clauses[] = "(name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%' OR message LIKE '%$search%')";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$messages = $conn->query("
    SELECT m.* 
    FROM contact_messages m
    $where_sql
    ORDER BY 
        CASE WHEN m.status = 'new' OR m.status = 'unread' THEN 0 ELSE 1 END,
        m.created_at DESC
");

// Get statistics
$stats = [];
$total_query = $conn->query("SELECT COUNT(*) as count FROM contact_messages");
$stats['total'] = $total_query->fetch_assoc()['count'] ?? 0;

$unread_query = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
$stats['unread'] = $unread_query->fetch_assoc()['count'] ?? 0;

$read_query = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'read'");
$stats['read'] = $read_query->fetch_assoc()['count'] ?? 0;

$replied_query = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'replied'");
$stats['replied'] = $replied_query->fetch_assoc()['count'] ?? 0;
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

    .status-unread {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .status-read {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-replied {
        background-color: #dbeafe;
        color: #1e40af;
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

    .data-table tr.unread-row {
        background-color: #fef2f2;
        font-weight: 500;
    }

    .data-table tr.unread-row:hover {
        background-color: #fee2e2;
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
        max-width: 600px;
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
</style>

<div class="space-y-6">
    <!-- Header with Search -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="relative flex-1 max-w-md">
            <i data-feather="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <form method="GET" action="" id="searchForm">
                <input type="text" name="search" id="searchInput" placeholder="Search by name, email, subject or message..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:ring-2">
                <?php if ($status_filter): ?>
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($status_filter); ?>">
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Messages</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($stats['total']); ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-feather="mail" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Unread</p>
                    <p class="text-2xl font-bold text-red-600 mt-1"><?php echo number_format($stats['unread']); ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i data-feather="mail" class="w-6 h-6 text-red-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Read</p>
                    <p class="text-2xl font-bold text-green-600 mt-1"><?php echo number_format($stats['read']); ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Replied</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1"><?php echo number_format($stats['replied']); ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i data-feather="message-circle" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="flex flex-wrap gap-2">
        <a href="?<?php echo $search ? 'search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo !$status_filter ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            All Messages
        </a>
        <a href="?filter=unread<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo $status_filter == 'unread' ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            Unread
        </a>
        <a href="?filter=read<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo $status_filter == 'read' ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            Read
        </a>
        <a href="?filter=replied<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo $status_filter == 'replied' ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            Replied
        </a>
    </div>

    <!-- Messages Table -->
    <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>From</th>
                        <th>Contact</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($messages && $messages->num_rows > 0): ?>
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <tr class="<?php echo $msg['status'] == 'unread' ? 'unread-row' : ''; ?>">
                                <td class="text-sm"><?php echo $msg['id']; ?></td>
                                <td>
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($msg['name']); ?></div>
                                </td>
                                <td>
                                    <div class="text-sm"><?php echo htmlspecialchars($msg['email']); ?></div>
                                    <?php if ($msg['phone']): ?>
                                        <div class="text-xs text-gray-400"><?php echo htmlspecialchars($msg['phone']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars(substr($msg['subject'], 0, 35)); ?></div>
                                    <?php if (strlen($msg['subject']) > 35): ?>...<?php endif; ?>
                                    <div class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars(substr($msg['message'], 0, 40)); ?>...</div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $msg['status']; ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </td>
                                <td class="text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($msg['created_at'])); ?>
                                    <div class="text-xs text-gray-400"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <button onclick="viewMessage(<?php echo htmlspecialchars(json_encode($msg)); ?>)" class="btn-action text-blue-600 hover:bg-blue-50 p-2" title="View Details">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </button>
                                        <?php if ($msg['status'] != 'read'): ?>
                                            <a href="contact.php?mark=read&id=<?php echo $msg['id']; ?><?php echo $status_filter ? '&filter=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn-action text-green-600 hover:bg-green-50 p-2" title="Mark as Read">
                                                <i data-feather="check-circle" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($msg['status'] != 'replied'): ?>
                                            <a href="contact.php?mark=replied&id=<?php echo $msg['id']; ?><?php echo $status_filter ? '&filter=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn-action text-purple-600 hover:bg-purple-50 p-2" title="Mark as Replied">
                                                <i data-feather="message-circle" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="contact.php?reply=<?php echo $msg['id']; ?>" class="btn-action text-blue-600 hover:bg-blue-50 p-2" title="Reply via Email">
                                            <i data-feather="mail" class="w-4 h-4"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $msg['id']; ?>, '<?php echo htmlspecialchars($msg['name']); ?>')" class="btn-action text-red-600 hover:bg-red-50 p-2" title="Delete">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500">
                                <i data-feather="message-circle" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                                <p>No contact messages found.</p>
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
</div>

<!-- Message Details Modal -->
<div id="messageModal" class="modal">
    <div class="modal-content">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800">Message Details</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-6">
            <div id="messageDetails" class="space-y-4"></div>
        </div>
        <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
            <button onclick="closeModal()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 transition-colors">Close</button>
            <button id="replyFromModalBtn" class="px-5 py-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-colors">Reply</button>
        </div>
    </div>
</div>

<script>
    let currentMessageId = null;

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

    function viewMessage(message) {
        currentMessageId = message.id;

        // Automatically mark as read when viewed
        if (message.status === 'unread') {
            fetch(`contact.php?mark=read&id=${message.id}&<?php echo $status_filter ? 'filter=' . $status_filter : ''; ?><?php echo $search ? 'search=' . urlencode($search) : ''; ?>`)
                .then(() => {
                    // Reload page to update status
                    window.location.reload();
                });
        }

        const detailsHtml = `
            <div class="border-b pb-3">
                <p class="text-xs text-gray-500 mb-2">Sender Information</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Name</p>
                        <p class="text-gray-900">${escapeHtml(message.name)}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Email</p>
                        <p class="text-gray-900">${escapeHtml(message.email)}</p>
                    </div>
                    ${message.phone ? `
                    <div>
                        <p class="text-sm font-medium text-gray-700">Phone</p>
                        <p class="text-gray-900">${escapeHtml(message.phone)}</p>
                    </div>
                    ` : ''}
                    <div>
                        <p class="text-sm font-medium text-gray-700">Date</p>
                        <p class="text-gray-900">${new Date(message.created_at).toLocaleDateString()} at ${new Date(message.created_at).toLocaleTimeString()}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Status</p>
                        <span class="status-badge status-${message.status}">${message.status.charAt(0).toUpperCase() + message.status.slice(1)}</span>
                    </div>
                </div>
            </div>
            <div class="border-b pb-3">
                <p class="text-xs text-gray-500 mb-2">Subject</p>
                <p class="text-gray-900 font-medium">${escapeHtml(message.subject)}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-2">Message</p>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-700 whitespace-pre-wrap">${escapeHtml(message.message)}</p>
                </div>
            </div>
        `;

        document.getElementById('messageDetails').innerHTML = detailsHtml;
        document.getElementById('messageModal').classList.add('active');

        // Set up reply button
        document.getElementById('replyFromModalBtn').onclick = function() {
            window.location.href = `contact.php?reply=${message.id}`;
        };
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete the message from "${name}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `contact.php?delete=${id}&<?php echo $status_filter ? 'filter=' . $status_filter : ''; ?><?php echo $search ? 'search=' . urlencode($search) : ''; ?>`;
            }
        });
    }

    function closeModal() {
        document.getElementById('messageModal').classList.remove('active');
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('messageModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>