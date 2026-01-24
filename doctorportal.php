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

    <!-- Calendar Section -->
    <div class="bg-white rounded-xl shadow p-4 mb-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="font-bold text-base">Appointment Calendar</h3>
        <div class="flex gap-1">
          <a href="?month=<?php echo ($current_month > 1 ? $current_month - 1 : 12); ?>&year=<?php echo ($current_month > 1 ? $current_year : $current_year - 1); ?>" 
             class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm">← Prev</a>
          <span class="px-2 py-1 font-semibold text-sm"><?php echo "$month_name $current_year"; ?></span>
          <a href="?month=<?php echo ($current_month < 12 ? $current_month + 1 : 1); ?>&year=<?php echo ($current_month < 12 ? $current_year : $current_year + 1); ?>" 
             class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm">Next →</a>
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="grid grid-cols-7 gap-1">
        <!-- Day headers -->
        <div class="font-bold text-center py-1 bg-gray-100 rounded text-xs">Sun</div>
        <div class="font-bold text-center py-1 bg-gray-100 rounded text-xs">Mon</div>
        <div class="font-bold text-center py-1 bg-gray-100 rounded text-xs">Tue</div>
        <div class="font-bold text-center py-1 bg-gray-100 rounded text-xs">Wed</div>
        <div class="font-bold text-center py-1 bg-gray-100 rounded text-xs">Thu</div>
        <div class="font-bold text-center py-1 bg-gray-100 rounded text-xs">Fri</div>
        <div class="font-bold text-center py-1 bg-gray-100 rounded text-xs">Sat</div>

        <!-- Empty cells for days before month starts -->
        <?php for ($i = 0; $i < $first_day; $i++): ?>
          <div class="p-1 bg-gray-50 rounded min-h-16"></div>
        <?php endfor; ?>

        <!-- Days of the month -->
        <?php for ($day = 1; $day <= $days_in_month; $day++): 
          $date_str = sprintf("%04d-%02d-%02d", $current_year, $current_month, $day);
          $today_str = date('Y-m-d');
          $is_today = ($date_str === $today_str);
          $day_appointments = isset($appointments_by_date[$date_str]) ? $appointments_by_date[$date_str] : [];
          $apt_count = count($day_appointments);
        ?>
          <div class="p-1 border rounded min-h-16 <?php echo $is_today ? 'bg-green-50 border-green-400 border-2' : 'bg-white'; ?> hover:shadow-md transition">
            <!-- Date number -->
            <div class="font-bold text-xs mb-1 <?php echo $is_today ? 'text-green-700' : 'text-gray-700'; ?>">
              <?php echo $day; ?>
            </div>

            <!-- Appointments for this day -->
            <div class="space-y-0.5">
              <?php if ($apt_count > 0): ?>
                <?php foreach ($day_appointments as $apt): ?>
                  <div class="text-xs bg-blue-100 text-blue-800 p-0.5 rounded cursor-pointer hover:bg-blue-200" 
                       title="<?php echo htmlspecialchars($apt['patient_name']); ?> - <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>">
                    <div class="font-semibold truncate text-xs"><?php echo htmlspecialchars(substr($apt['patient_name'], 0, 9)); ?></div>
                    <div class="text-xs leading-none"><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="text-xs text-gray-400">—</div>
              <?php endif; ?>
            </div>
          </div>
        <?php endfor; ?>
      </div>

      <!-- Legend -->
      <div class="mt-3 pt-2 border-t flex gap-4 text-xs">
        <div class="flex items-center gap-1">
          <div class="w-3 h-3 bg-blue-100 border border-blue-400 rounded"></div>
          <span>Appointment</span>
        </div>
        <div class="flex items-center gap-1">
          <div class="w-3 h-3 bg-green-50 border-2 border-green-400 rounded"></div>
          <span>Today</span>
        </div>
      </div>
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
