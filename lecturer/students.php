<?php
require_once '../includes/auth.php';
requireLecturer();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$lecturer_id = $_SESSION['user_id'];

// Get filter parameters
$course_filter = isset($_GET['course_id']) ? $_GET['course_id'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$year_filter = isset($_GET['year']) ? $_GET['year'] : '';

// Get lecturer's courses for filter dropdown
$query = "SELECT c.id, c.course_code, c.course_name, c.school 
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id 
          WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed'
          ORDER BY c.course_code";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$lecturer_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get lecturer's school
$query = "SELECT school FROM lecturers WHERE id = :lecturer_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$lecturer_school = $stmt->fetch(PDO::FETCH_ASSOC)['school'];

// Get students enrolled in lecturer's courses
$query = "SELECT DISTINCT s.*, c.course_code, c.course_name, c.school as course_school, se.enrollment_date, se.grade
          FROM students s 
          JOIN student_enrollments se ON s.id = se.student_id 
          JOIN courses c ON se.course_id = c.id
          JOIN course_allocations ca ON c.id = ca.course_id 
          WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed' AND se.status = 'enrolled'";

$params = [':lecturer_id' => $lecturer_id];

if ($course_filter) {
    $query .= " AND c.id = :course_id";
    $params[':course_id'] = $course_filter;
}

if ($search) {
    $query .= " AND (s.first_name LIKE :search OR s.last_name LIKE :search OR s.student_id LIKE :search OR s.email LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($year_filter) {
    $query .= " AND s.year_of_study = :year";
    $params[':year'] = $year_filter;
}

$query .= " ORDER BY s.last_name, s.first_name";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$total_students = count($students);
$year_distribution = [];
$gpa_stats = ['total' => 0, 'count' => 0];
$school_distribution = [];

foreach ($students as $student) {
    $year = $student['year_of_study'];
    $year_distribution[$year] = ($year_distribution[$year] ?? 0) + 1;
    
    $school = $student['school'];
    $school_distribution[$school] = ($school_distribution[$school] ?? 0) + 1;
    
    if ($student['gpa']) {
        $gpa_stats['total'] += $student['gpa'];
        $gpa_stats['count']++;
    }
}

$avg_gpa = $gpa_stats['count'] > 0 ? $gpa_stats['total'] / $gpa_stats['count'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students - Course Allocation System</title>
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
        .school-distribution {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .school-bar {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .school-label {
            min-width: 150px;
            font-weight: 500;
        }
        .school-progress {
            flex: 1;
            height: 20px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .school-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        .school-count {
            min-width: 100px;
            text-align: right;
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
            <h1 class="page-title">My Students</h1>
            <p class="page-subtitle">Students enrolled in your courses</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="courses.php" class="nav-link">My Courses</a>
                <a href="schedule.php" class="nav-link">Schedule</a>
                <!-- <a href="students.php" class="nav-link active">Students</a> -->
                <a href="reports.php" class="nav-link">Reports</a>
                <a href="profile.php" class="nav-link">Profile</a>
            </div>
        </nav>

        <!-- School Information -->
        <div class="content-card school-banner">
            <div class="school-info">
                <h3>🏫 <?php echo htmlspecialchars($lecturer_school); ?></h3>
                <p>Teaching students from various schools within the institution</p>
            </div>
        </div>

        <!-- Student Statistics -->
        <div class="stats-grid">
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
                    <span class="stat-title">Average GPA</span>
                    <div class="stat-icon">📊</div>
                </div>
                <div class="stat-value"><?php echo number_format($avg_gpa, 2); ?></div>
                <div class="stat-change">Class average</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Year Distribution</span>
                    <div class="stat-icon">🎓</div>
                </div>
                <div class="stat-value">
                    <?php 
                    $max_year = 0;
                    $max_count = 0;
                    foreach ($year_distribution as $year => $count) {
                        if ($count > $max_count) {
                            $max_count = $count;
                            $max_year = $year;
                        }
                    }
                    echo "Year $max_year";
                    ?>
                </div>
                <div class="stat-change">Most common year</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Schools</span>
                    <div class="stat-icon">🏫</div>
                </div>
                <div class="stat-value"><?php echo count($school_distribution); ?></div>
                <div class="stat-change">Different schools</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Filter Students</h2>
            </div>
            <form method="GET" class="filters">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>Search Students</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, ID, or email...">
                    </div>
                    <div class="filter-group">
                        <label>Course</label>
                        <select name="course_id">
                            <option value="">All Courses</option>
                            <?php foreach ($lecturer_courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>" <?php echo $course_filter == $course['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name'] . ' (' . $course['school'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Year of Study</label>
                        <select name="year">
                            <option value="">All Years</option>
                            <option value="1" <?php echo $year_filter === '1' ? 'selected' : ''; ?>>Year 1</option>
                            <option value="2" <?php echo $year_filter === '2' ? 'selected' : ''; ?>>Year 2</option>
                            <option value="3" <?php echo $year_filter === '3' ? 'selected' : ''; ?>>Year 3</option>
                            <option value="4" <?php echo $year_filter === '4' ? 'selected' : ''; ?>>Year 4</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="students.php" class="btn btn-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="export-buttons">
            <button class="btn-export" onclick="exportData('pdf')">Export PDF</button>
            <button class="btn-export" onclick="exportData('excel')">Export Excel</button>
            <button class="btn-export" onclick="exportData('csv')">Export CSV</button>
        </div>

        <!-- Students Table -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Students (<?php echo count($students); ?>)</h2>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>School</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>GPA</th>
                        <th>Enrolled</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                            <?php if ($student['phone']): ?>
                                <br><small><?php echo htmlspecialchars($student['phone']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td>
                            <span class="school-badge"><?php echo htmlspecialchars($student['school']); ?></span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($student['course_code']); ?></strong><br>
                            <small><?php echo htmlspecialchars($student['course_name']); ?></small>
                        </td>
                        <td>Year <?php echo $student['year_of_study']; ?></td>
                        <td>
                            <?php if ($student['gpa']): ?>
                                <span class="status-badge <?php echo $student['gpa'] >= 3.5 ? 'status-confirmed' : ($student['gpa'] >= 2.5 ? 'status-pending' : 'status-active'); ?>">
                                    <?php echo number_format($student['gpa'], 2); ?>
                                </span>
                            <?php else: ?>
                                <span class="status-badge">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($student['enrollment_date'])); ?></td>
                        <td>
                            <?php if ($student['grade']): ?>
                                <span class="status-badge status-confirmed"><?php echo htmlspecialchars($student['grade']); ?></span>
                            <?php else: ?>
                                <span class="status-badge">Pending</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($students)): ?>
        <div class="content-card">
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <h3>No Students Found</h3>
                <p>No students are currently enrolled in your courses, or no students match your search criteria.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- School Distribution -->
        <?php if (!empty($school_distribution)): ?>
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Student School Distribution</h2>
            </div>
            <div class="school-distribution">
                <?php foreach ($school_distribution as $school => $count): ?>
                <div class="school-bar">
                    <div class="school-label"><?php echo htmlspecialchars($school); ?></div>
                    <div class="school-progress">
                        <div class="school-fill" style="width: <?php echo ($count / $total_students) * 100; ?>%"></div>
                    </div>
                    <div class="school-count"><?php echo $count; ?> students</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Year Distribution Chart -->
        <?php if (!empty($year_distribution)): ?>
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Student Year Distribution</h2>
            </div>
            <div class="year-distribution">
                <?php foreach ($year_distribution as $year => $count): ?>
                <div class="year-bar">
                    <div class="year-label">Year <?php echo $year; ?></div>
                    <div class="year-progress">
                        <div class="year-fill" style="width: <?php echo ($count / $total_students) * 100; ?>%"></div>
                    </div>
                    <div class="year-count"><?php echo $count; ?> students</div>
                </div>
                <?php endforeach; ?>
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