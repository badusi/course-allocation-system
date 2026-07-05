<?php
require_once '../includes/auth.php';
requireAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $user_id = $_SESSION['user_id'];
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                
                $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, 
                         email = :email, phone = :phone WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':last_name', $last_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':id', $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['last_name'] = $last_name;
                    $success_message = "Profile updated successfully!";
                } else {
                    $error_message = "Error updating profile.";
                }
                break;
                
            case 'change_password':
                $user_id = $_SESSION['user_id'];
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                // Verify current password
                $query = "SELECT password FROM users WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $user_id);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($current_password, $user['password'])) {
                    if ($new_password === $confirm_password) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        
                        $query = "UPDATE users SET password = :password WHERE id = :id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':password', $hashed_password);
                        $stmt->bindParam(':id', $user_id);
                        
                        if ($stmt->execute()) {
                            $success_message = "Password changed successfully!";
                        } else {
                            $error_message = "Error changing password.";
                        }
                    } else {
                        $error_message = "New passwords do not match.";
                    }
                } else {
                    $error_message = "Current password is incorrect.";
                }
                break;
        }
    }
}

// Get current user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get system statistics
$query = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'lecturer') as total_lecturers,
    (SELECT COUNT(*) FROM courses WHERE status = 'active') as total_courses,
    (SELECT COUNT(*) FROM students) as total_students,
    (SELECT COUNT(*) FROM course_allocations WHERE status = 'confirmed') as total_allocations";
$stmt = $db->prepare($query);
$stmt->execute();
$system_stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Course Allocation System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <div class="navbar-content">
            <a href="dashboard.php" class="navbar-brand">Course Allocation System</a>
            <div class="navbar-user">
                <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
                <button class="logout-btn">Logout</button>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">System Settings</h1>
            <p class="page-subtitle">Manage your profile and system preferences</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="lecturers.php" class="nav-link">Lecturers</a>
                <a href="courses.php" class="nav-link">Courses</a>
                <!-- <a href="students.php" class="nav-link">Students</a> -->
                <a href="allocations.php" class="nav-link">Allocations</a>
                <a href="add_schedule.php" class="nav-link">Schedule</a>
                <a href="reports.php" class="nav-link">Reports</a>
                <a href="settings.php" class="nav-link active">Settings</a>
            </div>
        </nav>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="content-grid">
            <!-- Profile Settings -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Profile Settings</h2>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($current_user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($current_user['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($current_user['phone'] ?: ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>

            <!-- System Overview -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">System Overview</h2>
                </div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-label">Total Lecturers</div>
                        <div class="stat-value"><?php echo $system_stats['total_lecturers']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Active Courses</div>
                        <div class="stat-value"><?php echo $system_stats['total_courses']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Total Students</div>
                        <div class="stat-value"><?php echo $system_stats['total_students']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Confirmed Allocations</div>
                        <div class="stat-value"><?php echo $system_stats['total_allocations']; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Change Password</h2>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>

        <!-- System Information -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">System Information</h2>
            </div>
            <table class="data-table">
                <tbody>
                    <tr>
                        <td><strong>System Version</strong></td>
                        <td>1.0.0</td>
                    </tr>
                    <tr>
                        <td><strong>Database Version</strong></td>
                        <td>MySQL 8.0</td>
                    </tr>
                    <tr>
                        <td><strong>PHP Version</strong></td>
                        <td><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Last Login</strong></td>
                        <td><?php echo date('M j, Y g:i A'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Account Created</strong></td>
                        <td><?php echo date('M j, Y', strtotime($current_user['created_at'])); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <style>
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        
        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</body>
</html>
