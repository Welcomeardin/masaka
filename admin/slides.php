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

$pageTitle = 'Slideshow Management';

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
    $check = $conn->query("SELECT id, title FROM slides WHERE id = $deleteId");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $deleteQuery = "DELETE FROM slides WHERE id = $deleteId";
        if ($conn->query($deleteQuery)) {
            $_SESSION['swal_message'] = ['title' => 'Deleted!', 'text' => 'Slide "' . htmlspecialchars($row['title']) . '" has been deleted.', 'icon' => 'success'];
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to delete: ' . $conn->error, 'icon' => 'error'];
        }
    }
    header("Location: slides.php");
    exit;
}

// Handle AJAX request for getting data
if (isset($_GET['get_id']) && is_numeric($_GET['get_id'])) {
    $get_id = (int)$_GET['get_id'];
    $result = $conn->query("SELECT * FROM slides WHERE id = $get_id");
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
    $language_id = (int)$_POST['language_id'];
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $content = $conn->real_escape_string($_POST['content'] ?? '');
    $button_text = $conn->real_escape_string($_POST['button_text'] ?? '');
    $button_link = $conn->real_escape_string($_POST['button_link'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    // Validate inputs
    if (empty($title)) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Title is required.', 'icon' => 'error']);
        exit;
    } elseif ($language_id == 0) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please select a language.', 'icon' => 'error']);
        exit;
    }
    
    // Handle image upload
    $image_url = $_POST['existing_image'] ?? '';
    if (!empty($_FILES['image']['name'])) {
        $base_dir = dirname(__DIR__);
        $upload_dir = $base_dir . '/uploads/slides/';
        
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
        
        if (!in_array($extension, $allowed)) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp', 'icon' => 'error']);
            exit;
        }
        
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'File too large. Max 5MB.', 'icon' => 'error']);
            exit;
        }
        
        $file_name = 'slide_' . uniqid() . '_' . mt_rand(10000000, 99999999) . '.' . $extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            @chmod($target_path, 0644);
            $image_url = 'uploads/slides/' . $file_name;
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to upload image.', 'icon' => 'error']);
            exit;
        }
    }

    // Ensure image_url has a value (use NULL if empty for database)
    $image_value = !empty($image_url) ? "'$image_url'" : "NULL";
    
    $result = ['success' => false, 'message' => '', 'icon' => 'error'];
    
    if (empty($id)) {
        // Insert new slide
        $query = "INSERT INTO slides (language_id, title, content, image_url, button_text, button_link, sort_order, status, created_at) 
                  VALUES ($language_id, '$title', '$content', $image_value, '$button_text', '$button_link', $sort_order, '$status', NOW())";
        if ($conn->query($query)) {
            $result = ['success' => true, 'message' => 'Slide created successfully!', 'icon' => 'success'];
        } else {
            $result['message'] = 'Database error: ' . $conn->error;
        }
    } else {
        // Update existing
        $id = (int)$id;
        $query = "UPDATE slides SET 
                  language_id = $language_id,
                  title = '$title', 
                  content = '$content', 
                  button_text = '$button_text', 
                  button_link = '$button_link', 
                  sort_order = $sort_order, 
                  status = '$status'";

        if (!empty($image_url)) $query .= ", image_url = '$image_url'";

        $query .= " WHERE id = $id";

        if ($conn->query($query)) {
            $result = ['success' => true, 'message' => 'Slide updated successfully!', 'icon' => 'success'];
        } else {
            $result['message'] = 'Database error: ' . $conn->error;
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

// Get all slides with language info
$slides = $conn->query("
    SELECT s.*, l.name as language_name, l.code as language_code 
    FROM slides s 
    LEFT JOIN languages l ON s.language_id = l.id 
    ORDER BY s.sort_order ASC, s.created_at DESC
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
        max-width: 200px;
        max-height: 150px;
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
                    <p class="text-sm font-medium text-gray-500">Total Slides</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $slides ? $slides->num_rows : 0; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-feather="image" class="w-6 h-6 text-blue-600"></i>
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
                    <p class="text-sm font-medium text-gray-500">Active Slides</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">
                        <?php
                        $activeQuery = $conn->query("SELECT COUNT(*) as count FROM slides WHERE status = 'active'");
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
                    <p class="text-sm font-medium text-gray-500">With Buttons</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">
                        <?php
                        $buttonQuery = $conn->query("SELECT COUNT(*) as count FROM slides WHERE button_text IS NOT NULL AND button_text != ''");
                        $buttonCount = $buttonQuery->fetch_assoc()['count'] ?? 0;
                        echo $buttonCount;
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i data-feather="link" class="w-6 h-6 text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Slides Table -->
    <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Language</th>
                        <th>Title</th>
                        <th>Button</th>
                        <th>Status</th>
                        <th>Image</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if ($slides && $slides->num_rows > 0): ?>
                        <?php while ($slide = $slides->fetch_assoc()): ?>
                            <tr data-title="<?php echo strtolower(htmlspecialchars($slide['title'])); ?>"
                                data-language="<?php echo strtolower(htmlspecialchars($slide['language_name'])); ?>">
                                <td class="text-sm font-medium text-gray-700"><?php echo $slide['sort_order']; ?></td>
                                <td>
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($slide['language_name']); ?></div>
                                    <div class="text-xs text-gray-400"><?php echo strtoupper($slide['language_code']); ?></div>
                                </td>
                                <td>
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars(substr($slide['title'], 0, 40)); ?></div>
                                    <?php if (strlen($slide['title']) > 40): ?>...<?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($slide['button_text'])): ?>
                                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars(substr($slide['button_text'], 0, 20)); ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">No button</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $slide['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($slide['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($slide['image_url'])): ?>
                                        <button onclick="showImagePreview('../<?php echo $slide['image_url']; ?>')" class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i data-feather="image" class="w-4 h-4"></i> View
                                        </button>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-sm text-gray-500"><?php echo date('M d, Y', strtotime($slide['updated_at'])); ?></td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <button onclick="viewSlide(<?php echo $slide['id']; ?>)" class="btn-action text-blue-600 hover:bg-blue-50 p-2" title="View">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="editSlide(<?php echo $slide['id']; ?>)" class="btn-action text-yellow-600 hover:bg-yellow-50 p-2" title="Edit">
                                            <i data-feather="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $slide['id']; ?>, '<?php echo htmlspecialchars($slide['title']); ?>')" class="btn-action text-red-600 hover:bg-red-50 p-2" title="Delete">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-500">
                                <i data-feather="image" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                                <p>No slides found. Click "Add New Slide" to create one.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit/View -->
<div id="slideModal" class="modal">
    <div class="modal-content">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800" id="modalTitle">Add Slide</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="slideForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="slideId">

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
                    <input type="text" name="title" id="title" required placeholder="Enter slide title" class="form-input">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Content</label>
                    <textarea name="content" id="content" rows="3" placeholder="Enter slide description..." class="form-input"></textarea>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Slide Image</label>
                    <input type="file" name="image" accept="image/*" id="slideImage" class="form-input p-2">
                    <input type="hidden" name="existing_image" id="existingImage">
                    <div id="imagePreview" class="mt-2"></div>
                </div>

                <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Button Text</label>
                        <input type="text" name="button_text" id="buttonText" placeholder="e.g., Learn More" class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Button Link</label>
                        <input type="text" name="button_link" id="buttonLink" placeholder="e.g., /about" class="form-input">
                    </div>
                </div> -->

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" id="sortOrder" value="0" class="form-input rounded">
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
            <button type="button" onclick="saveSlide()" class="px-5 py-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-colors">Save Changes</button>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div id="imageModal" class="modal">
    <div class="modal-content max-w-2xl">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800">Image Preview</h3>
            <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-6 flex justify-center">
            <img id="previewImage" src="" alt="Preview" class="max-w-full rounded-lg">
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
        document.getElementById('modalTitle').innerHTML = 'Add Slide';
        document.getElementById('slideForm').reset();
        document.getElementById('slideId').value = '';
        document.getElementById('languageId').value = '';
        document.getElementById('sortOrder').value = '0';
        clearPreviews();
        enableFormFields(true);
        document.getElementById('slideModal').classList.add('active');
        feather.replace();
    }

    function editSlide(id) {
        isViewMode = false;
        document.getElementById('modalTitle').innerHTML = 'Edit Slide';
        enableFormFields(true);

        fetch(`slides.php?get_id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('slideId').value = data.id;
                document.getElementById('languageId').value = data.language_id;
                document.getElementById('title').value = data.title;
                document.getElementById('content').value = data.content || '';
                document.getElementById('buttonText').value = data.button_text || '';
                document.getElementById('buttonLink').value = data.button_link || '';
                document.getElementById('sortOrder').value = data.sort_order || 0;
                document.getElementById('status').value = data.status;
                document.getElementById('existingImage').value = data.image_url || '';

                clearPreviews();
                if (data.image_url && data.image_url !== '') {
                    document.getElementById('imagePreview').innerHTML = `<img src="../${data.image_url}" class="image-preview" alt="Slide Image">`;
                }

                document.getElementById('slideModal').classList.add('active');
                feather.replace();
            })
            .catch(error => console.error('Error:', error));
    }

    function viewSlide(id) {
        isViewMode = true;
        document.getElementById('modalTitle').innerHTML = 'View Slide';
        enableFormFields(false);

        fetch(`slides.php?get_id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('slideId').value = data.id;
                document.getElementById('languageId').value = data.language_id;
                document.getElementById('title').value = data.title;
                document.getElementById('content').value = data.content || '';
                document.getElementById('buttonText').value = data.button_text || '';
                document.getElementById('buttonLink').value = data.button_link || '';
                document.getElementById('sortOrder').value = data.sort_order || 0;
                document.getElementById('status').value = data.status;

                clearPreviews();
                if (data.image_url && data.image_url !== '') {
                    document.getElementById('imagePreview').innerHTML = `<img src="../${data.image_url}" class="image-preview" alt="Slide Image">`;
                }

                document.getElementById('slideModal').classList.add('active');
                feather.replace();
            })
            .catch(error => console.error('Error:', error));
    }

    function saveSlide() {
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

        const form = document.getElementById('slideForm');
        const formData = new FormData(form);

        Swal.fire({
            title: 'Processing...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('slides.php', {
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
                    text: data.message || 'Failed to save slide.',
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
                window.location.href = `slides.php?delete=${id}`;
            }
        });
    }

    function showImagePreview(url) {
        document.getElementById('previewImage').src = url;
        document.getElementById('imageModal').classList.add('active');
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.remove('active');
    }

    function closeModal() {
        document.getElementById('slideModal').classList.remove('active');
        enableFormFields(true);
    }

    function enableFormFields(enable) {
        const inputs = document.querySelectorAll('#slideForm input, #slideForm textarea, #slideForm select');
        inputs.forEach(input => {
            if (input.type !== 'file') {
                input.disabled = !enable;
            }
        });

        const fileInputs = document.querySelectorAll('#slideForm input[type="file"]');
        fileInputs.forEach(input => {
            input.style.display = enable ? 'block' : 'none';
        });

        const saveButton = document.querySelector('#slideModal .modal-footer button:last-child');
        if (saveButton) {
            saveButton.style.display = enable ? 'block' : 'none';
        }
    }

    function clearPreviews() {
        document.getElementById('imagePreview').innerHTML = '';
    }

    // Image preview handler
    document.getElementById('slideImage')?.addEventListener('change', function(e) {
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
        const slideModal = document.getElementById('slideModal');
        const imageModal = document.getElementById('imageModal');
        if (event.target === slideModal) {
            closeModal();
        }
        if (event.target === imageModal) {
            closeImageModal();
        }
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>