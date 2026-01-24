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

<!-- NAVBAR (Consistent Style) -->
<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
  <!-- Logo + Portal Name -->
  <div class="flex items-center gap-2">
    <!-- Blue Hollow Heart -->
    <span class="text-blue-600 text-2xl">💜</span>
    <span class="font-bold text-xl text-blue-700">Student Portal</span>
  </div>

  <!-- Right side: Notifications + Profile Dropdown -->
  <div class="flex items-center gap-6">
    <!-- Notifications Bell -->
    <div class="relative">
      <button id="notificationBtn" class="relative text-gray-700 hover:text-blue-600 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z"/>
        </svg>
        <?php if ($unread_count > 0): ?>
          <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
            <?php echo $unread_count; ?>
          </span>
        <?php endif; ?>
      </button>

      <!-- Notifications Dropdown -->
      <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-lg z-50 max-h-96 overflow-y-auto">
        <div class="bg-blue-600 text-white px-4 py-3 font-semibold rounded-t-xl flex justify-between items-center">
          <span>Notifications</span>
          <?php if ($unread_count > 0): ?>
            <button onclick="markAllAsRead()" class="text-xs bg-blue-700 hover:bg-blue-800 px-2 py-1 rounded transition">Mark Read</button>
          <?php endif; ?>
        </div>
        
        <?php if ($unread_count > 0): ?>
          <div class="divide-y">
            <?php foreach ($notifications as $notif): ?>
              <div class="px-4 py-3 hover:bg-blue-50 transition border-l-4 border-blue-500">
                <p class="text-gray-800 font-medium text-sm"><?php echo htmlspecialchars($notif['message']); ?></p>
                <p class="text-gray-500 text-xs mt-1"><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="px-4 py-6 text-center text-gray-500">
            <p class="text-sm">No new notifications</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Profile Dropdown -->
    <div class="relative">
      <button id="profileBtn" class="flex items-center gap-2 text-gray-700 font-medium hover:text-blue-600 transition">
        <?php echo htmlspecialchars($student_name); ?>
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <!-- Dropdown -->
      <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white border rounded-xl shadow-lg hidden z-50">
        <a href="profile.php" class="block px-4 py-2 hover:bg-blue-100">Edit Profile</a>
        <a href="login.php" class="block px-4 py-2 text-red-600 hover:bg-red-100">Logout</a>
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
<div class="p-6 min-h-screen">

  <!-- CARDS GRID -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">

    <!-- Welcome Card -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-2xl p-6 shadow-xl">
      <h3 class="text-xl font-bold mb-2">Welcome Back, <?php echo htmlspecialchars($student_name); ?>!</h3>
      <p>Here’s your health overview for today.</p>
    </div>

    <!-- Reminder Card -->
    <div class="bg-white rounded-2xl p-6 shadow-md border-l-4 border-blue-300">
      <h3 class="font-semibold text-gray-800 mb-1">Reminder</h3>
      <p class="text-gray-600 text-sm">Appointment tomorrow at 2 PM</p>
    </div>

    <!-- Recent Notifications Card -->
    <?php if ($unread_count > 0): ?>
    <div class="bg-blue-50 rounded-2xl p-6 shadow-md border-l-4 border-blue-500">
      <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z"/>
        </svg>
        New Updates (<?php echo $unread_count; ?>)
      </h3>
      <div class="space-y-2">
        <?php foreach (array_slice($notifications, 0, 2) as $notif): ?>
          <div class="bg-white p-3 rounded border-l-2 border-blue-500">
            <p class="text-gray-800 text-sm"><?php echo htmlspecialchars($notif['message']); ?></p>
            <p class="text-gray-500 text-xs mt-1"><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if ($unread_count > 2): ?>
        <p class="text-gray-600 text-xs mt-2">+<?php echo ($unread_count - 2); ?> more notification(s)</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Health Tips Card -->
    <div class="bg-white rounded-2xl p-6 shadow-md border-l-4 border-blue-300">
      <h3 class="font-semibold text-gray-800 mb-1">Health Tips</h3>
      <p class="text-gray-600 text-sm">Stay hydrated! Aim for 8 glasses of water daily.</p>
    </div>

    <!-- New Lab Report Card -->
    <div class="bg-white rounded-2xl p-6 shadow-md border-l-4 border-blue-300">
      <h3 class="font-semibold text-gray-800 mb-1">New Lab Report</h3>
      <p class="text-gray-600 text-sm">Your latest blood test is ready.</p>
    </div>

    <!-- Upcoming Appointments Card -->
   <div class="bg-white rounded-2xl p-6 shadow-md border-l-4 border-blue-300">
  <h3 class="font-semibold text-gray-800 mb-2">Upcoming Appointments</h3>

  <ul class="text-gray-600 text-sm space-y-3">
    <!-- Appointment 1 -->
    <li class="flex justify-between items-center">
      <span>Dr. Smith - 12 Jan 2026, 10:00 AM</span>

      <a
        href="studentreschudleappointment.php"
        class="bg-blue-500 text-white px-3 py-1 rounded-lg text-xs font-semibold hover:bg-blue-600 transition"
      >
        Reschedule
      </a>
    </li>

    <!-- Appointment 2 -->
    <li class="flex justify-between items-center">
      <span>Dr. Adams - 14 Jan 2026, 2:00 PM</span>

      <a
        href="/reschedule/dr-adams"
        class="bg-blue-500 text-white px-3 py-1 rounded-lg text-xs font-semibold hover:bg-blue-600 transition"
      >
        Reschedule
      </a>
    </li>
  </ul>

  <!-- Book new appointment -->
  <a
    href="studentbooknewappointment.php"
    class="mt-3 inline-block text-blue-600 font-semibold hover:underline"
  >
    Book New Appointment
  </a>
</div>


    <!-- Emergency Guidelines Card -->
    <div class="bg-red-600 text-white rounded-2xl p-6 shadow-lg">
      <h3 class="font-semibold mb-2">Emergency Guidelines</h3>
      <ul class="text-sm space-y-1">
        <li>Severe Injury: Call 911 or use SOS button immediately (123-456-789)</li>
        <li>Mental Health Crisis: 24/7 Counseling Available, Crisis Line: 987-654-321</li>
        <li>Non-Emergency: Contact Health Center for appointment</li>
        <li>Ambulance Request: <button class="font-semibold underline">Request Ambulance</button></li>
      </ul>
    </div>

    <!-- Mental Health & Counseling Card -->
   <div class="bg-gradient-to-r from-pink-500 to-pink-400 rounded-2xl p-6 shadow-lg text-white">
  <div class="flex items-center gap-2 mb-2">
    <svg
      xmlns="http://www.w3.org/2000/svg"
      class="w-6 h-6"
      fill="currentColor"
      viewBox="0 0 24 24"
    >
      <path d="M12 2C9 2 6 3 6 6c0 3 3 5 6 9 3-4 6-6 6-9 0-3-3-4-6-4z"/>
    </svg>
    <h3 class="font-bold text-lg">Mental Health &amp; Counseling</h3>
  </div>

  <p class="text-sm mb-4">
    Free confidential counseling services available
  </p>

  <a
    href="studentmentalhealth.php"
    class="inline-block bg-white text-pink-500 px-4 py-2 rounded-lg font-semibold hover:bg-pink-100 transition"
  >
    Learn More
  </a>
</div>

  </div>

  <!-- FAQ Section -->
  <div class="mb-6">
    <h3 class="text-xl font-bold mb-4">FAQs</h3>
    <div class="space-y-3">
      <div class="bg-white p-4 rounded-xl shadow-md">
        <p class="font-semibold">How do I book an appointment?</p>
        <p class="text-sm text-gray-600">Go to the appointments section and select a date & time.</p>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-md">
        <p class="font-semibold">Where can I view my lab reports?</p>
        <p class="text-sm text-gray-600">All your reports are in the lab reports section on your dashboard.</p>
      </div>
    </div>
    <button class="mt-3 text-blue-600 font-semibold hover:underline">View All FAQs</button>
  </div>

  <!-- Nearby Hospitals -->
  <div class="mb-6">
    <h3 class="text-xl font-bold mb-4">Nearby Hospitals</h3>
    <div class="bg-white rounded-xl shadow-md h-64 flex items-center justify-center text-gray-500">
      Google Maps-style map placeholder
    </div>
  </div>

</div>

<!-- FOOTER -->
<footer class="bg-blue-900 text-white p-6 mt-6">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
    <div>
      <h4 class="font-bold mb-2">Contact Info</h4>
      <p>Phone: 123-456-789</p>
      <p>Email: info@universityhealth.edu</p>
    </div>
    <div>
      <h4 class="font-bold mb-2">Hours of Operation</h4>
      <p>Open 24/7</p>
    </div>
    <div>
      <h4 class="font-bold mb-2">Campus Map</h4>
      <p>Map Placeholder</p>
    </div>
  </div>
  <div class="text-center text-sm">&copy; 2026 University Medical Center. All rights reserved.</div>
</footer>

</body>
</html>
