<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$pdo = $db->getConnection();

$student_id = $_SESSION['student_id'];

// ✅ Fetch all courses the student is enrolled in using student_enrollments
$course_query = "
    SELECT 
        ca.*, 
        c.course_name, 
        CONCAT(l.first_name, ' ', l.last_name) AS lecturer_name,
        l.email AS lecturer_email,
        l.department
    FROM student_enrollments se
    JOIN course_allocations ca ON se.course_id = ca.course_id
    JOIN courses c ON ca.course_id = c.id
    JOIN lecturers l ON ca.lecturer_id = l.id
    WHERE se.student_id = ?
";
$stmt = $pdo->prepare($course_query);
$stmt->execute([$student_id]);
// $courses = $stmt->fetchAll();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);



// ✅ Initialize schedule_data
$schedule_data = [];
$days_map = ['Monday' => 'Mon', 'Tuesday' => 'Tue', 'Wednesday' => 'Wed', 'Thursday' => 'Thu', 'Friday' => 'Fri'];

foreach ($courses as $index => $course) {
    $course_id = $course['course_id'];

    $schedule_query = "SELECT * FROM schedules WHERE course_id = ?";
    $schedule_stmt = $pdo->prepare($schedule_query);
    $schedule_stmt->execute([$course_id]);
    $schedules = $schedule_stmt->fetchAll();

    $course_schedule_string = '';

    foreach ($schedules as $sched) {
        $day_full = ucfirst(strtolower($sched['day_of_week']));
        $day_abbr = $days_map[$day_full] ?? substr($day_full, 0, 3);
        $time = date("g:i A", strtotime($sched['start_time'])) . ' - ' . date("g:i A", strtotime($sched['end_time']));
        $room = $sched['room'] ?? 'TBA';

        $course_schedule_string .= "{$day_abbr} {$time}, ";

        if (!isset($schedule_data[$day_abbr])) {
            $schedule_data[$day_abbr] = [];
        }

        $schedule_data[$day_abbr][] = [
            'course' => $course,
            'time' => $time,
            'room' => $room
        ];
    }

    $courses[$index]['schedule'] = rtrim($course_schedule_string, ', ');
    $courses[$index]['location'] = implode(', ', array_column($schedules, 'room'));
}




?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - Student Portal</title>
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
            <h2 class="dashboard-title">My Schedule</h2>
            <p class="dashboard-subtitle">View your weekly class schedule.</p>
        </div>

        <nav class="nav-tabs">
            <a href="student-dashboard.php" class="nav-tab">Dashboard</a>
            <a href="my-courses.php" class="nav-tab">My Courses</a>
            <a href="schedule.php" class="nav-tab active">Schedule</a>
            <a href="grades.php" class="nav-tab">Grades</a>
            <a href="requests.php" class="nav-tab">Requests</a>
            <a href="profile.php" class="nav-tab">Profile</a>
        </nav>

        <div class="dashboard-content">
            <!-- Schedule Calendar -->
            <div class="content-section full-width">
                <div class="section-header">
                    <h3 class="section-title">Weekly Schedule</h3>
                    <div class="schedule-controls">
                        <button class="btn btn-primary" onclick="printSchedule()">
                            <i class="fas fa-print"></i> Print Schedule
                        </button>
                    </div>
                </div>

                <div class="schedule-calendar">
                    <div class="calendar-header">
                        <div class="time-slot">Time</div>
                        <div class="day-header">Monday</div>
                        <div class="day-header">Tuesday</div>
                        <div class="day-header">Wednesday</div>
                        <div class="day-header">Thursday</div>
                        <div class="day-header">Friday</div>
                    </div>
                    
                    <div class="calendar-body">
                        <?php
                        $time_slots = ['8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM'];
                        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

                        foreach ($time_slots as $time):
                        ?>
                            <div class="time-row">
                                <div class="time-slot"><?php echo $time; ?></div>
                                <?php foreach ($days as $day): ?>
                                    <div class="schedule-cell">
                                        <?php
                                        if (isset($schedule_data[$day])) {
                                            foreach ($schedule_data[$day] as $class) {
                                                $start_hour = explode(' - ', $class['time'])[0];
                                                $time_normalized = strtolower(trim($time));
                                                $start_hour_normalized = strtolower(trim($start_hour));

                                                if ($time_normalized === $start_hour_normalized) {
                                                    echo '<div class="class-block">';
                                                    echo '<div class="class-name">' . htmlspecialchars($class['course']['course_name']) . '</div>';
                                                    echo '<div class="class-lecturer">' . htmlspecialchars($class['course']['lecturer_name']) . '</div>';
                                                    echo '</div>';
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

            <!-- Course List View -->
            <div class="content-section full-width">
                <div class="section-header">
                    <h3 class="section-title">Course Schedule List</h3>
                </div>
                
                <?php
                    $seen_courses = [];

                        foreach ($courses as $course):
                            if (in_array($course['course_id'], $seen_courses)) {
                                continue; // skip duplicate
                            }
                            $seen_courses[] = $course['course_id'];
                        ?>
                            <div class="schedule-item">
                                <div class="schedule-info">
                                    <h4 class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></h4>
                                    <p class="course-details">
                                        <p><i class="fas fa-user"> </i> <?php echo htmlspecialchars($course['lecturer_name']); ?> </p>
                                        <p><i class="fas fa-clock">    </i>   <?php echo htmlspecialchars($course['schedule'] ?? 'TBA'); ?> </p>
                                        <p><i class="fas fa-map-marker-alt">   </i>   <?php echo htmlspecialchars($course['location'] ?? 'TBA'); ?> </p>
                                    </p>
                                </div>
                                <div class="schedule-actions">
                                    <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/student-script.js"></script>
    <script>
        function printSchedule() {
            window.print();
        }
    </script>
</body>
</html>
