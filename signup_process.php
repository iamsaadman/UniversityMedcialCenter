<?php
require_once __DIR__ . '/includes/dp.php';

function redirect($url) {
    header("Location: $url");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('signup.php');
}

$fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$institutionId = isset($_POST['id']) ? trim($_POST['id']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';
$termsAccepted = isset($_POST['terms']);

$validRoles = ['student', 'doctor', 'admin'];

if (empty($fullname) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($institutionId) || strlen($password) < 8 || !in_array($role, $validRoles, true) || !$termsAccepted) {
    redirect('signup.php?error=invalid');
}

$checkStmt = $mysqli->prepare('SELECT 1 FROM users WHERE email = ? OR institution_id = ? LIMIT 1');
if (!$checkStmt) {
    redirect('signup.php?error=db');
}

$checkStmt->bind_param('ss', $email, $institutionId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    redirect('signup.php?error=exists');
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$insertStmt = $mysqli->prepare('INSERT INTO users (fullname, email, institution_id, role, password_hash) VALUES (?, ?, ?, ?, ?)');
if (!$insertStmt) {
    redirect('signup.php?error=db');
}

$insertStmt->bind_param('sssss', $fullname, $email, $institutionId, $role, $passwordHash);
$ok = $insertStmt->execute();

if ($ok) {
    redirect('login.php?signup=success');
} else {
    redirect('signup.php?error=failed');
}
?>