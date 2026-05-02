<?php
/**
 * Language Initialization
 * Must be included at the top of every front-end page before any output
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Language mapping
$LANGUAGES = [
    1 => ['code' => 'en', 'name' => 'English', 'flag' => '🇬🇧'],
    2 => ['code' => 'sw', 'name' => 'Kiswahili', 'flag' => '🇰🇪'],
    3 => ['code' => 'fr', 'name' => 'Français', 'flag' => '🇫🇷']
];

// Default language (French)
$DEFAULT_LANGUAGE_ID = 3;
$DEFAULT_LANG_CODE = 'fr';

// Handle language switch via GET parameter
if (isset($_GET['lang']) && is_numeric($_GET['lang'])) {
    $requested_lang = (int)$_GET['lang'];
    if (isset($LANGUAGES[$requested_lang])) {
        $_SESSION['language_id'] = $requested_lang;
        $_SESSION['lang_code'] = $LANGUAGES[$requested_lang]['code'];
    }
}

// Set current language from session or default
$language_id = $_SESSION['language_id'] ?? $DEFAULT_LANGUAGE_ID;
$lang_code = $_SESSION['lang_code'] ?? $DEFAULT_LANG_CODE;

// Validate language exists
if (!isset($LANGUAGES[$language_id])) {
    $language_id = $DEFAULT_LANGUAGE_ID;
    $lang_code = $DEFAULT_LANG_CODE;
}

// Get current language info
$current_language = $LANGUAGES[$language_id];

/**
 * Build URL with current language parameter
 * @param string $path - the page path (e.g., 'index.php', 'about.php')
 * @return string - URL with lang parameter
 */
function getLanguageUrl($path) {
    global $language_id;
    $separator = (strpos($path, '?') !== false) ? '&' : '?';
    return $path . $separator . 'lang=' . $language_id;
}

/**
 * Get translation from database with fallback
 * @param mysqli $conn - database connection
 * @param string $table - table name
 * @param int $item_id - item ID
 * @param string $field - field to get
 * @return string - translated value or fallback
 */
function getTranslation($conn, $table, $item_id, $field) {
    global $language_id;
    
    $tables_with_translations = [
        'team' => ['trans_table' => 'team_translations', 'fk' => 'team_id'],
        'gallery_items' => ['trans_table' => 'gallery_items_translations', 'fk' => 'gallery_item_id']
    ];
    
    if (!isset($tables_with_translations[$table])) {
        return null;
    }
    
    $trans = $tables_with_translations[$table];
    $trans_table = $trans['trans_table'];
    $fk = $trans['fk'];
    
    // Try current language
    $query = "SELECT $field FROM $trans_table WHERE $fk = ? AND language_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $item_id, $language_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        if (!empty($row[$field])) {
            return $row[$field];
        }
    }
    
    // Fallback to English (language_id = 1)
    $stmt = $conn->prepare("SELECT $field FROM $trans_table WHERE $fk = ? AND language_id = 1");
    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row[$field];
    }
    
    return null;
}

/**
 * Fetch content from table with language fallback
 * @param mysqli $conn - database connection
 * @param string $table - table name
 * @param string $where - additional WHERE clause
 * @param string $order - ORDER BY clause
 * @param int $limit - LIMIT
 * @return mysqli_result|false
 */
function fetchContent($conn, $table, $where = '', $order = '', $limit = 0) {
    global $language_id;
    
    // Tables with language_id column
    $multilingual_tables = ['slides', 'events', 'about'];
    
    $sql = "SELECT * FROM $table WHERE 1=1";
    
    // Add language filter for multilingual tables
    if (in_array($table, $multilingual_tables)) {
        $sql .= " AND (language_id = $language_id OR language_id = 1)";
        // Prefer current language over English
        if (!empty($order)) {
            $order = "language_id = $language_id DESC, " . $order;
        } else {
            $order = "language_id = $language_id DESC";
        }
    }
    
    if (!empty($where)) {
        $sql .= " AND $where";
    }
    
    if (!empty($order)) {
        $sql .= " ORDER BY $order";
    }
    
    if ($limit > 0) {
        $sql .= " LIMIT $limit";
    }
    
    return $conn->query($sql);
}
