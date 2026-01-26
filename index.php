<?php include 'includes/header.php'; ?>

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

<!-- HERO SECTION -->
<section class="bg-gradient-to-r from-blue-600 via-blue-500 to-green-500">
  <div class="max-w-7xl mx-auto px-6 py-20 flex flex-col md:flex-row items-center gap-12">

    <!-- LEFT CONTENT -->
    <div class="md:w-1/2 text-white">
      <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">
        Your Health,<br>Our Priority
      </h1>

      <p class="mt-6 text-lg text-blue-100">
        Comprehensive healthcare services for students by dedicated medical professionals.
      </p>

      <div class="mt-8 flex flex-wrap gap-4">
        <a href="appointment.php"
           class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-semibold shadow">
          Book Appointment
        </a>

        <a href="mental-health.php"
           class="bg-white text-blue-600 hover:bg-blue-50 px-6 py-3 rounded-xl font-semibold shadow">
          Mental Health Support
        </a>
      </div>
    </div>

    <!-- RIGHT IMAGE -->
    <div class="md:w-1/2">
      <img src="assets/images/home.jpg"
           alt="Student Healthcare"
           class="rounded-3xl shadow-xl w-full">
    </div>

  </div>
</section>
<!-- QUICK ACCESS (PREMIUM CARDS) -->
<section class="max-w-7xl mx-auto px-6 py-20">
  <div class="grid md:grid-cols-3 gap-8">

    <!-- BOOK APPOINTMENT -->
    <div class="group bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
      <div class="w-14 h-14 bg-orange-500 text-white rounded-2xl flex items-center justify-center text-2xl shadow-lg">
        📅
      </div>
      <h3 class="mt-6 text-xl font-semibold text-gray-800">
        Book Appointment
      </h3>
      <p class="mt-2 text-gray-600">
        Schedule visits with campus doctors at your convenience.
      </p>
      <span class="inline-block mt-6 text-orange-600 font-semibold group-hover:underline">
        Get Started →
      </span>
    </div>

    <!-- FIND DOCTORS -->
    <div class="group bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
      <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-2xl shadow-lg">
        🩺
      </div>
      <h3 class="mt-6 text-xl font-semibold text-gray-800">
        Find Doctors
      </h3>
      <p class="mt-2 text-gray-600">
        Browse and connect with trusted campus medical professionals.
      </p>
      <span class="inline-block mt-6 text-green-600 font-semibold group-hover:underline">
        View Doctors →
      </span>
    </div>

    <!-- EMERGENCY HELP -->
    <div class="group bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
      <div class="w-14 h-14 bg-red-600 text-white rounded-2xl flex items-center justify-center text-2xl shadow-lg">
        📞
      </div>
      <h3 class="mt-6 text-xl font-semibold text-gray-800">
        Emergency Help
      </h3>
      <p class="mt-2 text-gray-600">
        Immediate emergency support and campus ambulance access.
      </p>
      <span class="inline-block mt-6 text-red-600 font-semibold group-hover:underline">
        Get Help →
      </span>
    </div>

  </div>
</section><!-- HEALTH NOTICES (NOTICE BOARD STYLE) -->
<section class="bg-blue-50 py-20">
  <div class="max-w-7xl mx-auto px-6">
    
    <h2 class="text-3xl font-bold text-blue-800 mb-12 text-center">
      Health Notices & Announcements
    </h2>

    <div class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">

      <!-- NOTICE 1 -->
      <div class="bg-white rounded-xl p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-semibold text-gray-800 text-lg">Flu Vaccination Drive</h4>
          <span class="text-xs text-gray-500">Jan 15, 2026</span>
        </div>
        <p class="text-gray-600 text-sm">
          Free flu shots available for all students this week at the campus clinic. Get vaccinated early to stay healthy.
        </p>
        <span class="text-blue-500 text-xs mt-2 inline-block">Category: Clinic Update</span>
      </div>

      <!-- NOTICE 2 -->
      <div class="bg-white rounded-xl p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-semibold text-gray-800 text-lg">Extended Hours</h4>
          <span class="text-xs text-gray-500">Jan 12, 2026</span>
        </div>
        <p class="text-gray-600 text-sm">
          Clinic hours extended during exam season from 8 AM to 10 PM daily to accommodate student visits.
        </p>
        <span class="text-green-500 text-xs mt-2 inline-block">Category: Schedule</span>
      </div>

      <!-- NOTICE 3 -->
      <div class="bg-white rounded-xl p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-semibold text-gray-800 text-lg">Mental Health Workshop</h4>
          <span class="text-xs text-gray-500">Jan 18, 2026</span>
        </div>
        <p class="text-gray-600 text-sm">
          Join our stress management & wellness sessions led by professional counselors. Open for all students.
        </p>
        <span class="text-purple-500 text-xs mt-2 inline-block">Category: Workshop</span>
      </div>

      <!-- NOTICE 4 -->
      <div class="bg-white rounded-xl p-6 border-l-4 border-orange-500">
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-semibold text-gray-800 text-lg">Wellness Fair</h4>
          <span class="text-xs text-gray-500">Jan 20, 2026</span>
        </div>
        <p class="text-gray-600 text-sm">
          Participate in fitness, nutrition, and health awareness activities organized on campus. Free entry for all students.
        </p>
        <span class="text-orange-500 text-xs mt-2 inline-block">Category: Event</span>
      </div>

    </div>
  </div>
</section>


<?php include 'includes/footer.php'; ?>
