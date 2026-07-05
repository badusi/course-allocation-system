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

// Fetch all enrolled courses
$courses_query = "
    SELECT c.*, CONCAT(l.first_name, ' ', l.last_name) AS lecturer_name, 
    sc.enrollment_date, sc.status
    FROM courses c
    JOIN student_courses sc ON c.id = sc.course_id
    JOIN course_allocations ca ON ca.course_id = c.id
    JOIN lecturers l ON ca.lecturer_id = l.id
    WHERE sc.student_id = ?
    ORDER BY sc.enrollment_date DESC
";
$stmt = $pdo->prepare($courses_query);
$stmt->execute([$student_id]); 
$courses = $stmt->fetchAll();



// Assuming this comes from a form via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    $enrollment_date = date('Y-m-d H:i:s');
    $status = 'enrolled'; // or 'active'

    $db = new Database();
    $pdo = $db->getConnection();

    try {
        $pdo->beginTransaction();

        // Insert into student_enrollments
        $stmt1 = $pdo->prepare("INSERT INTO student_enrollments (student_id, course_id, enrollment_date, status) VALUES (?, ?, ?, ?)");
        $stmt1->execute([$student_id, $course_id, $enrollment_date, $status]);

        // Insert into student_courses (if schema is similar)
        $stmt2 = $pdo->prepare("INSERT INTO student_courses (student_id, course_id, enrollment_date, status) VALUES (?, ?, ?, ?)");
        $stmt2->execute([$student_id, $course_id, $enrollment_date, $status]);

        $pdo->commit();

        $_SESSION['message'] = "Course enrolled successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Enrollment failed: " . $e->getMessage();
    }
}


// Fetch available courses for enrollment
$available_query = "
    SELECT c.*, CONCAT(l.first_name, ' ', l.last_name) AS lecturer_name
    FROM courses c
    JOIN course_allocations ca ON ca.course_id = c.id
    JOIN lecturers l ON ca.lecturer_id = l.id
    WHERE c.id NOT IN (
        SELECT course_id FROM student_courses WHERE student_id = ?
    )
    AND c.status = 'active'
    ORDER BY c.course_name
";
$stmt = $pdo->prepare($available_query);
$stmt->execute([$student_id]);
$available_courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Student Portal</title>
    <link rel="stylesheet" href="assets/css/student-styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="system-title">Course Allocation System - Student Portal</h1>
            <div class="user-info">
                <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Student'); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="dashboard-header">
            <h2 class="dashboard-title">My Courses</h2>
            <p class="dashboard-subtitle">Manage your enrolled courses and explore new ones.</p>
        </div>

        <nav class="nav-tabs">
            <a href="student-dashboard.php" class="nav-tab">Dashboard</a>
            <a href="my-courses.php" class="nav-tab active">My Courses</a>
            <a href="schedule.php" class="nav-tab">Schedule</a>
            <a href="grades.php" class="nav-tab">Grades</a>
            <a href="requests.php" class="nav-tab">Requests</a>
            <a href="profile.php" class="nav-tab">Profile</a>
        </nav>

        <div class="dashboard-content">
            <!-- Enrolled Courses -->
            <div class="content-section full-width">
                <div class="section-header">
                    <h3 class="section-title">Enrolled Courses</h3>
                    <span class="course-count"><?php echo count($courses); ?> courses</span>
                </div>
                <div class="courses-grid">
                    <?php if (empty($courses)): ?>
                        <p class="no-data">You haven't enrolled in any courses yet.</p>
                    <?php else: ?>
                        <?php foreach ($courses as $course): ?>
                            <div class="course-card">
                                <div class="course-header">
                                    <h4 class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></h4>
                                    <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                                </div>
                                <div class="course-details">
                                    <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['lecturer_name']); ?></p>
                                    <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($course['schedule'] ?? 'TBA'); ?></p>
                                    <p><i class="fas fa-credit-card"></i> <?php echo $course['credits']; ?> Credits</p>
                                    <p><i class="fas fa-calendar"></i> Enrolled: <?php echo date('M j, Y', strtotime($course['enrollment_date'])); ?></p>
                                </div>
                                <div class="course-actions">
                                    <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">View Details</a>
                                    <?php if ($course['status'] === 'enrolled'): ?>
                                        <button onclick="dropCourse(<?php echo $course['id']; ?>)" class="btn btn-danger">Drop Course</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Available Courses -->
            <div class="content-section full-width">
                <div class="section-header">
                    <h3 class="section-title">Available Courses</h3>
                    <span class="course-count"><?php echo count($available_courses); ?> available</span>
                </div>
                <div class="courses-grid">
                    <?php if (empty($available_courses)): ?>
                        <p class="no-data">No courses available for enrollment.</p>
                    <?php else: ?>
                        <?php foreach ($available_courses as $course): ?>
                            <div class="course-card available">
                                <div class="course-header">
                                    <h4 class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></h4>
                                    <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                                </div>
                                <div class="course-details">
                                    <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['lecturer_name']); ?></p>
                                    <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($course['schedule'] ?? 'TBA'); ?></p>
                                    <p><i class="fas fa-credit-card"></i> <?php echo $course['credits']; ?> Credits</p>
                                    <p><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars(substr($course['description'] ?? 'No description available', 0, 50)) . '...'; ?></p>
                                </div>
                                 <form method="POST">
                                    <div class="course-actions">
                                            <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-secondary">View Details</a>
                                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                            <button type="submit" class="btn btn-success">Enroll</button>
                                    </div>
                                 </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/student-script.js"></script>
</body>
</html>
