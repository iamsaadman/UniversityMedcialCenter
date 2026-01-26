<?php
session_start();
require_once 'includes/dp.php';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Get logged-in user
$doctor_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $stmt->close();
} else {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $blood_type = $_POST['blood_type'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $height = $_POST['height'] ?? null;

    if (!empty($password)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET fullname=?, email=?, password=?, blood_type=?, weight=?, height=? WHERE id=?";
        $stmt = $mysqli->prepare($update_query);
        $stmt->bind_param('ssssddi', $fullname, $email, $password_hashed, $blood_type, $weight, $height, $doctor_id);
    } else {
        $update_query = "UPDATE users SET fullname=?, email=?, blood_type=?, weight=?, height=? WHERE id=?";
        $stmt = $mysqli->prepare($update_query);
        $stmt->bind_param('sssddi', $fullname, $email, $blood_type, $weight, $height, $doctor_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully'); window.location.href='edit_profile.php';</script>";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
}
?>


<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile</title>
<style>
    /* Reset and basic styles */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background: #f3f4f6;
        color: #1f2937;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    main {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem;
    }

    form {
        background: white;
        padding: 2.5rem 2rem;
        border-radius: 1rem;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 450px;
    }

    form h2 {
        margin-bottom: 1.5rem;
        text-align: center;
        color: #1f2937;
        font-size: 1.75rem;
        font-weight: 700;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        font-size: 0.95rem;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
        width: 100%;
        padding: 0.75rem 1rem;
        margin-bottom: 1.25rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        transition: border-color 0.3s, box-shadow 0.3s;
        font-size: 0.95rem;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    select:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }

    button {
        width: 100%;
        padding: 0.75rem;
        background: linear-gradient(to right, #10b981, #3b82f6);
        color: white;
        font-weight: 600;
        font-size: 1rem;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: background 0.3s, transform 0.1s;
    }

    button:hover {
        background: linear-gradient(to right, #059669, #2563eb);
        transform: translateY(-1px);
    }

    .form-footer {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.875rem;
    }

    .form-footer a {
        color: #10b981;
        text-decoration: none;
        font-weight: 600;
    }

    .form-footer a:hover {
        text-decoration: underline;
    }

    @media (max-width: 500px) {
        form {
            padding: 2rem 1.5rem;
        }
    }
</style>
</head>
<body>
<main>
    <form method="post" action="edit_profile.php">
        <h2>Edit Profile</h2>

        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($row['fullname']); ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Leave blank to keep current">

        <label for="blood_type">Blood Type</label>
        <select name="blood_type" id="blood_type" required>
            <option value="">Select your blood type</option>
            <?php
            $blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            foreach ($blood_types as $type) {
                $selected = ($row['blood_type'] === $type) ? 'selected' : '';
                echo "<option value=\"$type\" $selected>$type</option>";
            }
            ?>
        </select>

        <label for="weight">Weight (kg)</label>
        <input type="text" id="weight" name="weight" value="<?php echo htmlspecialchars($row['weight']); ?>">

        <label for="height">Height (cm)</label>
        <input type="text" id="height" name="height" value="<?php echo htmlspecialchars($row['height']); ?>">

        <button type="submit">Save Changes</button>

        <div class="form-footer">
            <a href="studentportal.php">&larr; Back to Dashboard</a>
        </div>
    </form>
</main>
</body>
</html>

