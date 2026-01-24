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
                  <div class="text-xs bg-blue-100 text-blue-800 p-0.5 rounded cursor-pointer hover:bg-blue-200 appointment-card" 
                       onclick="openAppointmentDetails(<?php echo $apt['id']; ?>)"
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
