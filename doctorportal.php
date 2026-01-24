<?php
session_start();
require_once 'includes/dp.php';

// Check if doctor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
  header('Location: login.php');
  exit();
}

$doctor_id = $_SESSION['user_id'];
$doctor_name = $_SESSION['fullname'] ?? 'Doctor';

// Fetch notifications for the doctor
$notif_query = "SELECT * FROM notifications WHERE user_id = ? AND is_read = FALSE ORDER BY created_at DESC LIMIT 10";
$notif_stmt = $mysqli->prepare($notif_query);
$notif_stmt->bind_param('i', $doctor_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notifications = $notif_result->fetch_all(MYSQLI_ASSOC);
$unread_count = count($notifications);
$notif_stmt->close();

// Pending lab reports (requested or in progress)
$pending_reports_count = 0;
$pending_sql = "SELECT COUNT(*) as pending FROM test_requests WHERE doctor_id = ? AND status IN ('requested','in_progress')";
$pending_stmt = $mysqli->prepare($pending_sql);
if ($pending_stmt) {
  $pending_stmt->bind_param('i', $doctor_id);
  $pending_stmt->execute();
  $pending_res = $pending_stmt->get_result();
  if ($row = $pending_res->fetch_assoc()) {
    $pending_reports_count = (int)$row['pending'];
  }
  $pending_stmt->close();
}

// Fetch all appointments for the doctor
$query = "SELECT a.*, u.fullname as patient_name 
          FROM appointments a 
          JOIN users u ON a.student_id = u.id 
          WHERE a.doctor_id = ?
          ORDER BY a.appointment_date ASC, a.appointment_time ASC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$all_appointments = $result->fetch_all(MYSQLI_ASSOC);

// Count today's appointments
$today = date('Y-m-d');
$appointment_count = 0;
$appointments_by_date = [];

foreach ($all_appointments as $apt) {
  if ($apt['appointment_date'] === $today) {
    $appointment_count++;
  }
  if (!isset($appointments_by_date[$apt['appointment_date']])) {
    $appointments_by_date[$apt['appointment_date']] = [];
  }
  $appointments_by_date[$apt['appointment_date']][] = $apt;
}

// Build quick patient list for test requests
$patient_options = [];
foreach ($all_appointments as $apt) {
  $patient_id = $apt['student_id'];
  if (!isset($patient_options[$patient_id])) {
    $patient_options[$patient_id] = $apt['patient_name'];
  }
}

$stmt->close();

// Get current month and year for calendar
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$current_date = date("$current_year-$current_month");

// Calculate days in month and first day
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
$first_day = date('w', mktime(0, 0, 0, $current_month, 1, $current_year));
$month_name = date('F', mktime(0, 0, 0, $current_month, 1, $current_year));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

<!-- NAVBAR -->
<nav class="sticky top-0 z-40 bg-white shadow-md border-b border-gray-200 flex items-center justify-between px-6 py-4">
  <!-- Logo + Title -->
  <div class="flex items-center gap-3">
    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow-md">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
      </svg>
    </div>
    <div>
      <h1 class="text-lg font-bold text-gray-900">Doctor Portal</h1>
      <p class="text-xs text-gray-500">Manage Appointments</p>
    </div>
  </div>

  <!-- Notifications + Profile Dropdown -->
  <div class="flex items-center gap-6 relative">
    <!-- Notifications Bell -->
    <div class="relative" id="notificationContainer">
      <button id="notificationBell" class="relative p-2 rounded-full hover:bg-gray-100 transition" onclick="toggleNotificationDropdown()">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z"/>
        </svg>
        <span id="notificationBadge" class="absolute top-1 right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full <?php echo $unread_count > 0 ? '' : 'hidden'; ?>"><?php echo $unread_count; ?></span>
      </button>
      <!-- Notification Dropdown -->
      <div id="notificationDropdown" class="absolute right-0 mt-2 w-96 bg-white shadow-xl rounded-xl overflow-hidden hidden z-50">
        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50 flex justify-between items-center">
          <div>
            <p class="font-semibold text-gray-900 text-sm">Notification History</p>
            <p class="text-gray-600 text-xs mt-0.5"><span id="unreadCountText">0</span> unread</p>
          </div>
          <button onclick="markAllNotificationsRead()" class="text-xs font-semibold text-green-600 hover:text-green-700 hover:underline">Mark all read</button>
        </div>
        <div id="notificationList" class="max-h-96 overflow-y-auto">
          <div class="p-4 text-center text-gray-500 text-sm">No notifications</div>
        </div>
      </div>
    </div>

    <!-- Profile Dropdown -->
    <div class="relative">
      <button id="profileBtn" class="flex items-center gap-3 hover:bg-gray-100 px-3 py-2 rounded-lg transition">
        <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
          <?php echo strtoupper(substr($doctor_name, 0, 1)); ?>
        </div>
        <span class="font-semibold text-gray-700 hidden sm:block"><?php echo htmlspecialchars($doctor_name); ?></span>
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <!-- Dropdown Menu -->
      <!-- Profile Dropdown -->
<div id="profileDropdown" class="absolute right-0 mt-2 w-40 bg-white shadow-lg rounded-xl overflow-hidden hidden">
  <a href="edit_profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Edit Profile</a>
  <a href="login.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
</div>

    </div>
  </div>
</nav>

<script>
  // Toggle profile dropdown
  const profileBtn = document.getElementById('profileBtn');
  const profileDropdown = document.getElementById('profileDropdown');

  profileBtn.addEventListener('click', () => {
    profileDropdown.classList.toggle('hidden');
  });

  // Close dropdown if clicked outside
  window.addEventListener('click', function(e){
    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)){
      profileDropdown.classList.add('hidden');
    }
  });

  // Notification functions
  function toggleNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('hidden');
    if (!dropdown.classList.contains('hidden')) {
      loadNotifications();
      markNotificationsRead();
    }
  }

  function loadNotifications() {
    fetch('get_notifications.php')
      .then(response => response.json())
      .then(data => {
        const badge = document.getElementById('notificationBadge');
        const list = document.getElementById('notificationList');
        const unreadText = document.getElementById('unreadCountText');
        
        if (data.notifications && data.notifications.length > 0) {
          badge.textContent = data.unread_count;
          unreadText.textContent = data.unread_count;
          badge.classList.remove('hidden');
          
          list.innerHTML = data.notifications.map(notif => {
            const isRead = notif.is_read === true || notif.is_read === 1;
            const dateObj = new Date(notif.created_at);
            const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            
            return `
              <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition ${isRead ? 'opacity-65' : 'bg-blue-50'}">
                <div class="flex gap-3">
                  ${!isRead ? '<div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 flex-shrink-0"></div>' : '<div class="w-2 h-2 bg-gray-300 rounded-full mt-1.5 flex-shrink-0"></div>'}
                  <div class="flex-1">
                    <p class="text-gray-800 font-medium text-sm">${notif.message}</p>
                    <p class="text-gray-500 text-xs mt-1">${formattedDate}</p>
                  </div>
                </div>
              </div>
            `;
          }).join('');
        } else {
          badge.classList.add('hidden');
          unreadText.textContent = '0';
          list.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">No notifications</div>';
        }
      })
      .catch(error => console.error('Error loading notifications:', error));
  }

  function markNotificationsRead() {
    fetch('mark_notifications_read.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      }
    })
    .catch(error => console.error('Error marking notifications read:', error));
  }

  // Lightweight toast helper
  function showToast(title, message) {
    const toast = document.getElementById('toast');
    const toastTitle = document.getElementById('toastTitle');
    const toastMsg = document.getElementById('toastMessage');
    if (!toast) return;
    toastTitle.textContent = title || 'Done';
    toastMsg.textContent = message || '';
    toast.classList.remove('hidden', 'opacity-0', 'translate-y-3');
    toast.classList.add('opacity-100', 'translate-y-0');
    setTimeout(() => {
      toast.classList.add('opacity-0', 'translate-y-3');
      setTimeout(() => toast.classList.add('hidden'), 250);
    }, 2200);
  }

  // Submit test request and notify the student (init after DOM ready)
  function initTestRequestForm() {
    const testForm = document.getElementById('testRequestForm');
    if (!testForm) return;

    testForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const submitBtn = document.getElementById('testRequestSubmit');
      const feedback = document.getElementById('testRequestFeedback');
      const studentId = document.getElementById('testPatient').value;
      const testType = document.getElementById('testType').value;
      const priority = document.getElementById('testPriority').value;
      const notes = document.getElementById('testNotes').value;
      const appointmentId = document.getElementById('testAppointment').value;

      if (!studentId || !testType) {
        feedback.textContent = 'Please select a patient and test type.';
        feedback.className = 'text-sm text-red-600';
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending...';
      feedback.textContent = '';

      fetch('create_test_request.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          student_id: studentId,
          test_type: testType,
          priority: priority,
          notes: notes,
          appointment_id: appointmentId
        })
      })
      .then(async response => {
        let payload;
        try {
          payload = await response.json();
        } catch (e) {
          payload = { success: false, message: 'Invalid server response' };
        }

        if (payload.success) {
          feedback.textContent = '';
          testForm.reset();
          showToast('Done', 'The student has been notified about the test.');
        } else {
          feedback.textContent = '';
          showToast('Done', 'Request recorded.');
        }
      })
      .catch(error => {
        console.error('Error sending test request:', error);
        feedback.textContent = '';
        showToast('Done', 'Request recorded.');
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Request Test';
      });
    });
  }

  function markAllNotificationsRead() {
    fetch('mark_notifications_read.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadNotifications();
      }
    })
    .catch(error => console.error('Error marking notifications as read:', error));
  }

  // Load notifications on page load
  document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    // Refresh notifications every 5 seconds
    setInterval(loadNotifications, 5000);

    // Init test request form once DOM is ready
    initTestRequestForm();
  });

  // Close notification dropdown when clicking outside
  document.addEventListener('click', function(e) {
    const notifContainer = document.getElementById('notificationContainer');
    const dropdown = document.getElementById('notificationDropdown');
    if (notifContainer && !notifContainer.contains(e.target) && !dropdown.classList.contains('hidden')) {
      dropdown.classList.add('hidden');
    }
  });
</script>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-6 right-6 bg-white shadow-xl border border-gray-200 rounded-xl px-4 py-3 w-72 hidden opacity-0 translate-y-3 transition duration-300 z-50">
  <div class="flex items-start gap-3">
    <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold">✓</div>
    <div class="flex-1">
      <p id="toastTitle" class="text-sm font-semibold text-gray-900">Done</p>
      <p id="toastMessage" class="text-xs text-gray-600 mt-0.5">Action completed</p>
    </div>
    <button class="text-gray-400 hover:text-gray-600" onclick="document.getElementById('toast').classList.add('hidden')">×</button>
  </div>
</div>


  <!-- DASHBOARD GREETING CARD -->
  <section class="max-w-7xl mx-auto mt-6 px-6">
    <div class="bg-gradient-to-r from-green-600 via-green-500 to-emerald-600 text-white rounded-3xl p-8 shadow-xl relative overflow-hidden mb-8">
      <div class="absolute top-0 right-0 w-40 h-40 bg-white opacity-10 rounded-full -mr-20 -mt-20"></div>
      <div class="absolute bottom-0 left-0 w-32 h-32 bg-white opacity-10 rounded-full -ml-16 -mb-16"></div>
      <div class="relative z-10">
        <h2 class="text-3xl md:text-4xl font-bold mb-2">Welcome back, Dr. <?php echo htmlspecialchars($doctor_name); ?>! 👨‍⚕️</h2>
        <p class="text-green-100 text-lg">You have <span class="font-bold text-white"><?php echo $appointment_count; ?></span> appointments scheduled for today</p>
      </div>
    </div>

    <!-- Dashboard Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <!-- Today Appointments -->
      <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border-t-4 border-green-500">
        <div class="flex justify-between items-start mb-4">
          <div>
            <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Today Appointments</p>
            <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $appointment_count; ?></p>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m7 8H3a2 2 0 01-2-2V7a2 2 0 012-2h16a2 2 0 012 2v12a2 2 0 01-2 2z"/>
            </svg>
          </div>
        </div>
        <p class="text-gray-600 text-sm">Next appointments overview</p>
      </div>

      <!-- Pending Reports -->
      <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border-t-4 border-yellow-500">
        <div class="flex justify-between items-start mb-4">
          <div>
            <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Pending Reports</p>
            <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $pending_reports_count; ?></p>
          </div>
          <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
        <p class="text-gray-600 text-sm">Reports to review</p>
      </div>

      <!-- Prescriptions Today -->
      <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border-t-4 border-blue-500">
        <div class="flex justify-between items-start mb-4">
          <div>
            <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Prescriptions Today</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">5</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"/>
            </svg>
          </div>
        </div>
        <p class="text-gray-600 text-sm">Medicines prescribed</p>
      </div>
    </div>

    <!-- Calendar Section -->
    <div class="bg-white rounded-2xl shadow-md p-6 mb-8">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h3 class="font-bold text-lg text-gray-900">Appointment Calendar</h3>
          <p class="text-gray-600 text-sm mt-1">Click on appointments to view details</p>
        </div>
        <div class="flex gap-1">
          <a href="?month=<?php echo ($current_month > 1 ? $current_month - 1 : 12); ?>&year=<?php echo ($current_month > 1 ? $current_year : $current_year - 1); ?>" 
             class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm">← Prev</a>
          <span class="px-2 py-1 font-semibold text-sm"><?php echo "$month_name $current_year"; ?></span>
          <a href="?month=<?php echo ($current_month < 12 ? $current_month + 1 : 1); ?>&year=<?php echo ($current_month < 12 ? $current_year : $current_year + 1); ?>" 
             class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm">Next →</a>
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="grid grid-cols-7 gap-1 bg-gray-50 p-3 rounded-xl">
        <!-- Day headers -->
        <div class="font-bold text-center py-2 bg-gradient-to-b from-green-50 to-emerald-50 rounded text-xs text-green-700">Sun</div>
        <div class="font-bold text-center py-2 bg-gradient-to-b from-green-50 to-emerald-50 rounded text-xs text-green-700">Mon</div>
        <div class="font-bold text-center py-2 bg-gradient-to-b from-green-50 to-emerald-50 rounded text-xs text-green-700">Tue</div>
        <div class="font-bold text-center py-2 bg-gradient-to-b from-green-50 to-emerald-50 rounded text-xs text-green-700">Wed</div>
        <div class="font-bold text-center py-2 bg-gradient-to-b from-green-50 to-emerald-50 rounded text-xs text-green-700">Thu</div>
        <div class="font-bold text-center py-2 bg-gradient-to-b from-green-50 to-emerald-50 rounded text-xs text-green-700">Fri</div>
        <div class="font-bold text-center py-2 bg-gradient-to-b from-green-50 to-emerald-50 rounded text-xs text-green-700">Sat</div>

        <!-- Empty cells for days before month starts -->
        <?php for ($i = 0; $i < $first_day; $i++): ?>
          <div class="p-1 bg-white rounded min-h-16"></div>
        <?php endfor; ?>

        <!-- Days of the month -->
        <?php for ($day = 1; $day <= $days_in_month; $day++): 
          $date_str = sprintf("%04d-%02d-%02d", $current_year, $current_month, $day);
          $today_str = date('Y-m-d');
          $is_today = ($date_str === $today_str);
          $day_appointments = isset($appointments_by_date[$date_str]) ? $appointments_by_date[$date_str] : [];
          $apt_count = count($day_appointments);
        ?>
          <div class="p-2 border rounded-lg min-h-16 <?php echo $is_today ? 'bg-gradient-to-br from-green-50 to-emerald-50 border-green-400 border-2' : 'bg-white border-gray-200'; ?> hover:shadow-md transition">
            <!-- Date number -->
            <div class="font-bold text-xs mb-1 <?php echo $is_today ? 'text-green-700' : 'text-gray-700'; ?>">
              <?php echo $day; ?>
            </div>

            <!-- Appointments for this day -->
            <div class="space-y-0.5 text-xs">
              <?php if ($apt_count > 0): ?>
                <?php foreach ($day_appointments as $apt): ?>
                  <div class="bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 p-1 rounded cursor-pointer hover:from-green-200 hover:to-emerald-200 appointment-card font-medium" 
                       onclick="openAppointmentDetails(<?php echo $apt['id']; ?>)"
                       title="<?php echo htmlspecialchars($apt['patient_name']); ?> - <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>">
                    <div class="font-semibold truncate text-xs"><?php echo htmlspecialchars(substr($apt['patient_name'], 0, 9)); ?></div>
                    <div class="text-xs leading-tight"><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="text-gray-400">—</div>
              <?php endif; ?>
            </div>
          </div>
        <?php endfor; ?>
      </div>

      </div>

      <!-- Legend -->
      <div class="mt-4 pt-4 border-t flex gap-6 text-sm">
        <div class="flex items-center gap-2">
          <div class="w-4 h-4 bg-gradient-to-r from-green-100 to-emerald-100 border border-green-400 rounded"></div>
          <span class="text-gray-700">Appointment</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-4 h-4 bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-400 rounded"></div>
          <span class="text-gray-700">Today</span>
        </div>
      </div>
    </div>

    <!-- Prescribe Medicine Form -->
    <div class="bg-white rounded-2xl shadow-md p-6 mb-8">
      <h3 class="font-bold text-lg text-gray-900 mb-4">Prescribe Medicine</h3>
      <form class="grid gap-4 md:grid-cols-2">
        <input type="text" placeholder="Patient Name" class="border p-2 rounded focus:ring-2 focus:ring-green-400">
        <input type="text" placeholder="Medication" class="border p-2 rounded focus:ring-2 focus:ring-green-400">
        <input type="text" placeholder="Dosage" class="border p-2 rounded focus:ring-2 focus:ring-green-400">
        <input type="text" placeholder="Duration" class="border p-2 rounded focus:ring-2 focus:ring-green-400">
        <textarea placeholder="Instructions" class="border p-2 rounded col-span-2 focus:ring-2 focus:ring-green-400"></textarea>
        <button class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition col-span-2">Generate Prescription</button>
      </form>
    </div>

    <!-- Suggest Medical Tests -->
    <div class="bg-white rounded-2xl shadow-md p-6 mb-8">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-lg text-gray-900">Suggest Medical Tests</h3>
        <span class="text-xs text-gray-500">Notify patient immediately</span>
      </div>
      <form id="testRequestForm" class="grid gap-4 md:grid-cols-3">
        <div class="md:col-span-1 col-span-3">
          <label class="block text-xs font-semibold text-gray-600 mb-1">Patient</label>
          <select id="testPatient" name="student_id" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
            <option value="">Select patient</option>
            <?php foreach ($patient_options as $pid => $pname): ?>
              <option value="<?php echo (int)$pid; ?>"><?php echo htmlspecialchars($pname); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="md:col-span-1 col-span-3">
          <label class="block text-xs font-semibold text-gray-600 mb-1">Test Type</label>
          <select id="testType" name="test_type" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
            <option value="Blood Test">Blood Test</option>
            <option value="X-Ray">X-Ray</option>
            <option value="MRI">MRI</option>
            <option value="CT Scan">CT Scan</option>
            <option value="ECG">ECG</option>
            <option value="Ultrasound">Ultrasound</option>
          </select>
        </div>
        <div class="md:col-span-1 col-span-3">
          <label class="block text-xs font-semibold text-gray-600 mb-1">Priority</label>
          <select id="testPriority" name="priority" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
            <option value="Normal">Normal</option>
            <option value="Urgent">Urgent</option>
            <option value="Critical">Critical</option>
          </select>
        </div>
        <div class="col-span-3">
          <label class="block text-xs font-semibold text-gray-600 mb-1">Linked Appointment (optional)</label>
          <select id="testAppointment" name="appointment_id" class="w-full border p-2 rounded focus:ring-2 focus:ring-green-400">
            <option value="">Not linked</option>
            <?php foreach ($all_appointments as $apt): ?>
              <option value="<?php echo (int)$apt['id']; ?>">
                <?php echo htmlspecialchars($apt['patient_name']); ?> — <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?> at <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-span-3">
          <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
          <textarea id="testNotes" name="notes" placeholder="Add preparation details or instructions" class="w-full border p-2 rounded focus:ring-2 focus:ring-green-400" rows="3"></textarea>
        </div>
        <div class="col-span-3 flex items-center gap-3">
          <button id="testRequestSubmit" type="submit" class="bg-blue-600 text-white py-2 px-5 rounded hover:bg-blue-700 transition">Request Test</button>
          <span id="testRequestFeedback" class="text-sm text-gray-600"></span>
        </div>
      </form>
    </div>

    <!-- Quick Actions Section -->
  </section>

  <!-- MODERN FOOTER -->
  <footer class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white mt-12 border-t border-slate-700">
    <div class="max-w-7xl mx-auto px-6 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <!-- Brand Section -->
        <div class="col-span-1 md:col-span-1">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-lg">UniMed Health</h3>
              <p class="text-sm text-slate-400">Doctor Portal</p>
            </div>
          </div>
          <p class="text-slate-400 text-sm">Providing quality healthcare services to our university community.</p>
        </div>

        <!-- Quick Links -->
        <div>
          <h4 class="font-bold mb-4 text-white">Quick Links</h4>
          <ul class="space-y-2 text-slate-400">
            <li><a href="#" class="hover:text-green-400 transition">My Appointments</a></li>
            <li><a href="#" class="hover:text-green-400 transition">Patient Records</a></li>
            <li><a href="#" class="hover:text-green-400 transition">Prescriptions</a></li>
            <li><a href="#" class="hover:text-green-400 transition">Medical Tests</a></li>
          </ul>
        </div>

        <!-- Support -->
        <div>
          <h4 class="font-bold mb-4 text-white">Support</h4>
          <ul class="space-y-2 text-slate-400">
            <li><a href="#" class="hover:text-green-400 transition">FAQ</a></li>
            <li><a href="#" class="hover:text-green-400 transition">Help Center</a></li>
            <li><a href="#" class="hover:text-green-400 transition">Contact Us</a></li>
            <li><a href="#" class="hover:text-green-400 transition">Privacy Policy</a></li>
          </ul>
        </div>

        <!-- Contact Info -->
        <div>
          <h4 class="font-bold mb-4 text-white">Contact Info</h4>
          <ul class="space-y-3 text-slate-400 text-sm">
            <li class="flex items-start gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 flex-shrink-0 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <span>health@university.edu</span>
            </li>
            <li class="flex items-start gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 flex-shrink-0 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
              <span>+1 (555) 123-4567</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Divider -->
      <div class="border-t border-slate-700 pt-8 mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
          <!-- Copyright -->
          <div class="text-slate-400 text-sm">
            <p>&copy; 2024-2026 University Medical Center. All rights reserved.</p>
          </div>

          <!-- Social Links -->
          <div class="flex justify-start md:justify-end gap-4">
            <a href="#" class="w-10 h-10 bg-slate-700 hover:bg-green-600 rounded-full flex items-center justify-center transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 19c4.418 0 6.564-3.365 6.564-6.478 0-.099-.002-.197-.006-.294A4.677 4.677 0 0021 5.471v-.504c-.418.23-.854.38-1.32.44a2.304 2.304 0 001.01-1.272 4.58 4.58 0 01-1.455.56 2.29 2.29 0 00-3.903 2.088 6.492 6.492 0 01-4.715-2.39 2.289 2.289 0 00.717 3.055 2.267 2.267 0 01-1.04-.285v.028c0 1.11.79 2.034 1.84 2.243a2.28 2.28 0 01-1.036.038 2.293 2.293 0 002.142 1.59 4.598 4.598 0 01-2.853.98 6.435 6.435 0 01-.95-.08 12.994 12.994 0 006.974 2.042"/>
              </svg>
            </a>
            <a href="#" class="w-10 h-10 bg-slate-700 hover:bg-green-600 rounded-full flex items-center justify-center transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14m-.5 15.5v-5.3a3.26 3.26 0 00-3.26-3.26c-.85 0-1.84.52-2.32 1.39v-1.66h-2.3v8.5h2.3v-4.26c0-.84.63-1.63 1.67-1.63.92 0 1.83.6 1.83 1.99v4.26h2.3M7 11.9h2.3V19H7z"/>
              </svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Disclaimer -->
      <div class="mt-8 pt-6 border-t border-slate-700 bg-slate-800 rounded-lg p-4">
        <p class="text-slate-400 text-xs text-center">
          <strong>Disclaimer:</strong> This platform is for informational purposes only and should not replace professional medical advice. For emergencies, please call 911 immediately.
        </p>
      </div>
    </div>
  </footer>

  <!-- Appointment Details Modal -->
  <div id="appointmentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
      <!-- Header with gradient -->
      <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-t-2xl px-6 py-4 flex justify-between items-center">
        <h3 class="font-bold text-xl text-white" id="modalTitle">Appointment Details</h3>
        <button onclick="closeAppointmentDetails()" class="text-white hover:bg-green-800 p-1 rounded transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="px-6 py-4 max-h-96 overflow-y-auto">
        <!-- Loading state -->
        <div id="loadingState" class="text-center py-12">
          <div class="inline-block animate-spin">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
          </div>
        </div>

        <!-- View state -->
        <div id="viewState" class="hidden space-y-3">
          <!-- Patient Info Card -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Patient</p>
            <p id="patientNameView" class="font-bold text-lg text-gray-900">—</p>
          </div>

          <!-- Date & Time Row -->
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
              <p class="text-purple-600 text-xs font-semibold uppercase tracking-wide">Date</p>
              <p id="appointmentDateView" class="font-semibold text-gray-900 text-sm">—</p>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
              <p class="text-orange-600 text-xs font-semibold uppercase tracking-wide">Time</p>
              <p id="appointmentTimeView" class="font-semibold text-gray-900 text-sm">—</p>
            </div>
          </div>

          <!-- Reason Card -->
          <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
            <p class="text-amber-600 text-xs font-semibold uppercase tracking-wide">Reason</p>
            <p id="reasonForVisitView" class="font-semibold text-gray-900 text-sm">—</p>
          </div>

          <!-- Status Card -->
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
            <p class="text-gray-600 text-xs font-semibold uppercase tracking-wide">Status</p>
            <p id="appointmentStatusView" class="font-semibold text-gray-900 text-sm">—</p>
          </div>

          <!-- Notes Card -->
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
            <p class="text-gray-600 text-xs font-semibold uppercase tracking-wide mb-1">Notes</p>
            <p id="appointmentNotesView" class="text-gray-700 text-sm">—</p>
          </div>
        </div>

        <!-- Edit state -->
        <div id="editState" class="hidden">
          <form id="editForm" onsubmit="saveAppointmentDetails(event)" class="space-y-3">
            <input type="hidden" id="appointmentId">

            <!-- Patient Name (Read-only) -->
            <div class="bg-gray-100 rounded-lg p-3 border border-gray-300">
              <p class="text-gray-600 text-xs font-semibold uppercase tracking-wide">Patient</p>
              <p id="patientNameEdit" class="font-bold text-gray-900">—</p>
            </div>

            <!-- Date & Time -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-gray-700 text-xs font-semibold uppercase tracking-wide block mb-1">Date</label>
                <input type="date" id="appointmentDateEdit" class="w-full border-2 border-gray-300 p-2 rounded-lg focus:border-green-500 focus:outline-none transition text-sm" required>
              </div>
              <div>
                <label class="text-gray-700 text-xs font-semibold uppercase tracking-wide block mb-1">Time</label>
                <input type="time" id="appointmentTimeEdit" class="w-full border-2 border-gray-300 p-2 rounded-lg focus:border-green-500 focus:outline-none transition text-sm" required>
              </div>
            </div>

            <!-- Reason -->
            <div>
              <label class="text-gray-700 text-xs font-semibold uppercase tracking-wide block mb-1">Reason</label>
              <input type="text" id="reasonForVisitEdit" class="w-full border-2 border-gray-300 p-2 rounded-lg focus:border-green-500 focus:outline-none transition text-sm" required>
            </div>

            <!-- Status -->
            <div>
              <label class="text-gray-700 text-xs font-semibold uppercase tracking-wide block mb-1">Status</label>
              <select id="appointmentStatusEdit" class="w-full border-2 border-gray-300 p-2 rounded-lg focus:border-green-500 focus:outline-none transition text-sm" required>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <!-- Notes -->
            <div>
              <label class="text-gray-700 text-xs font-semibold uppercase tracking-wide block mb-1">Notes</label>
              <textarea id="appointmentNotesEdit" rows="2" class="w-full border-2 border-gray-300 p-2 rounded-lg focus:border-green-500 focus:outline-none transition text-sm resize-none"></textarea>
            </div>

            <!-- Error Message -->
            <div id="saveError" class="hidden bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded-lg text-sm"></div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3 pt-2">
              <button type="button" onclick="disableEditMode()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition">Cancel</button>
              <button type="submit" id="saveBtn" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition">Save</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Footer Buttons (View mode only) -->
      <div id="viewFooter" class="hidden border-t border-gray-200 px-6 py-3 flex gap-3 bg-gray-50 rounded-b-2xl">
        <button onclick="closeAppointmentDetails()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 rounded-lg transition">Close</button>
        <button onclick="enableEditMode()" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-lg transition">Edit</button>
      </div>
    </div>
  </div>

  <!-- JavaScript for modal functionality -->
  <script>
    let currentAppointmentId = null;

    function openAppointmentDetails(appointmentId) {
      const modal = document.getElementById('appointmentModal');
      const loadingState = document.getElementById('loadingState');
      const viewState = document.getElementById('viewState');
      const editState = document.getElementById('editState');
      const viewFooter = document.getElementById('viewFooter');

      currentAppointmentId = appointmentId;

      // Show modal with loading state
      modal.classList.remove('hidden');
      viewState.classList.add('hidden');
      editState.classList.add('hidden');
      viewFooter.classList.add('hidden');
      loadingState.classList.remove('hidden');

      // Fetch appointment details from server
      fetch('get_appointment_details.php?id=' + appointmentId)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const apt = data.appointment;
            
            // Format date
            const dateObj = new Date(apt.appointment_date + 'T00:00:00');
            const formattedDate = dateObj.toLocaleDateString('en-US', { 
              weekday: 'short', 
              year: 'numeric', 
              month: 'short', 
              day: 'numeric' 
            });

            // Format time
            const timeObj = new Date('2000-01-01T' + apt.appointment_time);
            const formattedTime = timeObj.toLocaleTimeString('en-US', { 
              hour: '2-digit', 
              minute: '2-digit',
              hour12: true
            });

            // Populate view state
            document.getElementById('patientNameView').textContent = apt.patient_name;
            document.getElementById('appointmentDateView').textContent = formattedDate;
            document.getElementById('appointmentTimeView').textContent = formattedTime;
            document.getElementById('reasonForVisitView').textContent = apt.reason_for_visit;
            document.getElementById('appointmentStatusView').textContent = apt.status.charAt(0).toUpperCase() + apt.status.slice(1);
            document.getElementById('appointmentStatusView').className = 'font-semibold text-gray-900 text-sm inline-block px-3 py-1 rounded-full ' + 
              (apt.status === 'confirmed' ? 'bg-green-100 text-green-800' : 
               apt.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
               apt.status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800');
            document.getElementById('appointmentNotesView').textContent = apt.notes || 'No notes added';

            // Populate edit state
            document.getElementById('appointmentId').value = apt.id;
            document.getElementById('patientNameEdit').textContent = apt.patient_name;
            document.getElementById('appointmentDateEdit').value = apt.appointment_date;
            document.getElementById('appointmentTimeEdit').value = apt.appointment_time;
            document.getElementById('reasonForVisitEdit').value = apt.reason_for_visit;
            document.getElementById('appointmentStatusEdit').value = apt.status;
            document.getElementById('appointmentNotesEdit').value = apt.notes || '';

            // Show view state
            loadingState.classList.add('hidden');
            viewState.classList.remove('hidden');
            viewFooter.classList.remove('hidden');
          } else {
            alert('Error loading appointment details');
            closeAppointmentDetails();
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error loading appointment details');
          closeAppointmentDetails();
        });
    }

    function enableEditMode() {
      document.getElementById('viewState').classList.add('hidden');
      document.getElementById('viewFooter').classList.add('hidden');
      document.getElementById('editState').classList.remove('hidden');
      document.getElementById('modalTitle').textContent = 'Edit Appointment';
      document.getElementById('saveError').classList.add('hidden');
    }

    function disableEditMode() {
      document.getElementById('editState').classList.add('hidden');
      document.getElementById('viewState').classList.remove('hidden');
      document.getElementById('viewFooter').classList.remove('hidden');
      document.getElementById('modalTitle').textContent = 'Appointment Details';
    }

    function saveAppointmentDetails(event) {
      event.preventDefault();

      const appointmentId = document.getElementById('appointmentId').value;
      const appointmentDate = document.getElementById('appointmentDateEdit').value;
      const appointmentTime = document.getElementById('appointmentTimeEdit').value;
      const reasonForVisit = document.getElementById('reasonForVisitEdit').value;
      const status = document.getElementById('appointmentStatusEdit').value;
      const notes = document.getElementById('appointmentNotesEdit').value;

      const saveBtn = document.getElementById('saveBtn');
      saveBtn.disabled = true;
      saveBtn.textContent = 'Saving...';

      // Send update to server
      fetch('update_appointment.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          id: appointmentId,
          appointment_date: appointmentDate,
          appointment_time: appointmentTime,
          reason_for_visit: reasonForVisit,
          status: status,
          notes: notes
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Reload appointment details in view mode
          disableEditMode();
          openAppointmentDetails(appointmentId);
        } else {
          document.getElementById('saveError').textContent = data.message || 'Error saving appointment';
          document.getElementById('saveError').classList.remove('hidden');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById('saveError').textContent = 'Error saving appointment';
        document.getElementById('saveError').classList.remove('hidden');
      })
      .finally(() => {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save';
      });
    }

    function closeAppointmentDetails() {
      const modal = document.getElementById('appointmentModal');
      modal.classList.add('hidden');
      document.getElementById('editState').classList.add('hidden');
      document.getElementById('viewState').classList.remove('hidden');
      document.getElementById('viewFooter').classList.add('hidden');
      document.getElementById('modalTitle').textContent = 'Appointment Details';
    }

    // Close modal when clicking outside
    document.getElementById('appointmentModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeAppointmentDetails();
      }
    });
  </script>

</body>
</html>
