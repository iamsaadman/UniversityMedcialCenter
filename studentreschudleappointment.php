<?php
// Example doctors array
$doctors = [
    ['name' => 'Dr. John Smith', 'department' => 'Cardiology'],
    ['name' => 'Dr. Emily Adams', 'department' => 'Neurology'],
    ['name' => 'Dr. Mark Lee', 'department' => 'Dermatology'],
    ['name' => 'Dr. Susan Clark', 'department' => 'Pediatrics']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Appointment | Health Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

<!-- NAVBAR -->
<nav class="bg-blue-900 shadow px-6 py-4 flex justify-between items-center">
  <div class="flex items-center gap-2">
    <!-- Blue Heart Logo -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
      <path d="M12 21s-7.5-4.8-10-9.6C.6 7.2 3.1 3 7.2 3c2.1 0 3.6 1.2 4.8 2.7C13.2 4.2 14.7 3 16.8 3c4.1 0 6.6 4.2 5.2 8.4C19.5 16.2 12 21 12 21z"/>
    </svg>
    <span class="font-bold text-xl text-white">Book Appointment</span>
  </div>
  <a href="studentportal.php" class="text-white hover:text-blue-300">← Back to Home</a>
</nav>

<!-- MAIN CONTENT -->
<div class="p-6">

  <!-- Header Card -->
  <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-2xl p-6 shadow-xl mb-6">
    <h2 class="text-2xl font-bold mb-2">Schedule Your Appointment</h2>
    <p>Select a doctor, date, and time that works best for you.</p>
  </div>

  <!-- Step 1: Select Doctor -->
  <div class="bg-white rounded-2xl p-6 shadow-md mb-6">
    <h3 class="font-semibold text-gray-800 mb-3">Step 1: Select a Doctor</h3>
    <div class="space-y-3">
      <?php foreach ($doctors as $doctor): ?>
        <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-blue-50 cursor-pointer">
          <input type="radio" name="doctor" value="<?php echo $doctor['name']; ?>" class="accent-blue-500">
          <div>
            <p class="font-semibold"><?php echo $doctor['name']; ?></p>
            <p class="text-gray-500 text-sm"><?php echo $doctor['department']; ?></p>
          </div>
        </label>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Step 2: Select Date -->
  <div class="bg-white rounded-2xl p-6 shadow-md mb-6">
    <h3 class="font-semibold text-gray-800 mb-3">Step 2: Select Date</h3>
    <input type="date" name="appointment_date" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500">
  </div>

  <!-- Step 3: Select Time -->
  <div class="bg-white rounded-2xl p-6 shadow-md mb-6">
    <h3 class="font-semibold text-gray-800 mb-3">Step 3: Select Time</h3>
    <div class="grid grid-cols-3 md:grid-cols-4 gap-3">
      <?php
      $startHour = 9;
      $endHour = 16;
      $minutes = ['00', '30'];
      foreach(range($startHour, $endHour) as $h) {
          foreach($minutes as $m) {
              $time = sprintf("%02d:%s", $h, $m);
              echo "<button type='button' class='py-2 px-3 rounded-lg border border-blue-300 hover:bg-blue-50 text-gray-700'>$time</button>";
          }
      }
      ?>
    </div>
  </div>

  <!-- Appointment Summary -->
  <div class="bg-white rounded-2xl p-6 shadow-md mb-6">
    <h3 class="font-semibold text-gray-800 mb-3">Appointment Summary</h3>
    <div class="mb-3">
      <label class="block text-gray-700 mb-1">Reason for Visit</label>
      <input type="text" name="reason" placeholder="Describe your reason" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500">
    </div>
    <div class="mb-3">
      <label class="block text-gray-700 mb-1">Additional Notes (Optional)</label>
      <textarea name="notes" placeholder="Any additional notes" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500"></textarea>
    </div>

    <!-- Your Selection Card -->
    <div class="bg-gray-100 p-4 rounded-lg mb-3">
      <p><strong>Doctor:</strong> Not Selected</p>
      <p><strong>Date:</strong> Not Selected</p>
      <p><strong>Time:</strong> Not Selected</p>
    </div>

    <!-- Confirm Button -->
    <button class="bg-blue-600 text-white py-3 px-6 rounded-xl font-semibold hover:bg-blue-700 transition">Confirm Appointment</button>
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
