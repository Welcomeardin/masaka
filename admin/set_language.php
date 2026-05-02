<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lang_id'])) {
    $langId = (int)$_POST['lang_id'];
    $_SESSION['current_lang_id'] = $langId;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
