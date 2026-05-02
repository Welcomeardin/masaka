<?php
// MUST be first - suppress all errors before any includes
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering immediately to catch any accidental output
ob_start();

session_start();

// Check if this is an AJAX POST request FIRST before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once __DIR__ . '/../auth/require_login.php';
        require_once __DIR__ . '/../API/config.php';
        
        // Check database connection
        if (!isset($conn) || $conn->connect_error) {
            while (ob_get_level()) { ob_end_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . ($conn->connect_error ?? 'Unknown error'), 'icon' => 'error']);
            exit;
        }
    
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $address = $conn->real_escape_string($_POST['address'] ?? '');
    $facebook = $conn->real_escape_string($_POST['facebook_link'] ?? '');
    $instagram = $conn->real_escape_string($_POST['instagram_link'] ?? '');
    $youtube = $conn->real_escape_string($_POST['youtube_link'] ?? '');
    $twitter = $conn->real_escape_string($_POST['twitter_link'] ?? '');

    // Handle translations
    $address_texts = $_POST['address_text'] ?? [];
    $footer_texts = $_POST['footer_text'] ?? [];
    $copyright_texts = $_POST['copyright_text'] ?? [];

    // Handle logo upload
    $logo_url = '';
    if (!empty($_FILES['logo']['name'])) {
        // Correct path - go up from admin to root, then to uploads/settings
        $base_dir = dirname(__DIR__); // Get the root directory
        $uploads_parent = $base_dir . '/uploads/';
        $upload_dir = $uploads_parent . 'settings/';
        
        // Create parent uploads directory if needed
        if (!is_dir($uploads_parent)) {
            if (!@mkdir($uploads_parent, 0777, true)) {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to create uploads directory. Contact server administrator.', 'icon' => 'error']);
                exit;
            }
            @chmod($uploads_parent, 0777);
        }
        
        // Create settings subdirectory if needed
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0777, true)) {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to create uploads/settings directory. Contact server administrator.', 'icon' => 'error']);
                exit;
            }
            @chmod($upload_dir, 0777);
        } else {
            // If directory exists, make sure it's writable
            @chmod($upload_dir, 0777);
        }
        
        // Clear PHP's realpath cache to ensure fresh check
        clearstatcache(true, $upload_dir);
        
        // Verify directory is writable
        if (!is_writable($upload_dir)) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Upload directory is not writable. Please check server permissions for: ' . $upload_dir . ' | Please run setup_permissions.sh or chmod -R 777 on uploads folder', 'icon' => 'error']);
            exit;
        }
        
        $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        
        if (in_array($extension, $allowed)) {
            // Check file size (max 5MB)
            if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 5MB.', 'icon' => 'error']);
                exit;
            }
            
            $file_name = 'logo_' . uniqid() . '_' . time() . '.' . $extension;
            $target_path = $upload_dir . $file_name;
            
            // Check if file was uploaded successfully
            if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => 'File is too large (server limit)',
                    UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit)',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                ];
                $error_message = $upload_errors[$_FILES['logo']['error']] ?? 'Unknown upload error';
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Upload error: ' . $error_message, 'icon' => 'error']);
                exit;
            }
            
            // Try to move the file
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)) {
                // Set proper file permissions
                chmod($target_path, 0644);
                
                // Store relative path for database (from root)
                $logo_url = 'uploads/settings/' . $file_name;
            } else {
                // Get more detailed error information
                $error_info = error_get_last();
                $error_msg = 'Failed to move uploaded file. ';
                if ($error_info) {
                    $error_msg .= 'Error: ' . $error_info['message'];
                }
                
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error_msg, 'icon' => 'error']);
                exit;
            }
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp, svg', 'icon' => 'error']);
            exit;
        }
    }

    $result = ['success' => false, 'message' => '', 'icon' => 'error'];
    
    // Check if settings exist
    $checkSettings = $conn->query("SELECT id, logo_url FROM settings LIMIT 1");
    
    if ($checkSettings && $checkSettings->num_rows > 0) {
        // UPDATE existing settings
        $logo_part = '';
        if (!empty($logo_url)) {
            $logo_part = ", logo_url = '$logo_url'";
        }
        
        $query = "UPDATE settings SET 
                  email='$email', 
                  phone='$phone', 
                  address='$address', 
                  facebook_link='$facebook', 
                  instagram_link='$instagram', 
                  youtube_link='$youtube', 
                  twitter_link='$twitter' 
                  $logo_part 
                  WHERE id=1";
        
        if ($conn->query($query)) {
            $result = ['success' => true, 'message' => 'Settings updated successfully!', 'icon' => 'success'];
        } else {
            $result['message'] = 'Failed to update: ' . $conn->error;
        }
    } else {
        // INSERT new settings - try without specifying id first (auto-increment)
        $logo_value = !empty($logo_url) ? "'$logo_url'" : "NULL";
        $query = "INSERT INTO settings (email, phone, address, facebook_link, instagram_link, youtube_link, twitter_link, logo_url) 
                  VALUES ('$email', '$phone', '$address', '$facebook', '$instagram', '$youtube', '$twitter', $logo_value)";
        
        if ($conn->query($query)) {
            $result = ['success' => true, 'message' => 'Settings created successfully!', 'icon' => 'success'];
        } else {
            $result['message'] = 'Database error: ' . $conn->error;
        }
    }

    // Update or insert translations
    foreach ($address_texts as $lang_id => $address_text) {
        $address_text = $conn->real_escape_string($address_text);
        $footer_text = $conn->real_escape_string($footer_texts[$lang_id] ?? '');
        $copyright_text = $conn->real_escape_string($copyright_texts[$lang_id] ?? '');

        $checkTrans = $conn->query("SELECT id FROM settings_translations WHERE language_id = $lang_id");
        if ($checkTrans && $checkTrans->num_rows > 0) {
            $transQuery = "UPDATE settings_translations SET 
                          address_text = '$address_text', 
                          footer_text = '$footer_text', 
                          copyright_text = '$copyright_text' 
                          WHERE language_id = $lang_id";
        } else {
            $transQuery = "INSERT INTO settings_translations (language_id, address_text, footer_text, copyright_text) 
                          VALUES ($lang_id, '$address_text', '$footer_text', '$copyright_text')";
        }
        $conn->query($transQuery);
    }
    
    // Ensure we have a valid result
    if (empty($result)) {
        $result = ['success' => false, 'message' => 'Unknown error occurred', 'icon' => 'error'];
    }
    
        // Clear any output buffers and send JSON response
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

// For non-POST requests, continue with normal page display
require_once __DIR__ . '/../auth/require_login.php';
require_once __DIR__ . '/../API/config.php';

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
}

$pageTitle = 'Site Settings';

// Get all languages for translations
$languages_query = $conn->query("SELECT id, code, name FROM languages WHERE is_active = 1");
$langs = [];
if ($languages_query) {
    while ($lang = $languages_query->fetch_assoc()) {
        $langs[$lang['id']] = $lang;
    }
}

// Get settings data
$settings = $conn->query("SELECT * FROM settings LIMIT 1");
$settingsData = $settings->num_rows > 0 ? $settings->fetch_assoc() : null;

// Get translations for settings
$translations = [];
if ($settingsData) {
    $transQuery = $conn->query("SELECT * FROM settings_translations");
    while ($trans = $transQuery->fetch_assoc()) {
        $translations[$trans['language_id']] = $trans;
    }
}

// Get SweetAlert message from session
$swal_message = null;
if (isset($_SESSION['swal_message'])) {
    $swal_message = $_SESSION['swal_message'];
    unset($_SESSION['swal_message']);
}
?>

<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
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

    .form-textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 14px;
        resize: vertical;
        transition: all 0.2s;
    }

    .form-textarea:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .section-card {
        transition: all 0.3s;
    }

    .section-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .image-preview {
        max-width: 150px;
        max-height: 150px;
        margin-top: 10px;
        border-radius: 5px;
        border: 2px solid #e5e7eb;
        padding: 4px;
        object-fit: contain;
    }

    .social-icon {
        transition: all 0.2s;
    }

    .social-icon:hover {
        transform: scale(1.05);
    }

    .language-tab {
        cursor: pointer;
        padding: 8px 16px;
        transition: all 0.2s;
    }

    .language-tab-active {
        background-color: #6366f1;
        color: white;
    }

    .language-tab-inactive {
        background-color: #f3f4f6;
        color: #374151;
    }

    .language-tab-inactive:hover {
        background-color: #e5e7eb;
    }

    .language-content {
        display: none;
    }

    .language-content.active {
        display: block;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Site Settings</h2>
            <p class="text-gray-500 mt-1">Manage your website configuration and branding</p>
        </div>
        <div class="flex gap-2">
            <a href="../index.php" target="_blank" class="text-primary-600 hover:text-primary-700 text-sm flex items-center gap-1">
                <i data-feather="external-link" class="w-4 h-4"></i> View Site
            </a>
        </div>
    </div>

    <!-- Main Settings Form -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <form method="POST" enctype="multipart/form-data" id="settingsForm">
            <!-- General Settings -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-feather="settings" class="w-5 h-5 text-gray-500"></i>
                    General Settings
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Email</label>
                        <input type="email" name="email" class="form-input"
                            value="<?php echo $settingsData ? htmlspecialchars($settingsData['email']) : ''; ?>"
                            placeholder="admin@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Phone</label>
                        <input type="text" name="phone" class="form-input"
                            value="<?php echo $settingsData ? htmlspecialchars($settingsData['phone']) : ''; ?>"
                            placeholder="+1 234 567 8900">
                    </div>
                </div>
                <div class="mt-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Address (Default Language)</label>
                    <textarea name="address" class="form-textarea" rows="2"
                        placeholder="Your organization address..."><?php echo $settingsData ? htmlspecialchars($settingsData['address']) : ''; ?></textarea>
                </div>
            </div>

            <!-- Logo Settings -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-feather="image" class="w-5 h-5 text-gray-500"></i>
                    Branding
                </h3>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Site Logo</label>
                    <?php if ($settingsData && $settingsData['logo_url'] && file_exists('../' . $settingsData['logo_url'])): ?>
                        <div class="mb-3">
                            <img src="../<?php echo htmlspecialchars($settingsData['logo_url']); ?>?v=<?php echo time(); ?>" alt="Current Logo" class="image-preview">
                            <p class="text-xs text-gray-500 mt-1">Current logo</p>
                        </div>
                    <?php else: ?>
                        <div class="mb-3 text-gray-400">
                            <i data-feather="image" class="w-12 h-12 mx-auto"></i>
                            <p class="text-xs text-center mt-1">No logo uploaded</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="logo" class="form-input p-2" accept="image/*">
                    <p class="text-xs text-gray-500 mt-1">Recommended size: 200x50px. Max 5MB.</p>
                </div>
            </div>

            <!-- Social Media Links -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-feather="share-2" class="w-5 h-5 text-gray-500"></i>
                    Social Media Links
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-[#1877f2] bg-opacity-10 rounded-lg flex items-center justify-center">
                            <i data-feather="facebook" class="w-5 h-5 text-[#1877f2]"></i>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Facebook</label>
                            <input type="url" name="facebook_link" class="form-input"
                                value="<?php echo $settingsData ? htmlspecialchars($settingsData['facebook_link']) : ''; ?>"
                                placeholder="https://facebook.com/yourpage">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-[#e4405f] bg-opacity-10 rounded-lg flex items-center justify-center">
                            <i data-feather="instagram" class="w-5 h-5 text-[#e4405f]"></i>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Instagram</label>
                            <input type="url" name="instagram_link" class="form-input"
                                value="<?php echo $settingsData ? htmlspecialchars($settingsData['instagram_link']) : ''; ?>"
                                placeholder="https://instagram.com/yourpage">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-[#ff0000] bg-opacity-10 rounded-lg flex items-center justify-center">
                            <i data-feather="youtube" class="w-5 h-5 text-[#ff0000]"></i>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">YouTube</label>
                            <input type="url" name="youtube_link" class="form-input"
                                value="<?php echo $settingsData ? htmlspecialchars($settingsData['youtube_link']) : ''; ?>"
                                placeholder="https://youtube.com/c/yourchannel">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-[#1da1f2] bg-opacity-10 rounded-lg flex items-center justify-center">
                            <i data-feather="twitter" class="w-5 h-5 text-[#1da1f2]"></i>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Twitter/X</label>
                            <input type="url" name="twitter_link" class="form-input"
                                value="<?php echo $settingsData ? htmlspecialchars($settingsData['twitter_link']) : ''; ?>"
                                placeholder="https://twitter.com/yourprofile">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Multi-language Content -->
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-feather="globe" class="w-5 h-5 text-gray-500"></i>
                    Multi-language Content
                </h3>

                <!-- Language Tabs -->
                <div class="flex flex-wrap gap-2 mb-5">
                    <?php foreach ($langs as $langId => $lang): ?>
                        <button type="button" onclick="switchLanguage(<?php echo $langId; ?>)"
                            class="language-tab <?php echo $langId == 1 ? 'language-tab-active' : 'language-tab-inactive'; ?> rounded-full"
                            id="tab_<?php echo $langId; ?>">
                            <?php echo htmlspecialchars($lang['name']); ?> (<?php echo strtoupper($lang['code']); ?>)
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Language Content -->
                <?php foreach ($langs as $langId => $lang): ?>
                    <div id="content_<?php echo $langId; ?>" class="language-content <?php echo $langId == 1 ? 'active' : ''; ?>">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Address Text (<?php echo htmlspecialchars($lang['name']); ?>)</label>
                                <textarea name="address_text[<?php echo $langId; ?>]" class="form-textarea" rows="2"
                                    placeholder="Address in <?php echo htmlspecialchars($lang['name']); ?>..."><?php
                                                                                                                echo isset($translations[$langId]) ? htmlspecialchars($translations[$langId]['address_text']) : '';
                                                                                                                ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Footer Text (<?php echo htmlspecialchars($lang['name']); ?>)</label>
                                <textarea name="footer_text[<?php echo $langId; ?>]" class="form-textarea" rows="2"
                                    placeholder="Footer text in <?php echo htmlspecialchars($lang['name']); ?>..."><?php
                                                                                                                    echo isset($translations[$langId]) ? htmlspecialchars($translations[$langId]['footer_text']) : '';
                                                                                                                    ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Copyright Text (<?php echo htmlspecialchars($lang['name']); ?>)</label>
                                <textarea name="copyright_text[<?php echo $langId; ?>]" class="form-textarea" rows="2"
                                    placeholder="Copyright text in <?php echo htmlspecialchars($lang['name']); ?>..."><?php
                                                                                                                        echo isset($translations[$langId]) ? htmlspecialchars($translations[$langId]['copyright_text']) : '';
                                                                                                                        ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                <button type="reset" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 transition-colors">
                    Reset
                </button>
                <button type="submit" class="px-5 py-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-colors flex items-center gap-2">
                    <i data-feather="save" class="w-4 h-4"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Information Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 section-card">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-feather="mail" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Email Configuration</p>
                    <p class="text-sm text-gray-700">Update contact email for receiving inquiries</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 section-card">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i data-feather="share-2" class="w-6 h-6 text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Social Media</p>
                    <p class="text-sm text-gray-700">Connect your social media profiles</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 section-card">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i data-feather="globe" class="w-6 h-6 text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Multi-language</p>
                    <p class="text-sm text-gray-700">Content available in <?php echo count($langs); ?> languages</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let activeLanguage = 1;

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

    function switchLanguage(langId) {
        // Remove active class from all tabs and contents
        document.querySelectorAll('.language-tab').forEach(tab => {
            tab.classList.remove('language-tab-active');
            tab.classList.add('language-tab-inactive');
        });
        document.querySelectorAll('.language-content').forEach(content => {
            content.classList.remove('active');
        });

        // Add active class to selected tab and content
        document.getElementById(`tab_${langId}`).classList.add('language-tab-active');
        document.getElementById(`tab_${langId}`).classList.remove('language-tab-inactive');
        document.getElementById(`content_${langId}`).classList.add('active');

        activeLanguage = langId;
    }

    // Logo preview
    document.querySelector('input[name="logo"]')?.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Find or create preview container
                let container = document.querySelector('.image-preview')?.parentElement;
                if (!container) {
                    container = document.querySelector('input[name="logo"]').parentElement.querySelector('div');
                }
                if (container) {
                    // Clear existing preview
                    container.innerHTML = '';
                    // Create new image
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    img.alt = 'Logo Preview';
                    img.style.maxWidth = '150px';
                    img.style.maxHeight = '150px';
                    img.style.marginTop = '10px';
                    img.style.borderRadius = '5px';
                    img.style.border = '2px solid #e5e7eb';
                    img.style.padding = '4px';
                    img.style.objectFit = 'contain';
                    container.appendChild(img);
                    // Add preview text
                    const p = document.createElement('p');
                    p.className = 'text-xs text-blue-500 mt-1';
                    p.textContent = 'New logo preview';
                    container.appendChild(p);
                }
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Reset button functionality
    document.querySelector('button[type="reset"]')?.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Reset Form?',
            text: 'This will reset all unsaved changes.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6b7280',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Reset',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload();
            }
        });
    });

    // Form submission with SweetAlert
    document.getElementById('settingsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Save Settings?',
            text: 'Are you sure you want to save these settings?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6366f1',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, save!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('settingsForm');
                const formData = new FormData(form);

                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(async response => {
                    const text = await response.text();
                    console.log('Server response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw new Error('Invalid JSON response from server');
                    }
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to save settings.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Server error. Check browser console for details.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                });
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>