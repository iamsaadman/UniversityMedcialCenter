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

// Fetch unread notifications
$notification_query = "SELECT id, message, type, reference_id, created_at 
                      FROM notifications 
                      WHERE user_id = ? AND is_read = FALSE 
                      ORDER BY created_at DESC 
                      LIMIT 5";
$notification_stmt = $mysqli->prepare($notification_query);
$notification_stmt->bind_param('i', $student_id);
$notification_stmt->execute();
$notification_result = $notification_stmt->get_result();
$notifications = $notification_result->fetch_all(MYSQLI_ASSOC);
$unread_count = count($notifications);
$notification_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard | Health Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
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
        <?php if ($unread_count > 0): ?>
          <span class="absolute top-1 right-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full animate-pulse">
            <?php echo $unread_count; ?>
          </span>
        <?php endif; ?>
      </button>

      <!-- Notifications Dropdown -->
      <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-xl z-50 max-h-96 overflow-y-auto">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-3 font-semibold rounded-t-xl flex justify-between items-center sticky top-0">
          <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z"/>
            </svg>
            <span>Notifications</span>
          </div>
          <?php if ($unread_count > 0): ?>
            <button onclick="markAllAsRead()" class="text-xs bg-blue-700 hover:bg-blue-800 px-2 py-1 rounded transition">Mark Read</button>
          <?php endif; ?>
        </div>
        
        <?php if ($unread_count > 0): ?>
          <div class="divide-y">
            <?php foreach ($notifications as $notif): ?>
              <div class="px-4 py-3 hover:bg-blue-50 transition border-l-4 border-blue-500 bg-white">
                <p class="text-gray-800 font-medium text-sm"><?php echo htmlspecialchars($notif['message']); ?></p>
                <p class="text-gray-500 text-xs mt-1"><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="px-4 py-8 text-center text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z"/>
            </svg>
            <p class="text-sm">No new notifications</p>
          </div>
        <?php endif; ?>
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

  notificationBtn.addEventListener('click', () => {
    notificationDropdown.classList.toggle('hidden');
    profileDropdown.classList.add('hidden');
  });

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
        location.reload();
      }
    });
  }
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
          <p class="text-3xl font-bold text-gray-900 mt-1">2</p>
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
          <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Lab Reports</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">3</p>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
      <p class="text-gray-600 text-sm">Available</p>
    </div>

    <!-- Notifications Card -->
    <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition border-t-4 border-orange-500">
      <div class="flex justify-between items-start mb-4">
        <div>
          <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Notifications</p>
          <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $unread_count; ?></p>
        </div>
        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z"/>
          </svg>
        </div>
      </div>
      <p class="text-gray-600 text-sm">Unread</p>
    </div>
  </div>

  <!-- Main Content Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    
    <!-- Left Column (2/3 width) -->
    <div class="lg:col-span-2 space-y-8">
      
      <!-- Recent Notifications -->
      <?php if ($unread_count > 0): ?>
      <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition">
        <div class="flex items-center gap-2 mb-5">
          <div class="w-1 h-6 bg-blue-500 rounded"></div>
          <h3 class="font-bold text-lg text-gray-900">Recent Updates</h3>
          <span class="ml-auto bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full"><?php echo $unread_count; ?> new</span>
        </div>
        <div class="space-y-3">
          <?php foreach (array_slice($notifications, 0, 3) as $notif): ?>
            <div class="flex gap-4 p-4 bg-gradient-to-r from-blue-50 to-transparent rounded-xl border-l-4 border-blue-500 hover:shadow-md transition">
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

      <!-- Upcoming Appointments -->
      <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition">
        <div class="flex items-center gap-2 mb-5">
          <div class="w-1 h-6 bg-indigo-500 rounded"></div>
          <h3 class="font-bold text-lg text-gray-900">Upcoming Appointments</h3>
        </div>
        
        <div class="space-y-4">
          <div class="flex gap-4 p-4 bg-gradient-to-r from-indigo-50 to-transparent rounded-xl border-l-4 border-indigo-500 hover:shadow-md transition">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m7 8H3a2 2 0 01-2-2V7a2 2 0 012-2h16a2 2 0 012 2v12a2 2 0 01-2 2z"/>
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <p class="font-semibold text-gray-900">Dr. Smith - Checkup</p>
              <p class="text-sm text-gray-600">12 Jan 2026, 10:00 AM</p>
            </div>
            <a href="studentreschudleappointment.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition self-center">Reschedule</a>
          </div>

          <div class="flex gap-4 p-4 bg-gradient-to-r from-purple-50 to-transparent rounded-xl border-l-4 border-purple-500 hover:shadow-md transition">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m7 8H3a2 2 0 01-2-2V7a2 2 0 012-2h16a2 2 0 012 2v12a2 2 0 01-2 2z"/>
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <p class="font-semibold text-gray-900">Dr. Adams - Follow-up</p>
              <p class="text-sm text-gray-600">14 Jan 2026, 2:00 PM</p>
            </div>
            <a href="studentreschudleappointment.php" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition self-center">Reschedule</a>
          </div>
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
      <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition">
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

          <a href="#" class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-orange-50 transition group">
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

      <!-- Health Tip -->
      <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-2xl p-6 shadow-md hover:shadow-lg transition">
        <div class="flex items-start gap-2 mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
            </svg>
          <h3 class="font-bold">Health Tip</h3>
        </div>
        <p class="text-sm leading-relaxed opacity-90">Stay hydrated! Drinking 8 glasses of water daily helps maintain good health and energy levels.</p>
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
    </div>
  </div>

</div>


</div>

<!-- MODERN FOOTER -->
<footer class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white mt-12 border-t border-slate-700">
  <div class="max-w-7xl mx-auto px-6 py-12">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
      <!-- Brand Section -->
      <div class="col-span-1 md:col-span-1">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
            </svg>
          </div>
          <div>
            <h3 class="font-bold text-lg">UniMed Health</h3>
            <p class="text-sm text-slate-400">Your Health, Our Priority</p>
          </div>
        </div>
        <p class="text-slate-400 text-sm">Providing quality healthcare services to our university community.</p>
      </div>

      <!-- Quick Links -->
      <div>
        <h4 class="font-bold mb-4 text-white">Quick Links</h4>
        <ul class="space-y-2 text-slate-400">
          <li><a href="#" class="hover:text-blue-400 transition">My Appointments</a></li>
          <li><a href="#" class="hover:text-blue-400 transition">Lab Reports</a></li>
          <li><a href="studentmentalhealth.php" class="hover:text-blue-400 transition">Mental Health</a></li>
          <li><a href="#" class="hover:text-blue-400 transition">Prescriptions</a></li>
        </ul>
      </div>

      <!-- Support -->
      <div>
        <h4 class="font-bold mb-4 text-white">Support</h4>
        <ul class="space-y-2 text-slate-400">
          <li><a href="#" class="hover:text-blue-400 transition">FAQ</a></li>
          <li><a href="#" class="hover:text-blue-400 transition">Help Center</a></li>
          <li><a href="#" class="hover:text-blue-400 transition">Contact Us</a></li>
          <li><a href="#" class="hover:text-blue-400 transition">Privacy Policy</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div>
        <h4 class="font-bold mb-4 text-white">Contact Info</h4>
        <ul class="space-y-3 text-slate-400 text-sm">
          <li class="flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 flex-shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span>health@university.edu</span>
          </li>
          <li class="flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 flex-shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            <span>+1 (555) 123-4567</span>
          </li>
          <li class="flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 flex-shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Campus Health Center, Building A</span>
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
          <a href="#" class="w-10 h-10 bg-slate-700 hover:bg-blue-600 rounded-full flex items-center justify-center transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8 19c4.418 0 6.564-3.365 6.564-6.478 0-.099-.002-.197-.006-.294A4.677 4.677 0 0021 5.471v-.504c-.418.23-.854.38-1.32.44a2.304 2.304 0 001.01-1.272 4.58 4.58 0 01-1.455.56 2.29 2.29 0 00-3.903 2.088 6.492 6.492 0 01-4.715-2.39 2.289 2.289 0 00.717 3.055 2.267 2.267 0 01-1.04-.285v.028c0 1.11.79 2.034 1.84 2.243a2.28 2.28 0 01-1.036.038 2.293 2.293 0 002.142 1.59 4.598 4.598 0 01-2.853.98 6.435 6.435 0 01-.95-.08 12.994 12.994 0 006.974 2.042"/>
            </svg>
          </a>
          <a href="#" class="w-10 h-10 bg-slate-700 hover:bg-blue-600 rounded-full flex items-center justify-center transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M19 3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14m-.5 15.5v-5.3a3.26 3.26 0 00-3.26-3.26c-.85 0-1.84.52-2.32 1.39v-1.66h-2.3v8.5h2.3v-4.26c0-.84.63-1.63 1.67-1.63.92 0 1.83.6 1.83 1.99v4.26h2.3M7 11.9h2.3V19H7z"/>
            </svg>
          </a>
          <a href="#" class="w-10 h-10 bg-slate-700 hover:bg-blue-600 rounded-full flex items-center justify-center transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.477 2 2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12c0-5.523-4.477-10-10-10z"/>
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

</body>
</html>
