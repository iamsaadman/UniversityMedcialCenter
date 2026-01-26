<?php
session_start();
require_once __DIR__ . '/includes/dp.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

// Fetch available doctors from users table where role is 'doctor'
$doctorsResult = $mysqli->query('
    SELECT id, fullname 
    FROM users 
    WHERE role = \'doctor\' 
    ORDER BY fullname
');
$doctors = [];
if ($doctorsResult) {
    while ($row = $doctorsResult->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Get success/error messages
$message = '';
$messageType = '';
if (isset($_GET['success']) && $_GET['success'] === '1') {
    $message = 'Appointment booked successfully!';
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $error = $_GET['error'];
    if ($error === 'invalid') {
        $message = 'Please fill all required fields correctly.';
    } elseif ($error === 'past_date') {
        $message = 'Cannot book appointments for past dates.';
    } elseif ($error === 'db') {
        $message = 'Database error. Please try again later.';
    } else {
        $message = 'An error occurred. Please try again.';
    }
    $messageType = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Appointment | Health Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

<!-- EMERGENCY BUTTON - Fixed position bottom left -->
<button id="emergencyBtn" class="fixed bottom-6 left-6 z-50 w-16 h-16 rounded-full bg-red-600 hover:bg-red-700 shadow-lg hover:shadow-xl transition transform hover:scale-110 flex items-center justify-center animate-pulse" title="Emergency Services">
  <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="white" viewBox="0 0 24 24">
    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
  </svg>
</button>

<!-- EMERGENCY MODAL -->
<div id="emergencyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center">
  <div class="bg-white rounded-3xl p-8 shadow-2xl w-full max-w-sm mx-4">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-red-600">Emergency Services</h2>
      <button id="closeModal" class="text-gray-600 hover:text-gray-800 text-2xl">&times;</button>
    </div>
    
    <div class="space-y-4">
      <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded">
        <p class="text-gray-700 font-semibold mb-2">Campus Emergency:</p>
        <a href="tel:333" class="text-red-600 hover:text-red-700 font-bold text-lg">📞 333</a>
      </div>
      
      <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded">
        <p class="text-gray-700 font-semibold mb-2">Crisis Hotline:</p>
        <a href="tel:999" class="text-red-600 hover:text-red-700 font-bold text-lg">📞 999</a>
      </div>
    </div>
    
    <button id="closeModalBtn" class="w-full mt-6 bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg font-semibold">Close</button>
  </div>
</div>

<script>
  const emergencyBtn = document.getElementById('emergencyBtn');
  const emergencyModal = document.getElementById('emergencyModal');
  const closeModal = document.getElementById('closeModal');
  const closeModalBtn = document.getElementById('closeModalBtn');
  
  emergencyBtn.addEventListener('click', () => {
    emergencyModal.classList.remove('hidden');
  });
  
  closeModal.addEventListener('click', () => {
    emergencyModal.classList.add('hidden');
  });
  
  closeModalBtn.addEventListener('click', () => {
    emergencyModal.classList.add('hidden');
  });
  
  emergencyModal.addEventListener('click', (e) => {
    if (e.target === emergencyModal) {
      emergencyModal.classList.add('hidden');
    }
  });
</script>

<!-- NAVBAR -->
<nav class="bg-blue-900 shadow px-6 py-4 flex justify-between items-center">
  <div class="flex items-center gap-2">
    <!-- Blue Heart Logo -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
      <path d="M12 21s-7.5-4.8-10-9.6C.6 7.2 3.1 3 7.2 3c2.1 0 3.6 1.2 4.8 2.7C13.2 4.2 14.7 3 16.8 3c4.1 0 6.6 4.2 5.2 8.4C19.5 16.2 12 21 12 21z"/>
    </svg>
    <span class="font-bold text-xl text-white">New Appointment</span>
  </div>
  <!-- Back to Student Portal -->
  <a href="studentportal.php" class="text-white hover:text-blue-300">← Back to Home</a>
</nav>

<!-- MAIN CONTENT -->
<div class="p-6">

  <!-- Success/Error Message -->
  <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-xl <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
      <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <!-- Header Card -->
  <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-2xl p-6 shadow-xl mb-6">
    <h2 class="text-2xl font-bold mb-2">Schedule Your New Appointment</h2>
    <p>Select a date and time that works best for you.</p>
  </div>

  <!-- Appointment Form -->
  <form action="appointment_process.php" method="POST" class="space-y-6" id="appointmentForm">

    <!-- Auto-selected Doctor -->
    <?php if (!empty($doctors)): ?>
      <div class="bg-blue-50 rounded-2xl p-6 shadow-md border-l-4 border-blue-600">
        <p class="text-gray-700"><strong>Assigned Doctor:</strong> <?php echo htmlspecialchars($doctors[0]['fullname']); ?></p>
        <input type="hidden" name="doctor_id" value="<?php echo (int)$doctors[0]['id']; ?>">
      </div>
    <?php else: ?>
      <div class="bg-red-50 rounded-2xl p-6 shadow-md border-l-4 border-red-600">
        <p class="text-red-700"><strong>No doctors available at the moment.</strong></p>
      </div>
    <?php endif; ?>

    <!-- Step 1: Select Date -->
    <div class="bg-white rounded-2xl p-6 shadow-md">
      <h3 class="font-semibold text-gray-800 mb-3">Step 1: Select Date</h3>
      <input type="date" name="appointment_date" id="appointmentDate" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500" required>
      <p class="text-gray-500 text-sm mt-2">Select a date at least 1 day from now</p>
    </div>

    <!-- Step 2: Select Time -->
    <div class="bg-white rounded-2xl p-6 shadow-md">
      <h3 class="font-semibold text-gray-800 mb-3">Step 2: Select Time</h3>
      <select name="appointment_time" id="selectedTime" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500" required>
        <option value="">-- Select a time slot --</option>
      </select>
    </div>

    <!-- Appointment Summary -->
    <div class="bg-white rounded-2xl p-6 shadow-md">
      <h3 class="font-semibold text-gray-800 mb-3">Appointment Summary</h3>
      
      <div class="mb-3">
        <label class="block text-gray-700 mb-1 font-semibold">Reason for Visit <span class="text-red-500">*</span></label>
        <input type="text" name="reason_for_visit" placeholder="Describe your reason for the appointment" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500" maxlength="255" required>
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Additional Notes (Optional)</label>
        <textarea name="notes" placeholder="Any additional information for the doctor" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500" maxlength="1000"></textarea>
      </div>

      <!-- Your Selection Card -->
      <div class="bg-gray-100 p-4 rounded-lg mb-4">
        <p><strong>Doctor:</strong> <span id="summaryDoctor">Not Selected</span></p>
        <p><strong>Date:</strong> <span id="summaryDate">Not Selected</span></p>
        <p><strong>Time:</strong> <span id="summaryTime">Not Selected</span></p>
      </div>

      <!-- Confirm Button -->
      <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-xl font-semibold hover:bg-blue-700 transition">Confirm Appointment</button>
    </div>

  </form>

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

<script>
  // Get doctor info from database result
  const doctors = <?php echo json_encode($doctors); ?>;
  
  // Generate time slots
  function generateTimeSlots() {
    const startHour = 9;
    const endHour = 16;
    const minutes = ['00', '30'];
    const select = document.getElementById('selectedTime');
    
    for (let h = startHour; h <= endHour; h++) {
      for (let m of minutes) {
        const time = `${String(h).padStart(2, '0')}:${m}`;
        const option = document.createElement('option');
        option.value = time;
        option.textContent = time;
        select.appendChild(option);
      }
    }
  }

  // Update summary display
  function updateSummary() {
    // Doctor (now auto-selected)
    const doctorHidden = document.querySelector('input[name="doctor_id"]');
    if (doctorHidden && doctorHidden.value) {
      const doctor = doctors.find(d => d.id == doctorHidden.value);
      document.getElementById('summaryDoctor').textContent = doctor ? doctor.fullname : 'Not Selected';
    } else {
      document.getElementById('summaryDoctor').textContent = 'Not Selected';
    }
    
    // Date
    const dateValue = document.getElementById('appointmentDate').value;
    if (dateValue) {
      const dateObj = new Date(dateValue);
      document.getElementById('summaryDate').textContent = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    } else {
      document.getElementById('summaryDate').textContent = 'Not Selected';
    }
    
    // Time
    const timeValue = document.getElementById('selectedTime').value;
    document.getElementById('summaryTime').textContent = timeValue || 'Not Selected';
  }

  // Set minimum date to tomorrow
  function setMinDate() {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    document.getElementById('appointmentDate').setAttribute('min', minDate);
  }

  // Event listeners
  document.addEventListener('DOMContentLoaded', () => {
    generateTimeSlots();
    setMinDate();
    updateSummary();
    
    document.getElementById('appointmentDate').addEventListener('change', updateSummary);
    document.getElementById('selectedTime').addEventListener('change', updateSummary);
  });

  // Form validation before submit
  document.getElementById('appointmentForm').addEventListener('submit', (e) => {
    const appointmentDate = document.getElementById('appointmentDate').value;
    const appointmentTime = document.getElementById('selectedTime').value;
    const reasonForVisit = document.querySelector('input[name="reason_for_visit"]').value;

    if (!appointmentDate || !appointmentTime || !reasonForVisit.trim()) {
      e.preventDefault();
      alert('Please fill in all required fields.');
    }
  });
</script>

</body>
</html>
