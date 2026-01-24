<?php
session_start();
require_once __DIR__ . '/includes/dp.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  exit('Unauthorized');
}

$prescription_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user_id = (int) $_SESSION['user_id'];

if ($prescription_id === 0) {
  http_response_code(400);
  exit('Invalid prescription id');
}

$sql = "SELECT p.*, d.fullname AS doctor_name, s.fullname AS student_name, a.appointment_date, a.appointment_time
        FROM prescriptions p
        JOIN users d ON p.doctor_id = d.id
        JOIN users s ON p.student_id = s.id
        LEFT JOIN appointments a ON p.appointment_id = a.id
        WHERE p.id = ? AND (p.student_id = ? OR p.doctor_id = ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('iii', $prescription_id, $user_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
  http_response_code(404);
  exit('Not found');
}
$p = $res->fetch_assoc();
$stmt->close();

// Render a simple detail view instead of generating a PDF
$title = htmlspecialchars($p['title'] ?? 'Prescription');
$doctor = htmlspecialchars($p['doctor_name'] ?? '');
$student = htmlspecialchars($p['student_name'] ?? '');
$diagnosis = htmlspecialchars($p['diagnosis'] ?? '');
$medications = nl2br(htmlspecialchars($p['medications'] ?? ''));
$instructions = nl2br(htmlspecialchars($p['instructions'] ?? ''));
$follow_up = htmlspecialchars($p['follow_up_date'] ?? '');
$created = htmlspecialchars(substr($p['created_at'], 0, 16));
$appt_date = htmlspecialchars($p['appointment_date'] ?? '');
$appt_time = htmlspecialchars($p['appointment_time'] ?? '');
$role = $_SESSION['role'] ?? '';
// Prepare medications as a list (split by newline)
$medications_list = array_filter(array_map('trim', explode("\n", $p['medications'] ?? '')));

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?> | Prescription</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @media print {
      .no-print { display: none !important; }
      body { background: #fff !important; }
    }
  </style>
</head>
<body class="bg-white">
  <div class="w-full">
    <div class="px-8 py-6">
      <!-- Header: Clinic Info + Print -->
      <div class="flex items-start justify-between border-b pb-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">University Medical Center</h1>
          <p class="text-sm text-gray-600">Campus Health Services • Tel: (555) 000-0000</p>
          <p class="text-xs text-gray-500 mt-1">Created on <?php echo $created; ?><?php if ($appt_date): ?> • Appointment: <?php echo $appt_date . ($appt_time ? ' at ' . $appt_time : ''); ?><?php endif; ?></p>
        </div>
        <div class="flex items-center gap-2">
          <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo ($role === 'student') ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'; ?>"><?php echo ($role === 'student') ? 'Student View' : 'Doctor View'; ?></span>
          <button class="no-print bg-blue-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-blue-700" onclick="window.print()">Print Prescription</button>
        </div>
      </div>

      <!-- Rx Title Row -->
      <div class="flex items-center gap-3 mt-4">
        <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
          <span class="text-orange-600 font-bold text-lg">℞</span>
        </div>
        <div>
          <p class="text-xs text-gray-500">Prescription</p>
          <h2 class="text-xl font-bold text-gray-900"><?php echo $title; ?></h2>
        </div>
      </div>

      <!-- Patient/Doctor/Follow-up -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="border rounded-xl p-4">
          <p class="text-xs font-semibold text-gray-600">Patient</p>
          <p class="text-gray-900 font-medium mt-1"><?php echo $student; ?></p>
        </div>
        <div class="border rounded-xl p-4">
          <p class="text-xs font-semibold text-gray-600">Doctor</p>
          <p class="text-gray-900 font-medium mt-1"><?php echo 'Dr. ' . $doctor; ?></p>
        </div>
        <div class="border rounded-xl p-4">
          <p class="text-xs font-semibold text-gray-600">Date</p>
          <p class="text-gray-900 font-medium mt-1"><?php echo htmlspecialchars(substr($p['created_at'], 0, 10)); ?></p>
        </div>
        <?php if ($follow_up): ?>
        <div class="border rounded-xl p-4 md:col-span-3 bg-emerald-50">
          <p class="text-xs font-semibold text-emerald-700">Follow-up</p>
          <p class="text-emerald-800 font-medium mt-1"><?php echo $follow_up; ?></p>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($diagnosis): ?>
      <div class="mt-6 border rounded-xl p-4 bg-gray-50">
        <p class="text-xs font-semibold text-gray-600">Diagnosis</p>
        <p class="text-gray-800 mt-1"><?php echo $diagnosis; ?></p>
      </div>
      <?php endif; ?>

      <div class="mt-6 border rounded-xl p-4 bg-gray-50">
        <p class="text-xs font-semibold text-gray-600">Medications</p>
        <?php if (!empty($medications_list)): ?>
          <ul class="mt-2 space-y-2">
            <?php foreach ($medications_list as $line): ?>
              <li class="flex items-start gap-2">
                <span class="mt-1 w-2 h-2 rounded-full bg-orange-400"></span>
                <span class="text-gray-900"><?php echo htmlspecialchars($line); ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="text-gray-800 mt-2 leading-relaxed"><?php echo $medications; ?></div>
        <?php endif; ?>
      </div>

      <?php if ($instructions): ?>
      <div class="mt-6 border rounded-xl p-4 bg-gray-50">
        <p class="text-xs font-semibold text-gray-600">Instructions</p>
        <div class="text-gray-800 mt-2 leading-relaxed"><?php echo $instructions; ?></div>
      </div>
      <?php endif; ?>

      <!-- Signature Block -->
      <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <div class="h-10 border-b border-gray-300"></div>
          <p class="text-xs text-gray-500 mt-2">Signature</p>
        </div>
        <div class="text-right">
          <p class="text-sm text-gray-700">Dr. <?php echo $doctor; ?></p>
          <p class="text-xs text-gray-400">University Medical Center</p>
        </div>
      </div>

      <div class="mt-8 flex justify-start no-print px-8">
        <a href="studentportal.php" class="text-blue-600 hover:text-blue-700 font-semibold">&larr; Back to Dashboard</a>
      </div>
    </div>
  </div>
</body>
</html>
