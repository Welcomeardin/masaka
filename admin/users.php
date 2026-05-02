<?php
session_start();
require_once __DIR__ . '/../auth/require_login.php';
require_once __DIR__ . '/../API/config.php';

// Check admin permission
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$pageTitle = 'Users Management';

// Start output buffering for the content
ob_start();

// Get all languages for display
$languages_query = $conn->query("SELECT id, code, name FROM languages WHERE is_active = 1");
$langs = [];
if ($languages_query) {
    while ($lang = $languages_query->fetch_assoc()) {
        $langs[$lang['id']] = $lang;
    }
}

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $full_name = $conn->real_escape_string($_POST['full_name'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $conn->real_escape_string($_POST['role'] ?? 'user');
    $status = 'active';

    // Validate inputs
    if (empty($full_name)) {
        $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Full name is required.', 'icon' => 'error'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Invalid email address.', 'icon' => 'error'];
    } elseif (strlen($password) < 6) {
        $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Password must be at least 6 characters.', 'icon' => 'error'];
    } else {
        // Check if email exists
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check && $check->num_rows > 0) {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Email already exists!', 'icon' => 'error'];
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            // Fixed INSERT query with proper escaping
            $full_name_escaped = $conn->real_escape_string($full_name);
            $query = "INSERT INTO users (full_name, email, password, role, status, created_at) VALUES ('$full_name_escaped', '$email', '$hashed', '$role', '$status', NOW())";
            if ($conn->query($query)) {
                $_SESSION['swal_message'] = ['title' => 'Success!', 'text' => 'User created successfully!', 'icon' => 'success'];
            } else {
                $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to create user. Please try again.', 'icon' => 'error'];
            }
        }
    }
    header("Location: users.php");
    exit;
}

// Handle user status change
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    if ($id != $_SESSION['user_id']) {
        $user = $conn->query("SELECT status, full_name FROM users WHERE id=$id");
        if ($user && $user->num_rows > 0) {
            $userData = $user->fetch_assoc();
            $newStatus = $userData['status'] == 'active' ? 'inactive' : 'active';
            if ($conn->query("UPDATE users SET status='$newStatus' WHERE id=$id")) {
                $action = $newStatus == 'active' ? 'activated' : 'deactivated';
                $_SESSION['swal_message'] = ['title' => 'Updated!', 'text' => "User '{$userData['full_name']}' has been {$action}.", 'icon' => 'success'];
            } else {
                $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to update user status.', 'icon' => 'error'];
            }
        }
    } else {
        $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'You cannot change your own status.', 'icon' => 'error'];
    }
    header("Location: users.php");
    exit;
}

// Handle delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($deleteId != $_SESSION['user_id']) {
        $check = $conn->query("SELECT id, full_name FROM users WHERE id = $deleteId");
        if ($check && $check->num_rows > 0) {
            $row = $check->fetch_assoc();
            $deleteQuery = "DELETE FROM users WHERE id = $deleteId";
            if ($conn->query($deleteQuery)) {
                $_SESSION['swal_message'] = ['title' => 'Deleted!', 'text' => "User '{$row['full_name']}' has been deleted.", 'icon' => 'success'];
            } else {
                $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to delete user.', 'icon' => 'error'];
            }
        }
    } else {
        $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'You cannot delete your own account.', 'icon' => 'error'];
    }
    header("Location: users.php");
    exit;
}

// Handle role change
if (isset($_GET['change_role']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $role = $conn->real_escape_string($_GET['change_role']);
    if ($id == $_SESSION['user_id']) {
        $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'You cannot change your own role.', 'icon' => 'error'];
    } elseif (in_array($role, ['admin', 'editor', 'user'])) {
        $user_check = $conn->query("SELECT full_name FROM users WHERE id=$id");
        if ($user_check && $user_check->num_rows > 0) {
            $user_data = $user_check->fetch_assoc();
            if ($conn->query("UPDATE users SET role='$role' WHERE id=$id")) {
                $_SESSION['swal_message'] = ['title' => 'Updated!', 'text' => "User '{$user_data['full_name']}' role changed to " . ucfirst($role) . '.', 'icon' => 'success'];
            } else {
                $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to update role.', 'icon' => 'error'];
            }
        }
    } else {
        $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Invalid role specified.', 'icon' => 'error'];
    }
    header("Location: users.php");
    exit;
}

// Get SweetAlert message from session
$swal_message = null;
if (isset($_SESSION['swal_message'])) {
    $swal_message = $_SESSION['swal_message'];
    unset($_SESSION['swal_message']);
}

// Get filters
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query with filters
$where_clauses = [];
if ($role_filter && in_array($role_filter, ['admin', 'editor', 'user'])) {
    $where_clauses[] = "role = '$role_filter'";
}
if ($status_filter && in_array($status_filter, ['active', 'inactive', 'suspended'])) {
    $where_clauses[] = "status = '$status_filter'";
}
if ($search) {
    $search = $conn->real_escape_string($search);
    $where_clauses[] = "(full_name LIKE '%$search%' OR email LIKE '%$search%')";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get all users
$users = $conn->query("
    SELECT id, full_name, email, role, status, created_at 
    FROM users 
    $where_sql
    ORDER BY 
        CASE WHEN role = 'admin' THEN 0 ELSE 1 END,
        created_at DESC
");

// Get statistics
$stats = [];
$total_query = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total'] = $total_query && $total_query->num_rows > 0 ? $total_query->fetch_assoc()['count'] : 0;

$admin_query = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$stats['admins'] = $admin_query && $admin_query->num_rows > 0 ? $admin_query->fetch_assoc()['count'] : 0;

$active_query = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
$stats['active'] = $active_query && $active_query->num_rows > 0 ? $active_query->fetch_assoc()['count'] : 0;

$inactive_query = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'inactive'");
$stats['inactive'] = $inactive_query && $inactive_query->num_rows > 0 ? $inactive_query->fetch_assoc()['count'] : 0;
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

    .status-inactive {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .status-suspended {
        background-color: #fef3c7;
        color: #92400e;
    }

    .role-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
    }

    .role-admin {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .role-editor {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .role-user {
        background-color: #e5e7eb;
        color: #374151;
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

    .form-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
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
        border-radius: 5px;
        max-width: 450px;
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
                <input type="text" name="search" id="searchInput" placeholder="Search by name or email..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:ring-2">
                <?php if ($role_filter): ?>
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($role_filter); ?>">
                <?php endif; ?>
                <?php if ($status_filter): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                <?php endif; ?>
            </form>
        </div>
        <button onclick="openCreateModal()" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-full flex items-center gap-2 transition-all shadow-sm">
            <i data-feather="user-plus" class="w-4 h-4"></i>
            Add New User
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
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
                    <p class="text-sm font-medium text-gray-500">Administrators</p>
                    <p class="text-2xl font-bold text-red-600 mt-1"><?php echo number_format($stats['admins']); ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i data-feather="shield" class="w-6 h-6 text-red-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active Users</p>
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
                    <p class="text-sm font-medium text-gray-500">Inactive</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1"><?php echo number_format($stats['inactive']); ?></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i data-feather="user-x" class="w-6 h-6 text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="flex flex-wrap gap-2">
        <a href="?<?php echo $search ? 'search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo !$role_filter && !$status_filter ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
            All Users
        </a>
        <div class="relative group">
            <button class="filter-btn <?php echo $role_filter ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
                Role: <?php echo $role_filter ? ucfirst($role_filter) : 'All'; ?>
                <i data-feather="chevron-down" class="w-3 h-3 inline ml-1"></i>
            </button>
            <div class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg hidden group-hover:block z-10 min-w-32">
                <a href="?<?php echo $search ? 'search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>" class="block px-4 py-2 text-sm hover:bg-gray-100">All Roles</a>
                <a href="?role=admin<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>" class="block px-4 py-2 text-sm hover:bg-gray-100">Admin</a>
                <a href="?role=editor<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>" class="block px-4 py-2 text-sm hover:bg-gray-100">Editor</a>
                <a href="?role=user<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>" class="block px-4 py-2 text-sm hover:bg-gray-100">User</a>
            </div>
        </div>
        <div class="relative group">
            <button class="filter-btn <?php echo $status_filter ? 'filter-btn-active' : 'filter-btn-inactive'; ?>">
                Status: <?php echo $status_filter ? ucfirst($status_filter) : 'All'; ?>
                <i data-feather="chevron-down" class="w-3 h-3 inline ml-1"></i>
            </button>
            <div class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg hidden group-hover:block z-10 min-w-32">
                <a href="?<?php echo $search ? 'search=' . urlencode($search) : ''; ?><?php echo $role_filter ? '&role=' . $role_filter : ''; ?>" class="block px-4 py-2 text-sm hover:bg-gray-100">All Status</a>
                <a href="?status=active<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $role_filter ? '&role=' . $role_filter : ''; ?>" class="block px-4 py-2 text-sm hover:bg-gray-100">Active</a>
                <a href="?status=inactive<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $role_filter ? '&role=' . $role_filter : ''; ?>" class="block px-4 py-2 text-sm hover:bg-gray-100">Inactive</a>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td class="text-sm"><?php echo $user['id']; ?></td>
                                <td>
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                </td>
                                <td>
                                    <div class="text-sm text-gray-700"><?php echo htmlspecialchars($user['email']); ?></div>
                                </td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td class="text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <div class="relative group">
                                                <button class="btn-action text-purple-600 hover:bg-purple-50 p-2" title="Change Role">
                                                    <i data-feather="shuffle" class="w-4 h-4"></i>
                                                </button>
                                                <div class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg hidden group-hover:block z-10 min-w-32">
                                                    <button type="button" onclick="confirmRoleChange(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>', 'admin')" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Admin</button>
                                                    <button type="button" onclick="confirmRoleChange(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>', 'editor')" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Editor</button>
                                                    <button type="button" onclick="confirmRoleChange(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>', 'user')" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">User</button>
                                                </div>
                                            </div>
                                            <button type="button" onclick="confirmToggleStatus(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo $user['status']; ?>')" class="btn-action text-yellow-600 hover:bg-yellow-50 p-2" title="<?php echo $user['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                <i data-feather="<?php echo $user['status'] == 'active' ? 'user-x' : 'user-check'; ?>" class="w-4 h-4"></i>
                                            </button>
                                            <button onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" class="btn-action text-red-600 hover:bg-red-50 p-2" title="Delete">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">Current user</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="7" class="text-center py-12 text-gray-500">
            <i data-feather="users" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
            <p>No users found.</p>
            <?php if ($search || $role_filter || $status_filter): ?>
                <p class="text-sm mt-2">Try clearing your filters or search criteria.</p>
            <?php endif; ?>
            </div>
            </div>
        <?php endif; ?>
        </tbody>
        </div>
        </div>
        </div>
        </div>

        <!-- Create User Modal -->
        <div id="createUserModal" class="modal">
            <div class="modal-content">
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800">Create New User</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-feather="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="p-6">
                    <form method="POST" id="createUserForm">
                        <input type="hidden" name="action" value="create">

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="full_name" class="form-input" required placeholder="Enter full name">
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address *</label>
                            <input type="email" name="email" class="form-input" required placeholder="user@example.com">
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Password *</label>
                            <input type="password" name="password" class="form-input" required placeholder="Min. 6 characters">
                            <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters</p>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                            <select name="role" class="form-input">
                                <option value="user">User</option>
                                <option value="editor">Editor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 transition-colors">Cancel</button>
                            <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-colors">Create User</button>
                        </div>
                    </form>
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
                    timer: 3000,
                    timerProgressBar: true
                });
            <?php endif; ?>

            function openCreateModal() {
                document.getElementById('createUserModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                document.getElementById('createUserModal').classList.remove('active');
                document.getElementById('createUserForm').reset();
                document.body.style.overflow = '';
            }

            // Form submission with validation
            document.getElementById('createUserForm')?.addEventListener('submit', function(e) {
                const fullName = this.querySelector('input[name="full_name"]').value.trim();
                const email = this.querySelector('input[name="email"]').value.trim();
                const password = this.querySelector('input[name="password"]').value;

                if (!fullName) {
                    e.preventDefault();
                    Swal.fire('Error!', 'Full name is required.', 'error');
                    return;
                }
                if (!email) {
                    e.preventDefault();
                    Swal.fire('Error!', 'Email is required.', 'error');
                    return;
                }
                if (password.length < 6) {
                    e.preventDefault();
                    Swal.fire('Error!', 'Password must be at least 6 characters.', 'error');
                    return;
                }
            });

            function confirmRoleChange(id, name, newRole) {
                Swal.fire({
                    title: 'Change User Role?',
                    text: `Change "${name}" role to ${newRole}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6366f1',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, change it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `users.php?change_role=${newRole}&id=${id}&<?php echo $search ? 'search=' . urlencode($search) : ''; ?><?php echo $role_filter ? '&role=' . $role_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>`;
                    }
                });
            }

            function confirmToggleStatus(id, name, currentStatus) {
                const action = currentStatus === 'active' ? 'deactivate' : 'activate';
                const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
                Swal.fire({
                    title: `${action.charAt(0).toUpperCase() + action.slice(1)} User?`,
                    text: `Are you sure you want to ${action} "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: currentStatus === 'active' ? '#f59e0b' : '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: `Yes, ${action}!`
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `users.php?toggle=${id}&<?php echo $search ? 'search=' . urlencode($search) : ''; ?><?php echo $role_filter ? '&role=' . $role_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>`;
                    }
                });
            }

            function confirmDelete(id, name) {
                Swal.fire({
                    title: 'Delete User?',
                    text: `You are about to permanently delete "${name}". This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `users.php?delete=${id}&<?php echo $search ? 'search=' . urlencode($search) : ''; ?><?php echo $role_filter ? '&role=' . $role_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>`;
                    }
                });
            }

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

            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('createUserModal');
                if (event.target === modal) {
                    closeModal();
                }
            }
        </script>

        <?php
        $content = ob_get_clean();
        require_once __DIR__ . '/layout.php';
        ?>