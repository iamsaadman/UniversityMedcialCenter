<?php
session_start();
require_once __DIR__ . '/includes/dp.php';

function redirect($url) {
    header("Location: $url");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';

$validRoles = ['student', 'doctor', 'admin'];
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password) || !in_array($role, $validRoles, true)) {
    redirect('login.php?error=invalid');
}

$stmt = $mysqli->prepare('SELECT id, fullname, email, role, password_hash FROM users WHERE email = ? AND role = ? LIMIT 1');
if (!$stmt) {
    redirect('login.php?error=db');
}

$stmt->bind_param('ss', $email, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password_hash'])) {
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['fullname'] = $row['fullname'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['role'] = $row['role'];

        if ($row['role'] === 'student') {
            redirect('studentportal.php');
        } elseif ($row['role'] === 'doctor') {
            redirect('doctorportal.php');
        } else {
            redirect('adminportal.php');
        }
    } else {
        redirect('login.php?error=auth');
    }
} else {
    redirect('login.php?error=notfound');
}

redirect('login.php?error=unknown');
?>