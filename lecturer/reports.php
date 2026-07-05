<?php
require_once '../includes/auth.php';
requireLecturer();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$lecturer_id = $_SESSION['user_id'];

// Get lecturer's school
$query = "SELECT school FROM lecturers WHERE id = :lecturer_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$lecturer_school = $stmt->fetch(PDO::FETCH_ASSOC)['school'];

// Get lecturer's teaching statistics
$stats = [];

// Course statistics by school
$query = "SELECT c.school, 
          COUNT(*) as total_courses,
          SUM(CASE WHEN ca.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_courses,
          SUM(CASE WHEN ca.status = 'pending' THEN 1 ELSE 0 END) as pending_courses
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id
          WHERE ca.lecturer_id = :lecturer_id
          GROUP BY c.school";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$course_stats_by_school = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Student statistics by school
$query = "SELECT c.school,
          COUNT(DISTINCT se.student_id) as total_students,
          AVG(s.gpa) as avg_gpa,
          COUNT(DISTINCT c.department) as departments_taught
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id
          LEFT JOIN student_enrollments se ON c.id = se.course_id AND se.status = 'enrolled'
          LEFT JOIN students s ON se.student_id = s.id
          WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed'
          GROUP BY c.school";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$student_stats_by_school = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate overall totals
$total_courses = array_sum(array_column($course_stats_by_school, 'confirmed_courses'));
$total_students = array_sum(array_column($student_stats_by_school, 'total_students'));
$total_schools = count($course_stats_by_school);

// Course enrollment details with school information
$query = "SELECT c.course_code, c.course_name, c.credits, c.max_students, c.school,
          COUNT(se.id) as enrolled_students,
          ROUND((COUNT(se.id) / c.max_students) * 100, 1) as fill_percentage,
          AVG(s.gpa) as class_avg_gpa
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id
          LEFT JOIN student_enrollments se ON c.id = se.course_id AND se.status = 'enrolled'
          LEFT JOIN students s ON se.student_id = s.id
          WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed'
          GROUP BY c.id
          ORDER BY c.school, c.course_code";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$course_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Student performance by course and school
$query = "SELECT c.course_code, c.course_name, c.school,
          COUNT(s.id) as total_students,
          SUM(CASE WHEN s.gpa >= 3.5 THEN 1 ELSE 0 END) as high_performers,
          SUM(CASE WHEN s.gpa >= 2.5 AND s.gpa < 3.5 THEN 1 ELSE 0 END) as average_performers,
          SUM(CASE WHEN s.gpa < 2.5 AND s.gpa > 0 THEN 1 ELSE 0 END) as low_performers,
          AVG(s.gpa) as avg_gpa
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id
          JOIN student_enrollments se ON c.id = se.course_id AND se.status = 'enrolled'
          JOIN students s ON se.student_id = s.id
          WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed' AND s.gpa > 0
          GROUP BY c.id
          ORDER BY c.school, avg_gpa DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$performance_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Teaching load by semester and school
$query = "SELECT c.semester, c.year, c.school, 
          COUNT(*) as course_count, SUM(c.credits) as total_credits
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id
          WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed'
          GROUP BY c.semester, c.year, c.school
          ORDER BY c.year DESC, c.semester, c.school";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$teaching_load = $stmt->fetchAll(PDO::FETCH_ASSOC);

// School-wise summary
$school_summary = [];
foreach ($course_stats_by_school as $school_stat) {
    $school = $school_stat['school'];
    $student_stat = array_filter($student_stats_by_school, function($stat) use ($school) {
        return $stat['school'] === $school;
    });
    $student_stat = $student_stat ? array_values($student_stat)[0] : null;
    
    $school_summary[$school] = [
        'courses' => $school_stat['confirmed_courses'],
        'students' => $student_stat ? $student_stat['total_students'] : 0,
        'avg_gpa' => $student_stat ? $student_stat['avg_gpa'] : null,
        'departments' => $student_stat ? $student_stat['departments_taught'] : 0
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - Course Allocation System</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
        }
        .schools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .school-card {
            border: 1px solid #10b981;
            border-radius: 8px;
            padding: 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .school-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .school-header h3 {
            margin: 0;
            color: #fff;
            font-size: 1.2rem;
        }
        .school-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .school-stat {
            text-align: center;
        }
        .school-stat .stat-label {
            display: block;
            font-size: 0.8rem;
            color: #fff;
            margin-bottom: 5px;
        }
        .school-stat .stat-value {
            display: block;
            font-size: 1.2rem;
            font-weight: bold;
            color: #fff;
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
            <h1 class="page-title">Teaching Reports</h1>
            <p class="page-subtitle">Analytics and insights for your teaching activities</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="courses.php" class="nav-link">My Courses</a>
                <a href="schedule.php" class="nav-link">Schedule</a>
                <!-- <a href="students.php" class="nav-link">Students</a> -->
                <a href="reports.php" class="nav-link active">Reports</a>
                <a href="profile.php" class="nav-link">Profile</a>
            </div>
        </nav>

        <!-- School Information Banner -->
        <div class="content-card school-banner">
            <div class="school-info">
                <h3>🏫 <?php echo htmlspecialchars($lecturer_school); ?></h3>
                <p>Your teaching reports across different schools in the institution</p>
            </div>
        </div>

        <div class="export-buttons">
            <button class="btn-export" onclick="exportReport('pdf')">Export PDF</button>
            <button class="btn-export" onclick="exportReport('excel')">Export Excel</button>
        </div>

        <!-- School-wise Summary -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Teaching Summary by School</h2>
            </div>
            <div class="schools-grid">
                <?php foreach ($school_summary as $school => $summary): ?>
                <div class="school-card">
                    <div class="school-header">
                        <h3><?php echo htmlspecialchars($school); ?></h3>
                        <span class="school-badge"><?php echo $summary['courses']; ?> Courses</span>
                    </div>
                    <div class="school-stats">
                        <div class="school-stat">
                            <span class="stat-label">Students</span>
                            <span class="stat-value"><?php echo $summary['students']; ?></span>
                        </div>
                        <div class="school-stat">
                            <span class="stat-label">Departments</span>
                            <span class="stat-value"><?php echo $summary['departments']; ?></span>
                        </div>
                        <div class="school-stat">
                            <span class="stat-label">Avg GPA</span>
                            <span class="stat-value">
                                <?php echo $summary['avg_gpa'] ? number_format($summary['avg_gpa'], 2) : 'N/A'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Courses</span>
                    <div class="stat-icon">📚</div>
                </div>
                <div class="stat-value"><?php echo $total_courses; ?></div>
                <div class="stat-change">Across <?php echo $total_schools; ?> schools</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Students</span>
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-value"><?php echo $total_students; ?></div>
                <div class="stat-change">Across all courses</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Schools Teaching</span>
                    <div class="stat-icon">🏫</div>
                </div>
                <div class="stat-value"><?php echo $total_schools; ?></div>
                <div class="stat-change">Different schools</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Departments</span>
                    <div class="stat-icon">🏢</div>
                </div>
                <div class="stat-value">
                    <?php 
                    $total_departments = array_sum(array_column($school_summary, 'departments'));
                    echo $total_departments ?: 0; 
                    ?>
                </div>
                <div class="stat-change">Teaching across</div>
            </div>
        </div>

        <div class="content-grid">
            <!-- Course Enrollment Analysis -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Course Enrollment Analysis by School</h2>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>School</th>
                            <th>Course</th>
                            <th>Enrollment</th>
                            <th>Fill Rate</th>
                            <th>Avg GPA</th>
                            <th>Credits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($course_details as $course): ?>
                        <tr>
                            <td>
                                <span class="school-badge"><?php echo htmlspecialchars($course['school']); ?></span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($course['course_code']); ?></strong><br>
                                <small><?php echo htmlspecialchars($course['course_name']); ?></small>
                            </td>
                            <td><?php echo $course['enrolled_students']; ?>/<?php echo $course['max_students']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $course['fill_percentage'] > 80 ? 'confirmed' : ($course['fill_percentage'] > 50 ? 'pending' : 'active'); ?>">
                                    <?php echo $course['fill_percentage']; ?>%
                                </span>
                            </td>
                            <td>
                                <?php if ($course['class_avg_gpa']): ?>
                                    <span class="status-badge <?php echo $course['class_avg_gpa'] >= 3.5 ? 'status-confirmed' : ($course['class_avg_gpa'] >= 2.5 ? 'status-pending' : 'status-active'); ?>">
                                        <?php echo number_format($course['class_avg_gpa'], 2); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $course['credits']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Teaching Load History by School -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Teaching Load by Semester & School</h2>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Semester</th>
                            <th>School</th>
                            <th>Courses</th>
                            <th>Total Credits</th>
                            <th>Load</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teaching_load as $load): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($load['semester'] . ' ' . $load['year']); ?></strong></td>
                            <td>
                                <span class="school-badge"><?php echo htmlspecialchars($load['school']); ?></span>
                            </td>
                            <td><?php echo $load['course_count']; ?></td>
                            <td><?php echo $load['total_credits']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $load['total_credits'] > 12 ? 'pending' : ($load['total_credits'] > 9 ? 'confirmed' : 'active'); ?>">
                                    <?php 
                                    if ($load['total_credits'] > 12) echo 'Heavy';
                                    elseif ($load['total_credits'] > 9) echo 'Normal';
                                    else echo 'Light';
                                    ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Student Performance Analysis by School -->
        <?php if (!empty($performance_data)): ?>
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Student Performance by Course & School</h2>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>School</th>
                        <th>Course</th>
                        <th>Total Students</th>
                        <th>High Performers</th>
                        <th>Average Performers</th>
                        <th>Low Performers</th>
                        <th>Class Average</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($performance_data as $perf): ?>
                    <tr>
                        <td>
                            <span class="school-badge"><?php echo htmlspecialchars($perf['school']); ?></span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($perf['course_code']); ?></strong><br>
                            <small><?php echo htmlspecialchars($perf['course_name']); ?></small>
                        </td>
                        <td><?php echo $perf['total_students']; ?></td>
                        <td>
                            <span class="status-badge status-confirmed">
                                <?php echo $perf['high_performers']; ?> (<?php echo round(($perf['high_performers'] / $perf['total_students']) * 100); ?>%)
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-pending">
                                <?php echo $perf['average_performers']; ?> (<?php echo round(($perf['average_performers'] / $perf['total_students']) * 100); ?>%)
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-active">
                                <?php echo $perf['low_performers']; ?> (<?php echo round(($perf['low_performers'] / $perf['total_students']) * 100); ?>%)
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $perf['avg_gpa'] >= 3.5 ? 'status-confirmed' : ($perf['avg_gpa'] >= 2.5 ? 'status-pending' : 'status-active'); ?>">
                                <?php echo number_format($perf['avg_gpa'], 2); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- School-wise Performance Insights -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Teaching Insights by School</h2>
            </div>
            <div class="insights-grid">
                <?php foreach ($school_summary as $school => $summary): ?>
                <div class="insight-item">
                    <h4>🏫 <?php echo htmlspecialchars($school); ?></h4>
                    <p>
                        You teach <strong><?php echo $summary['courses']; ?> courses</strong> with 
                        <strong><?php echo $summary['students']; ?> students</strong> across 
                        <strong><?php echo $summary['departments']; ?> department(s)</strong>.
                        <?php if ($summary['avg_gpa']): ?>
                            Student performance averages <strong><?php echo number_format($summary['avg_gpa'], 2); ?> GPA</strong>.
                        <?php endif; ?>
                    </p>
                </div>
                <?php endforeach; ?>

                <div class="insight-item">
                    <h4>📈 Overall Enrollment Trends</h4>
                    <p>
                        <?php
                        $total_capacity = array_sum(array_column($course_details, 'max_students'));
                        $total_enrolled = array_sum(array_column($course_details, 'enrolled_students'));
                        $overall_fill = $total_capacity > 0 ? round(($total_enrolled / $total_capacity) * 100) : 0;
                        ?>
                        Your courses have an overall fill rate of <strong><?php echo $overall_fill; ?>%</strong> across 
                        <strong><?php echo $total_schools; ?> schools</strong>.
                    </p>
                </div>

                <div class="insight-item">
                    <h4>🎯 Cross-School Impact</h4>
                    <p>
                        You are making an impact across <strong><?php echo $total_schools; ?> different schools</strong>, 
                        teaching students from various academic backgrounds and departments.
                    </p>
                </div>

                <div class="insight-item">
                    <h4>🌟 Recommendations</h4>
                    <p>
                        <?php if ($total_schools > 1): ?>
                            Leverage your cross-school experience to share best practices and teaching methodologies.
                        <?php endif; ?>
                        <?php if ($overall_fill < 70): ?>
                            Focus on course marketing strategies tailored to each school's student population.
                        <?php elseif ($total_courses > 4): ?>
                            Consider balancing your teaching load across schools for optimal impact.
                        <?php else: ?>
                            Continue building strong relationships within each school's academic community.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <script>
        function exportReport(format) {
            alert('Export to ' + format.toUpperCase() + ' functionality would be implemented here');
        }
    </script>
</body>
</html>