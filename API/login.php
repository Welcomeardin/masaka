<?php
session_start();
include __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth/login.php');
    exit;
}

$login_id = trim($_POST['login_id'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($login_id === '' || $password === '') {
    $_SESSION['login_error'] = 'Veuillez entrer votre identifiant et mot de passe.';
    header('Location: ../auth/login.php');
    exit;
}

// Query login
$stmt = $conn->prepare("SELECT id, type_user, first_name, second_name, email, password 
                        FROM users 
                        WHERE email = ? OR telephone = ? OR identifiant = ?");
$stmt->bind_param("sss", $login_id, $login_id, $login_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    $_SESSION['login_error'] = 'Utilisateur introuvable.';
    header('Location: ../auth/login.php');
    exit;
}

$stmt->bind_result($id, $type_user, $first_name, $second_name, $email, $hash);
$stmt->fetch();
$stmt->close();

// Verify password
$authenticated = false;
if (password_verify($password, $hash)) {
    $authenticated = true;
} else {
    if ($hash === md5($password) || $hash === sha1($password) || $hash === $password) {
        $authenticated = true;
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $up = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $up->bind_param("si", $newHash, $id);
        $up->execute();
        $up->close();
    }
}

if (!$authenticated) {
    $_SESSION['login_error'] = 'Mot de passe incorrect.';
    header('Location: ../auth/login.php');
    exit;
}

// Set unified user session
$_SESSION['user'] = [
    'id'          => $id,
    'first_name'  => $first_name,
    'second_name' => $second_name,
    'email'       => $email,
    'type_user'   => $type_user,
    'identifiant' => $login_id  // this should be the login name, NOT email
];


// Redirect
if ($type_user === 'admin') {
    $_SESSION['login_success'] = "Connexion réussie ! Bienvenue Admin.";
    header('Location: ../Nouveaux/English/dashboard.php');
} else {
    $_SESSION['login_success'] = "Connexion réussie ! Bienvenue.";
    header('Location: ../Nouveaux/user/dashboard.php');
}
exit;
