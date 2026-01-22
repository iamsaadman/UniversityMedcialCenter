<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal | Health Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- NAVBAR -->
  <nav class="bg-white shadow flex items-center justify-between px-6 py-4">
    <!-- Logo + Title -->
    <div class="flex items-center gap-3">
      <!-- Hollow red heart -->
      <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21C12 21 4 14.8 4 9.5 4 6 6.5 4 9 4c1.5 0 3 1 3 3 0-2 1.5-3 3-3 2.5 0 5 2 5 5 0 5.3-8 11.5-8 11.5z"/>
      </svg>
      <h1 class="text-lg font-bold text-gray-800">Admin Portal</h1>
    </div>

    <!-- Profile Dropdown -->
    <div class="relative">
      <button id="profileBtn" class="flex items-center gap-2 bg-gray-100 p-2 rounded-full hover:bg-gray-200 transition">
        <span class="font-semibold text-gray-700">Admin</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <!-- Dropdown Menu -->
      <div id="profileDropdown" class="absolute right-0 mt-2 w-40 bg-white shadow-lg rounded-xl overflow-hidden hidden">
        <a href="edit_profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Edit Profile</a>
        <a href="login.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
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
    window.addEventListener('click', function(e){
      if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)){
        profileDropdown.classList.add('hidden');
      }
    });
  </script>

  <!-- ADMIN DASHBOARD HEADER -->
  <section class="bg-gradient-to-r from-red-700 to-red-500 text-white p-8 rounded-b-3xl shadow">
    <h2 class="text-2xl font-bold">Admin Dashboard</h2>
    <p class="mt-2">Manage users, content, and system settings efficiently</p>
  </section>

  <!-- DASHBOARD CARDS -->
  <section class="grid grid-cols-1 md:grid-cols-4 gap-6 p-6">
    <div class="bg-white shadow rounded-xl p-6 border-t-4 border-red-700 relative">
      <h3 class="text-gray-700 font-semibold">Total Students</h3>
      <span class="absolute top-2 right-4 text-red-700 font-bold text-lg">120</span>
    </div>
    <div class="bg-white shadow rounded-xl p-6 border-t-4 border-green-700 relative">
      <h3 class="text-gray-700 font-semibold">Total Doctors</h3>
      <span class="absolute top-2 right-4 text-green-700 font-bold text-lg">45</span>
    </div>
    <div class="bg-white shadow rounded-xl p-6 border-t-4 border-blue-700 relative">
      <h3 class="text-gray-700 font-semibold">This Month's Appointments</h3>
      <span class="absolute top-2 right-4 text-blue-700 font-bold text-lg">75</span>
    </div>
    <div class="bg-white shadow rounded-xl p-6 border-t-4 border-purple-700 relative">
      <h3 class="text-gray-700 font-semibold">Active Admins</h3>
      <span class="absolute top-2 right-4 text-purple-700 font-bold text-lg">3</span>
    </div>
  </section>

  <!-- MANAGE USERS -->
  <section class="p-6 bg-gray-50 rounded-xl shadow mb-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-semibold">Manage Users</h3>
      <div class="flex gap-3">
        <button class="bg-blue-700 text-white px-4 py-2 rounded-xl hover:bg-blue-800">Add Student</button>
        <button class="bg-green-700 text-white px-4 py-2 rounded-xl hover:bg-green-800">Add Doctor</button>
      </div>
    </div>
    <div class="mb-4">
      <input type="text" placeholder="Search users..." class="w-full p-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-red-700">
    </div>
    <table class="w-full table-auto border-collapse bg-white rounded-xl overflow-hidden shadow">
      <thead class="bg-red-600 text-white">
        <tr>
          <th class="p-3 text-left">Name</th>
          <th class="p-3 text-left">Role</th>
          <th class="p-3 text-left">Email</th>
          <th class="p-3 text-left">Status</th>
          <th class="p-3 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr class="border-b">
          <td class="p-3">John Doe</td>
          <td class="p-3">Student</td>
          <td class="p-3">johndoe@example.com</td>
          <td class="p-3 text-green-600 font-semibold">Active</td>
          <td class="p-3 flex gap-2">
            <button class="bg-yellow-600 px-2 py-1 rounded hover:bg-yellow-700 text-white">Edit</button>
            <button class="bg-red-600 px-2 py-1 rounded hover:bg-red-700 text-white">Delete</button>
          </td>
        </tr>
        <tr class="border-b">
          <td class="p-3">Dr. Smith</td>
          <td class="p-3">Doctor</td>
          <td class="p-3">drsmith@example.com</td>
          <td class="p-3 text-green-600 font-semibold">Active</td>
          <td class="p-3 flex gap-2">
            <button class="bg-yellow-600 px-2 py-1 rounded hover:bg-yellow-700 text-white">Edit</button>
            <button class="bg-red-600 px-2 py-1 rounded hover:bg-red-700 text-white">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </section>

  <!-- REPORTS & STATISTICS -->
  <section class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white p-6 shadow rounded-xl">
      <h4 class="text-gray-700 font-semibold">Monthly Appointments</h4>
      <p class="text-red-700 font-bold text-xl mt-2">75</p>
      <p class="text-gray-500 mt-1 text-sm">↑ 12% from last month</p>
    </div>
    <div class="bg-white p-6 shadow rounded-xl">
      <h4 class="text-gray-700 font-semibold">New Student Registration</h4>
      <p class="text-green-700 font-bold text-xl mt-2">45</p>
      <p class="text-gray-500 mt-1 text-sm">This semester</p>
    </div>
    <div class="bg-white p-6 shadow rounded-xl">
      <h4 class="text-gray-700 font-semibold">Prescription Issued</h4>
      <p class="text-blue-700 font-bold text-xl mt-2">30</p>
      <p class="text-gray-500 mt-1 text-sm">This week</p>
    </div>
    <div class="bg-white p-6 shadow rounded-xl">
      <h4 class="text-gray-700 font-semibold">Emergency Calls</h4>
      <p class="text-red-700 font-bold text-xl mt-2">12</p>
      <p class="text-gray-500 mt-1 text-sm">This week</p>
    </div>
  </section>

  <!-- FAQ MANAGER -->
  <section class="p-6 bg-gray-50 rounded-xl shadow mb-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-semibold">FAQ Manager</h3>
      <button class="bg-blue-700 text-white px-4 py-2 rounded-xl hover:bg-blue-800">Add New FAQ</button>
    </div>
    <div class="space-y-2">
      <div class="bg-white p-4 rounded-xl shadow">What is the procedure to book an appointment?</div>
      <div class="bg-white p-4 rounded-xl shadow">How to reset my password?</div>
      <div class="bg-white p-4 rounded-xl shadow">Where can I find emergency contacts?</div>
    </div>
  </section>

  <!-- EMERGENCY TIPS MANAGER -->
  <section class="p-6 bg-gray-50 rounded-xl shadow mb-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-semibold">Emergency Tips Manager</h3>
      <button class="bg-blue-700 text-white px-4 py-2 rounded-xl hover:bg-blue-800">Add New Guideline</button>
    </div>
    <div class="space-y-2">
      <div class="bg-white p-4 rounded-xl shadow flex justify-between">
        <span>Severe Injury Protocol</span>
        <button class="bg-yellow-600 px-2 py-1 rounded hover:bg-yellow-700 text-white">Edit</button>
      </div>
      <div class="bg-white p-4 rounded-xl shadow flex justify-between">
        <span>Mental Health Crisis</span>
        <button class="bg-yellow-600 px-2 py-1 rounded hover:bg-yellow-700 text-white">Edit</button>
      </div>
      <div class="bg-white p-4 rounded-xl shadow flex justify-between">
        <span>Non-Emergency Care</span>
        <button class="bg-yellow-600 px-2 py-1 rounded hover:bg-yellow-700 text-white">Edit</button>
      </div>
    </div>
  </section>

  <!-- SYSTEM SETTINGS -->
  <section class="p-6 mb-6">
    <div class="bg-gradient-to-r from-orange-700 to-orange-500 p-6 rounded-xl shadow text-white">
      <h3 class="text-xl font-semibold">System Settings</h3>
      <p class="mt-2">Configure platform settings and preferences here.</p>
    </div>
  </section>

</body>
</html>
