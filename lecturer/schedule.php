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

// Get lecturer's schedule with school information
$query = "SELECT c.course_code, c.course_name, c.school, s.day_of_week, s.start_time, s.end_time, s.room
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id 
          JOIN schedules s ON c.id = s.course_id
          WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed'
          ORDER BY 
            CASE s.day_of_week 
                WHEN 'Monday' THEN 1
                WHEN 'Tuesday' THEN 2
                WHEN 'Wednesday' THEN 3
                WHEN 'Thursday' THEN 4
                WHEN 'Friday' THEN 5
                WHEN 'Saturday' THEN 6
                WHEN 'Sunday' THEN 7
            END, s.start_time";
$stmt = $db->prepare($query);
$stmt->bindParam(':lecturer_id', $lecturer_id);
$stmt->execute();
$schedule_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize schedule by day
$schedule_by_day = [];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// Initialize all days
foreach ($days as $day) {
    $schedule_by_day[$day] = [];
}

// Fill with items
foreach ($schedule_items as $item) {
    $day_raw = trim($item['day_of_week']);
    $day = ucfirst(strtolower($day_raw)); // Ensure "monday", "Monday", "MONDAY" → "Monday"

    if (in_array($day, $days)) {
        $schedule_by_day[$day][] = $item;
    } else {
        error_log("Invalid day: '$day_raw' parsed as '$day'"); // log invalid days
    }
}

// Get time slots (8 AM to 6 PM)
$time_slots = [];
for ($hour = 8; $hour <= 18; $hour++) {
    $time_slots[] = sprintf('%02d:00', $hour); // Now matches format '08:00', '09:00'
}

// Calculate teaching statistics by school
$school_teaching_hours = [];
$total_hours = 0;
$days_teaching = [];

foreach ($schedule_items as $item) {
    $start = new DateTime($item['start_time']);
    $end = new DateTime($item['end_time']);
    $duration = $end->diff($start);
    $hours = $duration->h + ($duration->i / 60);
    $total_hours += $hours;
    
    // Track hours by school
    $school = $item['school'];
    $school_teaching_hours[$school] = ($school_teaching_hours[$school] ?? 0) + $hours;
    
    if (!in_array($item['day_of_week'], $days_teaching)) {
        $days_teaching[] = $item['day_of_week'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - Course Allocation System</title>
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
        .schedule-school {
            font-size: 0.8em;
            color: #666;
            margin-top: 2px;
        }
        .school-breakdown {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        .school-breakdown h4 {
            margin-bottom: 15px;
            color: #333;
        }
        .school-hours {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .school-hour-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .school-name {
            min-width: 150px;
            font-weight: 500;
        }
        .hour-bar {
            flex: 1;
            height: 20px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .hour-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        .hour-count {
            min-width: 80px;
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
            <h1 class="page-title">My Schedule</h1>
            <p class="page-subtitle">Your weekly teaching schedule</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="courses.php" class="nav-link">My Courses</a>
                <a href="schedule.php" class="nav-link active">Schedule</a>
                <!-- <a href="students.php" class="nav-link">Students</a> -->
                <a href="reports.php" class="nav-link">Reports</a>
                <a href="profile.php" class="nav-link">Profile</a>
            </div>
        </nav>

        <!-- School Information Banner -->
        <div class="content-card school-banner">
            <div class="school-info">
                <h3>🏫 <?php echo htmlspecialchars($lecturer_school); ?></h3>
                <p>Your teaching schedule across different schools</p>
            </div>
        </div>

        <div class="export-buttons">
            <button class="btn-export" onclick="exportSchedule('pdf')">Export PDF</button>
            <button class="btn-export" onclick="exportSchedule('ical')">Export Calendar</button>
        </div>

        <!-- Weekly Schedule Grid -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Weekly Schedule</h2>
            </div>
            <div class="schedule-container">
                <div class="schedule-grid">
                    <!-- Header row -->
                    <div class="schedule-header time-header">Time</div>
                    <?php foreach ($days as $day): ?>
                        <div class="schedule-header"><?php echo $day; ?></div>
                    <?php endforeach; ?>

                    <!-- Time slots and schedule items -->
                    <?php foreach ($time_slots as $time): ?>
                        <div class="time-slot"><?php echo date('g:i A', strtotime($time)); ?></div>
                        <?php foreach ($days as $day): ?>
                            <div class="schedule-cell" data-day="<?php echo $day; ?>" data-time="<?php echo $time; ?>">
                                <?php
                                if (isset($schedule_by_day[$day])) {
                                    foreach ($schedule_by_day[$day] as $item) {
                                        $item_start_time = date('H:i', strtotime($item['start_time']));
                                        if ($item_start_time === $time) {
                                            echo '<div class="schedule-event">';
                                            echo '<div class="schedule-time">' . date('g:i A', strtotime($item['start_time'])) . ' - ' . date('g:i A', strtotime($item['end_time'])) . '</div>';
                                            echo '<div class="schedule-course">' . htmlspecialchars($item['course_code']) . '</div>';
                                            echo '<div class="schedule-title">' . htmlspecialchars($item['course_name']) . '</div>';
                                            echo '<div class="schedule-school">' . htmlspecialchars($item['school']) . '</div>';
                                            if (!empty($item['room'])) {
                                                echo '<div class="schedule-room">Room: ' . htmlspecialchars($item['room']) . '</div>';
                                            }
                                            echo '</div>';
                                        }
                                    }
                                }
                                ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Schedule List View -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Schedule Details</h2>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Course</th>
                        <th>School</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedule_items as $item): ?>
                    <tr>
                        <td><strong><?php echo $item['day_of_week']; ?></strong></td>
                        <td>
                            <?php echo date('g:i A', strtotime($item['start_time'])); ?> - 
                            <?php echo date('g:i A', strtotime($item['end_time'])); ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($item['course_code']); ?></strong><br>
                            <small><?php echo htmlspecialchars($item['course_name']); ?></small>
                        </td>
                        <td>
                            <span class="school-badge"><?php echo htmlspecialchars($item['school']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($item['room'] ?: 'TBA'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($schedule_items)): ?>
        <div class="content-card">
            <div class="empty-state">
                <div class="empty-icon">📅</div>
                <h3>No Schedule Available</h3>
                <p>Your courses don't have scheduled times yet. Please contact the administrator for scheduling information.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Teaching Load Summary by School -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Teaching Load Summary by School</h2>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Teaching Hours</span>
                        <div class="stat-icon">⏰</div>
                    </div>
                    <div class="stat-value"><?php echo number_format($total_hours, 1); ?></div>
                    <div class="stat-change">Hours per week</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Teaching Days</span>
                        <div class="stat-icon">📅</div>
                    </div>
                    <div class="stat-value"><?php echo count($days_teaching); ?></div>
                    <div class="stat-change">Days per week</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Classes</span>
                        <div class="stat-icon">🏫</div>
                    </div>
                    <div class="stat-value"><?php echo count($schedule_items); ?></div>
                    <div class="stat-change">Class sessions</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Schools Teaching</span>
                        <div class="stat-icon">🎓</div>
                    </div>
                    <div class="stat-value"><?php echo count($school_teaching_hours); ?></div>
                    <div class="stat-change">Different schools</div>
                </div>
            </div>

            <!-- School-wise breakdown -->
            <?php if (!empty($school_teaching_hours)): ?>
            <div class="school-breakdown">
                <h4>Teaching Hours by School</h4>
                <div class="school-hours">
                    <?php foreach ($school_teaching_hours as $school => $hours): ?>
                    <div class="school-hour-item">
                        <div class="school-name"><?php echo htmlspecialchars($school); ?></div>
                        <div class="hour-bar">
                            <div class="hour-fill" style="width: <?php echo ($hours / $total_hours) * 100; ?>%"></div>
                        </div>
                        <div class="hour-count"><?php echo number_format($hours, 1); ?> hours</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <script>
        function exportSchedule(format) {
            alert('Export to ' + format.toUpperCase() + ' functionality would be implemented here');
        }
    </script>
</body>
</html>