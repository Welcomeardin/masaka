<?php
session_start();
require_once __DIR__ . '/../auth/require_login.php';
require_once __DIR__ . '/../API/config.php';

$pageTitle = 'Donations Management';

// Start output buffering for the content
ob_start();

// Get all languages (not used for donations but keeping for consistency)
$languages_query = $conn->query("SELECT id, code, name FROM languages WHERE is_active = 1");
$langs = [];
if ($languages_query) {
    while ($lang = $languages_query->fetch_assoc()) {
        $langs[$lang['id']] = $lang;
    }
}

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['donation_id']) && isset($_POST['new_status'])) {
    $donation_id = (int)$_POST['donation_id'];
    $new_status = $conn->real_escape_string($_POST['new_status']);
    $updateQuery = "UPDATE donations SET status = '$new_status' WHERE id = $donation_id";
    if ($conn->query($updateQuery)) {
        $_SESSION['swal_message'] = ['title' => 'Updated!', 'text' => 'Donation status updated successfully!', 'icon' => 'success'];
    } else {
        $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to update status: ' . $conn->error, 'icon' => 'error'];
    }
    header("Location: donations.php");
    exit;
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $check = $conn->query("SELECT id, full_name FROM donations WHERE id = $deleteId");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $deleteQuery = "DELETE FROM donations WHERE id = $deleteId";
        if ($conn->query($deleteQuery)) {
            $_SESSION['swal_message'] = ['title' => 'Deleted!', 'text' => 'Donation from "' . htmlspecialchars($row['full_name']) . '" has been deleted.', 'icon' => 'success'];
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to delete: ' . $conn->error, 'icon' => 'error'];
        }
    }
    header("Location: donations.php");
    exit;
}

// Get SweetAlert message from session
$swal_message = null;
if (isset($_SESSION['swal_message'])) {
    $swal_message = $_SESSION['swal_message'];
    unset($_SESSION['swal_message']);
}

// Get all donations with optional filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_clauses = [];
if ($status_filter && $status_filter != 'all') {
    $where_clauses[] = "status = '$status_filter'";
}
if ($search) {
    $search = $conn->real_escape_string($search);
    $where_clauses[] = "(full_name LIKE '%$search%' OR email LIKE '%$search%' OR transaction_id LIKE '%$search%')";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Fixed query - removed LEFT JOIN with languages since donations table doesn't have language_id
$donations = $conn->query("
    SELECT * FROM donations 
    $where_sql
    ORDER BY created_at DESC
");

// Get statistics
$stats = [];
$total_query = $conn->query("SELECT SUM(amount) as total, COUNT(*) as count FROM donations WHERE status = 'completed'");
$total_data = $total_query->fetch_assoc();
$stats['total_amount'] = $total_data['total'] ?? 0;
$stats['total_count'] = $total_data['count'] ?? 0;

$pending_query = $conn->query("SELECT COUNT(*) as count FROM donations WHERE status = 'pending'");
$stats['pending_count'] = $pending_query ? ($pending_query->fetch_assoc()['count'] ?? 0) : 0;

$completed_query = $conn->query("SELECT COUNT(*) as count FROM donations WHERE status = 'completed'");
$stats['completed_count'] = $completed_query ? ($completed_query->fetch_assoc()['count'] ?? 0) : 0;

$failed_query = $conn->query("SELECT COUNT(*) as count FROM donations WHERE status = 'failed'");
$stats['failed_count'] = $failed_query ? ($failed_query->fetch_assoc()['count'] ?? 0) : 0;
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

    .status-completed {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-failed {
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
</style>

<div class="space-y-6">
    <!-- Header with Search -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="relative flex-1 max-w-md">
            <i data-feather="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <form method="GET" action="" id="searchForm">
                <input type="text" name="search" id="searchInput" placeholder="Search by name, email or transaction ID..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:ring-2">
                <?php if ($status_filter && $status_filter != 'all'): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Donations</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($stats['total_count']); ?></p>
                    <p class="text-sm text-green-600 mt-1">$<?php echo number_format($stats['total_amount'], 2); ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i data-feather="dollar-sign" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1"><?php echo number_format($stats['pending_count']); ?></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i data-feather="clock" class="w-6 h-6 text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <p class="text-2xl font-bold text-green-600 mt-1"><?php echo number_format($stats['completed_count']); ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-feather="check-circle" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Failed</p>
                    <p class="text-2xl font-bold text-red-600 mt-1"><?php echo number_format($stats['failed_count']); ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i data-feather="alert-circle" class="w-6 h-6 text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="flex flex-wrap gap-2">
        <a href="?status=all<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo !$status_filter || $status_filter == 'all' ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            All
        </a>
        <a href="?status=completed<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo $status_filter == 'completed' ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            Completed
        </a>
        <a href="?status=pending<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo $status_filter == 'pending' ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            Pending
        </a>
        <a href="?status=failed<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo $status_filter == 'failed' ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            Failed
        </a>
    </div>

    <!-- Donations Table -->
    <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Donor Name</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($donations && $donations->num_rows > 0): ?>
                        <?php while ($donation = $donations->fetch_assoc()): ?>
                            <tr>
                                <td class="text-sm"><?php echo $donation['id']; ?></td>
                                <td>
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($donation['full_name']); ?></div>
                                    <?php if ($donation['phone']): ?>
                                        <div class="text-xs text-gray-400"><?php echo htmlspecialchars($donation['phone']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-sm text-gray-700"><?php echo htmlspecialchars($donation['email']); ?></div>
                                </td>
                                <td>
                                    <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($donation['currency']); ?> <?php echo number_format($donation['amount'], 2); ?></div>
                                </td>
                                <td class="text-sm"><?php echo htmlspecialchars($donation['currency']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $donation['status']; ?>">
                                        <?php echo ucfirst($donation['status']); ?>
                                    </span>
                                </td>
                                <td class="text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($donation['created_at'])); ?>
                                    <div class="text-xs text-gray-400"><?php echo date('h:i A', strtotime($donation['created_at'])); ?></div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
        <button onclick="viewDonation(<?php echo htmlspecialchars(json_encode($donation)); ?>)" class="btn-action text-blue-600 hover:bg-blue-50 p-2" title="View Details">
            <i data-feather="eye" class="w-4 h-4"></i>
        </button>
        <?php if ($donation['status'] != 'completed'): ?>
            <button onclick="updateStatus(<?php echo $donation['id']; ?>, 'completed')" class="btn-action text-green-600 hover:bg-green-50 p-2" title="Mark as Completed">
                <i data-feather="check-circle" class="w-4 h-4"></i>
            </button>
        <?php endif; ?>
        <?php if ($donation['status'] != 'failed'): ?>
            <button onclick="updateStatus(<?php echo $donation['id']; ?>, 'failed')" class="btn-action text-red-600 hover:bg-red-50 p-2" title="Mark as Failed">
                <i data-feather="x-circle" class="w-4 h-4"></i>
            </button>
        <?php endif; ?>
        <button onclick="confirmDelete(<?php echo $donation['id']; ?>, '<?php echo htmlspecialchars($donation['full_name']); ?>')" class="btn-action text-red-600 hover:bg-red-50 p-2" title="Delete">
            <i data-feather="trash-2" class="w-4 h-4"></i>
        </button>
                                    </div>
                                </td>
                            </tr>
<?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="8" class="text-center py-12 text-gray-500">
            <i data-feather="heart" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
            <p>No donations found.</p>
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

<!-- Donation Details Modal -->
        <div id="donationModal" class="modal">
            <div class="modal-content">
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800">Donation Details</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-feather="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div id="donationDetails" class="space-y-4"></div>
                </div>
                <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex justify-end">
                    <button onclick="closeModal()" class="px-5 py-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-colors">Close</button>
                </div>
            </div>
        </div>

        <script>
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
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        document.getElementById('searchForm').submit();
                    }, 500);
                });
            }

            function viewDonation(donation) {
                const detailsHtml = `
            <div class="border-b pb-3">
                <p class="text-xs text-gray-500 mb-1">Donor Information</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Full Name</p>
                        <p class="text-gray-900">${escapeHtml(donation.full_name)}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Email</p>
                        <p class="text-gray-900">${escapeHtml(donation.email)}</p>
                    </div>
                    ${donation.phone ? `
                    <div>
                        <p class="text-sm font-medium text-gray-700">Phone</p>
                        <p class="text-gray-900">${escapeHtml(donation.phone)}</p>
                    </div>
                    ` : ''}
                    ${donation.transaction_id ? `
                    <div>
                        <p class="text-sm font-medium text-gray-700">Transaction ID</p>
                        <p class="text-gray-900 text-xs">${escapeHtml(donation.transaction_id)}</p>
                    </div>
                    ` : ''}
                </div>
            </div>
            <div class="border-b pb-3">
                <p class="text-xs text-gray-500 mb-1">Donation Details</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Amount</p>
                        <p class="text-xl font-bold text-green-600">${donation.currency} ${formatNumber(donation.amount)}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Status</p>
                        <span class="status-badge status-${donation.status}">${donation.status.charAt(0).toUpperCase() + donation.status.slice(1)}</span>
                    </div>
                    ${donation.payment_method ? `
                    <div>
                        <p class="text-sm font-medium text-gray-700">Payment Method</p>
                        <p class="text-gray-900">${escapeHtml(donation.payment_method)}</p>
                    </div>
                    ` : ''}
                    <div>
                        <p class="text-sm font-medium text-gray-700">Date</p>
                        <p class="text-gray-900">${new Date(donation.created_at).toLocaleDateString()} at ${new Date(donation.created_at).toLocaleTimeString()}</p>
                    </div>
                </div>
            </div>
            ${donation.message ? `
            <div>
                <p class="text-xs text-gray-500 mb-1">Message</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-gray-700 text-sm">${escapeHtml(donation.message)}</p>
                </div>
            </div>
            ` : ''}
        `;

                document.getElementById('donationDetails').innerHTML = detailsHtml;
                document.getElementById('donationModal').classList.add('active');
            }

            function updateStatus(id, status) {
                Swal.fire({
                    title: 'Update Status',
                    text: `Are you sure you want to mark this donation as ${status}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: status === 'completed' ? '#10b981' : '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: `Yes, mark as ${status}`,
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '';
                        form.innerHTML = `
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="donation_id" value="${id}">
                    <input type="hidden" name="new_status" value="${status}">
                `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }

            function confirmDelete(id, name) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete the donation from "${name}". This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `donations.php?delete=${id}${window.location.search}`;
                    }
                });
            }

            function closeModal() {
                document.getElementById('donationModal').classList.remove('active');
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function formatNumber(amount) {
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(amount);
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('donationModal');
                if (event.target === modal) {
                    closeModal();
                }
            }
        </script>

        <?php
        $content = ob_get_clean();
        require_once __DIR__ . '/layout.php';
        ?>