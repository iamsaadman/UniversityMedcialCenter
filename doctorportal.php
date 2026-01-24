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

// Fetch today's appointments
$today = date('Y-m-d');
$query = "SELECT a.*, u.fullname as patient_name 
          FROM appointments a 
          JOIN users u ON a.student_id = u.id 
          WHERE a.doctor_id = ? AND a.appointment_date = ?
          ORDER BY a.appointment_time ASC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('is', $doctor_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$appointment_count = count($appointments);

$stmt->close();
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
<nav class="bg-white shadow flex items-center justify-between px-6 py-4">
  <!-- Logo + Title -->
  <div class="flex items-center gap-3">
    <!-- Hollow green heart -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 21C12 21 4 14.8 4 9.5 4 6 6.5 4 9 4c1.5 0 3 1 3 3 0-2 1.5-3 3-3 2.5 0 5 2 5 5 0 5.3-8 11.5-8 11.5z"/>
    </svg>
    <h1 class="text-lg font-bold text-gray-800">Doctor Portal</h1>
  </div>

  <!-- Notifications + Profile Dropdown -->
  <div class="flex items-center gap-6 relative">
    <!-- Notifications -->
    <div class="relative">
      <button class="bg-gray-100 p-2 rounded-full hover:bg-gray-200 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z"/>
        </svg>
      </button>
      <!-- Notification Dropdown (hidden by default) -->
      <div class="absolute right-0 mt-2 w-72 bg-white shadow-lg rounded-xl overflow-hidden hidden">
        <div class="p-4 border-b border-gray-200 bg-red-100 text-red-700">⚠ Urgent Notification: Patient critical</div>
        <div class="p-4 border-b border-gray-200 bg-green-100 text-green-700">🟢 New Appointment Request</div>
        <div class="p-4 bg-blue-100 text-blue-700">💙 Patient Checked In</div>
      </div>
    </div>

    <!-- Profile Dropdown -->
    <div class="relative">
      <button id="profileBtn" class="flex items-center gap-2 bg-gray-100 p-2 rounded-full hover:bg-gray-200 transition">
        <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($doctor_name); ?></span>
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
</script>


  <!-- DASHBOARD GREETING CARD -->
  <section class="max-w-6xl mx-auto mt-6">
    <div class="bg-gradient-to-r from-green-200 to-green-400 p-6 rounded-2xl shadow mb-6">
      <h2 class="text-2xl font-bold mb-2">Good Morning, <?php echo htmlspecialchars($doctor_name); ?></h2>
      <p class="text-gray-700">You have <span class="font-semibold text-green-700"><?php echo $appointment_count; ?></span> appointments scheduled for today.</p>
    </div>

    <!-- Dashboard Stats Cards -->
    <div class="grid md:grid-cols-3 gap-6 mb-6">
      <!-- Today Appointments -->
      <div class="bg-white p-4 rounded-xl shadow flex justify-between items-center border-l-4 border-green-500">
        <div>
          <p class="font-semibold text-gray-800">Today Appointments</p>
          <p class="text-gray-500 text-sm">Next appointments overview</p>
        </div>
        <div class="text-right">
          <p class="text-xl font-bold text-green-700"><?php echo $appointment_count; ?></p>
          <!-- example patient images -->
          <div class="flex -space-x-2 mt-1">
            <img class="w-6 h-6 rounded-full border-2 border-white" src="https://i.pravatar.cc/32?img=1" alt="">
            <img class="w-6 h-6 rounded-full border-2 border-white" src="https://i.pravatar.cc/32?img=2" alt="">
            <img class="w-6 h-6 rounded-full border-2 border-white" src="https://i.pravatar.cc/32?img=3" alt="">
          </div>
        </div>
      </div>

      <!-- Pending Reports -->
      <div class="bg-white p-4 rounded-xl shadow flex justify-between items-center border-l-4 border-yellow-500">
        <div>
          <p class="font-semibold text-gray-800">Pending Reports</p>
          <p class="text-gray-500 text-sm">Reports to review today</p>
        </div>
        <div class="text-right">
          <p class="text-xl font-bold text-yellow-600">3</p>
        </div>
      </div>

      <!-- Prescriptions Today -->
      <div class="bg-white p-4 rounded-xl shadow flex justify-between items-center border-l-4 border-blue-500">
        <div>
          <p class="font-semibold text-gray-800">Prescriptions Today</p>
          <p class="text-gray-500 text-sm">Medicines prescribed</p>
        </div>
        <div class="text-right">
          <p class="text-xl font-bold text-blue-600">5</p>
        </div>
      </div>
    </div>

    <!-- Today's Schedule Table -->
    <div class="bg-white rounded-xl shadow p-4 mb-6">
      <h3 class="font-bold text-lg mb-3">Today's Schedule</h3>
      <?php if ($appointment_count > 0): ?>
      <div class="grid grid-cols-6 gap-2 font-medium text-gray-700 border-b pb-2 mb-2">
        <span>Time</span>
        <span>Patient</span>
        <span>Appointment Type</span>
        <span>Status</span>
        <span>Doctor</span>
        <span>Action</span>
      </div>
      <!-- Display appointments from database -->
      <?php foreach ($appointments as $apt): ?>
      <div class="grid grid-cols-6 gap-2 items-center py-2 border-b">
        <span><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></span>
        <span><?php echo htmlspecialchars($apt['patient_name']); ?></span>
        <span><?php echo htmlspecialchars($apt['reason_for_visit']); ?></span>
        <span class="<?php echo $apt['status'] === 'confirmed' ? 'text-green-600' : ($apt['status'] === 'pending' ? 'text-yellow-600' : 'text-gray-600'); ?>">
          <?php echo ucfirst($apt['status']); ?>
        </span>
        <span><?php echo htmlspecialchars($doctor_name); ?></span>
        <button class="text-blue-600 hover:underline">View</button>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="text-center py-6 text-gray-500">
        <p>No appointments scheduled for today.</p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Prescribe Medicine Form -->
    <div class="bg-white rounded-xl shadow p-6 mb-6">
      <h3 class="font-bold text-lg mb-4">Prescribe Medicine</h3>
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
    <div class="bg-white rounded-xl shadow p-6 mb-6">
      <h3 class="font-bold text-lg mb-4">Suggest Medical Tests</h3>
      <form class="grid gap-4 md:grid-cols-3">
        <input type="text" placeholder="Patient Name" class="border p-2 rounded focus:ring-2 focus:ring-blue-400">
        <select class="border p-2 rounded focus:ring-2 focus:ring-blue-400">
          <option>Blood Test</option>
          <option>X-Ray</option>
          <option>MRI</option>
          <option>CT Scan</option>
          <option>ECG</option>
          <option>Ultrasound</option>
        </select>
        <select class="border p-2 rounded focus:ring-2 focus:ring-blue-400">
          <option>Normal</option>
          <option>Urgent</option>
          <option>Critical</option>
        </select>
        <textarea placeholder="Notes" class="border p-2 rounded col-span-3 focus:ring-2 focus:ring-green-400"></textarea>
        <button class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition col-span-3">Request Test</button>
      </form>
    </div>

    <!-- Quick Actions Section -->
    <div class="bg-gradient-to-r from-green-200 to-green-400 p-6 rounded-xl shadow mb-6">
      <h3 class="font-bold text-lg mb-4 text-gray-800">Quick Actions</h3>
      <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded shadow text-center">Action 1</div>
        <div class="bg-white p-4 rounded shadow text-center">Action 2</div>
        <div class="bg-white p-4 rounded shadow text-center">Action 3</div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gradient-to-r from-green-700 to-green-500 text-white p-6 mt-12">
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
