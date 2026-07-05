<?php
require_once '../includes/auth.php';
requireAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get report statistics
$stats = [];

// Allocation statistics by school
$query = "SELECT c.school, ca.status, COUNT(*) as count 
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id 
          GROUP BY c.school, ca.status 
          ORDER BY c.school, ca.status";
$stmt = $db->prepare($query);
$stmt->execute();
$allocation_stats_by_school = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Department statistics by school
$query = "SELECT c.school, c.department, COUNT(ca.id) as allocations, COUNT(DISTINCT ca.lecturer_id) as lecturers
          FROM courses c 
          LEFT JOIN course_allocations ca ON c.id = ca.course_id AND ca.status = 'confirmed'
          GROUP BY c.school, c.department 
          ORDER BY c.school, allocations DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$department_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lecturer workload by school
$query = "SELECT l.school, l.username, l.first_name, l.last_name, l.department, COUNT(ca.id) as course_count,
          AVG(se.enrollment_count) as avg_students
          FROM lecturers l 
          LEFT JOIN course_allocations ca ON l.id = ca.lecturer_id AND ca.status = 'confirmed'
          LEFT JOIN (
              SELECT course_id, COUNT(*) as enrollment_count 
              FROM student_enrollments 
              WHERE status = 'enrolled' 
              GROUP BY course_id
          ) se ON ca.course_id = se.course_id
          GROUP BY l.id 
          ORDER BY l.school, course_count DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$lecturer_workload = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Course enrollment statistics by school
$query = "SELECT c.school, c.course_code, c.course_name, c.max_students, COUNT(se.id) as enrolled_students,
          ROUND((COUNT(se.id) / c.max_students) * 100, 1) as fill_percentage
          FROM courses c 
          LEFT JOIN student_enrollments se ON c.id = se.course_id AND se.status = 'enrolled'
          WHERE c.status = 'active'
          GROUP BY c.id 
          ORDER BY c.school, fill_percentage DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$enrollment_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// School-wise summary
$query = "SELECT 
    c.school,
    COUNT(DISTINCT c.id) as total_courses,
    COUNT(DISTINCT l.id) as total_lecturers,
    COUNT(DISTINCT s.id) as total_students,
    COUNT(DISTINCT ca.id) as total_allocations
    FROM courses c
    LEFT JOIN lecturers l ON c.school = l.school
    LEFT JOIN students s ON c.school = s.school
    LEFT JOIN course_allocations ca ON c.id = ca.course_id AND ca.status = 'confirmed'
    GROUP BY c.school
    ORDER BY total_courses DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$school_summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Course Allocation System</title>
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
            <h1 class="page-title">System Reports</h1>
            <p class="page-subtitle">Analytics and insights for course allocation system</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="lecturers.php" class="nav-link">Lecturers</a>
                <a href="courses.php" class="nav-link">Courses</a>
                <!-- <a href="students.php" class="nav-link">Students</a> -->
                <a href="allocations.php" class="nav-link">Allocations</a>
                <a href="add_schedule.php" class="nav-link">Schedule</a>
                <a href="reports.php" class="nav-link active">Reports</a>
                <a href="settings.php" class="nav-link">Settings</a>
            </div>
        </nav>

        <div class="export-buttons">
            <a href="#" class="btn-export" data-format="pdf" data-type="reports">Export PDF</a>
            <a href="#" class="btn-export" data-format="excel" data-type="reports">Export Excel</a>
            <a href="#" class="btn-export" data-format="csv" data-type="reports">Export CSV</a>
        </div>

        <!-- School Summary -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">School Summary</h2>
            </div>
            <div class="stats-grid">
                <?php foreach ($school_summary as $school): ?>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title"><?php echo htmlspecialchars($school['school']); ?></span>
                        <div class="stat-icon">🏫</div>
                    </div>
                    <div class="stat-value"><?php echo $school['total_courses']; ?> Courses</div>
                    <div class="stat-change">
                        <?php echo $school['total_lecturers']; ?> Lecturers • 
                        <?php echo $school['total_students']; ?> Students • 
                        <?php echo $school['total_allocations']; ?> Allocations
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Allocation Status Overview by School -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Allocation Status by School</h2>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>School</th>
                        <th>Pending</th>
                        <th>Confirmed</th>
                        <th>Cancelled</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $school_stats = [];
                    foreach ($allocation_stats_by_school as $stat) {
                        if (!isset($school_stats[$stat['school']])) {
                            $school_stats[$stat['school']] = [
                                'pending' => 0,
                                'confirmed' => 0,
                                'cancelled' => 0,
                                'total' => 0
                            ];
                        }
                        $school_stats[$stat['school']][$stat['status']] = $stat['count'];
                        $school_stats[$stat['school']]['total'] += $stat['count'];
                    }
                    
                    foreach ($school_stats as $school => $stats): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($school); ?></strong></td>
                        <td><?php echo $stats['pending']; ?></td>
                        <td><?php echo $stats['confirmed']; ?></td>
                        <td><?php echo $stats['cancelled']; ?></td>
                        <td><strong><?php echo $stats['total']; ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Department Statistics by School -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Department Statistics by School</h2>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>School</th>
                        <th>Department</th>
                        <th>Course Allocations</th>
                        <th>Active Lecturers</th>
                        <th>Allocation Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($department_stats as $dept): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($dept['school']); ?></strong></td>
                        <td><?php echo htmlspecialchars($dept['department']); ?></td>
                        <td><?php echo $dept['allocations']; ?></td>
                        <td><?php echo $dept['lecturers']; ?></td>
                        <td>
                            <?php if ($dept['lecturers'] > 0): ?>
                                <span class="status-badge status-active">
                                    <?php echo round($dept['allocations'] / $dept['lecturers'], 1); ?> per lecturer
                                </span>
                            <?php else: ?>
                                <span class="status-badge">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="content-grid">
            <!-- Lecturer Workload by School -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Lecturer Workload by School</h2>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>School</th>
                            <th>Lecturer</th>
                            <th>Courses</th>
                            <th>Avg Students</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($lecturer_workload, 0, 15) as $lecturer): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($lecturer['school']); ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($lecturer['first_name'] . ' ' . $lecturer['last_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($lecturer['department']); ?></small>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $lecturer['course_count'] > 3 ? 'pending' : 'confirmed'; ?>">
                                    <?php echo $lecturer['course_count']; ?> courses
                                </span>
                            </td>
                            <td>
                                <?php echo $lecturer['avg_students'] ? round($lecturer['avg_students']) : 0; ?> students
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Course Enrollment by School -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Course Enrollment by School</h2>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>School</th>
                            <th>Course</th>
                            <th>Enrollment</th>
                            <th>Fill Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($enrollment_stats, 0, 15) as $course): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($course['school']); ?></strong></td>
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- School Distribution Chart -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">School Distribution</h2>
            </div>
            <div class="chart-container">
                <canvas id="schoolDistributionChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <script>
        // School Distribution Chart
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('schoolDistributionChart');
            const ctx = canvas.getContext('2d');
            
            // Sample data for school distribution
            const schoolData = {
                labels: [<?php echo '"' . implode('","', array_column($school_summary, 'school')) . '"'; ?>],
                datasets: [{
                    label: 'Courses per School',
                    data: [<?php echo implode(',', array_column($school_summary, 'total_courses')); ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            };
            
            drawBarChart(ctx, schoolData, canvas.width, canvas.height);
        });
        
        function drawBarChart(ctx, data, width, height) {
            const padding = 40;
            const chartWidth = width - 2 * padding;
            const chartHeight = height - 2 * padding;
            
            // Clear canvas
            ctx.clearRect(0, 0, width, height);
            
            // Set styles
            ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            
            // Draw bars
            const maxValue = Math.max(...data.datasets[0].data);
            const barWidth = chartWidth / data.labels.length;
            const barSpacing = 10;
            
            data.labels.forEach((label, index) => {
                const value = data.datasets[0].data[index];
                const barHeight = (value / maxValue) * chartHeight;
                const x = padding + index * barWidth + barSpacing;
                const y = height - padding - barHeight;
                
                // Draw bar
                ctx.fillStyle = data.datasets[0].backgroundColor[index];
                ctx.fillRect(x, y, barWidth - 2 * barSpacing, barHeight);
                
                // Draw label
                ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
                ctx.fillText(label, x + (barWidth - 2 * barSpacing) / 2, height - padding + 20);
                
                // Draw value
                ctx.fillText(value, x + (barWidth - 2 * barSpacing) / 2, y - 10);
            });
        }
    </script>
</body>
</html>