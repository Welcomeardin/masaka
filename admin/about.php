<?php
// Admin About Page - about.php
// MUST be first - suppress all errors before any includes
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering immediately to catch any accidental output
ob_start();

session_start();
require_once __DIR__ . '/../API/config.php';
require_once __DIR__ . '/../auth/require_login.php';

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . ($conn->connect_error ?? 'Unknown error'), 'icon' => 'error']);
    exit;
}

$pageTitle = 'About Page Management';

// Get all languages
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
    $check = $conn->query("SELECT id, title FROM about WHERE id = $deleteId");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $deleteQuery = "DELETE FROM about WHERE id = $deleteId";
        if ($conn->query($deleteQuery)) {
            $_SESSION['swal_message'] = ['title' => 'Deleted!', 'text' => 'About page "' . htmlspecialchars($row['title']) . '" has been deleted.', 'icon' => 'success'];
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to delete: ' . $conn->error, 'icon' => 'error'];
        }
    }
    header("Location: about.php");
    exit;
}

// Handle AJAX request for getting data
if (isset($_GET['get_id']) && is_numeric($_GET['get_id'])) {
    $get_id = (int)$_GET['get_id'];
    $result = $conn->query("SELECT * FROM about WHERE id = $get_id");
    if ($result && $result->num_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode($result->fetch_assoc());
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    $id = $_POST['id'] ?? '';
    $language_id = isset($_POST['language_id']) ? (int)$_POST['language_id'] : 0;
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $content = $conn->real_escape_string($_POST['content'] ?? '');
    $vision_title = $conn->real_escape_string($_POST['vision_title'] ?? '');
    $vision_text = $conn->real_escape_string($_POST['vision_text'] ?? '');
    $mission_title = $conn->real_escape_string($_POST['mission_title'] ?? '');
    $mission_text = $conn->real_escape_string($_POST['mission_text'] ?? '');
    $status = $_POST['status'] ?? 'active';

    // Validate required fields
    if ($language_id == 0) {
        $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Please select a language.', 'icon' => 'error'];
        header("Location: about.php");
        exit;
    }
    if (empty($title)) {
        $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Please enter a title.', 'icon' => 'error'];
        header("Location: about.php");
        exit;
    }

    // Create upload directory once
    $base_dir = dirname(__DIR__);
    $upload_dir = $base_dir . '/uploads/about/';
    if (!is_dir($upload_dir)) {
        if (!@mkdir($upload_dir, 0777, true)) {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to create upload directory.', 'icon' => 'error'];
            header("Location: about.php");
            exit;
        }
    }
    
    @chmod($upload_dir, 0777);
    clearstatcache(true, $upload_dir);
    
    if (!is_writable($upload_dir)) {
        $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Upload directory not writable. Contact admin.', 'icon' => 'error'];
        header("Location: about.php");
        exit;
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Handle main image upload
    $image_url = $_POST['existing_image'] ?? '';
    if (!empty($_FILES['image']['name'])) {
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($extension, $allowed)) {
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Main image too large. Max 5MB.', 'icon' => 'error'];
                header("Location: about.php");
                exit;
            }
            $file_name = 'about_' . uniqid() . '_' . mt_rand(10000000, 99999999) . '.' . $extension;
            $target_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                @chmod($target_path, 0644);
                $image_url = 'uploads/about/' . $file_name;
            } else {
                $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to upload main image.', 'icon' => 'error'];
                header("Location: about.php");
                exit;
            }
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Invalid file type for main image. Allowed: jpg, jpeg, png, gif, webp', 'icon' => 'error'];
            header("Location: about.php");
            exit;
        }
    }

    // Handle vision image upload
    $vision_image = $_POST['existing_vision_image'] ?? '';
    if (!empty($_FILES['vision_image']['name'])) {
        $extension = strtolower(pathinfo($_FILES['vision_image']['name'], PATHINFO_EXTENSION));
        if (in_array($extension, $allowed)) {
            if ($_FILES['vision_image']['size'] > 5 * 1024 * 1024) {
                $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Vision image too large. Max 5MB.', 'icon' => 'error'];
                header("Location: about.php");
                exit;
            }
            $file_name = 'vision_' . uniqid() . '_' . mt_rand(10000000, 99999999) . '.' . $extension;
            $target_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['vision_image']['tmp_name'], $target_path)) {
                @chmod($target_path, 0644);
                $vision_image = 'uploads/about/' . $file_name;
            } else {
                $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to upload vision image.', 'icon' => 'error'];
                header("Location: about.php");
                exit;
            }
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Invalid file type for vision image.', 'icon' => 'error'];
            header("Location: about.php");
            exit;
        }
    }

    // Handle mission image upload
    $mission_image = $_POST['existing_mission_image'] ?? '';
    if (!empty($_FILES['mission_image']['name'])) {
        $extension = strtolower(pathinfo($_FILES['mission_image']['name'], PATHINFO_EXTENSION));
        if (in_array($extension, $allowed)) {
            if ($_FILES['mission_image']['size'] > 5 * 1024 * 1024) {
                $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Mission image too large. Max 5MB.', 'icon' => 'error'];
                header("Location: about.php");
                exit;
            }
            $file_name = 'mission_' . uniqid() . '_' . mt_rand(10000000, 99999999) . '.' . $extension;
            $target_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['mission_image']['tmp_name'], $target_path)) {
                @chmod($target_path, 0644);
                $mission_image = 'uploads/about/' . $file_name;
            } else {
                $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to upload mission image.', 'icon' => 'error'];
                header("Location: about.php");
                exit;
            }
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Invalid file type for mission image.', 'icon' => 'error'];
            header("Location: about.php");
            exit;
        }
    }

    $result = ['success' => false, 'message' => '', 'icon' => 'error'];
    
    // Handle empty images for database
    $image_value = !empty($image_url) ? "'$image_url'" : "NULL";
    $vision_value = !empty($vision_image) ? "'$vision_image'" : "NULL";
    $mission_value = !empty($mission_image) ? "'$mission_image'" : "NULL";
    
    if (empty($id)) {
        // Insert new - check if language already exists
        $check_lang = $conn->query("SELECT id FROM about WHERE language_id = $language_id");
        if ($check_lang && $check_lang->num_rows > 0) {
            $result['message'] = 'An about page for this language already exists!';
        } else {
            $query = "INSERT INTO about (language_id, title, content, image_url, vision_title, vision_text, vision_image, mission_title, mission_text, mission_image, status) 
                      VALUES ($language_id, '$title', '$content', $image_value, '$vision_title', '$vision_text', $vision_value, '$mission_title', '$mission_text', $mission_value, '$status')";
            if ($conn->query($query)) {
                $result = ['success' => true, 'message' => 'About page created successfully!', 'icon' => 'success'];
            } else {
                $result['message'] = 'Database error: ' . $conn->error;
            }
        }
    } else {
        // Update existing
        $id = (int)$id;
        $query = "UPDATE about SET 
                  language_id = $language_id,
                  title = '$title', 
                  content = '$content', 
                  vision_title = '$vision_title', 
                  vision_text = '$vision_text', 
                  mission_title = '$mission_title', 
                  mission_text = '$mission_text', 
                  status = '$status'";

        if (!empty($image_url)) $query .= ", image_url = '$image_url'";
        if (!empty($vision_image)) $query .= ", vision_image = '$vision_image'";
        if (!empty($mission_image)) $query .= ", mission_image = '$mission_image'";

        $query .= " WHERE id = $id";

        if ($conn->query($query)) {
            $result = ['success' => true, 'message' => 'About page updated successfully!', 'icon' => 'success'];
        } else {
            $result['message'] = 'Failed to update: ' . $conn->error;
        }
    }
    
    // Ensure we have a valid result
    if (empty($result)) {
        $result = ['success' => false, 'message' => 'Unknown error occurred', 'icon' => 'error'];
    }
    
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

// Get all about pages with language info
$about_pages = $conn->query("
    SELECT a.*, l.name as language_name, l.code as language_code 
    FROM about a 
    LEFT JOIN languages l ON a.language_id = l.id 
    ORDER BY l.name, a.created_at DESC
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
        border-radius: 5px;
        max-width: 800px;
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
        max-width: 120px;
        max-height: 120px;
        margin-top: 10px;
        border-radius: 5px;
        border: 2px solid #e5e7eb;
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

    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
        margin: 20px 0 16px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid #e5e7eb;
    }
</style>

<div class="space-y-6">
    <!-- Header with Search and Add Button -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="relative flex-1 max-w-md">
            <i data-feather="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" id="searchInput" placeholder="Search by title or language..."
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
                    <p class="text-sm font-medium text-gray-500">Total Pages</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $about_pages ? $about_pages->num_rows : 0; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-feather="file-text" class="w-6 h-6 text-blue-600"></i>
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
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active Pages</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">
                        <?php
                        $activeQuery = $conn->query("SELECT COUNT(*) as count FROM about WHERE status = 'active'");
                        $activeCount = $activeQuery ? ($activeQuery->fetch_assoc()['count'] ?? 0) : 0;
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
                    <p class="text-sm font-medium text-gray-500">With Images</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">
                        <?php
                        $imageQuery = $conn->query("SELECT COUNT(*) as count FROM about WHERE image_url IS NOT NULL AND image_url != ''");
                        $imageCount = $imageQuery ? ($imageQuery->fetch_assoc()['count'] ?? 0) : 0;
                        echo $imageCount;
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i data-feather="image" class="w-6 h-6 text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- About Pages Table -->
    <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Language</th>
                        <th>Title</th>
                        <th>Vision</th>
                        <th>Mission</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if ($about_pages && $about_pages->num_rows > 0): ?>
                        <?php while ($page = $about_pages->fetch_assoc()): ?>
                            <tr data-title="<?php echo strtolower(htmlspecialchars($page['title'])); ?>"
                                data-language="<?php echo strtolower(htmlspecialchars($page['language_name'])); ?>">
                                <td class="text-sm"><?php echo $page['id']; ?></td>
                                <td>
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($page['language_name']); ?></div>
                                    <div class="text-xs text-gray-400"><?php echo strtoupper($page['language_code']); ?></div>
                                </td>
                                <td>
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars(substr($page['title'], 0, 40)); ?></div>
                                    <?php if (strlen($page['title']) > 40): ?>...<?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($page['vision_title'])): ?>
                                        <span class="text-sm text-gray-700">✓ <?php echo htmlspecialchars(substr($page['vision_title'], 0, 25)); ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($page['mission_title'])): ?>
                                        <span class="text-sm text-gray-700">✓ <?php echo htmlspecialchars(substr($page['mission_title'], 0, 25)); ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $page['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($page['status']); ?>
                                    </span>
                                </td>
                                <td class="text-sm text-gray-500"><?php echo date('M d, Y', strtotime($page['updated_at'])); ?></td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <button onclick="viewAbout(<?php echo $page['id']; ?>)" class="btn-action text-blue-600 hover:bg-blue-50 p-2" title="View">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="editAbout(<?php echo $page['id']; ?>)" class="btn-action text-yellow-600 hover:bg-yellow-50 p-2" title="Edit">
                                            <i data-feather="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $page['id']; ?>, '<?php echo htmlspecialchars($page['title']); ?>')" class="btn-action text-red-600 hover:bg-red-50 p-2" title="Delete">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-500">
                                <i data-feather="file-text" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                                <p>No about pages found. Click "Add New" to create one.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit/View -->
<div id="aboutModal" class="modal">
    <div class="modal-content">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800" id="modalTitle">Add About Page</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="aboutForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="aboutId">

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
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Title *</label>
                    <input type="text" name="title" id="title" required placeholder="Enter about page title" class="form-input">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Main Content</label>
                    <textarea name="content" id="content" rows="4" placeholder="Enter main content..." class="form-input"></textarea>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Main Image</label>
                    <input type="file" name="image" accept="image/*" id="mainImage" class="form-input p-2">
                    <input type="hidden" name="existing_image" id="existingImage">
                    <div id="imagePreview" class="mt-2"></div>
                </div>

                <div class="section-title">Vision Section</div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Vision Title</label>
                    <input type="text" name="vision_title" id="visionTitle" placeholder="Enter vision title" class="form-input">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Vision Text</label>
                    <textarea name="vision_text" id="visionText" rows="3" placeholder="Enter vision description..." class="form-input"></textarea>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Vision Image</label>
                    <input type="file" name="vision_image" accept="image/*" id="visionImage" class="form-input p-2">
                    <input type="hidden" name="existing_vision_image" id="existingVisionImage">
                    <div id="visionImagePreview" class="mt-2"></div>
                </div>

                <div class="section-title">Mission Section</div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mission Title</label>
                    <input type="text" name="mission_title" id="missionTitle" placeholder="Enter mission title" class="form-input">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mission Text</label>
                    <textarea name="mission_text" id="missionText" rows="3" placeholder="Enter mission description..." class="form-input"></textarea>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mission Image</label>
                    <input type="file" name="mission_image" accept="image/*" id="missionImage" class="form-input p-2">
                    <input type="hidden" name="existing_mission_image" id="existingMissionImage">
                    <div id="missionImagePreview" class="mt-2"></div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="form-input">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
            <button type="button" onclick="closeModal()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 transition-colors">Cancel</button>
            <button type="button" onclick="saveAbout()" class="px-5 py-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-colors">Save Changes</button>
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
        document.getElementById('modalTitle').innerHTML = 'Add About Page';
        document.getElementById('aboutForm').reset();
        document.getElementById('aboutId').value = '';
        document.getElementById('languageId').value = '';
        clearPreviews();
        enableFormFields(true);
        document.getElementById('aboutModal').classList.add('active');
        feather.replace();
    }

    function editAbout(id) {
        isViewMode = false;
        document.getElementById('modalTitle').innerHTML = 'Edit About Page';
        enableFormFields(true);

        fetch(`about.php?get_id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('aboutId').value = data.id;
                document.getElementById('languageId').value = data.language_id;
                document.getElementById('title').value = data.title;
                document.getElementById('content').value = data.content || '';
                document.getElementById('visionTitle').value = data.vision_title || '';
                document.getElementById('visionText').value = data.vision_text || '';
                document.getElementById('missionTitle').value = data.mission_title || '';
                document.getElementById('missionText').value = data.mission_text || '';
                document.getElementById('status').value = data.status;

                document.getElementById('existingImage').value = data.image_url || '';
                document.getElementById('existingVisionImage').value = data.vision_image || '';
                document.getElementById('existingMissionImage').value = data.mission_image || '';

                clearPreviews();
                if (data.image_url && data.image_url !== '') {
                    document.getElementById('imagePreview').innerHTML = `<img src="../${data.image_url}" class="image-preview" alt="Main Image">`;
                }
                if (data.vision_image && data.vision_image !== '') {
                    document.getElementById('visionImagePreview').innerHTML = `<img src="../${data.vision_image}" class="image-preview" alt="Vision Image">`;
                }
                if (data.mission_image && data.mission_image !== '') {
                    document.getElementById('missionImagePreview').innerHTML = `<img src="../${data.mission_image}" class="image-preview" alt="Mission Image">`;
                }

                document.getElementById('aboutModal').classList.add('active');
                feather.replace();
            })
            .catch(error => console.error('Error:', error));
    }

    function viewAbout(id) {
        isViewMode = true;
        document.getElementById('modalTitle').innerHTML = 'View About Page';
        enableFormFields(false);

        fetch(`about.php?get_id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('aboutId').value = data.id;
                document.getElementById('title').value = data.title;
                document.getElementById('content').value = data.content || '';
                document.getElementById('visionTitle').value = data.vision_title || '';
                document.getElementById('visionText').value = data.vision_text || '';
                document.getElementById('missionTitle').value = data.mission_title || '';
                document.getElementById('missionText').value = data.mission_text || '';
                document.getElementById('status').value = data.status;

                clearPreviews();
                if (data.image_url && data.image_url !== '') {
                    document.getElementById('imagePreview').innerHTML = `<img src="../${data.image_url}" class="image-preview" alt="Main Image">`;
                }
                if (data.vision_image && data.vision_image !== '') {
                    document.getElementById('visionImagePreview').innerHTML = `<img src="../${data.vision_image}" class="image-preview" alt="Vision Image">`;
                }
                if (data.mission_image && data.mission_image !== '') {
                    document.getElementById('missionImagePreview').innerHTML = `<img src="../${data.mission_image}" class="image-preview" alt="Mission Image">`;
                }

                document.getElementById('aboutModal').classList.add('active');
                feather.replace();
            })
            .catch(error => console.error('Error:', error));
    }

    function saveAbout() {
        if (isViewMode) {
            closeModal();
            return;
        }

        const languageId = document.getElementById('languageId').value;
        const title = document.getElementById('title').value.trim();

        if (!languageId) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please select a language',
                icon: 'warning',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }
        if (!title) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please enter a title',
                icon: 'warning',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        const form = document.getElementById('aboutForm');
        const formData = new FormData(form);

        Swal.fire({
            title: 'Processing...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('about.php', {
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
                    text: data.message || 'Failed to save about page.',
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

    function confirmDelete(id, title) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete "${title}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `about.php?delete=${id}`;
            }
        });
    }

    function closeModal() {
        document.getElementById('aboutModal').classList.remove('active');
        enableFormFields(true);
    }

    function enableFormFields(enable) {
        const inputs = document.querySelectorAll('#aboutForm input, #aboutForm textarea, #aboutForm select');
        inputs.forEach(input => {
            if (input.type !== 'file') {
                input.disabled = !enable;
            }
        });

        const fileInputs = document.querySelectorAll('#aboutForm input[type="file"]');
        fileInputs.forEach(input => {
            input.style.display = enable ? 'block' : 'none';
        });

        const saveButton = document.querySelector('#aboutModal .modal-footer button:last-child');
        if (saveButton) {
            saveButton.style.display = enable ? 'block' : 'none';
        }
    }

    function clearPreviews() {
        document.getElementById('imagePreview').innerHTML = '';
        document.getElementById('visionImagePreview').innerHTML = '';
        document.getElementById('missionImagePreview').innerHTML = '';
    }

    // Image preview handlers
    document.getElementById('mainImage')?.addEventListener('change', function(e) {
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

    document.getElementById('visionImage')?.addEventListener('change', function(e) {
        const preview = document.getElementById('visionImagePreview');
        preview.innerHTML = '';
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" class="image-preview" alt="Preview">`;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    document.getElementById('missionImage')?.addEventListener('change', function(e) {
        const preview = document.getElementById('missionImagePreview');
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
            const title = row.getAttribute('data-title') || '';
            const language = row.getAttribute('data-language') || '';

            if (title.includes(searchTerm) || language.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('aboutModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>