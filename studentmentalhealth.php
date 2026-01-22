<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mental Health & Counseling | Health Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

<!-- NAVBAR -->
<nav class="bg-white shadow flex items-center justify-between px-6 py-4">
  <!-- Logo + Title -->
  <div class="flex items-center gap-3">
    <!-- Hollow heart SVG -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 21C12 21 4 14.8 4 9.5 4 6 6.5 4 9 4c1.5 0 3 1 3 3 0-2 1.5-3 3-3 2.5 0 5 2 5 5 0 5.3-8 11.5-8 11.5z"/>
    </svg>
    <h1 class="text-lg font-bold text-gray-800">Mental Health & Counseling</h1>
  </div>

  <!-- Back to Home Button -->
  <a href="studentportal.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
    &larr; Back to Home
  </a>
</nav>


<!-- HERO SECTION -->
<section class="bg-gradient-to-r from-purple-700 to-purple-800 text-white p-10 rounded-b-3xl">
  <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-8">
    <!-- Left -->
    <div class="md:w-1/2">
      <h2 class="text-3xl font-bold mb-4">Your Mental Health & Wellness Matters</h2>
      <p class="mb-6 text-lg">Free confidential counseling services for all students. We are here to support you through every challenge.</p>
      <div class="flex flex-wrap gap-4">
        <button class="bg-white text-purple-700 py-2 px-4 rounded-xl font-semibold hover:bg-gray-100 transition">Book Counseling Session</button>
        <button class="bg-purple-900 text-white flex items-center gap-2 py-2 px-4 rounded-xl font-semibold hover:bg-purple-700 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M3 6v12h18V6H3zm2 2h14v8H5V8zm8 1h2v2h-2v-2zm0 4h2v2h-2v-2z"/>
          </svg>
          Crisis Hotline 988
        </button>
      </div>
    </div>
    <!-- Right could be image or illustration -->
    <div class="md:w-1/2">
      <img src="https://cdn-icons-png.flaticon.com/512/3208/3208192.png" alt="Mental Health Illustration" class="w-full h-auto rounded-xl">
    </div>
  </div>
</section>

<!-- OUR SERVICES -->
<section class="max-w-6xl mx-auto p-6 mt-8">
  <h3 class="text-2xl font-bold mb-6">Our Services</h3>
  <div class="grid md:grid-cols-3 gap-6">
    <!-- Service 1 -->
    <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition border-l-4 border-purple-500">
      <h4 class="font-semibold mb-2">Individual Counseling</h4>
      <p class="text-gray-600 mb-4">One-to-one session with licensed therapists to address personal challenges, anxiety, and more.</p>
      <button class="bg-purple-600 text-white py-2 px-4 rounded-xl hover:bg-purple-700 transition">Schedule Session</button>
    </div>
    <!-- Service 2 -->
    <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition border-l-4 border-green-500">
      <h4 class="font-semibold mb-2">Group Therapy</h4>
      <p class="text-gray-600 mb-4">Supportive sessions with peers to explore issues collectively.</p>
      <button class="bg-green-600 text-white py-2 px-4 rounded-xl hover:bg-green-700 transition">Schedule Session</button>
    </div>
    <!-- Service 3 -->
    <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition border-l-4 border-blue-500">
      <h4 class="font-semibold mb-2">Stress Management</h4>
      <p class="text-gray-600 mb-4">Learn techniques to manage stress, improve focus, and maintain balance.</p>
      <button class="bg-blue-600 text-white py-2 px-4 rounded-xl hover:bg-blue-700 transition">Schedule Session</button>
    </div>
  </div>
</section>

<!-- UPCOMING WORKSHOPS -->
<section class="max-w-6xl mx-auto p-6 mt-12">
  <h3 class="text-2xl font-bold mb-6">Upcoming Workshops</h3>
  <div class="grid md:grid-cols-4 gap-6">
    <div class="bg-white p-4 rounded-xl shadow border-l-4 border-purple-500">
      <h4 class="font-semibold mb-1">Mindfulness Meditation</h4>
      <p class="text-gray-600 mb-2 text-sm">Learn techniques for relaxation and focus.</p>
      <button class="bg-purple-600 text-white py-1 px-3 rounded hover:bg-purple-700 transition text-sm">Register</button>
    </div>
    <div class="bg-white p-4 rounded-xl shadow border-l-4 border-blue-500">
      <h4 class="font-semibold mb-1">Managing Exam Anxiety</h4>
      <p class="text-gray-600 mb-2 text-sm">Practical tips to stay calm during exams.</p>
      <button class="bg-blue-600 text-white py-1 px-3 rounded hover:bg-blue-700 transition text-sm">Register</button>
    </div>
    <div class="bg-white p-4 rounded-xl shadow border-l-4 border-green-500">
      <h4 class="font-semibold mb-1">Building Healthy Relationships</h4>
      <p class="text-gray-600 mb-2 text-sm">Improve communication and connections.</p>
      <button class="bg-green-600 text-white py-1 px-3 rounded hover:bg-green-700 transition text-sm">Register</button>
    </div>
    <div class="bg-white p-4 rounded-xl shadow border-l-4 border-pink-500">
      <h4 class="font-semibold mb-1">Sleep Hygiene & Wellness</h4>
      <p class="text-gray-600 mb-2 text-sm">Tips for better sleep and recovery.</p>
      <button class="bg-pink-600 text-white py-1 px-3 rounded hover:bg-pink-700 transition text-sm">Register</button>
    </div>
  </div>
</section>

<!-- EMERGENCY / CRISIS SECTION -->
<section class="max-w-6xl mx-auto p-6 mt-12 bg-red-50 rounded-2xl">
  <h3 class="text-xl font-bold text-red-700 mb-2">In Crisis?</h3>
  <p class="text-gray-700 mb-4">If you or someone you know is in crisis or having thoughts of self-harm, please reach out immediately. Help is available.</p>
  <div class="flex flex-wrap gap-4">
    <button class="bg-red-600 text-white py-2 px-4 rounded-xl hover:bg-red-700 transition flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
        <path d="M3 6v12h18V6H3zm2 2h14v8H5V8zm8 1h2v2h-2v-2zm0 4h2v2h-2v-2z"/>
      </svg>
      Crisis Hotline 988
    </button>
    <button class="bg-black text-white py-2 px-4 rounded-xl hover:bg-gray-800 transition flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
        <path d="M6 2h12v20H6V2zm2 2v16h8V4H8z"/>
      </svg>
      Emergency 911
    </button>
    <button class="bg-white text-red-600 border border-red-600 py-2 px-4 rounded-xl hover:bg-red-50 transition flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
      </svg>
      Chat with Counselor
    </button>
  </div>
</section>

<!-- SELF-HELP RESOURCES WITH DESCRIPTIONS -->
<section class="max-w-6xl mx-auto p-6 mt-12">
  <h3 class="text-2xl font-bold mb-6">Self-Help Resources</h3>
  <div class="grid md:grid-cols-4 gap-6">
    <!-- Articles & Guidelines -->
    <div class="bg-white p-4 rounded-xl shadow flex flex-col items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
        <path d="M3 6v12h18V6H3zm2 2h14v8H5V8z"/>
      </svg>
      <p class="font-semibold">Articles & Guidelines</p>
      <p class="text-gray-500 text-sm text-center">Read helpful articles, health tips, and official guidelines to improve your wellness.</p>
      <button class="bg-blue-600 text-white py-1 px-3 rounded hover:bg-blue-700 transition text-sm">Read</button>
    </div>
    <!-- Meditation Apps -->
    <div class="bg-white p-4 rounded-xl shadow flex flex-col items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 2C9.243 2 7 4.243 7 7v10c0 2.757 2.243 5 5 5s5-2.243 5-5V7c0-2.757-2.243-5-5-5z"/>
      </svg>
      <p class="font-semibold">Meditation Apps</p>
      <p class="text-gray-500 text-sm text-center">Explore recommended meditation and mindfulness apps to relax and refocus daily.</p>
      <button class="bg-purple-600 text-white py-1 px-3 rounded hover:bg-purple-700 transition text-sm">Open</button>
    </div>
    <!-- Online Support -->
    <div class="bg-white p-4 rounded-xl shadow flex flex-col items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 24 24">
        <path d="M3 3h18v18H3V3zm2 2v14h14V5H5z"/>
      </svg>
      <p class="font-semibold">Online Support</p>
      <p class="text-gray-500 text-sm text-center">Join online student communities, chat with counselors, and find peer support anytime.</p>
      <button class="bg-green-600 text-white py-1 px-3 rounded hover:bg-green-700 transition text-sm">Join</button>
    </div>
    <!-- Wellness Tools -->
    <div class="bg-white p-4 rounded-xl shadow flex flex-col items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 21s-7.5-4.8-10-9.6C.6 7.2 3.1 3 7.2 3c2.1 0 3.6 1.2 4.8 2.7C13.2 4.2 14.7 3 16.8 3c4.1 0 6.6 4.2 5.2 8.4C19.5 16.2 12 21 12 21z"/>
      </svg>
      <p class="font-semibold">Wellness Tools</p>
      <p class="text-gray-500 text-sm text-center">Access practical wellness tools, exercises, and resources to track your mental health.</p>
      <button class="bg-pink-600 text-white py-1 px-3 rounded hover:bg-pink-700 transition text-sm">Access</button>
    </div>
  </div>
</section>

<!-- FOOTER WITH BLUE GRADIENT -->
<footer class="bg-gradient-to-r from-blue-700 to-blue-500 text-white p-6 mt-12">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
    <!-- Contact Info -->
    <div>
      <h4 class="font-bold mb-2">Contact Info</h4>
      <p>Phone: 123-456-789</p>
      <p>Email: info@universityhealth.edu</p>
    </div>
    <!-- Hours -->
    <div>
      <h4 class="font-bold mb-2">Hours of Operation</h4>
      <p>Open 24/7</p>
    </div>
    <!-- Campus Map -->
    <div>
      <h4 class="font-bold mb-2">Campus Map</h4>
      <p>Map Placeholder</p>
    </div>
  </div>
  <div class="text-center text-sm">&copy; 2026 University Medical Center. All rights reserved.</div>
</footer>


</body>
</html>
