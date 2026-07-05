<?php
require_once '../includes/auth.php';
requireAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get dashboard statistics
$stats = [];

// Total lecturers by school
$query = "SELECT school, COUNT(*) as count FROM lecturers GROUP BY school";
$stmt = $db->prepare($query);
$stmt->execute();
$lecturers_by_school = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total courses by school
$query = "SELECT school, COUNT(*) as count FROM courses WHERE status = 'active' GROUP BY school";
$stmt = $db->prepare($query);
$stmt->execute();
$courses_by_school = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total students by school
$query = "SELECT school, COUNT(*) as count FROM students GROUP BY school";
$stmt = $db->prepare($query);
$stmt->execute();
$students_by_school = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total allocations by school
$query = "SELECT c.school, COUNT(*) as count 
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id 
          WHERE ca.status = 'confirmed' 
          GROUP BY c.school";
$stmt = $db->prepare($query);
$stmt->execute();
$allocations_by_school = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$stats['lecturers'] = array_sum(array_column($lecturers_by_school, 'count'));
$stats['courses'] = array_sum(array_column($courses_by_school, 'count'));
$stats['students'] = array_sum(array_column($students_by_school, 'count'));
$stats['allocations'] = array_sum(array_column($allocations_by_school, 'count'));

// Recent allocations with school info
$query = "SELECT ca.*, c.course_code, c.course_name, c.school,
                 l.username as lecturer_name, 
                 l.first_name, l.last_name 
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id 
          JOIN lecturers l ON ca.lecturer_id = l.id 
          ORDER BY ca.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_allocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent courses with school info
$query = "SELECT * FROM courses ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Course Allocation System</title>
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
            <h1 class="page-title">Admin Dashboard</h1>
            <p class="page-subtitle">Manage courses, lecturers, and allocations</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <a href="lecturers.php" class="nav-link">Lecturers</a>
                <a href="courses.php" class="nav-link">Courses</a>
                <!-- <a href="students.php" class="nav-link">Students</a> -->
                <a href="allocations.php" class="nav-link">Allocations</a>
                <a href="add_schedule.php" class="nav-link">Schedule</a>
                <a href="reports.php" class="nav-link">Reports</a>
                <a href="settings.php" class="nav-link">Settings</a>
            </div>
        </nav>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Lecturers</span>
                    <div class="stat-icon">👨‍🏫</div>
                </div>
                <div class="stat-value" data-stat="lecturers"><?php echo $stats['lecturers']; ?></div>
                <div class="stat-change">
                    <?php foreach ($lecturers_by_school as $school): ?>
                        <?php echo $school['school'] . ': ' . $school['count']; ?><br>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Active Courses</span>
                    <div class="stat-icon">📚</div>
                </div>
                <div class="stat-value" data-stat="courses"><?php echo $stats['courses']; ?></div>
                <div class="stat-change">
                    <?php foreach ($courses_by_school as $school): ?>
                        <?php echo $school['school'] . ': ' . $school['count']; ?><br>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Students</span>
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-value" data-stat="students"><?php echo $stats['students']; ?></div>
                <div class="stat-change">
                    <?php foreach ($students_by_school as $school): ?>
                        <?php echo $school['school'] . ': ' . $school['count']; ?><br>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Course Allocations</span>
                    <div class="stat-icon">📋</div>
                </div>
                <div class="stat-value" data-stat="allocations"><?php echo $stats['allocations']; ?></div>
                <div class="stat-change">
                    <?php foreach ($allocations_by_school as $school): ?>
                        <?php echo $school['school'] . ': ' . $school['count']; ?><br>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Recent Course Allocations</h2>
                    <a href="allocations.php" class="view-all-btn">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Lecturer</th>
                            <th>School</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_allocations as $allocation): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($allocation['course_code']); ?></strong><br>
                                <small><?php echo htmlspecialchars($allocation['course_name']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($allocation['first_name'] . ' ' . $allocation['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($allocation['school']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $allocation['status']; ?>">
                                    <?php echo ucfirst($allocation['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($allocation['allocated_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Recent Courses</h2>
                    <a href="courses.php" class="view-all-btn">View All</a>
                </div>
                <div class="course-list">
                    <?php foreach ($recent_courses as $course): ?>
                    <div class="course-item">
                        <div class="course-header">
                            <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                            <span class="status-badge status-<?php echo $course['status']; ?>">
                                <?php echo ucfirst($course['status']); ?>
                            </span>
                        </div>
                        <h4><?php echo htmlspecialchars($course['course_name']); ?></h4>
                        <p>
                            <strong>School:</strong> <?php echo htmlspecialchars($course['school']); ?> • 
                            <strong>Dept:</strong> <?php echo htmlspecialchars($course['department']); ?> • 
                            <?php echo $course['credits']; ?> Credits
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>