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

// Fetch grades
$grades_query = "
    SELECT 
        g.*, 
        c.course_name AS course_name, 
        c.course_code AS course_code, 
        c.credits, 
        l.username AS lecturer_name
    FROM grades g
    JOIN courses c ON g.course_id = c.id
    LEFT JOIN course_allocations ca ON ca.course_id = c.id
    LEFT JOIN lecturers l ON ca.lecturer_id = l.id
    WHERE g.student_id = ?
";
$stmt = $pdo->prepare($grades_query);
$stmt->execute([$student_id]);
$grades = $stmt->fetchAll();


// Calculate GPA
$total_points = 0;
$total_credits = 0;
foreach ($grades as $grade) {
    $total_points += $grade['grade'] * $grade['credits'];
    $total_credits += $grade['credits'];
}
$gpa = $total_credits > 0 ? $total_points / $total_credits : 0;

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades - Student Portal</title>
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
            <h2 class="dashboard-title">My Grades</h2>
            <p class="dashboard-subtitle">View your academic performance and GPA.</p>
        </div>

        <nav class="nav-tabs">
            <a href="student-dashboard.php" class="nav-tab">Dashboard</a>
            <a href="my-courses.php" class="nav-tab">My Courses</a>
            <a href="schedule.php" class="nav-tab">Schedule</a>
            <a href="grades.php" class="nav-tab active">Grades</a>
            <a href="requests.php" class="nav-tab">Requests</a>
            <a href="profile.php" class="nav-tab">Profile</a>
        </nav>

        <div class="dashboard-content">
            <!-- GPA Summary -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-title">Current GPA</h3>
                        <div class="stat-number"><?php echo number_format($gpa, 2); ?></div>
                        <p class="stat-description">Overall average</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-title">Total Credits</h3>
                        <div class="stat-number"><?php echo $total_credits; ?></div>
                        <p class="stat-description">Credits earned</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-title">Courses Completed</h3>
                        <div class="stat-number"><?php echo count($grades); ?></div>
                        <p class="stat-description">With grades</p>
                    </div>
                </div>
            </div>

            <!-- Grades Table -->
            <div class="content-section full-width">
                <div class="section-header">
                    <h3 class="section-title">Grade Report</h3>
                    <button class="btn btn-primary" onclick="printGrades()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
                
                <div class="grades-table-container">
                    <table class="grades-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Lecturer</th>
                                <th>Credits</th>
                                <th>Grade</th>
                                <th>Letter Grade</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($grades)): ?>
                                <tr>
                                    <td colspan="7" class="no-data">No grades available yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($grades as $grade): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['course_code']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['lecturer_name']); ?></td>
                                        <td><?php echo $grade['credits']; ?></td>
                                        <td><?php echo number_format($grade['grade'], 1); ?></td>
                                        <td>
                                            <span class="grade-letter <?php echo getGradeClass($grade['grade']); ?>">
                                                <?php echo getLetterGrade($grade['grade']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($grade['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/student-script.js"></script>
    <script>
        function printGrades() {
            window.print();
        }
    </script>
</body>
</html>
