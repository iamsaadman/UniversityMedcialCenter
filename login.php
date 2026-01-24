<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Health Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-emerald-800 flex items-center justify-center py-16 px-4">

  <!-- LOGIN FORM CARD -->
  <div class="bg-white/95 backdrop-blur rounded-3xl shadow-2xl w-full max-w-xl p-10 border border-white/30">

    <!-- Header -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-emerald-500 shadow-lg mb-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 21s-7.5-4.8-10-9.6C.6 7.2 3.1 3 7.2 3c2.1 0 3.6 1.2 4.8 2.7C13.2 4.2 14.7 3 16.8 3c4.1 0 6.6 4.2 5.2 8.4C19.5 16.2 12 21 12 21z"/>
        </svg>
      </div>
      <h2 class="text-3xl font-bold text-slate-900">Welcome back</h2>
      <p class="text-slate-500 text-sm mt-1">Sign in to continue to your Health Portal</p>
    </div>

    <!-- FORM -->
    <?php if (isset($_GET['signup']) && $_GET['signup'] === 'success'): ?>
      <div class="mb-4 p-3 rounded-xl bg-green-100 text-green-700 text-sm">Account created successfully. Please sign in.</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
      <div class="mb-4 p-3 rounded-xl bg-red-100 text-red-700 text-sm">Sign-in error. Please check your credentials.</div>
    <?php endif; ?>
    <form action="login_process.php" method="POST" class="space-y-5">

      <!-- EMAIL -->
      <div>
        <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" name="email" id="email" placeholder="Enter your email" required
               class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500">
      </div>

      <!-- PASSWORD -->
      <div>
        <label for="password" class="block text-gray-700 font-medium mb-1">Password</label>
        <input type="password" name="password" id="password" placeholder="Enter your password" required
               class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500">
      </div>

      <!-- LOGIN AS TOGGLE -->
      <div>
        <label class="block text-gray-700 font-medium mb-2">Login as</label>
        <div class="bg-slate-100 rounded-xl p-1 flex gap-1" id="roleToggle">
          <button type="button" data-role="student" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-white shadow">Student</button>
          <button type="button" data-role="doctor" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-white/80 transition">Doctor</button>
        </div>
        <input type="hidden" name="role" id="role" value="student">
      </div>

      <!-- REMEMBER + FORGOT -->
      <div class="flex items-center justify-between text-sm text-gray-600">
        <label class="flex items-center gap-2">
          <input type="checkbox" name="remember" class="accent-blue-500"> Remember me
        </label>
        <a href="forgot_password.php" class="text-blue-600 hover:underline">Forgot Password?</a>
      </div>

      <!-- SIGN IN BUTTON -->
      <button type="submit"
          class="w-full bg-gradient-to-r from-blue-600 to-emerald-500 text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl hover:translate-y-[-1px] transition transform">
        Sign In
      </button>
    </form>

    <!-- SIGN UP & BACK -->
    <div class="mt-6 text-center text-sm text-gray-600 space-y-2">
      <p>Don't have an account? <a href="signup.php" class="text-blue-600 font-semibold hover:underline">Sign Up</a></p>
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
