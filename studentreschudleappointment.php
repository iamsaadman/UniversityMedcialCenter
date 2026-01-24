<?php
session_start();
require_once 'includes/dp.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header('Location: login.php');
  exit();
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['fullname'] ?? 'Student';

$appointments = [];
$appt_stmt = $mysqli->prepare(
  "SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.reason_for_visit, a.notes, d.fullname AS doctor_name, d.id AS doctor_id
   FROM appointments a
   JOIN users d ON d.id = a.doctor_id
   WHERE a.student_id = ?
   ORDER BY a.appointment_date ASC, a.appointment_time ASC"
);
if ($appt_stmt) {
  $appt_stmt->bind_param('i', $student_id);
  $appt_stmt->execute();
  $res = $appt_stmt->get_result();
  $appointments = $res->fetch_all(MYSQLI_ASSOC);
  $appt_stmt->close();
}

$current_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($current_id === 0 && !empty($appointments)) {
  $current_id = (int)$appointments[0]['id'];
}

$current_appt = null;
if ($current_id > 0) {
  $detail_stmt = $mysqli->prepare(
    "SELECT a.*, d.fullname AS doctor_name, d.id AS doctor_id
     FROM appointments a
     JOIN users d ON d.id = a.doctor_id
     WHERE a.id = ? AND a.student_id = ?"
  );
  if ($detail_stmt) {
    $detail_stmt->bind_param('ii', $current_id, $student_id);
    $detail_stmt->execute();
    $detail_res = $detail_stmt->get_result();
    if ($detail_res->num_rows > 0) {
      $current_appt = $detail_res->fetch_assoc();
    }
    $detail_stmt->close();
  }
}

$flash_success = isset($_GET['success']) ? ($_GET['success'] === '1') : false;
$flash_error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reschedule Appointment | Student Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">

<nav class="bg-white shadow px-6 py-4 flex justify-between items-center sticky top-0 z-30">
  <div class="flex items-center gap-3">
    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
      </svg>
    </div>
    <div>
      <h1 class="font-bold text-lg text-gray-800">Reschedule Appointment</h1>
      <p class="text-xs text-gray-500">Manage your bookings</p>
    </div>
  </div>
  <a href="studentportal.php" class="text-blue-600 font-semibold hover:underline">← Back to Dashboard</a>
</nav>

<div class="max-w-5xl mx-auto p-6 space-y-6">
  <?php if ($flash_success): ?>
    <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3">Appointment updated successfully.</div>
  <?php endif; ?>
  <?php if ($flash_error): ?>
    <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3"><?php echo htmlspecialchars($flash_error); ?></div>
  <?php endif; ?>

  <div class="bg-white rounded-2xl shadow p-6">
    <div class="flex items-center gap-2 mb-4">
      <div class="w-1 h-6 bg-blue-500 rounded"></div>
      <h2 class="text-xl font-bold text-gray-900">Select Appointment</h2>
    </div>
    <?php if (empty($appointments)): ?>
      <p class="text-sm text-gray-600">You have no appointments to reschedule.</p>
    <?php else: ?>
      <form method="get" class="flex gap-3 items-center">
        <label class="text-sm text-gray-700">Choose:</label>
        <select name="id" class="border rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
          <?php foreach ($appointments as $apt):
            $label = sprintf('%s with Dr. %s at %s', date('M d, Y', strtotime($apt['appointment_date'])), $apt['doctor_name'], date('g:i A', strtotime($apt['appointment_time'])));
          ?>
            <option value="<?php echo (int)$apt['id']; ?>" <?php echo $current_id == $apt['id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($label); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    <?php endif; ?>
  </div>

  <?php if ($current_appt): ?>
  <div class="bg-white rounded-2xl shadow p-6">
    <div class="flex items-center gap-2 mb-4">
      <div class="w-1 h-6 bg-green-500 rounded"></div>
      <h2 class="text-xl font-bold text-gray-900">Update Appointment</h2>
    </div>
    <p class="text-sm text-gray-600 mb-4">Doctor: <span class="font-semibold text-gray-900">Dr. <?php echo htmlspecialchars($current_appt['doctor_name']); ?></span></p>

    <form method="post" action="student_update_appointment.php" class="space-y-4">
      <input type="hidden" name="id" value="<?php echo (int)$current_appt['id']; ?>">

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Date</label>
        <input type="date" name="appointment_date" value="<?php echo htmlspecialchars($current_appt['appointment_date']); ?>" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Time</label>
        <input type="time" name="appointment_time" value="<?php echo htmlspecialchars(substr($current_appt['appointment_time'], 0, 5)); ?>" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Reason for Visit</label>
        <input type="text" name="reason_for_visit" value="<?php echo htmlspecialchars($current_appt['reason_for_visit']); ?>" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Notes (optional)</label>
        <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($current_appt['notes'] ?? ''); ?></textarea>
      </div>

      <p class="text-xs text-gray-500">Status will be set to Pending after reschedule. Your doctor will be notified.</p>

      <div class="flex gap-3">
        <a href="studentportal.php" class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700">Save Changes</button>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>

</body>
</html>
