<?php
require_once '../includes/auth.php';
requireLecturer();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$lecturer_id = $_SESSION['user_id'];

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                $school = $_POST['school']; // NEW: School field
                $department = $_POST['department'];
                
                $query = "UPDATE lecturers SET first_name = :first_name, last_name = :last_name, 
                         email = :email, phone = :phone, school = :school, department = :department WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':last_name', $last_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':school', $school); // NEW
                $stmt->bindParam(':department', $department);
                $stmt->bindParam(':id', $lecturer_id);
                
                if ($stmt->execute()) {
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['last_name'] = $last_name;
                    $success_message = "Profile updated successfully!";
                } else {
                    $error_message = "Error updating profile.";
                }
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                // Verify current password
                $query = "SELECT password FROM lecturers WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $lecturer_id);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($current_password, $user['password'])) {
                    if ($new_password === $confirm_password) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        
                        $query = "UPDATE lecturers SET password = :password WHERE id = :id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':password', $hashed_password);
                        $stmt->bindParam(':id', $lecturer_id);
                        
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
$query = "SELECT * FROM lecturers WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $lecturer_id);
$stmt->execute();
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$current_user || !is_array($current_user)) {
    die("<p style='color:red;'>Lecturer profile not found. Please contact the system administrator.</p>");
}

// Get teaching statistics with school information
$query = "SELECT 
    COUNT(DISTINCT ca.course_id) as total_courses,
    COUNT(DISTINCT se.student_id) as total_students,
    SUM(c.credits) as total_credits,
    AVG(s.gpa) as avg_student_gpa,
    c.school
    FROM course_allocations ca 
    LEFT JOIN courses c ON ca.course_id = c.id
    LEFT JOIN student_enrollments se ON c.id = se.course_id AND se.status = 'enrolled'
    LEFT JOIN students s ON se.student_id = s.id
    WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed'
    GROUP BY c.school";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$teaching_stats_by_school = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Course Allocation System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
        <style>
        .school-stats {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .school-stats h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.1rem;
        }
        .stats-overview {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
    </style>
</head>
<body class="dashboard lecturer-dashboard">
    <nav class="navbar">
        <div class="navbar-content">
            <a href="dashboard.php" class="navbar-brand">Course Allocation System - Lecturer Portal</a>
            <div class="navbar-user">
                <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">My Profile</h1>
            <p class="page-subtitle">Manage your personal information and account settings</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="courses.php" class="nav-link">My Courses</a>
                <a href="schedule.php" class="nav-link">Schedule</a>
                <!-- <a href="students.php" class="nav-link">Students</a> -->
                <a href="reports.php" class="nav-link">Reports</a>
                <a href="profile.php" class="nav-link active">Profile</a>
            </div>
        </nav>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="content-grid">
            <!-- Profile Information -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Profile Information</h2>
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
                        <!-- NEW: School Field -->
                        <div class="form-group">
                            <label for="school">School</label>
                            <select id="school" name="school" required>
                                <option value="School of Science" <?php echo $current_user['school'] === 'School of Science' ? 'selected' : ''; ?>>School of Science</option>
                                <option value="School of Engineering" <?php echo $current_user['school'] === 'School of Engineering' ? 'selected' : ''; ?>>School of Engineering</option>
                                <option value="School of Business" <?php echo $current_user['school'] === 'School of Business' ? 'selected' : ''; ?>>School of Business</option>
                                <option value="School of Arts" <?php echo $current_user['school'] === 'School of Arts' ? 'selected' : ''; ?>>School of Arts</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department">Department</label>
                            <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($current_user['department']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($current_user['username']); ?>" disabled>
                            <small>Username cannot be changed</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>

            <!-- Teaching Statistics by School -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Teaching Overview by School</h2>
                </div>
                <div class="stats-overview">
                    <?php foreach ($teaching_stats_by_school as $stats): ?>
                    <div class="school-stats">
                        <h4>🏫 <?php echo htmlspecialchars($stats['school']); ?></h4>
                        <div class="stat-item">
                            <div class="stat-label">Courses</div>
                            <div class="stat-value"><?php echo $stats['total_courses'] ?: 0; ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Students</div>
                            <div class="stat-value"><?php echo $stats['total_students'] ?: 0; ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Credit Hours</div>
                            <div class="stat-value"><?php echo $stats['total_credits'] ?: 0; ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Avg Student GPA</div>
                            <div class="stat-value">
                                <?php echo $stats['avg_student_gpa'] ? number_format($stats['avg_student_gpa'], 2) : 'N/A'; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($teaching_stats_by_school)): ?>
                    <div class="empty-state">
                        <p>No teaching statistics available yet.</p>
                    </div>
                    <?php endif; ?>
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

        <!-- Account Information -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Account Information</h2>
            </div>
            <table class="data-table">
                <tbody>
                    <tr>
                        <td><strong>Account Type</strong></td>
                        <td>Lecturer</td>
                    </tr>
                    <tr>
                        <td><strong>School</strong></td>
                        <td><?php echo htmlspecialchars($current_user['school']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Department</strong></td>
                        <td><?php echo htmlspecialchars($current_user['department']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Member Since</strong></td>
                        <td><?php echo date('M j, Y', strtotime($current_user['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Last Updated</strong></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($current_user['updated_at'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Account Status</strong></td>
                        <td><span class="status-badge status-confirmed">Active</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Quick Actions -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Quick Actions</h2>
            </div>
            <div class="quick-actions">
                <a href="courses.php" class="btn btn-primary">View My Courses</a>
                <a href="schedule.php" class="btn btn-secondary">Check Schedule</a>
                <a href="students.php" class="btn btn-secondary">View Students</a>
                <a href="reports.php" class="btn btn-secondary">Generate Reports</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>