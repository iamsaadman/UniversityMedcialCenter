<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up | Health Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center py-16 px-4 bg-gradient-to-br from-emerald-900 via-blue-900 to-slate-900">


  <!-- SIGNUP FORM CARD -->
  <div class="bg-white/95 backdrop-blur rounded-3xl shadow-2xl w-full max-w-xl p-10 border border-white/30">

    <!-- Heart + Header -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-blue-500 shadow-lg mb-3">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-8 h-8 text-white"
             fill="currentColor"
             viewBox="0 0 24 24">
          <path d="M12 21s-7.5-4.8-10-9.6C.6 7.2 3.1 3 7.2 3c2.1 0 3.6 1.2 4.8 2.7C13.2 4.2 14.7 3 16.8 3c4.1 0 6.6 4.2 5.2 8.4C19.5 16.2 12 21 12 21z"/>
        </svg>
      </div>
      <h2 class="text-3xl font-bold text-slate-900">Create your account</h2>
      <p class="text-slate-500 mt-1 text-sm">Join our healthcare community</p>
    </div>

    <!-- SIGNUP FORM -->
    <?php if (isset($_GET['error'])): ?>
      <?php $err = $_GET['error']; ?>
      <?php if ($err === 'invalid'): ?>
        <div class="mb-4 p-3 rounded-xl bg-red-100 text-red-700 text-sm">
          Please fill all fields correctly. Password must be at least 8 characters, accept terms, and choose a role.
        </div>
      <?php elseif ($err === 'exists'): ?>
        <div class="mb-4 p-3 rounded-xl bg-yellow-100 text-yellow-800 text-sm">
          An account with this email or ID already exists.
        </div>
      <?php elseif ($err === 'db'): ?>
        <div class="mb-4 p-3 rounded-xl bg-red-100 text-red-700 text-sm">
          Database error. Please try again later.
        </div>
      <?php elseif ($err === 'failed'): ?>
        <div class="mb-4 p-3 rounded-xl bg-red-100 text-red-700 text-sm">
          Signup failed unexpectedly. Please retry.
        </div>
      <?php else: ?>
        <div class="mb-4 p-3 rounded-xl bg-red-100 text-red-700 text-sm">Signup error. Please review your details.</div>
      <?php endif; ?>
    <?php endif; ?>
    <form action="signup_process.php" method="POST" class="space-y-5">

      <!-- FULL NAME -->
      <div>
        <label for="fullname" class="block text-gray-700 font-medium mb-1">Full Name</label>
        <input type="text" name="fullname" id="fullname" placeholder="Enter your full name" required
               class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-500">
      </div>

      <!-- EMAIL -->
      <div>
        <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" name="email" id="email" placeholder="Enter your email" required
               class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-500">
      </div>

      <!-- STUDENT / STAFF ID -->
      <div>
        <label for="id" class="block text-gray-700 font-medium mb-1">Student / Staff ID</label>
        <input type="text" name="id" id="id" placeholder="Enter your ID" required
               class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-500">
      </div>

      <!-- PASSWORD -->
      <div>
        <label for="password" class="block text-gray-700 font-medium mb-1">Password</label>
        <input type="password" name="password" id="password" placeholder="Enter your password" required minlength="8"
               class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-500">
      </div>

      <!-- REGISTER AS TOGGLE -->
      <div>
        <label class="block text-gray-700 font-medium mb-2">Register as</label>
        <div class="bg-slate-100 rounded-xl p-1 flex gap-1" id="roleToggle">
          <button type="button" data-role="student" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-white shadow">Student</button>
          <button type="button" data-role="doctor" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-white/80 transition">Doctor</button>
        </div>
        <input type="hidden" name="role" id="role" value="student">
      </div>

      <!-- TERMS & CONDITIONS -->
      <div class="flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox" name="terms" required class="accent-green-500">
        <label>I agree to the <a href="#" class="text-green-600 hover:underline">Terms of Service</a> and <a href="#" class="text-green-600 hover:underline">Privacy Policy</a></label>
      </div>

      <!-- CREATE ACCOUNT BUTTON -->
      <button type="submit"
          class="w-full bg-gradient-to-r from-emerald-600 to-blue-600 text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl hover:translate-y-[-1px] transition transform">
        Create Account
      </button>
    </form>

    <!-- SIGN IN & BACK TO HOME -->
    <div class="mt-6 text-center text-sm text-gray-600 space-y-2">
      <p>Already have an account? <a href="login.php" class="text-green-600 font-semibold hover:underline">Sign In</a></p>
      <p><a href="index.php" class="text-gray-800 hover:underline">&larr; Back to Home</a></p>
    </div>

  </div>

<script>
  const roleToggle = document.getElementById('roleToggle');
  const roleInput = document.getElementById('role');
  if (roleToggle && roleInput) {
    roleToggle.querySelectorAll('button').forEach(btn => {
      btn.addEventListener('click', () => {
        const selected = btn.dataset.role;
        roleInput.value = selected;
        roleToggle.querySelectorAll('button').forEach(b => {
          b.classList.remove('bg-white', 'shadow', 'text-slate-900');
          b.classList.add('text-slate-600');
        });
        btn.classList.add('bg-white', 'shadow');
        btn.classList.remove('text-slate-600');
        btn.classList.add('text-slate-900');
      });
    });
  }
</script>

</body>
</html>
