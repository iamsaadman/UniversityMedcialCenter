<?php
session_start();
require_once 'includes/dp.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header('Location: login.php');
  exit();
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['fullname'] ?? 'Student';

$notifications = [];
$unread_count = 0; // will be refreshed via AJAX but hydrated for initial render

// Doctor test requests count (all statuses)
$lab_reports_count = 0;
$lab_sql = "SELECT COUNT(*) as total FROM test_requests WHERE student_id = ?";
$lab_stmt = $mysqli->prepare($lab_sql);
if ($lab_stmt) {
  $lab_stmt->bind_param('i', $student_id);
  $lab_stmt->execute();
  $lab_res = $lab_stmt->get_result();
  if ($row = $lab_res->fetch_assoc()) {
    $lab_reports_count = (int)$row['total'];
  }
  $lab_stmt->close();
}

// Prefetch latest notifications for server render
$notif_stmt = $mysqli->prepare("SELECT id, message, type, reference_id, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
if ($notif_stmt) {
  $notif_stmt->bind_param('i', $student_id);
  $notif_stmt->execute();
  $notif_res = $notif_stmt->get_result();
  $notifications = $notif_res->fetch_all(MYSQLI_ASSOC);
  $unread_count = array_reduce($notifications, fn($c, $n) => $c + ($n['is_read'] ? 0 : 1), 0);
  $notif_stmt->close();
}

// Fetch recent prescriptions for this student
$prescriptions = [];
$prescription_count = 0;
$presc_sql = "SELECT p.id, p.title, p.created_at, p.follow_up_date, d.fullname AS doctor_name
              FROM prescriptions p
              JOIN users d ON p.doctor_id = d.id
              WHERE p.student_id = ?
              ORDER BY p.created_at DESC
              LIMIT 5";
$presc_stmt = $mysqli->prepare($presc_sql);
if ($presc_stmt) {
  $presc_stmt->bind_param('i', $student_id);
  $presc_stmt->execute();
  $presc_res = $presc_stmt->get_result();
  $prescriptions = $presc_res->fetch_all(MYSQLI_ASSOC);
  $prescription_count = count($prescriptions);
  $presc_stmt->close();
}
// Latest prescription title for dashboard card
$latest_rx_title = $prescription_count > 0 ? ($prescriptions[0]['title'] ?? '') : '';

// Fetch ALL appointments for calendar view
$all_appointments = [];
$appointments_by_date = [];
$all_appt_sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, d.fullname AS doctor_name
                 FROM appointments a
                 JOIN users d ON a.doctor_id = d.id
                 WHERE a.student_id = ?
                 ORDER BY a.appointment_date ASC, a.appointment_time ASC";
$all_appt_stmt = $mysqli->prepare($all_appt_sql);
if ($all_appt_stmt) {
  $all_appt_stmt->bind_param('i', $student_id);
  $all_appt_stmt->execute();
  $all_appt_res = $all_appt_stmt->get_result();
  $all_appointments = $all_appt_res->fetch_all(MYSQLI_ASSOC);
  
  // Organize appointments by date for calendar
  foreach ($all_appointments as $apt) {
    if (!isset($appointments_by_date[$apt['appointment_date']])) {
      $appointments_by_date[$apt['appointment_date']] = [];
    }
    $appointments_by_date[$apt['appointment_date']][] = $apt;
  }
  
  $all_appt_stmt->close();
}

// Fetch upcoming appointments for list display (next 5)
$upcoming_appointments = [];
$upcoming_count = 0;
$appt_sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, d.fullname AS doctor_name
             FROM appointments a
             JOIN users d ON a.doctor_id = d.id
             WHERE a.student_id = ? AND a.appointment_date >= CURDATE()
             ORDER BY a.appointment_date ASC, a.appointment_time ASC
             LIMIT 5";
$appt_stmt = $mysqli->prepare($appt_sql);
if ($appt_stmt) {
  $appt_stmt->bind_param('i', $student_id);
  $appt_stmt->execute();
  $appt_res = $appt_stmt->get_result();
  $upcoming_appointments = $appt_res->fetch_all(MYSQLI_ASSOC);
  $upcoming_count = count($upcoming_appointments);
  $appt_stmt->close();
}

// Get current month and year for calendar
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Calculate days in month and first day
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
$first_day = date('w', mktime(0, 0, 0, $current_month, 1, $current_year));
$month_name = date('F', mktime(0, 0, 0, $current_month, 1, $current_year));

// Fetch prescriptions with follow-up dates for calendar
$prescriptions_by_date = [];
foreach ($prescriptions as $rx) {
  if (!empty($rx['follow_up_date'])) {
    $follow_date = $rx['follow_up_date'];
    if (!isset($prescriptions_by_date[$follow_date])) {
      $prescriptions_by_date[$follow_date] = [];
    }
    $prescriptions_by_date[$follow_date][] = $rx;
  }
}

// Health tips array with shuffled selection
$health_tips = [
  "Stay hydrated! Drinking 8 glasses of water daily helps maintain good health and energy levels.",
  "Get 7-9 hours of quality sleep each night. Good sleep is essential for physical and mental health.",
  "Exercise regularly! Aim for at least 30 minutes of moderate physical activity 5 days a week.",
  "Eat a balanced diet rich in fruits, vegetables, whole grains, and lean proteins.",
  "Wash your hands frequently with soap and water for at least 20 seconds to prevent illness.",
  "Take regular breaks from screens. Follow the 20-20-20 rule: every 20 minutes, look 20 feet away for 20 seconds.",
  "Practice stress management through meditation, deep breathing, or yoga.",
  "Limit processed foods and added sugars. Choose whole, natural foods whenever possible.",
  "Stay socially connected. Strong relationships boost both mental and physical health.",
  "Don't skip breakfast! It jumpstarts your metabolism and provides energy for the day.",
  "Protect your skin from the sun. Use sunscreen with SPF 30+ and wear protective clothing.",
  "Schedule regular check-ups with your healthcare provider for preventive care.",
  "Limit alcohol consumption and avoid smoking to reduce health risks.",
  "Stay active throughout the day. Take the stairs, walk during breaks, and avoid prolonged sitting.",
  "Practice good posture to prevent back and neck pain, especially when studying or working.",
  "Include probiotics in your diet through yogurt or fermented foods for better gut health.",
  "Stay up-to-date with vaccinations to protect yourself and others from preventable diseases.",
  "Listen to your body. Rest when you're tired and seek medical help when something feels wrong.",
  "Maintain a healthy weight through balanced nutrition and regular physical activity.",
  "Practice mindfulness and gratitude daily to improve mental wellbeing and reduce stress."
];
$random_health_tip = $health_tips[array_rand($health_tips)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard | Health Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Leaflet (OpenStreetMap) -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    #hospitalMap {
      width: 100%;
      height: 400px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      display: block;
    }
  </style>
</head>
<body class="bg-gray-50">

<!-- NAVBAR (Improved Design) -->
<nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-40">
  <!-- Logo + Portal Name -->
  <div class="flex items-center gap-3">
    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
      </svg>
    </div>
    <div>
      <h1 class="font-bold text-lg text-gray-800">Student Portal</h1>
      <p class="text-xs text-gray-500">Health Management</p>
    </div>
  </div>

  <!-- Right side: Notifications + Profile Dropdown -->
  <div class="flex items-center gap-6">
    <!-- Notifications Bell -->
    <div class="relative">
      <button id="notificationBtn" class="relative text-gray-600 hover:text-blue-600 transition p-2 hover:bg-gray-100 rounded-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z"/>
        </svg>
        <span id="notificationBadge" class="absolute top-1 right-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full <?php echo $unread_count > 0 ? '' : 'hidden'; ?>">
          <?php echo $unread_count; ?>
        </span>
      </button>

      <!-- Notifications Dropdown -->
      <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-96 bg-white border border-gray-200 rounded-xl shadow-xl z-50 max-h-96 overflow-y-auto">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-3 font-semibold rounded-t-xl flex justify-between items-center sticky top-0">
          <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z"/>
            </svg>
            <span>Prescriptions</span>
          </div>
          <button onclick="markAllAsRead()" class="text-xs bg-white/10 hover:bg-white/20 px-2 py-1 rounded transition border border-white/20">Mark Read</button>
        </div>
        <div id="notificationList" class="divide-y bg-white">
          <div class="px-4 py-4 text-center text-gray-500 text-sm">Loading...</div>
        </div>
      </div>
    </div>

    <!-- Profile Dropdown -->
    <div class="relative">
      <button id="profileBtn" class="flex items-center gap-2 text-gray-700 font-medium hover:text-blue-600 transition px-3 py-2 hover:bg-gray-100 rounded-lg">
        <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
          <?php echo strtoupper(substr($student_name, 0, 1)); ?>
        </div>
        <span class="hidden md:inline"><?php echo htmlspecialchars($student_name); ?></span>
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <!-- Dropdown -->
      <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg hidden z-50">
        <a href="profile.php" class="block px-4 py-2 hover:bg-blue-100 text-gray-700 hover:text-blue-700 transition">Edit Profile</a>
        <a href="login.php" class="block px-4 py-2 text-red-600 hover:bg-red-100 transition">Logout</a>
      </div>
    </div>
  </div>
</nav>

<script>
  const notificationBtn = document.getElementById('notificationBtn');
  const notificationDropdown = document.getElementById('notificationDropdown');
  const profileBtn = document.getElementById('profileBtn');
  const profileDropdown = document.getElementById('profileDropdown');

  profileBtn.addEventListener('click', () => {
    profileDropdown.classList.toggle('hidden');
    notificationDropdown.classList.add('hidden');
  });

  window.addEventListener('click', function(e) {
    if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
      notificationDropdown.classList.add('hidden');
    }
    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
      profileDropdown.classList.add('hidden');
    }
  });

  function markAllAsRead() {
    fetch('mark_notifications_read.php', {
      method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadNotifications();
      }
    });
  }

  function renderNotifications(data) {
    const list = document.getElementById('notificationList');
    const badge = document.getElementById('notificationBadge');

    if (!data || !data.notifications || data.notifications.length === 0) {
      if (badge) badge.classList.add('hidden');
      list.innerHTML = '<div class="px-4 py-8 text-center text-gray-500 text-sm">No notifications</div>';
      return;
    }

    if (badge) {
      badge.textContent = data.unread_count;
      if (data.unread_count > 0) {
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    }

    list.innerHTML = data.notifications.map(notif => {
      const isRead = notif.is_read === true || notif.is_read === 1;
      const dateObj = new Date(notif.created_at);
      const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
      return `
        <div class="px-4 py-3 hover:bg-blue-50 transition ${isRead ? 'opacity-70' : 'bg-blue-50'} cursor-pointer notification-item" 
             data-type="${notif.type}" 
             data-ref="${notif.reference_id}" 
             data-message="${notif.message.replace(/"/g, '&quot;')}" 
             data-created="${notif.created_at}">
          <div class="flex items-start gap-3">
            ${!isRead ? '<div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 flex-shrink-0"></div>' : '<div class="w-2 h-2 bg-gray-300 rounded-full mt-1.5 flex-shrink-0"></div>'}
            <div class="flex-1">
              <p class="text-gray-800 font-medium text-sm">${notif.message}</p>
              <p class="text-gray-500 text-xs mt-1">${formattedDate}</p>
            </div>
          </div>
        </div>
      `;
    }).join('');

    attachNotificationClicks();
  }

  function loadNotifications() {
    fetch('get_notifications.php')
      .then(res => res.json())
      .then(data => renderNotifications(data))
      .catch(() => {
        const list = document.getElementById('notificationList');
        list.innerHTML = '<div class="px-4 py-4 text-center text-gray-500 text-sm">Unable to load notifications</div>';
      });
  }

  // Load notifications when dropdown is opened
  notificationBtn.addEventListener('click', () => {
    notificationDropdown.classList.toggle('hidden');
    profileDropdown.classList.add('hidden');
    if (!notificationDropdown.classList.contains('hidden')) {
      loadNotifications();
      markAllAsRead();
    }
  });

  function attachNotificationClicks() {
    const items = document.querySelectorAll('.notification-item');
    items.forEach(item => {
      item.addEventListener('click', () => {
        openNotificationDetail(
          item.dataset.type,
          item.dataset.ref,
          item.dataset.message,
          item.dataset.created
        );
      });
    });
  }

  function openNotificationDetail(type, refId, message, createdAt) {
    // For prescriptions, navigate to the details page
    if (type === 'prescription' && refId) {
      window.location.href = 'download_prescription.php?id=' + encodeURIComponent(refId);
      return;
    }

    const modal = document.getElementById('notificationModal');
    const body = document.getElementById('notifModalBody');
    const title = document.getElementById('notifModalTitle');

    modal.classList.remove('hidden');
    body.innerHTML = '<p class="text-sm text-gray-500">Loading details...</p>';
    title.textContent = 'Notification';

    if (type === 'test_request' && refId) {
      fetch(`get_test_request.php?id=${refId}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const d = data.data;
            title.textContent = 'Medical Test Recommendation';
            const formattedDate = new Date(d.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            body.innerHTML = `
              <div class="space-y-2">
                <p class="text-sm text-gray-600">Recommended by <span class="font-semibold text-gray-800">${d.doctor_name}</span></p>
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3">
                  <p class="text-gray-900 font-semibold">${d.test_type}</p>
                  <p class="text-xs text-blue-700 font-semibold mt-1">Priority: ${d.priority}</p>
                </div>
                <p class="text-gray-700 text-sm"><span class="font-semibold">Patient:</span> ${d.student_name}</p>
                ${d.appointment_date ? `<p class="text-gray-700 text-sm"><span class="font-semibold">Linked Appointment:</span> ${d.appointment_date} at ${d.appointment_time}</p>` : ''}
                <p class="text-gray-500 text-xs">Requested on ${formattedDate}</p>
                ${d.notes ? `<div class="mt-3"><p class="text-sm font-semibold text-gray-800">Notes</p><p class="text-gray-700 text-sm whitespace-pre-line">${d.notes}</p></div>` : ''}
              </div>
            `;
          } else {
            body.innerHTML = `<p class="text-sm text-gray-700">${message || 'No additional details available.'}</p>`;
          }
        })
        .catch(() => {
          body.innerHTML = `<p class="text-sm text-gray-700">${message || 'No additional details available.'}</p>`;
        });
    } else {
      const formattedDate = createdAt ? new Date(createdAt).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
      body.innerHTML = `
        <p class="text-gray-800 font-medium">${message}</p>
        ${formattedDate ? `<p class="text-gray-500 text-sm mt-2">${formattedDate}</p>` : ''}
      `;
    }
  }

  function closeNotificationModal() {
    const modal = document.getElementById('notificationModal');
    modal.classList.add('hidden');
  }

  function toggleFAQ(index) {
    const answer = document.getElementById(`faq-answer-${index}`);
    const icon = document.getElementById(`faq-icon-${index}`);
    
    if (answer.classList.contains('hidden')) {
      answer.classList.remove('hidden');
      icon.style.transform = 'rotate(180deg)';
    } else {
      answer.classList.add('hidden');
      icon.style.transform = 'rotate(0deg)';
    }
  }

  function openStudentAppointmentDetails(appointmentId) {
    const modal = document.getElementById('notificationModal');
    const body = document.getElementById('notifModalBody');
    const title = document.getElementById('notifModalTitle');

    modal.classList.remove('hidden');
    body.innerHTML = '<p class="text-sm text-gray-500">Loading appointment details...</p>';
    title.textContent = 'Appointment Details';

    fetch(`get_appointment_details.php?id=${appointmentId}`)
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const apt = data.appointment;
          const formattedDate = new Date(apt.appointment_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
          const formattedTime = new Date('2000-01-01 ' + apt.appointment_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
          
          let statusBadge = '';
          if (apt.status === 'completed') {
            statusBadge = '<span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">Completed</span>';
          } else if (apt.status === 'cancelled') {
            statusBadge = '<span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">Cancelled</span>';
          } else if (apt.status === 'confirmed') {
            statusBadge = '<span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">Confirmed</span>';
          } else if (apt.status === 'pending') {
            statusBadge = '<span class="inline-block bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-sm font-semibold">Pending</span>';
          }

          body.innerHTML = `
            <div class="space-y-4">
              <div class="flex justify-between items-start">
                <div>
                  <p class="text-sm text-gray-600">Doctor</p>
                  <p class="font-semibold text-gray-900">Dr. ${apt.doctor_name || 'N/A'}</p>
                </div>
                <div>
                  ${statusBadge}
                </div>
              </div>
              
              <div>
                <p class="text-sm text-gray-600">Date & Time</p>
                <p class="font-semibold text-gray-900">${formattedDate}</p>
                <p class="text-gray-700">${formattedTime}</p>
              </div>
              
              ${apt.reason ? `
              <div>
                <p class="text-sm text-gray-600">Reason for Visit</p>
                <p class="text-gray-900">${apt.reason}</p>
              </div>
              ` : ''}
              
              ${apt.notes ? `
              <div>
                <p class="text-sm text-gray-600">Notes</p>
                <p class="text-gray-700 text-sm">${apt.notes}</p>
              </div>
              ` : ''}
              
              ${apt.status === 'pending' || apt.status === 'confirmed' ? `
              <div class="pt-3 border-t">
                <a href="studentreschudleappointment.php?id=${apt.id}" 
                   class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                  Reschedule Appointment
                </a>
              </div>
              ` : ''}
            </div>
          `;
        } else {
          body.innerHTML = '<p class="text-sm text-red-600">Unable to load appointment details.</p>';
        }
      })
      .catch(() => {
        body.innerHTML = '<p class="text-sm text-red-600">Error loading appointment details.</p>';
      });
  }

  function openMentalHealthModal(type) {
    const modal = document.getElementById('mentalHealthModal');
    const content = document.getElementById('mentalHealthModalContent');
    
    modal.classList.remove('hidden');
    
    const contentMap = {
      counseling: {
        title: 'Counseling Services',
        icon: '🧠',
        color: 'purple',
        content: `
          <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6">
            <div class="flex items-center gap-3 mb-2">
              <div class="text-4xl">🧠</div>
              <h3 class="text-2xl font-bold">Counseling Services</h3>
            </div>
            <p class="text-purple-100">Professional, confidential support for your mental wellbeing</p>
          </div>
          <div class="p-6 space-y-4">
            <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
              <h4 class="font-bold text-purple-900 mb-2">Available Services</h4>
              <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start gap-2">
                  <span class="text-purple-600">✓</span>
                  <span>Individual counseling sessions (50 minutes)</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-purple-600">✓</span>
                  <span>Group therapy and support groups</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-purple-600">✓</span>
                  <span>Crisis intervention and emergency support</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-purple-600">✓</span>
                  <span>Virtual teletherapy options available</span>
                </li>
              </ul>
            </div>
            
            <div class="grid md:grid-cols-2 gap-4">
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">📅 How to Book</h4>
                <p class="text-sm text-gray-600 mb-3">Schedule an appointment with one of our licensed counselors</p>
                <a href="studentbooknewappointment.php" class="block w-full bg-purple-600 text-white text-center py-2 rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                  Book Appointment
                </a>
              </div>
              
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">📞 Contact</h4>
                <p class="text-sm text-gray-600 mb-1">Mental Health Center</p>
                <p class="text-sm font-semibold text-purple-600">Hours: Mon-Fri 8AM-6PM</p>
                <p class="text-sm text-gray-600 mt-2">Email: counseling@university.edu</p>
              </div>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg">
              <p class="text-sm text-blue-900">
                <strong>Confidential & Free:</strong> All counseling services are confidential and provided at no cost to students.
              </p>
            </div>
          </div>
        `
      },
      crisis: {
        title: 'Crisis Support',
        icon: '🆘',
        color: 'red',
        content: `
          <div class="bg-gradient-to-br from-red-500 to-red-600 text-white p-6">
            <div class="flex items-center gap-3 mb-2">
              <div class="text-4xl">🆘</div>
              <h3 class="text-2xl font-bold">Crisis Support</h3>
            </div>
            <p class="text-red-100">Immediate help is available 24/7</p>
          </div>
          <div class="p-6 space-y-4">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
              <h4 class="font-bold text-red-900 mb-2">🚨 Emergency Hotlines</h4>
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="font-semibold text-gray-900">National Suicide Prevention</p>
                    <p class="text-sm text-gray-600">24/7 Support Line</p>
                  </div>
                  <a href="tel:988" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition font-bold text-xl">
                    988
                  </a>
                </div>
                
                <div class="flex justify-between items-center">
                  <div>
                    <p class="font-semibold text-gray-900">Crisis Text Line</p>
                    <p class="text-sm text-gray-600">Text support available</p>
                  </div>
                  <a href="sms:741741?body=HOME" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-semibold">
                    Text HOME to 741741
                  </a>
                </div>
                
                <div class="flex justify-between items-center">
                  <div>
                    <p class="font-semibold text-gray-900">Campus Security</p>
                    <p class="text-sm text-gray-600">On-campus emergency</p>
                  </div>
                  <a href="tel:911" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition font-bold text-xl">
                    911
                  </a>
                </div>
              </div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-4">
              <div class="border border-gray-200 rounded-lg p-4 bg-gradient-to-br from-blue-50 to-indigo-50">
                <h4 class="font-semibold text-gray-900 mb-2">🏥 Campus Resources</h4>
                <ul class="space-y-1 text-sm text-gray-700">
                  <li>• Student Health Center</li>
                  <li>• After-hours nurse line</li>
                  <li>• Walk-in crisis counseling</li>
                  <li>• Peer support groups</li>
                </ul>
              </div>
              
              <div class="border border-gray-200 rounded-lg p-4 bg-gradient-to-br from-purple-50 to-pink-50">
                <h4 class="font-semibold text-gray-900 mb-2">🤝 Support Networks</h4>
                <ul class="space-y-1 text-sm text-gray-700">
                  <li>• Student support groups</li>
                  <li>• Resident advisors (RAs)</li>
                  <li>• Dean of Students office</li>
                  <li>• Campus ministry</li>
                </ul>
              </div>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
              <p class="text-sm text-yellow-900">
                <strong>⚠️ If you're in immediate danger:</strong> Call 911 or go to your nearest emergency room. Help is available right now.
              </p>
            </div>
          </div>
        `
      },
      wellness: {
        title: 'Wellness Resources',
        icon: '🧘',
        color: 'green',
        content: `
          <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white p-6">
            <div class="flex items-center gap-3 mb-2">
              <div class="text-4xl">🧘</div>
              <h3 class="text-2xl font-bold">Wellness Resources</h3>
            </div>
            <p class="text-green-100">Self-care tools and practices for better mental health</p>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
              <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg">
                <div class="text-3xl mb-2">😌</div>
                <h4 class="font-bold text-gray-900 mb-2">Stress Management</h4>
                <ul class="space-y-1 text-sm text-gray-700">
                  <li>• Deep breathing exercises</li>
                  <li>• Guided meditation (10-20 min)</li>
                  <li>• Progressive muscle relaxation</li>
                  <li>• Time management techniques</li>
                </ul>
              </div>
              
              <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg">
                <div class="text-3xl mb-2">💪</div>
                <h4 class="font-bold text-gray-900 mb-2">Physical Wellness</h4>
                <ul class="space-y-1 text-sm text-gray-700">
                  <li>• Regular exercise (30 min/day)</li>
                  <li>• Healthy sleep habits (7-9 hrs)</li>
                  <li>• Balanced nutrition</li>
                  <li>• Stay hydrated</li>
                </ul>
              </div>
              
              <div class="bg-gradient-to-br from-pink-50 to-pink-100 p-4 rounded-lg">
                <div class="text-3xl mb-2">🤝</div>
                <h4 class="font-bold text-gray-900 mb-2">Social Connection</h4>
                <ul class="space-y-1 text-sm text-gray-700">
                  <li>• Join student organizations</li>
                  <li>• Attend campus events</li>
                  <li>• Build support networks</li>
                  <li>• Volunteer opportunities</li>
                </ul>
              </div>
              
              <div class="bg-gradient-to-br from-yellow-50 to-amber-100 p-4 rounded-lg">
                <div class="text-3xl mb-2">📚</div>
                <h4 class="font-bold text-gray-900 mb-2">Academic Balance</h4>
                <ul class="space-y-1 text-sm text-gray-700">
                  <li>• Set realistic goals</li>
                  <li>• Take regular breaks</li>
                  <li>• Seek tutoring when needed</li>
                  <li>• Practice self-compassion</li>
                </ul>
              </div>
            </div>
            
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
              <h4 class="font-bold text-green-900 mb-2">📱 Wellness Apps</h4>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="bg-white p-2 rounded">
                  <p class="font-semibold">Headspace</p>
                  <p class="text-xs text-gray-600">Meditation & Sleep</p>
                </div>
                <div class="bg-white p-2 rounded">
                  <p class="font-semibold">Calm</p>
                  <p class="text-xs text-gray-600">Relaxation & Mindfulness</p>
                </div>
                <div class="bg-white p-2 rounded">
                  <p class="font-semibold">Sanvello</p>
                  <p class="text-xs text-gray-600">Mood Tracking</p>
                </div>
                <div class="bg-white p-2 rounded">
                  <p class="font-semibold">Insight Timer</p>
                  <p class="text-xs text-gray-600">Free Meditation</p>
                </div>
              </div>
            </div>
            
            <a href="studentmentalhealth.php" class="block w-full bg-green-600 text-white text-center py-3 rounded-lg hover:bg-green-700 transition font-semibold">
              Explore All Wellness Resources
            </a>
          </div>
        `
      },
      assessment: {
        title: 'Mental Health Check-In',
        icon: '📋',
        color: 'indigo',
        content: `
          <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-6">
            <div class="flex items-center gap-3 mb-2">
              <div class="text-4xl">📋</div>
              <h3 class="text-2xl font-bold">Mental Health Check-In</h3>
            </div>
            <p class="text-indigo-100">Take a moment to assess your wellbeing</p>
          </div>
          <div class="p-6 space-y-4">
            <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded">
              <h4 class="font-bold text-indigo-900 mb-2">Quick Self-Assessment</h4>
              <p class="text-sm text-gray-700 mb-3">
                Rate how you've been feeling over the past week on a scale of 1-5:
              </p>
              
              <div class="space-y-3">
                <div>
                  <p class="text-sm font-semibold text-gray-800 mb-2">1. Overall mood and emotional state</p>
                  <div class="flex gap-2">
                    <button class="flex-1 bg-red-100 hover:bg-red-200 text-red-800 py-2 rounded transition text-sm">1 Poor</button>
                    <button class="flex-1 bg-orange-100 hover:bg-orange-200 text-orange-800 py-2 rounded transition text-sm">2</button>
                    <button class="flex-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 py-2 rounded transition text-sm">3 OK</button>
                    <button class="flex-1 bg-lime-100 hover:bg-lime-200 text-lime-800 py-2 rounded transition text-sm">4</button>
                    <button class="flex-1 bg-green-100 hover:bg-green-200 text-green-800 py-2 rounded transition text-sm">5 Great</button>
                  </div>
                </div>
                
                <div>
                  <p class="text-sm font-semibold text-gray-800 mb-2">2. Sleep quality</p>
                  <div class="flex gap-2">
                    <button class="flex-1 bg-red-100 hover:bg-red-200 text-red-800 py-2 rounded transition text-sm">1</button>
                    <button class="flex-1 bg-orange-100 hover:bg-orange-200 text-orange-800 py-2 rounded transition text-sm">2</button>
                    <button class="flex-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 py-2 rounded transition text-sm">3</button>
                    <button class="flex-1 bg-lime-100 hover:bg-lime-200 text-lime-800 py-2 rounded transition text-sm">4</button>
                    <button class="flex-1 bg-green-100 hover:bg-green-200 text-green-800 py-2 rounded transition text-sm">5</button>
                  </div>
                </div>
                
                <div>
                  <p class="text-sm font-semibold text-gray-800 mb-2">3. Stress level</p>
                  <div class="flex gap-2">
                    <button class="flex-1 bg-green-100 hover:bg-green-200 text-green-800 py-2 rounded transition text-sm">1 Low</button>
                    <button class="flex-1 bg-lime-100 hover:bg-lime-200 text-lime-800 py-2 rounded transition text-sm">2</button>
                    <button class="flex-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 py-2 rounded transition text-sm">3 Moderate</button>
                    <button class="flex-1 bg-orange-100 hover:bg-orange-200 text-orange-800 py-2 rounded transition text-sm">4</button>
                    <button class="flex-1 bg-red-100 hover:bg-red-200 text-red-800 py-2 rounded transition text-sm">5 High</button>
                  </div>
                </div>
                
                <div>
                  <p class="text-sm font-semibold text-gray-800 mb-2">4. Energy and motivation</p>
                  <div class="flex gap-2">
                    <button class="flex-1 bg-red-100 hover:bg-red-200 text-red-800 py-2 rounded transition text-sm">1</button>
                    <button class="flex-1 bg-orange-100 hover:bg-orange-200 text-orange-800 py-2 rounded transition text-sm">2</button>
                    <button class="flex-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 py-2 rounded transition text-sm">3</button>
                    <button class="flex-1 bg-lime-100 hover:bg-lime-200 text-lime-800 py-2 rounded transition text-sm">4</button>
                    <button class="flex-1 bg-green-100 hover:bg-green-200 text-green-800 py-2 rounded transition text-sm">5</button>
                  </div>
                </div>
                
                <div>
                  <p class="text-sm font-semibold text-gray-800 mb-2">5. Social connections</p>
                  <div class="flex gap-2">
                    <button class="flex-1 bg-red-100 hover:bg-red-200 text-red-800 py-2 rounded transition text-sm">1</button>
                    <button class="flex-1 bg-orange-100 hover:bg-orange-200 text-orange-800 py-2 rounded transition text-sm">2</button>
                    <button class="flex-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 py-2 rounded transition text-sm">3</button>
                    <button class="flex-1 bg-lime-100 hover:bg-lime-200 text-lime-800 py-2 rounded transition text-sm">4</button>
                    <button class="flex-1 bg-green-100 hover:bg-green-200 text-green-800 py-2 rounded transition text-sm">5</button>
                  </div>
                </div>
              </div>
              
              <div class="mt-4 pt-4 border-t">
                <button class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition font-semibold">
                  Submit Assessment
                </button>
                <p class="text-xs text-gray-600 mt-2 text-center">
                  Your responses help us provide better support resources
                </p>
              </div>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg">
              <p class="text-sm text-blue-900">
                <strong>💡 Note:</strong> This is a brief check-in tool. For a comprehensive assessment, please schedule an appointment with a counselor.
              </p>
            </div>
          </div>
        `
      }
    };
    
    const data = contentMap[type] || contentMap.counseling;
    content.innerHTML = data.content;
  }

  function closeMentalHealthModal() {
    const modal = document.getElementById('mentalHealthModal');
    modal.classList.add('hidden');
  }

  function initHospitalMap() {
    const mapEl = document.getElementById('hospitalMap');
    const statusEl = document.getElementById('mapStatus');
    if (!mapEl) {
      console.error('Map container not found');
      return;
    }

    // Wait a bit for Leaflet to be ready
    setTimeout(() => {
      try {
        // Check if Leaflet is loaded
        if (typeof L === 'undefined') {
          console.error('Leaflet not loaded');
          return;
        }

        // Initialize map with default location
        const map = L.map('hospitalMap').setView([40.7128, -74.0060], 13);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        console.log('Map initialized');

        // Try to get user location
        if (navigator.geolocation) {
          statusEl.textContent = 'Getting your location…';
          
          navigator.geolocation.getCurrentPosition(
            (pos) => {
              const lat = pos.coords.latitude;
              const lng = pos.coords.longitude;
              console.log('User location:', lat, lng);
              
              map.setView([lat, lng], 14);

              // Add user location marker
              L.circleMarker([lat, lng], {
                radius: 8,
                fillColor: '#3b82f6',
                color: '#1e40af',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
              }).addTo(map).bindPopup('Your Location');

              // Add hospitals around the user
              addHospitalMarkers(map, lat, lng);
              statusEl.textContent = 'Found 5 hospitals nearby.';
            },
            (error) => {
              console.warn('Geolocation error:', error.message);
              statusEl.textContent = 'Location denied. Showing default location.';
              addHospitalMarkers(map, 40.7128, -74.0060);
            },
            { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 }
          );
        } else {
          statusEl.textContent = 'Geolocation not supported.';
          addHospitalMarkers(map, 40.7128, -74.0060);
        }

      } catch (err) {
        console.error('Map error:', err);
        statusEl.textContent = 'Error loading map.';
      }
    }, 500);
  }

  function addHospitalMarkers(map, centerLat, centerLng) {
    const hospitals = [
      { name: 'City Medical Center', lat: centerLat + 0.01, lng: centerLng + 0.01 },
      { name: 'Emergency Hospital', lat: centerLat - 0.008, lng: centerLng + 0.015 },
      { name: 'Heart Care Hospital', lat: centerLat + 0.015, lng: centerLng - 0.01 },
      { name: 'Community Hospital', lat: centerLat - 0.012, lng: centerLng - 0.012 },
      { name: 'Central Health Center', lat: centerLat + 0.005, lng: centerLng - 0.018 }
    ];

    hospitals.forEach((hospital) => {
      L.circleMarker([hospital.lat, hospital.lng], {
        radius: 7,
        fillColor: '#ef4444',
        color: '#991b1b',
        weight: 2,
        opacity: 1,
        fillOpacity: 0.8
      }).addTo(map).bindPopup(`<strong>${hospital.name}</strong><br/>Hospital`);
    });

    console.log('Added ' + hospitals.length + ' hospital markers');
  }

  document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    setInterval(loadNotifications, 5000);
    initHospitalMap();
  });
</script>

<!-- MAIN DASHBOARD -->
<div class="p-6 md:p-8 min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">

  <!-- Welcome Section -->
  <div class="mb-8">
    <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 text-white rounded-3xl p-8 shadow-xl relative overflow-hidden">
      <div class="absolute top-0 right-0 w-40 h-40 bg-white opacity-10 rounded-full -mr-20 -mt-20"></div>
      <div class="absolute bottom-0 left-0 w-32 h-32 bg-white opacity-10 rounded-full -ml-16 -mb-16"></div>
      <div class="relative z-10">
        <h2 class="text-3xl md:text-4xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($student_name); ?>! 👋</h2>
        <p class="text-blue-100 text-lg">Keep track of your health and appointments</p>
      </div>
    </div>
  </div>

  <!-- Quick Stats Row -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Appointments Card -->
    <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border-t-4 border-blue-500">
      <div class="flex justify-between items-start mb-4">
        <div>
          <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Upcoming</p>
          <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $upcoming_count; ?></p>
        </div>
        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m7 8H3a2 2 0 01-2-2V7a2 2 0 012-2h16a2 2 0 012 2v12a2 2 0 01-2 2z"/>
          </svg>
        </div>
      </div>
      <p class="text-gray-600 text-sm">appointments</p>
    </div>

    <!-- Health Reports Card -->
    <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border-t-4 border-green-500">
      <div class="flex justify-between items-start mb-4">
        <div>
          <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Doctor Test Requests</p>
          <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $lab_reports_count; ?></p>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
      <p class="text-gray-600 text-sm">Total requests from your doctor</p>
    </div>

    <!-- Prescriptions Card -->
    <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border-t-4 border-orange-500">
      <div class="flex justify-between items-start mb-4">
        <div>
          <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Prescriptions</p>
          <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $unread_count; ?></p>
        </div>
        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z"/>
          </svg>
        </div>
      </div>
      <p class="text-gray-600 text-sm">
        <?php echo $latest_rx_title !== '' ? ('Latest Rx: ' . htmlspecialchars($latest_rx_title)) : 'No prescriptions yet'; ?>
      </p>
    </div>
  </div>

  <!-- Main Content Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    
    <!-- Left Column (2/3 width) -->
    <div class="lg:col-span-2 space-y-8">
      
      <!-- Nearby Hospitals Map -->
      <div class="bg-white rounded-2xl p-0 shadow-md overflow-hidden border border-gray-100">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-sky-50 to-transparent">
          <h3 class="font-bold text-gray-900 mb-1 flex items-center gap-2 text-lg">
            <div class="w-1 h-6 bg-sky-500 rounded"></div>
            Nearby Hospitals
          </h3>
          <p id="mapStatus" class="text-xs text-gray-500">Finding your location… please allow location access.</p>
        </div>
        <div id="hospitalMap" style="height: 300px; width: 100%;"></div>
      </div>
      
      <!-- Recent Notifications -->
      <?php if ($unread_count > 0): ?>
      <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border border-gray-100">
        <div class="flex items-center gap-2 mb-5">
          <div class="w-1 h-6 bg-blue-500 rounded"></div>
          <h3 class="font-bold text-lg text-gray-900">Recent Updates</h3>
          <span class="ml-auto bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full"><?php echo $unread_count; ?> new</span>
        </div>
        <div class="space-y-3">
          <?php foreach (array_slice($notifications, 0, 3) as $notif): ?>
            <div class="flex gap-4 p-4 bg-gradient-to-r from-blue-50 to-transparent rounded-xl border-l-4 border-blue-500 hover:shadow-md transition cursor-pointer notification-item"
                 data-type="<?php echo htmlspecialchars($notif['type']); ?>"
                 data-ref="<?php echo (int)$notif['reference_id']; ?>"
                 data-message="<?php echo htmlspecialchars($notif['message'], ENT_QUOTES); ?>"
                 data-created="<?php echo htmlspecialchars($notif['created_at']); ?>">
              <div class="w-3 h-3 bg-blue-500 rounded-full mt-1 flex-shrink-0"></div>
              <div class="flex-1 min-w-0">
                <p class="text-gray-800 font-medium text-sm"><?php echo htmlspecialchars($notif['message']); ?></p>
                <p class="text-gray-500 text-xs mt-1"><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Calendar Section -->
      <div class="bg-white rounded-2xl shadow-md p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
              <div class="w-1 h-6 bg-indigo-500 rounded"></div>
              My Health Calendar
            </h3>
            <p class="text-gray-600 text-sm mt-1">Appointments & Follow-ups</p>
          </div>
          <div class="flex gap-1">
            <a href="?month=<?php echo ($current_month > 1 ? $current_month - 1 : 12); ?>&year=<?php echo ($current_month > 1 ? $current_year : $current_year - 1); ?>" 
               class="bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded text-sm font-medium transition">← Prev</a>
            <span class="px-3 py-1.5 font-semibold text-sm"><?php echo "$month_name $current_year"; ?></span>
            <a href="?month=<?php echo ($current_month < 12 ? $current_month + 1 : 1); ?>&year=<?php echo ($current_month < 12 ? $current_year : $current_year + 1); ?>" 
               class="bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded text-sm font-medium transition">Next →</a>
          </div>
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 gap-1 bg-gray-50 p-3 rounded-xl">
          <!-- Day headers -->
          <div class="font-bold text-center py-2 bg-gradient-to-b from-blue-50 to-indigo-50 rounded text-xs text-blue-700">Sun</div>
          <div class="font-bold text-center py-2 bg-gradient-to-b from-blue-50 to-indigo-50 rounded text-xs text-blue-700">Mon</div>
          <div class="font-bold text-center py-2 bg-gradient-to-b from-blue-50 to-indigo-50 rounded text-xs text-blue-700">Tue</div>
          <div class="font-bold text-center py-2 bg-gradient-to-b from-blue-50 to-indigo-50 rounded text-xs text-blue-700">Wed</div>
          <div class="font-bold text-center py-2 bg-gradient-to-b from-blue-50 to-indigo-50 rounded text-xs text-blue-700">Thu</div>
          <div class="font-bold text-center py-2 bg-gradient-to-b from-blue-50 to-indigo-50 rounded text-xs text-blue-700">Fri</div>
          <div class="font-bold text-center py-2 bg-gradient-to-b from-blue-50 to-indigo-50 rounded text-xs text-blue-700">Sat</div>

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
            $day_prescriptions = isset($prescriptions_by_date[$date_str]) ? $prescriptions_by_date[$date_str] : [];
            $total_events = count($day_appointments) + count($day_prescriptions);
          ?>
            <div class="p-2 border rounded-lg min-h-16 <?php echo $is_today ? 'bg-gradient-to-br from-blue-50 to-indigo-50 border-blue-400 border-2' : 'bg-white border-gray-200'; ?> hover:shadow-md transition">
              <!-- Date number -->
              <div class="font-bold text-xs mb-1 <?php echo $is_today ? 'text-blue-700' : 'text-gray-700'; ?>">
                <?php echo $day; ?>
              </div>

              <!-- Events for this day -->
              <div class="space-y-0.5 text-xs">
                <?php if ($total_events > 0): ?>
                  <!-- Appointments -->
                  <?php foreach ($day_appointments as $apt): 
                    $status = $apt['status'] ?? 'pending';
                    $card_class = 'bg-gradient-to-r ';
                    $hover_class = 'hover:opacity-80';
                    $icon = '📅';
                    
                    if ($status === 'completed') {
                      $card_class .= 'from-green-100 to-green-200 text-green-800';
                    } else if ($status === 'cancelled') {
                      $card_class .= 'from-red-100 to-red-200 text-red-800';
                    } else if ($status === 'confirmed') {
                      $card_class .= 'from-blue-100 to-blue-200 text-blue-800';
                    } else if ($status === 'pending') {
                      $card_class .= 'from-amber-100 to-amber-200 text-amber-800';
                    } else {
                      $card_class .= 'from-gray-100 to-gray-200 text-gray-800';
                    }
                  ?>
                    <div class="<?php echo $card_class; ?> p-1 rounded cursor-pointer <?php echo $hover_class; ?> font-medium" 
                         onclick="openStudentAppointmentDetails(<?php echo $apt['id']; ?>)"
                         title="<?php echo htmlspecialchars($apt['doctor_name']); ?> - <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?> (<?php echo ucfirst($status); ?>)">
                      <div class="text-xs leading-tight"><?php echo $icon; ?> <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></div>
                      <div class="font-semibold truncate text-xs">Dr. <?php echo htmlspecialchars(substr($apt['doctor_name'], 0, 8)); ?></div>
                    </div>
                  <?php endforeach; ?>
                  
                  <!-- Prescription Follow-ups -->
                  <?php foreach ($day_prescriptions as $rx): ?>
                    <div class="bg-gradient-to-r from-purple-100 to-purple-200 text-purple-800 p-1 rounded cursor-pointer hover:opacity-80 font-medium" 
                         onclick="window.location.href='download_prescription.php?id=<?php echo $rx['id']; ?>'"
                         title="Follow-up: <?php echo htmlspecialchars($rx['title']); ?>">
                      <div class="text-xs leading-tight">💊 Follow-up</div>
                      <div class="font-semibold truncate text-xs"><?php echo htmlspecialchars(substr($rx['title'], 0, 10)); ?></div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="text-gray-400">—</div>
                <?php endif; ?>
              </div>
            </div>
          <?php endfor; ?>
        </div>

        <!-- Legend -->
        <div class="mt-4 pt-4 border-t flex flex-wrap gap-4 text-sm">
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-gradient-to-r from-green-100 to-green-200 border border-green-400 rounded"></div>
            <span class="text-gray-700">Completed</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-gradient-to-r from-blue-100 to-blue-200 border border-blue-400 rounded"></div>
            <span class="text-gray-700">Confirmed</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-gradient-to-r from-amber-100 to-amber-200 border border-amber-400 rounded"></div>
            <span class="text-gray-700">Pending</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-gradient-to-r from-red-100 to-red-200 border border-red-400 rounded"></div>
            <span class="text-gray-700">Cancelled</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-gradient-to-r from-purple-100 to-purple-200 border border-purple-400 rounded"></div>
            <span class="text-gray-700">Follow-up</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-400 rounded"></div>
            <span class="text-gray-700">Today</span>
          </div>
        </div>
      </div>

      <!-- Upcoming Appointments (live data) -->
      <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border border-gray-100">
        <div class="flex items-center gap-2 mb-5">
          <div class="w-1 h-6 bg-indigo-500 rounded"></div>
          <h3 class="font-bold text-lg text-gray-900">Upcoming Appointments</h3>
          <span class="ml-auto text-xs text-gray-500"><?php echo $upcoming_count; ?> total</span>
        </div>
        
        <div class="space-y-4">
          <?php if ($upcoming_count === 0): ?>
            <p class="text-sm text-gray-500">No upcoming appointments scheduled.</p>
          <?php else: ?>
            <?php foreach ($upcoming_appointments as $apt): ?>
              <?php
                $date_label = date('M d, Y', strtotime($apt['appointment_date']));
                $time_label = date('g:i A', strtotime($apt['appointment_time']));
                $status = $apt['status'] ?? '';
                $status_color = 'bg-gray-100 text-gray-700';
                if ($status === 'completed') $status_color = 'bg-green-100 text-green-700';
                else if ($status === 'cancelled') $status_color = 'bg-red-100 text-red-700';
                else if ($status === 'confirmed') $status_color = 'bg-blue-100 text-blue-700';
                else if ($status === 'pending') $status_color = 'bg-amber-100 text-amber-700';
              ?>
              <div class="flex gap-4 p-4 bg-gradient-to-r from-indigo-50 to-transparent rounded-xl border-l-4 border-indigo-500 hover:shadow-md transition">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m7 8H3a2 2 0 01-2-2V7a2 2 0 012-2h16a2 2 0 012 2v12a2 2 0 01-2 2z"/>
                    </svg>
                  </div>
                </div>
                <div class="flex-1">
                  <p class="font-semibold text-gray-900 text-sm">Dr. <?php echo htmlspecialchars($apt['doctor_name']); ?></p>
                  <p class="text-sm text-gray-600"><?php echo $date_label; ?>, <?php echo $time_label; ?></p>
                </div>
                <div class="flex items-center gap-3 self-center">
                  <?php if ($status !== ''): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_color; ?>"><?php echo htmlspecialchars(ucfirst($status)); ?></span>
                  <?php endif; ?>
                  <a href="studentreschudleappointment.php?id=<?php echo (int)$apt['id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Reschedule</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <a href="studentbooknewappointment.php" class="mt-6 w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          Book New Appointment
        </a>
      </div>
    </div>

    <!-- Right Column (1/3 width) -->
    <div class="space-y-6">
      
      <!-- Quick Actions -->
      <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border border-gray-100">
        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
          <div class="w-1 h-6 bg-teal-500 rounded"></div>
          Quick Actions
        </h3>
        <div class="space-y-2">
          <a href="studentmentalhealth.php" class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-pink-50 transition group">
            <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center group-hover:bg-pink-200 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
              </svg>
            </div>
            <div>
              <p class="font-semibold text-gray-900 text-sm">Mental Health</p>
              <p class="text-gray-500 text-xs">Counseling Services</p>
            </div>
          </a>

          <a href="#" class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-green-50 transition group">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 7-7V3H3v7z"/>
              </svg>
            </div>
            <div>
              <p class="font-semibold text-gray-900 text-sm">Lab Reports</p>
              <p class="text-gray-500 text-xs">View Results</p>
            </div>
          </a>

          <a href="#prescriptions" class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-orange-50 transition group">
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <p class="font-semibold text-gray-900 text-sm">Prescriptions</p>
              <p class="text-gray-500 text-xs">Current Meds</p>
            </div>
          </a>
        </div>
      </div>

      <!-- Prescriptions -->
      <div id="prescriptions" class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border border-gray-100">
        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
          <div class="w-1 h-6 bg-orange-500 rounded"></div>
          Prescriptions
          <span class="ml-auto text-xs text-gray-500"><?php echo $prescription_count; ?> total</span>
        </h3>
        <div class="space-y-3">
          <?php if ($prescription_count === 0): ?>
            <p class="text-sm text-gray-500">No prescriptions yet.</p>
          <?php else: ?>
            <?php foreach ($prescriptions as $rx): ?>
              <div class="flex items-center justify-between border rounded-lg p-3 hover:bg-orange-50 transition">
                <div>
                  <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($rx['title']); ?></p>
                  <p class="text-xs text-gray-500">
                    <?php echo htmlspecialchars($rx['doctor_name']); ?> • <?php echo date('M d, Y', strtotime($rx['created_at'])); ?>
                    <?php if ($rx['follow_up_date']): ?> • Follow-up: <?php echo htmlspecialchars($rx['follow_up_date']); ?><?php endif; ?>
                  </p>
                </div>
                <a class="text-orange-600 text-sm font-semibold hover:underline" href="download_prescription.php?id=<?php echo (int)$rx['id']; ?>">View</a>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Mental Health Support Section -->
      <div class="bg-gradient-to-br from-purple-500 via-purple-600 to-indigo-600 text-white rounded-2xl p-6 shadow-md hover:shadow-xl transition">
        <div class="flex items-start gap-2 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
          <div>
            <h3 class="font-bold text-lg">Mental Health Support</h3>
          </div>
        </div>
        
        <div class="space-y-3 mb-4">
          <p class="text-sm leading-relaxed opacity-95">
            Your mental health matters. Access confidential counseling, wellness resources, and support whenever you need it.
          </p>
          
          <!-- Quick Resources -->
          <div class="grid grid-cols-2 gap-2">
            <button onclick="openMentalHealthModal('counseling')" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm p-3 rounded-lg transition text-left">
              <div class="text-xs font-semibold mb-0.5">🧠 Counseling</div>
              <div class="text-xs opacity-90">Book session</div>
            </button>
            
            <button onclick="openMentalHealthModal('crisis')" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm p-3 rounded-lg transition text-left">
              <div class="text-xs font-semibold mb-0.5">🆘 Crisis Help</div>
              <div class="text-xs opacity-90">Immediate support</div>
            </button>
            
            <button onclick="openMentalHealthModal('wellness')" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm p-3 rounded-lg transition text-left">
              <div class="text-xs font-semibold mb-0.5">🧘 Wellness</div>
              <div class="text-xs opacity-90">Self-care tips</div>
            </button>
            
            <button onclick="openMentalHealthModal('assessment')" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm p-3 rounded-lg transition text-left">
              <div class="text-xs font-semibold mb-0.5">📋 Assessment</div>
              <div class="text-xs opacity-90">Check-in</div>
            </button>
          </div>
        </div>

        <a href="studentmentalhealth.php" class="block w-full bg-white text-purple-600 font-semibold py-2.5 rounded-lg hover:bg-purple-50 transition text-center text-sm">
          View All Resources
        </a>
        
        <!-- Crisis Hotlines -->
        <div class="mt-4 pt-4 border-t border-white/20">
          <p class="text-xs font-semibold mb-2 opacity-90">Crisis Hotlines:</p>
          <div class="space-y-1 text-xs opacity-90">
            <div class="flex justify-between items-center">
              <span>National Suicide Hotline:</span>
              <a href="tel:988" class="font-bold hover:underline">988</a>
            </div>
            <div class="flex justify-between items-center">
              <span>Crisis Text Line:</span>
              <a href="sms:741741" class="font-bold hover:underline">Text HOME to 741741</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Health Tip -->
      <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-2xl p-6 shadow-md hover:shadow-lg transition">
        <div class="flex items-start gap-2 mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
            </svg>
          <h3 class="font-bold">Health Tip</h3>
        </div>
        <p class="text-sm leading-relaxed opacity-90"><?php echo htmlspecialchars($random_health_tip); ?></p>
      </div>

      <!-- Emergency Info -->
      <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-2xl p-6 shadow-md">
        <h3 class="font-bold mb-3 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
            </svg>
          Emergency
        </h3>
        <p class="text-sm mb-3 opacity-90">Need immediate help?</p>
        <button class="w-full bg-white text-red-600 font-bold py-2 rounded-lg hover:bg-red-50 transition">
          Call 911
        </button>
      </div>

      <!-- FAQ Section -->
      <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border border-gray-100">
        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
          <div class="w-1 h-6 bg-purple-500 rounded"></div>
          Frequently Asked Questions
        </h3>
        <div class="space-y-3">
          <div class="border-b pb-3">
            <button class="w-full text-left flex items-start justify-between gap-2 group" onclick="toggleFAQ(1)">
              <p class="font-semibold text-gray-900 text-sm group-hover:text-blue-600 transition">How do I book an appointment?</p>
              <svg id="faq-icon-1" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 flex-shrink-0 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div id="faq-answer-1" class="hidden mt-2 text-sm text-gray-600 leading-relaxed">
              Click on "Book New Appointment" button in the appointments section. Select your preferred doctor, date, and time slot, then fill in the reason for your visit.
            </div>
          </div>

          <div class="border-b pb-3">
            <button class="w-full text-left flex items-start justify-between gap-2 group" onclick="toggleFAQ(2)">
              <p class="font-semibold text-gray-900 text-sm group-hover:text-blue-600 transition">Can I reschedule my appointment?</p>
              <svg id="faq-icon-2" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 flex-shrink-0 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div id="faq-answer-2" class="hidden mt-2 text-sm text-gray-600 leading-relaxed">
              Yes! Click the "Reschedule" button next to your appointment in the upcoming appointments list. You can change the date, time, and other details.
            </div>
          </div>

          <div class="border-b pb-3">
            <button class="w-full text-left flex items-start justify-between gap-2 group" onclick="toggleFAQ(3)">
              <p class="font-semibold text-gray-900 text-sm group-hover:text-blue-600 transition">How do I view my prescriptions?</p>
              <svg id="faq-icon-3" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 flex-shrink-0 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div id="faq-answer-3" class="hidden mt-2 text-sm text-gray-600 leading-relaxed">
              Scroll down to the Prescriptions section on your dashboard. Click "View" next to any prescription to see details or download a PDF copy.
            </div>
          </div>

          <div class="border-b pb-3">
            <button class="w-full text-left flex items-start justify-between gap-2 group" onclick="toggleFAQ(4)">
              <p class="font-semibold text-gray-900 text-sm group-hover:text-blue-600 transition">What if I need mental health support?</p>
              <svg id="faq-icon-4" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 flex-shrink-0 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div id="faq-answer-4" class="hidden mt-2 text-sm text-gray-600 leading-relaxed">
              Click on "Mental Health" in the Quick Actions section. Our counseling services are confidential and available to all students.
            </div>
          </div>

          <div class="pb-2">
            <button class="w-full text-left flex items-start justify-between gap-2 group" onclick="toggleFAQ(5)">
              <p class="font-semibold text-gray-900 text-sm group-hover:text-blue-600 transition">How can I access my lab reports?</p>
              <svg id="faq-icon-5" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 flex-shrink-0 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div id="faq-answer-5" class="hidden mt-2 text-sm text-gray-600 leading-relaxed">
              Lab reports will appear in your notifications when ready. You'll receive an alert from your doctor with instructions on viewing or downloading your test results.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
</div>

<!-- Notification Detail Modal -->
<div id="notificationModal" class="fixed inset-0 bg-black/40 flex items-center justify-center px-4 hidden z-50">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative">
    <button class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl" onclick="closeNotificationModal()">&times;</button>
    <h3 id="notifModalTitle" class="text-lg font-bold text-gray-900 mb-3">Notification</h3>
    <div id="notifModalBody" class="text-sm text-gray-700 space-y-2">
      <p>Loading details...</p>
    </div>
    <div class="mt-6 text-right">
      <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition" onclick="closeNotificationModal()">Close</button>
    </div>
  </div>
</div>

<!-- Mental Health Support Modal -->
<div id="mentalHealthModal" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 hidden z-50">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-0 relative overflow-hidden">
    <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-2xl z-10" onclick="closeMentalHealthModal()">&times;</button>
    
    <!-- Modal Content -->
    <div id="mentalHealthModalContent" class="p-6">
      <p class="text-center text-gray-500">Loading...</p>
    </div>
  </div>
</div>

<!-- MODERN FOOTER -->
<footer class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white mt-12 border-t border-slate-700">
  <div class="max-w-6xl mx-auto px-6 py-10">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div>
        <h4 class="text-lg font-bold mb-3">University Medical Center</h4>
        <p class="text-slate-300 text-sm leading-relaxed">Your student health portal for appointments, prescriptions, and wellness resources.</p>
      </div>
      <div>
        <h4 class="text-lg font-bold mb-3">Quick Links</h4>
        <ul class="space-y-2 text-slate-300 text-sm">
          <li><a href="#" class="hover:text-white transition">Appointments</a></li>
          <li><a href="#" class="hover:text-white transition">Prescriptions</a></li>
          <li><a href="#" class="hover:text-white transition">Mental Health</a></li>
        </ul>
      </div>
      <div class="text-sm text-slate-300">
        <h4 class="text-lg font-bold mb-3">Stay Connected</h4>
        <p class="mb-3">Need help? Visit the student health center or call emergency services if needed.</p>
        <div class="flex gap-3">
          <a href="#" class="w-10 h-10 bg-slate-700 hover:bg-blue-600 rounded-full flex items-center justify-center transition" aria-label="Twitter">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 19c4.418 0 6.564-3.365 6.564-6.478 0-.099-.002-.197-.006-.294A4.677 4.677 0 0021 5.471v-.504c-.418.23-.854.38-1.32.44a2.304 2.304 0 001.01-1.272 4.58 4.58 0 01-1.455.56 2.29 2.29 0 00-3.903 2.088 6.492 6.492 0 01-4.715-2.39 2.289 2.289 0 00.717 3.055 2.267 2.267 0 01-1.04-.285v.028c0 1.11.79 2.034 1.84 2.243a2.28 2.28 0 01-1.036.038 2.293 2.293 0 002.142 1.59 4.598 4.598 0 01-2.853.98 6.435 6.435 0 01-.95-.08 12.994 12.994 0 006.974 2.042"/></svg>
          </a>
          <a href="#" class="w-10 h-10 bg-slate-700 hover:bg-blue-600 rounded-full flex items-center justify-center transition" aria-label="LinkedIn">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14m-.5 15.5v-5.3a3.26 3.26 0 00-3.26-3.26c-.85 0-1.84.52-2.32 1.39v-1.66h-2.3v8.5h2.3v-4.26c0-.84.63-1.63 1.67-1.63.92 0 1.83.6 1.83 1.99v4.26h2.3M7 11.9h2.3V19H7z"/></svg>
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
