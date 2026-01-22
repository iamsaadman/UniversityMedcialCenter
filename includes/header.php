<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>University Medical Center</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Tailwind CDN (use build version later) -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-gray-800">

<!-- HEADER -->
<header class="w-full bg-white shadow-sm sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

    <!-- LEFT: LOGO + NAME -->
    <div class="flex items-center gap-3">
      <!-- Heart Icon -->
      <svg xmlns="http://www.w3.org/2000/svg"
           class="w-8 h-8 text-blue-600"
           fill="currentColor"
           viewBox="0 0 24 24">
        <path d="M12 21s-7.5-4.8-10-9.6C.6 7.2 3.1 3 7.2 3c2.1 0 3.6 1.2 4.8 2.7C13.2 4.2 14.7 3 16.8 3c4.1 0 6.6 4.2 5.2 8.4C19.5 16.2 12 21 12 21z"/>
      </svg>

      <span class="text-xl md:text-2xl font-bold text-blue-700">
        University Medical Center
      </span>
    </div>

    <!-- RIGHT: AUTH BUTTONS -->
    <div class="flex items-center gap-4">
      <a href="login.php"
         class="text-gray-600 hover:text-blue-600 font-medium">
        Login
      </a>

      <a href="signup.php"
         class="bg-blue-600 text-white px-5 py-2 rounded-xl font-semibold shadow hover:bg-blue-700 transition">
        Sign Up
      </a>
    </div>

  </div>
</header>
