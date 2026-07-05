<?php
session_start();
require_once '../config/database.php';
$db = new Database();
$pdo = $db->getConnection(); // ✅ FIXED

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header('Location: my-courses.php');
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch course details
$course_query = "
    SELECT c.*, l.username as lecturer_name, l.email as lecturer_email, l.department
    FROM courses c
    JOIN lecturers l ON c.lecturer_id = l.id
    WHERE c.id = ?
";
$stmt = $pdo->prepare($course_query);
$stmt->execute([$course_id]);
$course = $stmt->fetch();


if (!$course) {
    header('Location: my-courses.php');
    exit();
}

// Check if student is enrolled
$enrollment_query = "SELECT status FROM student_courses WHERE student_id = ? AND course_id = ?";
$stmt = $pdo->prepare($enrollment_query);
$stmt->execute([$student_id, $course_id]);
$enrollment = $stmt->fetch();

// Get student's grade for this course
$grade_query = "SELECT grade, comments FROM grades WHERE student_id = ? AND course_id = ?";
$stmt = $pdo->prepare($grade_query);
$stmt->execute([$student_id, $course_id]);
$grade = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Details - Student Portal</title>
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
            <h2 class="dashboard-title"><?php echo htmlspecialchars($course['name']); ?></h2>
            <p class="dashboard-subtitle"><?php echo htmlspecialchars($course['code']); ?></p>
        </div>

        <nav class="nav-tabs">
            <a href="student-dashboard.php" class="nav-tab">Dashboard</a>
            <a href="my-courses.php" class="nav-tab">My Courses</a>
            <a href="schedule.php" class="nav-tab">Schedule</a>
            <a href="grades.php" class="nav-tab">Grades</a>
            <a href="requests.php" class="nav-tab">Requests</a>
            <a href="profile.php" class="nav-tab">Profile</a>
        </nav>

        <div class="dashboard-content">
            <div class="content-sections">
                <!-- Course Information -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">Course Information</h3>
                        <?php if ($enrollment): ?>
                            <span class="course-status enrolled">
                                <?php echo ucfirst($enrollment['status']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="course-info-details">
                        <p><strong>Course Code:</strong> <?php echo htmlspecialchars($course['code']); ?></p>
                        <p><strong>Credits:</strong> <?php echo $course['credits']; ?></p>
                        <p><strong>Schedule:</strong> <?php echo htmlspecialchars($course['schedule'] ?? 'TBA'); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($course['location'] ?? 'TBA'); ?></p>
                        <p><strong>Max Students:</strong> <?php echo $course['max_students']; ?></p>
                        
                        <?php if ($course['description']): ?>
                            <div class="course-description">
                                <strong>Description:</strong>
                                <p><?php echo htmlspecialchars($course['description']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lecturer Information -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">Lecturer Information</h3>
                    </div>
                    
                    <div class="lecturer-info">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($course['lecturer_name']); ?></p>
                        <p><strong>Department:</strong> <?php echo htmlspecialchars($course['department'] ?? 'N/A'); ?></p>
                        <p><strong>Email:</strong> 
                            <a href="mailto:<?php echo htmlspecialchars($course['lecturer_email']); ?>">
                                <?php echo htmlspecialchars($course['lecturer_email']); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <?php if ($grade): ?>
                <!-- Grade Information -->
                <div class="content-section full-width">
                    <div class="section-header">
                        <h3 class="section-title">Your Grade</h3>
                    </div>
                    
                    <div class="grade-info">
                        <div class="grade-display">
                            <span class="grade-number"><?php echo number_format($grade['grade'], 1); ?>%</span>
                            <span class="grade-letter <?php echo getGradeClass($grade['grade']); ?>">
                                <?php echo getLetterGrade($grade['grade']); ?>
                            </span>
                        </div>
                        
                        <?php if ($grade['comments']): ?>
                            <div class="grade-comments">
                                <strong>Comments:</strong>
                                <p><?php echo htmlspecialchars($grade['comments']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="content-section full-width">
                <div class="section-header">
                    <h3 class="section-title">Actions</h3>
                </div>
                
                <div class="course-actions">
                    <a href="my-courses.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to My Courses
                    </a>
                    
                    <?php if ($enrollment && $enrollment['status'] === 'enrolled'): ?>
                        <button onclick="dropCourse(<?php echo $course_id; ?>)" class="btn btn-danger">
                            <i class="fas fa-times"></i> Drop Course
                        </button>
                    <?php elseif (!$enrollment): ?>
                        <button onclick="enrollCourse(<?php echo $course_id; ?>)" class="btn btn-success">
                            <i class="fas fa-plus"></i> Enroll in Course
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/student-script.js"></script>
</body>
</html>

<?php
function getLetterGrade($grade) {
    if ($grade >= 90) return 'A';
    if ($grade >= 80) return 'B';
    if ($grade >= 70) return 'C';
    if ($grade >= 60) return 'D';
    return 'F';
}

function getGradeClass($grade) {
    if ($grade >= 90) return 'grade-a';
    if ($grade >= 80) return 'grade-b';
    if ($grade >= 70) return 'grade-c';
    if ($grade >= 60) return 'grade-d';
    return 'grade-f';
}
?>