<?php
session_start();
require_once '../config/database.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}




$name = isset($_SESSION['name']) ? $_SESSION['name'] : '';

$db = new Database();             // ✅ Create a new Database object
$pdo = $db->getConnection();      // ✅ Get the PDO connection


$student_id = $_SESSION['student_id'];

// Fetch student data
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();


// Store student name in session for other pages
$name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : '';
$_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];




// Fetch student statistics
$stats_query = "
    SELECT 
        COUNT(DISTINCT sc.course_id) as enrolled_courses,
        COUNT(DISTINCT cr.id) as pending_requests,
        COALESCE(AVG(g.grade), 0) as current_gpa,
        COALESCE(SUM(c.credits), 0) as credits_earned
    FROM students s
    LEFT JOIN student_courses sc ON s.id = sc.student_id AND sc.status = 'enrolled'
    LEFT JOIN course_requests cr ON s.id = cr.student_id AND cr.status = 'pending'
    LEFT JOIN grades g ON s.id = g.student_id
    LEFT JOIN courses c ON sc.course_id = c.id
    WHERE s.id = ?
";
$stmt = $pdo->prepare($stats_query);
$stmt->execute([$student_id]);
$stats = $stmt->fetch();

// Fetch current courses
$courses_query = "
    SELECT c.*, l.first_name AS lecturer_first_name, l.last_name AS lecturer_last_name
    FROM student_course_allocations sca
    JOIN courses c ON sca.course_id = c.id
    JOIN course_allocations ca ON ca.course_id = c.id
    JOIN lecturers l ON ca.lecturer_id = l.id
    WHERE sca.student_id = :student_id

";
$stmt = $pdo->prepare($courses_query);
$stmt->execute(['student_id' => $student_id]); // ✅ CORRECT
$current_courses = $stmt->fetchAll();

// Fetch recent announcements
$announcements_query = "
    SELECT * FROM announcements 
    WHERE target_audience IN ('all', 'students') AND status = 'active'
    ORDER BY created_at DESC 
    LIMIT 5
";
$stmt = $pdo->prepare($announcements_query);
$stmt->execute();
$announcements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Allocation System - Student Portal</title>
    <link rel="stylesheet" href="assets/css/student-styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <h1 class="system-title">Course Allocation System - Student Portal</h1>
            <div class="user-info">
                <span class="welcome-text">Welcome, <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-header">
            <h2 class="dashboard-title">Student Dashboard</h2>
            <p class="dashboard-subtitle">View your course allocations and academic progress.</p>
        </div>

        <!-- Navigation Tabs -->
        <nav class="nav-tabs">
            <a href="student-dashboard.php" class="nav-tab active">Dashboard</a>
            <a href="my-courses.php" class="nav-tab">My Courses</a>
            <a href="schedule.php" class="nav-tab">Schedule</a>
            <a href="grades.php" class="nav-tab">Grades</a>
            <a href="requests.php" class="nav-tab">Requests</a>
            <a href="profile.php" class="nav-tab">Profile</a>
        </nav>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-title">Enrolled Courses</h3>
                        <div class="stat-number"><?php echo $stats['enrolled_courses'] ?? 0; ?></div>
                        <p class="stat-description">This semester</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-title">Pending Requests</h3>
                        <div class="stat-number"><?php echo $stats['pending_requests'] ?? 0; ?></div>
                        <p class="stat-description">Awaiting approval</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-title">Current GPA</h3>
                        <div class="stat-number"><?php echo number_format($stats['current_gpa'] ?? 0, 1); ?></div>
                        <p class="stat-description">Overall average</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-title">Credits Earned</h3>
                        <div class="stat-number"><?php echo $stats['credits_earned'] ?? 0; ?></div>
                        <p class="stat-description">Total credits</p>
                    </div>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="content-sections">
                <!-- My Current Courses -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">My Current Courses</h3>
                        <a href="my-courses.php" class="view-all-btn">View All</a>
                    </div>
                    <div class="course-list">
                        <?php if (empty($current_courses)): ?>
                            <p class="no-data">No courses enrolled yet.</p>
                        <?php else: ?>
                            <?php foreach ($current_courses as $course): ?>
                                <div class="course-item" onclick="location.href='course-details.php?id=<?php echo $course['id']; ?>'">
                                    <div class="course-info">
                                        <h4 class="course-name"><?php echo htmlspecialchars($course['name']); ?></h4>
                                        <p class="course-details">
                                            <?php echo htmlspecialchars($course['lecturer_name']); ?> • 
                                            <?php echo htmlspecialchars($course['schedule'] ?? 'TBA'); ?>
                                        </p>
                                        <span class="course-status enrolled">Enrolled</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Announcements -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">Recent Announcements</h3>
                        <a href="#" class="view-all-btn">View All</a>
                    </div>
                    <div class="announcement-list">
                        <?php if (empty($announcements)): ?>
                            <p class="no-data">No announcements available.</p>
                        <?php else: ?>
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="announcement-item">
                                    <div class="announcement-info">
                                        <h4 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h4>
                                        <p class="announcement-details"><?php echo htmlspecialchars(substr($announcement['content'], 0, 100)) . '...'; ?></p>
                                        <span class="announcement-date">
                                            <?php echo date('M j, Y', strtotime($announcement['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/student-script.js"></script>
</body>
</html>
