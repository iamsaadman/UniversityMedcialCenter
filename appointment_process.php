<?php
session_start();
require_once __DIR__ . '/includes/dp.php';

function redirect($url) {
    header("Location: $url");
    exit;
}

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('studentbooknewappointment.php');
}

// Get and validate input
$doctor_id = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;
$appointment_date = isset($_POST['appointment_date']) ? trim($_POST['appointment_date']) : '';
$appointment_time = isset($_POST['appointment_time']) ? trim($_POST['appointment_time']) : '';
$reason_for_visit = isset($_POST['reason_for_visit']) ? trim($_POST['reason_for_visit']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// Validate inputs
if (!$doctor_id || empty($appointment_date) || empty($appointment_time) || empty($reason_for_visit)) {
    redirect('studentbooknewappointment.php?error=invalid');
}

// Validate date is not in the past
$appointmentDateTime = strtotime($appointment_date . ' ' . $appointment_time);
$tomorrow = strtotime('tomorrow midnight');
if ($appointmentDateTime < $tomorrow) {
    redirect('studentbooknewappointment.php?error=past_date');
}

// Validate reason_for_visit length
if (strlen($reason_for_visit) > 255) {
    redirect('studentbooknewappointment.php?error=invalid');
}

// Validate notes length
if (strlen($notes) > 1000) {
    redirect('studentbooknewappointment.php?error=invalid');
}

// Verify doctor exists in users table with role='doctor'
$doctorStmt = $mysqli->prepare('SELECT id FROM users WHERE id = ? AND role = \'doctor\' LIMIT 1');
if (!$doctorStmt) {
    redirect('studentbooknewappointment.php?error=db');
}

$doctorStmt->bind_param('i', $doctor_id);
$doctorStmt->execute();
$doctorResult = $doctorStmt->get_result();

if (!$doctorResult || $doctorResult->num_rows === 0) {
    redirect('studentbooknewappointment.php?error=invalid');
}

// Insert appointment
$insertStmt = $mysqli->prepare('
    INSERT INTO appointments 
    (student_id, doctor_id, appointment_date, appointment_time, reason_for_visit, notes, status) 
    VALUES (?, ?, ?, ?, ?, ?, \'pending\')
');

if (!$insertStmt) {
    redirect('studentbooknewappointment.php?error=db');
}

$student_id = (int)$_SESSION['user_id'];
$insertStmt->bind_param('iissss', $student_id, $doctor_id, $appointment_date, $appointment_time, $reason_for_visit, $notes);

if ($insertStmt->execute()) {
    $appointment_id = $insertStmt->insert_id;
    
    // Get student name for notification
    $studentStmt = $mysqli->prepare('SELECT fullname FROM users WHERE id = ?');
    $studentStmt->bind_param('i', $student_id);
    $studentStmt->execute();
    $studentResult = $studentStmt->get_result();
    $studentRow = $studentResult->fetch_assoc();
    $student_name = $studentRow['fullname'];
    
    // Create notification for doctor
    $notificationMsg = "New appointment booked by $student_name on " . date('M d, Y', strtotime($appointment_date)) . " at $appointment_time";
    $notificationStmt = $mysqli->prepare('
        INSERT INTO notifications 
        (user_id, message, type, reference_id) 
        VALUES (?, ?, \'appointment\', ?)
    ');
    
    if ($notificationStmt) {
        $notificationStmt->bind_param('isi', $doctor_id, $notificationMsg, $appointment_id);
        $notificationStmt->execute();
        $notificationStmt->close();
    }
    
    $studentStmt->close();
    redirect('studentbooknewappointment.php?success=1');
} else {
    redirect('studentbooknewappointment.php?error=db');
}
?>
