<?php
require_once '../includes/auth.php';
requireLecturer();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$lecturer_id = $_SESSION['user_id'];

// Get lecturer statistics
$stats = [];

// My courses
$query = "SELECT COUNT(*) as count FROM course_allocations WHERE lecturer_id = :lecturer_id AND status = 'confirmed'";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$stats['my_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total students
$query = "SELECT COUNT(DISTINCT se.student_id) as count 
          FROM course_allocations ca 
          JOIN student_enrollments se ON ca.course_id = se.course_id 
          WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed' AND se.status = 'enrolled'";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$stats['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Pending allocations
$query = "SELECT COUNT(*) as count FROM course_allocations WHERE lecturer_id = :lecturer_id AND status = 'pending'";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$stats['pending_allocations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Average class size
$query = "SELECT AVG(enrollment_count) as avg_size FROM (
            SELECT COUNT(se.id) as enrollment_count
            FROM course_allocations ca 
            LEFT JOIN student_enrollments se ON ca.course_id = se.course_id AND se.status = 'enrolled'
            WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed'
            GROUP BY ca.course_id
          ) as class_sizes";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['avg_class_size'] = $result['avg_size'] ? round($result['avg_size']) : 0;

// Get lecturer's school
$query = "SELECT school FROM lecturers WHERE id = :lecturer_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$lecturer_school = $stmt->fetch(PDO::FETCH_ASSOC)['school'];

// My courses with details
$query = "SELECT c.*, ca.allocated_date, ca.status, COUNT(se.id) as enrollment_count
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id 
          LEFT JOIN student_enrollments se ON c.id = se.course_id AND se.status = 'enrolled'
          WHERE ca.lecturer_id = :lecturer_id 
          GROUP BY c.id, ca.id
          ORDER BY ca.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$my_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent students
$query = "SELECT DISTINCT s.*, c.course_code, c.school as course_school
          FROM students s 
          JOIN student_enrollments se ON s.id = se.student_id 
          JOIN courses c ON se.course_id = c.id
          JOIN course_allocations ca ON c.id = ca.course_id 
          WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed' AND se.status = 'enrolled'
          ORDER BY se.enrollment_date DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$recent_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - Course Allocation System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
        <style>
        .school-banner {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
        }
        .school-banner .school-info h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        .school-banner .school-info p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        .school-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
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
            <h1 class="page-title">Lecturer Dashboard</h1>
            <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! Here's an overview of your teaching activities.</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <a href="courses.php" class="nav-link">My Courses</a>
                <a href="schedule.php" class="nav-link">Schedule</a>
                <!-- <a href="students.php" class="nav-link">Students</a> -->
                <a href="reports.php" class="nav-link">Reports</a>
                <a href="profile.php" class="nav-link">Profile</a>
            </div>
        </nav>

        <!-- School Information Banner -->
        <div class="content-card school-banner">
            <div class="school-info">
                <h3>🏫 <?php echo htmlspecialchars($lecturer_school); ?></h3>
                <p>You are assigned to teach courses within the <?php echo htmlspecialchars($lecturer_school); ?></p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">My Courses</span>
                    <div class="stat-icon">📚</div>
                </div>
                <div class="stat-value"><?php echo $stats['my_courses']; ?></div>
                <div class="stat-change">Active assignments</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Students</span>
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                <div class="stat-change">Across all courses</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Pending Allocations</span>
                    <div class="stat-icon">⏳</div>
                </div>
                <div class="stat-value"><?php echo $stats['pending_allocations']; ?></div>
                <div class="stat-change">Awaiting confirmation</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Avg Class Size</span>
                    <div class="stat-icon">📊</div>
                </div>
                <div class="stat-value"><?php echo $stats['avg_class_size']; ?></div>
                <div class="stat-change">Students per course</div>
            </div>
        </div>

        <div class="content-grid">
            <!-- My Courses -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">My Assigned Courses</h2>
                    <a href="courses.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                
                <div class="course-grid">
                    <?php foreach ($my_courses as $course): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                                <span class="status-badge status-<?php echo $course['status']; ?>">
                                    <?php echo ucfirst($course['status']); ?>
                                </span>
                            </div>
                            <h3 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            <div class="course-details">
                                <div class="detail-row">
                                    <span class="detail-label">School:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($course['school']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Department:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($course['department']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Credits:</span>
                                    <span class="detail-value"><?php echo $course['credits']; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Students:</span>
                                    <span class="detail-value"><?php echo $course['enrollment_count']; ?>/<?php echo $course['max_students']; ?></span>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo ($course['enrollment_count'] / $course['max_students']) * 100; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Students -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Recent Students</h2>
                    <a href="students.php" class="btn btn-secondary btn-sm">View All</a>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>School</th>
                            <th>Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($recent_students, 0, 8) as $student): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($student['student_id']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($student['course_code']); ?></td>
                            <td>
                                <span class="school-badge"><?php echo htmlspecialchars($student['school']); ?></span>
                            </td>
                            <td>Year <?php echo $student['year_of_study']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Quick Actions</h2>
            </div>
            <div class="quick-actions">
                <a href="schedule.php" class="btn btn-primary">View Schedule</a>
                <a href="students.php" class="btn btn-secondary">Manage Students</a>
                <a href="reports.php" class="btn btn-secondary">Generate Reports</a>
                <a href="profile.php" class="btn btn-secondary">Update Profile</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>