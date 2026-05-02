<?php
// MUST be first - suppress all errors before any includes
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering immediately to catch any accidental output
ob_start();

session_start();
require_once __DIR__ . '/../auth/require_login.php';
require_once __DIR__ . '/../API/config.php';

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . ($conn->connect_error ?? 'Unknown error'), 'icon' => 'error']);
    exit;
}

$pageTitle = 'Team Management';

// Get all languages for translations
$languages_query = $conn->query("SELECT id, code, name FROM languages WHERE is_active = 1");
$langs = [];
if ($languages_query) {
    while ($lang = $languages_query->fetch_assoc()) {
        $langs[$lang['id']] = $lang;
    }
}

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $check = $conn->query("SELECT id, name FROM team WHERE id = $deleteId");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $deleteQuery = "DELETE FROM team WHERE id = $deleteId";
        if ($conn->query($deleteQuery)) {
            $_SESSION['swal_message'] = ['title' => 'Deleted!', 'text' => 'Team member "' . htmlspecialchars($row['name']) . '" has been deleted.', 'icon' => 'success'];
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to delete: ' . $conn->error, 'icon' => 'error'];
        }
    }
    header("Location: team.php");
    exit;
}

// Handle AJAX request for getting data
if (isset($_GET['get_id']) && is_numeric($_GET['get_id'])) {
    $get_id = (int)$_GET['get_id'];
    $result = $conn->query("
        SELECT t.*, tt.role as translated_role, tt.bio as translated_bio, tt.language_id as translation_language_id
        FROM team t
        LEFT JOIN team_translations tt ON t.id = tt.team_id
        WHERE t.id = $get_id
        ORDER BY tt.language_id = (SELECT id FROM languages WHERE is_default = 1) DESC
        LIMIT 1
    ");
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        // Use translations if available
        if (!empty($data['translated_role'])) {
            $data['role'] = $data['translated_role'];
        }
        if (!empty($data['translated_bio'])) {
            $data['bio'] = $data['translated_bio'];
        }
        // Set language_id from translation if available
        if (!empty($data['translation_language_id'])) {
            $data['language_id'] = $data['translation_language_id'];
        }
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    $id = $_POST['id'] ?? '';
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $role = $conn->real_escape_string($_POST['role'] ?? '');
    $bio = $conn->real_escape_string($_POST['bio'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    $language_id = (int)($_POST['language_id'] ?? 1);

    // Handle image upload
    $image_url = $_POST['existing_image'] ?? '';
    if (!empty($_FILES['image']['name'])) {
        $base_dir = dirname(__DIR__);
        $upload_dir = $base_dir . '/uploads/team/';
        
        // Create directory
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0777, true)) {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to create upload directory.', 'icon' => 'error']);
                exit;
            }
        }
        
        // Ensure writable
        @chmod($upload_dir, 0777);
        clearstatcache(true, $upload_dir);
        
        if (!is_writable($upload_dir)) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Upload directory not writable. Contact admin.', 'icon' => 'error']);
            exit;
        }
        
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowed)) {
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'File too large. Max 5MB.', 'icon' => 'error']);
                exit;
            }
            
            $file_name = 'team_' . uniqid() . '_' . mt_rand(10000000, 99999999) . '.' . $extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                @chmod($target_path, 0644);
                $image_url = 'uploads/team/' . $file_name;
            } else {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.', 'icon' => 'error']);
                exit;
            }
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp', 'icon' => 'error']);
            exit;
        }
    }

    $result = ['success' => false, 'message' => '', 'icon' => 'error'];
    
    if (empty($id)) {
        // Validate inputs
        if (empty($name)) {
            $result['message'] = 'Name is required.';
        } elseif (empty($role)) {
            $result['message'] = 'Role is required.';
        } elseif (empty($language_id) || $language_id == 0) {
            $result['message'] = 'Please select a language.';
        } else {
            // Handle empty image_url for database
            $image_value = !empty($image_url) ? "'$image_url'" : "NULL";
            
            // Insert new team member
            $query = "INSERT INTO team (name, role, image_url, bio, sort_order, status, created_at) 
                      VALUES ('$name', '$role', $image_value, '$bio', $sort_order, '$status', NOW())";

            if ($conn->query($query)) {
                $team_id = $conn->insert_id;

                // Insert translation for selected language
                $transQuery = "INSERT INTO team_translations (team_id, language_id, role, bio) 
                              VALUES ($team_id, $language_id, '$role', '$bio')";
                if ($conn->query($transQuery)) {
                    $result = ['success' => true, 'message' => 'Team member created successfully!', 'icon' => 'success'];
                } else {
                    $result = ['success' => true, 'message' => 'Team member created but translation failed: ' . $conn->error, 'icon' => 'warning'];
                }
            } else {
                $result['message'] = 'Database error: ' . $conn->error;
            }
        }
    } else {
        // Update existing
        $id = (int)$id;
        $query = "UPDATE team SET 
                  name = '$name',
                  sort_order = $sort_order,
                  status = '$status'";

        if (!empty($image_url)) $query .= ", image_url = '$image_url'";

        $query .= " WHERE id = $id";

        if ($conn->query($query)) {
            // Update or insert translation
            $checkTrans = $conn->query("SELECT id FROM team_translations WHERE team_id = $id AND language_id = $language_id");
            if ($checkTrans->num_rows > 0) {
                $transQuery = "UPDATE team_translations SET 
                              role = '$role', 
                              bio = '$bio' 
                              WHERE team_id = $id AND language_id = $language_id";
            } else {
                $transQuery = "INSERT INTO team_translations (team_id, language_id, role, bio) 
                              VALUES ($id, $language_id, '$role', '$bio')";
            }
            $conn->query($transQuery);

            $result = ['success' => true, 'message' => 'Team member updated successfully!', 'icon' => 'success'];
        } else {
            $result['message'] = 'Failed to update: ' . $conn->error;
        }
    }
    
    // Ensure we have a valid result
    if (empty($result)) {
        $result = ['success' => false, 'message' => 'Unknown error occurred', 'icon' => 'error'];
    }
    
    // Return JSON for AJAX requests
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
    
    } catch (Exception $e) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage(), 'icon' => 'error']);
        exit;
    }
}

// Get SweetAlert message from session
$swal_message = null;
if (isset($_SESSION['swal_message'])) {
    $swal_message = $_SESSION['swal_message'];
    unset($_SESSION['swal_message']);
}

// Get all team members - show base data with translations if available
$team_members = $conn->query("
    SELECT t.*, 
           t.role as translated_role, 
           t.bio as translated_bio,
           NULL as language_name,
           NULL as trans_lang_id
    FROM team t
    ORDER BY t.sort_order ASC, t.created_at DESC
");
?>

<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
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
        max-width: 700px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
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

    .image-preview {
        width: 120px;
        height: 120px;
        margin-top: 10px;
        border-radius: 50%;
        border: 3px solid #e5e7eb;
        padding: 4px;
        object-fit: cover;
    }

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

    .btn-action {
        padding: 6px;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .btn-action:hover {
        transform: scale(1.1);
    }

    .floating-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        transition: all 0.3s;
        z-index: 100;
    }

    .floating-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
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

    .stat-card {
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .team-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }
</style>

<div class="space-y-6">
    <!-- Header with Search and Add Button -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="relative flex-1 max-w-md">
            <i data-feather="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" id="searchInput" placeholder="Search team members..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:ring-2">
        </div>
        <button onclick="openAddModal()" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-full flex items-center gap-2 transition-all shadow-sm">
            <i data-feather="plus" class="w-4 h-4"></i>
            Add New
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Members</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $team_members ? $team_members->num_rows : 0; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-feather="users" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active Members</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">
                        <?php
                        $activeQuery = $conn->query("SELECT COUNT(*) as count FROM team WHERE status = 'active'");
                        $activeCount = $activeQuery->fetch_assoc()['count'] ?? 0;
                        echo $activeCount;
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">With Photos</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">
                        <?php
                        $photoQuery = $conn->query("SELECT COUNT(*) as count FROM team WHERE image_url IS NOT NULL AND image_url != ''");
                        $photoCount = $photoQuery->fetch_assoc()['count'] ?? 0;
                        echo $photoCount;
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i data-feather="camera" class="w-6 h-6 text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Languages</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo count($langs); ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i data-feather="globe" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members Table -->
    <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if ($team_members && $team_members->num_rows > 0): ?>
                        <?php while ($member = $team_members->fetch_assoc()): ?>
                            <tr data-name="<?php echo strtolower(htmlspecialchars($member['name'])); ?>"
                                data-role="<?php echo strtolower(htmlspecialchars($member['translated_role'] ?? $member['role'])); ?>">
                                <td class="text-sm font-medium text-gray-700"><?php echo $member['sort_order']; ?></td>
                                <td>
                                    <?php if (!empty($member['image_url'])): ?>
                                        <img src="../<?php echo htmlspecialchars($member['image_url']); ?>" alt="Avatar" class="team-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center" style="display:none;">
                                            <i data-feather="user" class="w-6 h-6 text-gray-400"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i data-feather="user" class="w-6 h-6 text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($member['name']); ?></div>
                                    <div class="text-xs text-gray-400">ID: <?php echo $member['id']; ?></div>
                                </td>
                                <td>
                                    <div class="text-sm text-gray-700"><?php echo htmlspecialchars(substr($member['translated_role'] ?? $member['role'], 0, 30)); ?></div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $member['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($member['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <button onclick="viewMember(<?php echo $member['id']; ?>)" class="btn-action text-blue-600 hover:bg-blue-50 p-2" title="View">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="editMember(<?php echo $member['id']; ?>)" class="btn-action text-yellow-600 hover:bg-yellow-50 p-2" title="Edit">
                                            <i data-feather="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['name']); ?>')" class="btn-action text-red-600 hover:bg-red-50 p-2" title="Delete">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-500">
                                <i data-feather="users" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                                <p>No team members found. Click "Add New" to create one.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit/View -->
<div id="teamModal" class="modal">
    <div class="modal-content">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800" id="modalTitle">Add Team Member</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="teamForm" method="POST" action="team.php" enctype="multipart/form-data">
                <input type="hidden" name="id" id="memberId">

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Language *</label>
                    <select name="language_id" id="languageId" required class="form-input">
                        <option value="">Select Language</option>
                        <?php foreach ($langs as $langId => $lang): ?>
                            <option value="<?php echo $langId; ?>"><?php echo htmlspecialchars($lang['name']); ?> (<?php echo strtoupper($lang['code']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                    <input type="text" name="name" id="name" required placeholder="Enter full name" class="form-input">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Role *</label>
                    <input type="text" name="role" id="role" required placeholder="e.g., Executive Director" class="form-input">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bio</label>
                    <textarea name="bio" id="bio" rows="4" placeholder="Enter biography..." class="form-input"></textarea>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Profile Photo</label>
                    <input type="file" name="image" accept="image/*" id="memberImage" class="form-input p-2">
                    <input type="hidden" name="existing_image" id="existingImage">
                    <div id="imagePreview" class="mt-2 flex justify-center"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" id="sortOrder" value="0" class="form-input rounded">
                        <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status" id="status" class="form-input">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
            <button type="button" onclick="closeModal()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 transition-colors">Cancel</button>
            <button type="button" onclick="saveMember()" class="px-5 py-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-colors">Save Changes</button>
        </div>
    </div>
</div>

<!-- Floating Action Button -->
<div class="floating-btn" onclick="openAddModal()">
    <i data-feather="plus" class="w-6 h-6"></i>
</div>

<script>
    let isViewMode = false;

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

    function openAddModal() {
        isViewMode = false;
        document.getElementById('modalTitle').innerHTML = 'Add Team Member';
        document.getElementById('teamForm').reset();
        document.getElementById('memberId').value = '';
        document.getElementById('languageId').value = '';
        document.getElementById('sortOrder').value = '0';
        clearPreviews();
        enableFormFields(true);
        document.getElementById('teamModal').classList.add('active');
        feather.replace();
    }

    function editMember(id) {
        isViewMode = false;
        document.getElementById('modalTitle').innerHTML = 'Edit Team Member';
        enableFormFields(true);

        fetch(`team.php?get_id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('memberId').value = data.id;
                document.getElementById('languageId').value = data.language_id || <?php echo $defaultLangId ?? 1; ?>;
                document.getElementById('name').value = data.name;
                document.getElementById('role').value = data.role;
                document.getElementById('bio').value = data.bio || '';
                document.getElementById('sortOrder').value = data.sort_order || 0;
                document.getElementById('status').value = data.status;
                document.getElementById('existingImage').value = data.image_url || '';

                clearPreviews();
                if (data.image_url && data.image_url !== '') {
                    document.getElementById('imagePreview').innerHTML = `<img src="../${data.image_url}" class="image-preview" alt="Profile Photo">`;
                }

                document.getElementById('teamModal').classList.add('active');
                feather.replace();
            })
            .catch(error => console.error('Error:', error));
    }

    function viewMember(id) {
        isViewMode = true;
        document.getElementById('modalTitle').innerHTML = 'View Team Member';
        enableFormFields(false);

        fetch(`team.php?get_id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('memberId').value = data.id;
                document.getElementById('name').value = data.name;
                document.getElementById('role').value = data.role;
                document.getElementById('bio').value = data.bio || '';
                document.getElementById('sortOrder').value = data.sort_order || 0;
                document.getElementById('status').value = data.status;

                clearPreviews();
                if (data.image_url && data.image_url !== '') {
                    document.getElementById('imagePreview').innerHTML = `<img src="../${data.image_url}" class="image-preview" alt="Profile Photo">`;
                }

                document.getElementById('teamModal').classList.add('active');
                feather.replace();
            })
            .catch(error => console.error('Error:', error));
    }

    function saveMember() {
        if (isViewMode) {
            closeModal();
            return;
        }

        const languageId = document.getElementById('languageId').value;
        const name = document.getElementById('name').value.trim();
        const role = document.getElementById('role').value.trim();

        if (!languageId) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please select a language',
                icon: 'warning',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }
        if (!name) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please enter a name',
                icon: 'warning',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }
        if (!role) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please enter a role',
                icon: 'warning',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        const form = document.getElementById('teamForm');
        const formData = new FormData(form);

        Swal.fire({
            title: 'Processing...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('team.php', {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            console.log('Server response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error. Raw response:', text);
                throw new Error('Invalid JSON from server');
            }
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: data.icon || 'success',
                    confirmButtonColor: '#10b981',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to save team member.',
                    icon: data.icon || 'error',
                    confirmButtonColor: '#ef4444'
                });
            }
        }).catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Server error. Check browser console (F12) for the raw response.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        });
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete "${name}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `team.php?delete=${id}`;
            }
        });
    }

    function closeModal() {
        document.getElementById('teamModal').classList.remove('active');
        enableFormFields(true);
    }

    function enableFormFields(enable) {
        const inputs = document.querySelectorAll('#teamForm input, #teamForm textarea, #teamForm select');
        inputs.forEach(input => {
            if (input.type !== 'file') {
                input.disabled = !enable;
            }
        });

        const fileInputs = document.querySelectorAll('#teamForm input[type="file"]');
        fileInputs.forEach(input => {
            input.style.display = enable ? 'block' : 'none';
        });

        const saveButton = document.querySelector('#teamModal .modal-footer button:last-child');
        if (saveButton) {
            saveButton.style.display = enable ? 'block' : 'none';
        }
    }

    function clearPreviews() {
        document.getElementById('imagePreview').innerHTML = '';
    }

    // Image preview handler
    document.getElementById('memberImage')?.addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" class="image-preview" alt="Preview">`;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#tableBody tr');

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const role = row.getAttribute('data-role') || '';

            if (name.includes(searchTerm) || role.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const teamModal = document.getElementById('teamModal');
        if (event.target === teamModal) {
            closeModal();
        }
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>