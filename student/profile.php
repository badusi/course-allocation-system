<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}


// ✅ Create the database connection
$db = new Database();
$pdo = $db->getConnection();

$student_id = $_SESSION['student_id'];

// Handle form submission
if ($_POST) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Update password if provided
    if (!empty($_POST['new_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $user = $stmt->fetch();
        
        if (password_verify($current_password, $user['password'])) {
            $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $address, $new_password, $student_id]);
            $_SESSION['student_name'] = $name; // Update session
            $success_message = "Profile and password updated successfully!";
        } else {
            $error_message = "Current password is incorrect!";
        }
    } else {
        $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $address, $student_id]);
        $_SESSION['student_name'] = $name; // Update session
        $success_message = "Profile updated successfully!";
    }
}

// Fetch student data
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Student Portal</title>
    <link rel="stylesheet" href="assets/css/student-styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="system-title">Course Allocation System - Student Portal</h1>
            <div class="user-info">
                <span class="welcome-text">Welcome, <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="dashboard-header">
            <h2 class="dashboard-title">My Profile</h2>
            <p class="dashboard-subtitle">Manage your personal information and account settings.</p>
        </div>

        <nav class="nav-tabs">
            <a href="student-dashboard.php" class="nav-tab">Dashboard</a>
            <a href="my-courses.php" class="nav-tab">My Courses</a>
            <a href="schedule.php" class="nav-tab">Schedule</a>
            <a href="grades.php" class="nav-tab">Grades</a>
            <a href="requests.php" class="nav-tab">Requests</a>
            <a href="profile.php" class="nav-tab active">Profile</a>
        </nav>

        <div class="dashboard-content">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="content-sections">
                <!-- Profile Information -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">Personal Information</h3>
                    </div>
                    
                    <form method="POST" class="profile-form">
                        <div class="form-group">
                            <label for="student_id_display">Student ID</label>
                            <input type="text" id="student_id_display" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" name="name" id="name" value="<?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" rows="3"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">Change Password</h3>
                    </div>
                    
                    <form method="POST" class="password-form">
                        <!-- Hidden fields to maintain profile data -->
                        <input type="hidden" name="name" value="<?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($student['email']); ?>">
                        <input type="hidden" name="phone" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
                        <input type="hidden" name="address" value="<?php echo htmlspecialchars($student['address'] ?? ''); ?>">

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" name="current_password" id="current_password">
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" name="new_password" id="new_password" minlength="6">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" minlength="6">
                        </div>

                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Account Statistics -->
            <div class="content-section full-width">
                <div class="section-header">
                    <h3 class="section-title">Account Information</h3>
                </div>
                
                <div class="account-stats">
                    <div class="stat-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <strong>Member Since</strong>
                            <span><?php echo date('F j, Y', strtotime($student['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Last Login</strong>
                            <span><?php echo date('M j, Y g:i A', strtotime($student['last_login'] ?? $student['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>Account Status</strong>
                            <span class="status-active">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/student-script.js"></script>
    <script>
        // Password confirmation validation
        document.querySelector('.password-form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
            }
        });
    </script>
</body>
</html>
