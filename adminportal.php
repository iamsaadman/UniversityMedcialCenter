<?php
session_start();

/* TEMP admin session (REMOVE after login system is ready) */
$_SESSION['role'] = $_SESSION['role'] ?? 'admin';
$_SESSION['user_id'] = $_SESSION['user_id'] ?? 1;

if ($_SESSION['role'] !== 'admin') {
    die("Access denied");
}
$host = "localhost";
$db   = "university_medical_center";
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    die("Database connection failed");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$_POST['role'], $_POST['user_id']]);
    header("Location: adminportal.php");
    exit;
}

$stmt = $pdo->query("SELECT id, fullname, email, role FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

/* ADD USER (DOCTOR / ADMIN) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $institution_id = trim($_POST['institution_id']);
    $role = $_POST['role'];
    $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Prevent duplicate email / institution_id
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR institution_id = ?");
    $check->execute([$email, $institution_id]);

    if ($check->rowCount() === 0) {
        $stmt = $pdo->prepare("
            INSERT INTO users (fullname, email, institution_id, role, password_hash)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $fullname,
            $email,
            $institution_id,
            $role,
            $password_hash
        ]);
    }

    header("Location: adminportal.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal | Health Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

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

  <!-- ADMIN DASHBOARD HEADER
  <section class="bg-gradient-to-r from-red-700 to-red-500 text-white p-8 rounded-b-3xl shadow">
    <h2 class="text-2xl font-bold">Admin Dashboard</h2>
    <p class="mt-2">Manage users, content, and system settings efficiently</p>
  </section> -->

  <!-- ADD USER -->
<section class="p-6">
  <div class="bg-white p-6 rounded-xl shadow mb-6 max-w-full">
    <h2 class="text-xl font-semibold mb-4">Add Doctor / Admin</h2>

    <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full">
      <input type="hidden" name="add_user">

      <input required name="fullname" placeholder="Full Name"
        class="border p-2 rounded w-full">

      <input required name="email" type="email" placeholder="Email"
        class="border p-2 rounded w-full">

      <input required name="institution_id" placeholder="Institution ID"
        class="border p-2 rounded w-full">

      <select name="role" class="border p-2 rounded w-full">
        <option value="doctor">Doctor</option>
        <option value="admin">Admin</option>
      </select>

      <input required name="password" type="password" placeholder="Password"
        class="border p-2 rounded w-full">

      <button
        class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded md:col-span-3 w-full">
        Add User
      </button>
    </form>
  </div>
</section>



  <!-- MANAGE USERS -->
  <section class="p-6">
  <div class="bg-white p-6 rounded-xl shadow mb-6">
    <h2 class="text-xl font-semibold mb-4">Manage Users</h2>

    <table class="w-full border-collapse">
      <thead class="bg-red-600 text-white">
        <tr>
          <th class="p-3 text-left">Name</th>
          <th class="p-3 text-left">Role</th>
          <th class="p-3 text-left">Email</th>
          <th class="p-3 text-left">Status</th>
          <th class="p-3 text-left">Action</th>
        </tr>
      </thead>

      <tbody>
      <?php foreach ($users as $user): ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="p-3"><?= htmlspecialchars($user['fullname']) ?></td>

          <td class="p-3">
            <form method="POST">
              <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
              <select name="role"
                onchange="this.form.submit()"
                class="border rounded px-2 py-1">
                <option value="student" <?= $user['role']=='student'?'selected':'' ?>>Student</option>
                <option value="doctor" <?= $user['role']=='doctor'?'selected':'' ?>>Doctor</option>
                <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
              </select>
            </form>
          </td>

          <td class="p-3"><?= htmlspecialchars($user['email']) ?></td>

          <td class="p-3 text-green-600 font-semibold">Active</td>

          <td class="p-3">
            <?php if ($user['id'] != $_SESSION['user_id']): ?>
              <a href="?delete=<?= $user['id'] ?>"
                onclick="return confirm('Delete user?')"
                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                Delete
              </a>
            <?php else: ?>
              <span class="text-gray-400">Current User</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

  <!-- FAQ MANAGER
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
  </section> -->

</body>
</html>
