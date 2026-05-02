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

$pageTitle = 'Gallery Management';

// Get all languages
$languages_query = $conn->query("SELECT id, code, name FROM languages WHERE is_active = 1");
$langs = [];
if ($languages_query) {
    while ($lang = $languages_query->fetch_assoc()) {
        $langs[$lang['id']] = $lang;
    }
}

// Get categories for dropdown
$categories_query = $conn->query("SELECT id, name FROM gallery_categories WHERE status = 'active'");
$categories = [];
if ($categories_query) {
    while ($cat = $categories_query->fetch_assoc()) {
        $categories[$cat['id']] = $cat['name'];
    }
}

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $check = $conn->query("SELECT id FROM gallery_items WHERE id = $deleteId");
    if ($check->num_rows > 0) {
        $deleteQuery = "DELETE FROM gallery_items WHERE id = $deleteId";
        if ($conn->query($deleteQuery)) {
            $_SESSION['swal_message'] = ['title' => 'Deleted!', 'text' => 'Gallery item deleted successfully!', 'icon' => 'success'];
        } else {
            $_SESSION['swal_message'] = ['title' => 'Error!', 'text' => 'Failed to delete: ' . $conn->error, 'icon' => 'error'];
        }
    }
    header("Location: gallery.php");
    exit;
}

// Handle AJAX request for getting data
if (isset($_GET['get_id']) && is_numeric($_GET['get_id'])) {
    $get_id = (int)$_GET['get_id'];
    $result = $conn->query("
        SELECT gi.*, git.title, git.description, git.link_url, git.language_id, gct.name as category_name
        FROM gallery_items gi
        LEFT JOIN gallery_items_translations git ON gi.id = git.gallery_item_id
        LEFT JOIN gallery_categories gc ON gi.category_id = gc.id
        LEFT JOIN gallery_categories_translations gct ON gc.id = gct.category_id AND gct.language_id = (SELECT id FROM languages WHERE is_default = 1)
        WHERE gi.id = $get_id
        ORDER BY git.language_id = (SELECT id FROM languages WHERE is_default = 1) DESC
        LIMIT 1
    ");
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    $id = $_POST['id'] ?? '';
    $category_id = (int)$_POST['category_id'];
    $media_type = $_POST['media_type'] ?? 'image';
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    // Translation fields
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $link_url = $conn->real_escape_string($_POST['link_url'] ?? '');
    $language_id = (int)($_POST['language_id'] ?? 1);

    // Handle media upload
    $media_url = $_POST['existing_media'] ?? '';
    $thumbnail_url = $_POST['existing_thumbnail'] ?? '';
    
    if (!empty($_FILES['media']['name'])) {
        $base_dir = dirname(__DIR__);
        $upload_dir = $base_dir . '/uploads/gallery/';
        
        // Create directory with proper permissions
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0777, true)) {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to create upload directory.', 'icon' => 'error']);
                exit;
            }
        }
        
        @chmod($upload_dir, 0777);
        clearstatcache(true, $upload_dir);
        
        if (!is_writable($upload_dir)) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Upload directory not writable.', 'icon' => 'error']);
            exit;
        }
        
        $extension = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'ogg'];

        if (!in_array($extension, $allowed)) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp, mp4, mov, ogg', 'icon' => 'error']);
            exit;
        }
        
        if ($_FILES['media']['size'] > 10 * 1024 * 1024) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'File too large. Max 10MB.', 'icon' => 'error']);
            exit;
        }

        $file_name = 'gallery_' . uniqid() . '_' . mt_rand(10000000, 99999999) . '.' . $extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['media']['tmp_name'], $target_path)) {
            @chmod($target_path, 0644);
            $media_url = 'uploads/gallery/' . $file_name;

            // Generate thumbnail for images
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $thumbnail_url = $media_url;
            } else {
                $thumbnail_url = 'uploads/gallery/video_placeholder.jpg';
            }
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to upload media file.', 'icon' => 'error']);
            exit;
        }
    }

    $result = ['success' => false, 'message' => '', 'icon' => 'error'];
    
    if (empty($id)) {
        // Insert new gallery item
        $query = "INSERT INTO gallery_items (category_id, media_type, media_url, thumbnail_url, sort_order, status) 
                  VALUES ($category_id, '$media_type', '$media_url', '$thumbnail_url', $sort_order, '$status')";

        if ($conn->query($query)) {
            $item_id = $conn->insert_id;

            // Insert translation
            $transQuery = "INSERT INTO gallery_items_translations (gallery_item_id, language_id, title, description, link_url) 
                          VALUES ($item_id, $language_id, '$title', '$description', '$link_url')";
            $conn->query($transQuery);

            $result = ['success' => true, 'message' => 'Gallery item created successfully!', 'icon' => 'success'];
        } else {
            $result['message'] = 'Failed to create: ' . $conn->error;
        }
    } else {
        // Update existing
        $id = (int)$id;
        $query = "UPDATE gallery_items SET 
                  category_id = $category_id,
                  media_type = '$media_type',
                  sort_order = $sort_order,
                  status = '$status'";

        if (!empty($media_url)) $query .= ", media_url = '$media_url'";
        if (!empty($thumbnail_url)) $query .= ", thumbnail_url = '$thumbnail_url'";

        $query .= " WHERE id = $id";

        if ($conn->query($query)) {
            // Update translation
            $checkTrans = $conn->query("SELECT id FROM gallery_items_translations WHERE gallery_item_id = $id AND language_id = $language_id");
            if ($checkTrans->num_rows > 0) {
                $transQuery = "UPDATE gallery_items_translations SET 
                              title = '$title', 
                              description = '$description', 
                              link_url = '$link_url' 
                              WHERE gallery_item_id = $id AND language_id = $language_id";
            } else {
                $transQuery = "INSERT INTO gallery_items_translations (gallery_item_id, language_id, title, description, link_url) 
                              VALUES ($id, $language_id, '$title', '$description', '$link_url')";
            }
            $conn->query($transQuery);

            $result = ['success' => true, 'message' => 'Gallery item updated successfully!', 'icon' => 'success'];
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

// Get all gallery items with translations
$gallery_items = $conn->query("
    SELECT gi.*, gc.name as category_name, git.title, git.description, l.name as language_name
    FROM gallery_items gi
    LEFT JOIN gallery_categories gc ON gi.category_id = gc.id
    LEFT JOIN gallery_items_translations git ON gi.id = git.gallery_item_id AND git.language_id = (SELECT id FROM languages WHERE is_default = 1)
    LEFT JOIN languages l ON git.language_id = l.id
    ORDER BY gi.sort_order ASC, gi.created_at DESC
");

// Get categories with counts
$categories_with_counts = $conn->query("
    SELECT gc.id, gc.name, COUNT(gi.id) as item_count 
    FROM gallery_categories gc 
    LEFT JOIN gallery_items gi ON gc.id = gi.category_id AND gi.status = 'active'
    WHERE gc.status = 'active'
    GROUP BY gc.id
    ORDER BY gc.name
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
            <input type="text" id="searchInput" placeholder="Search gallery items..."
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
                    <p class="text-sm font-medium text-gray-500">Total Items</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $gallery_items ? $gallery_items->num_rows : 0; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-feather="grid" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Categories</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $categories_with_counts ? $categories_with_counts->num_rows : 0; ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i data-feather="folder" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded border border-gray-200 shadow-sm p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active Items</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">
                        <?php
                        $activeQuery = $conn->query("SELECT COUNT(*) as count FROM gallery_items WHERE status = 'active'");
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
                    <p class="text-sm font-medium text-gray-500">Languages</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo count($langs); ?></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i data-feather="globe" class="w-6 h-6 text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Items Table -->
    <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if ($gallery_items && $gallery_items->num_rows > 0): ?>
                        <?php while ($item = $gallery_items->fetch_assoc()): ?>
                            <tr data-title="<?php echo strtolower(htmlspecialchars($item['title'] ?? '')); ?>"
                                data-category="<?php echo strtolower(htmlspecialchars($item['category_name'] ?? '')); ?>">
                                <td class="text-sm font-medium text-gray-700"><?php echo $item['sort_order']; ?></td>
                                <td>
                                    <?php if (!empty($item['thumbnail_url'])): ?>
                                        <img src="../<?php echo $item['thumbnail_url']; ?>" alt="Thumb" class="w-12 h-12 object-cover rounded">
                                    <?php else: ?>
                                        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                            <i data-feather="image" class="w-6 h-6 text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars(substr($item['title'] ?? 'Untitled', 0, 40)); ?></div>
                                    <?php if (strlen($item['title'] ?? '') > 40): ?>...<?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-sm text-gray-700"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></span>
                                </td>
                                <td>
                                    <span class="inline-block px-2 py-1 text-xs rounded <?php echo $item['media_type'] == 'image' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'; ?>">
                                        <?php echo ucfirst($item['media_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $item['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <button onclick="viewItem(<?php echo $item['id']; ?>)" class="btn-action text-blue-600 hover:bg-blue-50 p-2" title="View">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="editItem(<?php echo $item['id']; ?>)" class="btn-action text-yellow-600 hover:bg-yellow-50 p-2" title="Edit">
                                            <i data-feather="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title'] ?? 'This item'); ?>')" class="btn-action text-red-600 hover:bg-red-50 p-2" title="Delete">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500">
                                <i data-feather="grid" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                                <p>No gallery items found. Click "Add New" to create one.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Categories Section -->
    <div class="bg-white rounded border border-gray-200 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Categories</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if ($categories_with_counts && $categories_with_counts->num_rows > 0): ?>
                    <?php while ($cat = $categories_with_counts->fetch_assoc()): ?>
                        <div class="p-4 border rounded-lg hover:shadow-md transition-all">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($cat['name']); ?></p>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo $cat['item_count']; ?> items</p>
                                </div>
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i data-feather="folder" class="w-5 h-5 text-gray-500"></i>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-8 text-gray-500">
                        <i data-feather="folder" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                        <p>No categories found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit/View -->
<div id="galleryModal" class="modal">
    <div class="modal-content">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800" id="modalTitle">Add Gallery Item</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="galleryForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="itemId">

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
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                    <select name="category_id" id="categoryId" required class="form-input">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $catId => $catName): ?>
                            <option value="<?php echo $catId; ?>"><?php echo htmlspecialchars($catName); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Title *</label>
                    <input type="text" name="title" id="title" required placeholder="Enter gallery item title" class="form-input">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" placeholder="Enter description..." class="form-input"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Media Type *</label>
                        <select name="media_type" id="mediaType" required class="form-input" onchange="toggleMediaType()">
                            <option value="image">Image</option>
                            <option value="video">Video</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" id="sortOrder" value="0" class="form-input rounded">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Media File</label>
                    <input type="file" name="media" accept="image/*,video/*" id="mediaFile" class="form-input p-2">
                    <input type="hidden" name="existing_media" id="existingMedia">
                    <input type="hidden" name="existing_thumbnail" id="existingThumbnail">
                    <div id="mediaPreview" class="mt-2"></div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Link URL (Optional)</label>
                    <input type="url" name="link_url" id="linkUrl" placeholder="https://example.com" class="form-input">
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
            <button type="button" onclick="saveItem()" class="px-5 py-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-colors">Save Changes</button>
        </div>
    </div>
</div>

<!-- Media Preview Modal -->
<div id="mediaModal" class="modal">
    <div class="modal-content max-w-2xl">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800">Media Preview</h3>
            <button onclick="closeMediaModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-6 flex justify-center">
            <div id="mediaPreviewContent"></div>
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

    function toggleMediaType() {
        const mediaType = document.getElementById('mediaType').value;
        const fileInput = document.getElementById('mediaFile');
        if (mediaType === 'image') {
            fileInput.accept = 'image/*';
        } else {
            fileInput.accept = 'video/*';
        }
    }

    function openAddModal() {
        isViewMode = false;
        document.getElementById('modalTitle').innerHTML = 'Add Gallery Item';
        document.getElementById('galleryForm').reset();
        document.getElementById('itemId').value = '';
        document.getElementById('languageId').value = '';
        document.getElementById('categoryId').value = '';
        document.getElementById('sortOrder').value = '0';
        clearPreviews();
        enableFormFields(true);
        document.getElementById('galleryModal').classList.add('active');
        feather.replace();
    }

    function editItem(id) {
        isViewMode = false;
        document.getElementById('modalTitle').innerHTML = 'Edit Gallery Item';
        enableFormFields(true);

        fetch(`gallery.php?get_id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('itemId').value = data.id;
                document.getElementById('languageId').value = data.language_id || <?php echo $defaultLangId ?? 1; ?>;
                document.getElementById('categoryId').value = data.category_id;
                document.getElementById('title').value = data.title || '';
                document.getElementById('description').value = data.description || '';
                document.getElementById('mediaType').value = data.media_type;
                document.getElementById('sortOrder').value = data.sort_order || 0;
                document.getElementById('status').value = data.status;
                document.getElementById('linkUrl').value = data.link_url || '';
                document.getElementById('existingMedia').value = data.media_url || '';
                document.getElementById('existingThumbnail').value = data.thumbnail_url || '';

                clearPreviews();
                if (data.media_url && data.media_url !== '') {
                    const mediaType = data.media_type;
                    const mediaUrl = '../' + data.media_url;
                    if (mediaType === 'image') {
                        document.getElementById('mediaPreview').innerHTML = `<img src="${mediaUrl}" class="image-preview" alt="Preview">`;
                    } else {
                        document.getElementById('mediaPreview').innerHTML = `<video src="${mediaUrl}" class="image-preview" controls></video>`;
                    }
                }

                document.getElementById('galleryModal').classList.add('active');
                feather.replace();
            })
            .catch(error => console.error('Error:', error));
    }

    function viewItem(id) {
        isViewMode = true;
        document.getElementById('modalTitle').innerHTML = 'View Gallery Item';
        enableFormFields(false);

        fetch(`gallery.php?get_id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('itemId').value = data.id;
                document.getElementById('categoryId').value = data.category_id;
                document.getElementById('title').value = data.title || '';
                document.getElementById('description').value = data.description || '';
                document.getElementById('mediaType').value = data.media_type;
                document.getElementById('sortOrder').value = data.sort_order || 0;
                document.getElementById('status').value = data.status;
                document.getElementById('linkUrl').value = data.link_url || '';

                clearPreviews();
                if (data.media_url && data.media_url !== '') {
                    const mediaType = data.media_type;
                    const mediaUrl = '../' + data.media_url;
                    if (mediaType === 'image') {
                        document.getElementById('mediaPreview').innerHTML = `<img src="${mediaUrl}" class="image-preview" alt="Preview">`;
                    } else {
                        document.getElementById('mediaPreview').innerHTML = `<video src="${mediaUrl}" class="image-preview" controls></video>`;
                    }
                }

                document.getElementById('galleryModal').classList.add('active');
                feather.replace();
            })
            .catch(error => console.error('Error:', error));
    }

    function saveItem() {
        if (isViewMode) {
            closeModal();
            return;
        }

        const languageId = document.getElementById('languageId').value;
        const categoryId = document.getElementById('categoryId').value;
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
        if (!categoryId) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please select a category',
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

        const form = document.getElementById('galleryForm');
        const formData = new FormData(form);

        Swal.fire({
            title: 'Processing...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('gallery.php', {
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
                    text: data.message || 'Failed to save gallery item.',
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
                window.location.href = `gallery.php?delete=${id}`;
            }
        });
    }

    function showMediaPreview(url, type) {
        const container = document.getElementById('mediaPreviewContent');
        if (type === 'image') {
            container.innerHTML = `<img src="${url}" class="max-w-full rounded-lg">`;
        } else {
            container.innerHTML = `<video src="${url}" class="max-w-full rounded-lg" controls autoplay></video>`;
        }
        document.getElementById('mediaModal').classList.add('active');
    }

    function closeMediaModal() {
        document.getElementById('mediaModal').classList.remove('active');
    }

    function closeModal() {
        document.getElementById('galleryModal').classList.remove('active');
        enableFormFields(true);
    }

    function enableFormFields(enable) {
        const inputs = document.querySelectorAll('#galleryForm input, #galleryForm textarea, #galleryForm select');
        inputs.forEach(input => {
            if (input.type !== 'file') {
                input.disabled = !enable;
            }
        });

        const fileInputs = document.querySelectorAll('#galleryForm input[type="file"]');
        fileInputs.forEach(input => {
            input.style.display = enable ? 'block' : 'none';
        });

        const saveButton = document.querySelector('#galleryModal .modal-footer button:last-child');
        if (saveButton) {
            saveButton.style.display = enable ? 'block' : 'none';
        }
    }

    function clearPreviews() {
        document.getElementById('mediaPreview').innerHTML = '';
    }

    // Media preview handler
    document.getElementById('mediaFile')?.addEventListener('change', function(e) {
        const preview = document.getElementById('mediaPreview');
        preview.innerHTML = '';
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            const mediaType = document.getElementById('mediaType').value;
            reader.onload = function(e) {
                if (mediaType === 'image') {
                    preview.innerHTML = `<img src="${e.target.result}" class="image-preview" alt="Preview">`;
                } else {
                    preview.innerHTML = `<video src="${e.target.result}" class="image-preview" controls></video>`;
                }
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
            const category = row.getAttribute('data-category') || '';

            if (title.includes(searchTerm) || category.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const galleryModal = document.getElementById('galleryModal');
        const mediaModal = document.getElementById('mediaModal');
        if (event.target === galleryModal) {
            closeModal();
        }
        if (event.target === mediaModal) {
            closeMediaModal();
        }
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>