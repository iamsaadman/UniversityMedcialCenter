<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Health Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-600 via-blue-500 to-green-400 flex items-center justify-center py-16">

  <!-- LOGIN FORM CARD -->
  <div class="bg-white rounded-3xl shadow-xl w-full max-w-md p-10">

    <!-- Heart + Header -->
    <div class="text-center mb-8">
      <svg xmlns="http://www.w3.org/2000/svg"
           class="w-12 h-12 text-blue-600 mx-auto mb-2"
           fill="currentColor"
           viewBox="0 0 24 24">
        <path d="M12 21s-7.5-4.8-10-9.6C.6 7.2 3.1 3 7.2 3c2.1 0 3.6 1.2 4.8 2.7C13.2 4.2 14.7 3 16.8 3c4.1 0 6.6 4.2 5.2 8.4C19.5 16.2 12 21 12 21z"/>
      </svg>
      <h2 class="text-2xl font-bold text-gray-800">Sign in to access your Health Portal</h2>
    </div>

    <!-- FORM -->
    <form action="studentportal.php" method="POST" class="space-y-5">

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

      <!-- LOGIN AS -->
      <div>
        <label for="role" class="block text-gray-700 font-medium mb-1">Login As</label>
        <select name="role" id="role" required
                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500">
          <option value="" disabled selected>Select Role</option>
          <option value="student">Student</option>
          <option value="doctor">Doctor</option>
          <option value="admin">Admin</option>
        </select>
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
              class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold shadow hover:bg-blue-700 transition">
        Sign In
      </button>
    </form>

    <!-- SIGN UP & BACK -->
    <div class="mt-6 text-center text-sm text-gray-600 space-y-2">
      <p>Don't have an account? <a href="signup.php" class="text-blue-600 font-semibold hover:underline">Sign Up</a></p>
      <p><a href="index.php" class="text-gray-800 hover:underline">&larr; Back to Home</a></p>
    </div>

  </div>

</body>
</html>
