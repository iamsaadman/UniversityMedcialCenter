<?php include 'includes/header.php'; ?>

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
