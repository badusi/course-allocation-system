<?php
require_once '../includes/auth.php';
requireLecturer();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$lecturer_id = $_SESSION['user_id'];

// Get lecturer's courses with enrollment details and school information
$query = "SELECT c.*, ca.allocated_date, ca.status as allocation_status, ca.notes,
          COUNT(se.id) as enrollment_count,
          GROUP_CONCAT(DISTINCT CONCAT(s.day_of_week, ' ', TIME_FORMAT(s.start_time, '%H:%i'), '-', TIME_FORMAT(s.end_time, '%H:%i')) SEPARATOR ', ') as schedule
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id 
          LEFT JOIN student_enrollments se ON c.id = se.course_id AND se.status = 'enrolled'
          LEFT JOIN schedules s ON c.id = s.course_id
          WHERE ca.lecturer_id = :lecturer_id 
          GROUP BY c.id, ca.id
          ORDER BY ca.status DESC, c.course_code";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get lecturer's school for filtering
$query = "SELECT school FROM lecturers WHERE id = :lecturer_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$lecturer_school = $stmt->fetch(PDO::FETCH_ASSOC)['school'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Course Allocation System</title>
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
            <h1 class="page-title">My Courses</h1>
            <p class="page-subtitle">Manage your assigned courses and track student enrollment</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="courses.php" class="nav-link active">My Courses</a>
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
                <p>You are assigned to courses within the <?php echo htmlspecialchars($lecturer_school); ?></p>
            </div>
        </div>

        <div class="export-buttons">
            <button class="btn-export" onclick="exportData('pdf')">Export PDF</button>
            <button class="btn-export" onclick="exportData('excel')">Export Excel</button>
        </div>

        <div class="course-grid">
            <?php foreach ($courses as $course): ?>
            <div class="course-card">
                <div class="course-header">
                    <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                    <span class="status-badge status-<?php echo $course['allocation_status']; ?>">
                        <?php echo ucfirst($course['allocation_status']); ?>
                    </span>
                </div>
                <h3 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                <p class="course-description"><?php echo htmlspecialchars(substr($course['description'], 0, 120)) . '...'; ?></p>
                
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
                        <span class="detail-label">Semester:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($course['semester'] . ' ' . $course['year']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Schedule:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($course['schedule'] ?: 'Not scheduled'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Allocated:</span>
                        <span class="detail-value"><?php echo date('M j, Y', strtotime($course['allocated_date'])); ?></span>
                    </div>
                </div>

                <div class="enrollment-info">
                    <div class="enrollment-stats">
                        <span class="enrollment-count"><?php echo $course['enrollment_count']; ?>/<?php echo $course['max_students']; ?> Students</span>
                        <span class="enrollment-percentage"><?php echo round(($course['enrollment_count'] / $course['max_students']) * 100); ?>% Full</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo ($course['enrollment_count'] / $course['max_students']) * 100; ?>%"></div>
                    </div>
                </div>

                <?php if ($course['notes']): ?>
                <div class="course-notes">
                    <strong>Notes:</strong> <?php echo htmlspecialchars($course['notes']); ?>
                </div>
                <?php endif; ?>

                <div class="course-actions">
                    <a href="students.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">View Students</a>
                    <?php if ($course['schedule']): ?>
                        <a href="schedule.php?course_id=<?php echo $course['id']; ?>" class="btn btn-secondary btn-sm">Schedule</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($courses)): ?>
        <div class="content-card">
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3>No Courses Assigned</h3>
                <p>You don't have any courses assigned yet. Please contact the administrator for course allocations.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <script>
        function exportData(format) {
            alert('Export to ' + format.toUpperCase() + ' functionality would be implemented here');
        }
    </script>
</body>
</html>