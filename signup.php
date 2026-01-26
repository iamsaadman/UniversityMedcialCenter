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
      <div class="mb-4 p-3 rounded-xl <?= $err === 'exists' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-700' ?> text-sm">
        <?php if ($err === 'invalid'): ?>
          Please fill all fields correctly. Password must be at least 8 characters and accept terms.
        <?php elseif ($err === 'exists'): ?>
          An account with this email or ID already exists.
        <?php else: ?>
          Signup failed. Please retry.
        <?php endif; ?>
      </div>
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

      <!-- STUDENT ID -->
      <div>
        <label for="id" class="block text-gray-700 font-medium mb-1">Student ID</label>
        <input type="text" name="id" id="id" placeholder="Enter your student ID" required
               class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-500">
      </div>

      <!-- PASSWORD -->
      <div>
        <label for="password" class="block text-gray-700 font-medium mb-1">Password</label>
        <input type="password" name="password" id="password" placeholder="Enter your password" required minlength="8"
               class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-500">
      </div>

      <!-- HIDDEN ROLE (STUDENT ONLY) -->
      <input type="hidden" name="role" value="student">

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

</body>
</html>
