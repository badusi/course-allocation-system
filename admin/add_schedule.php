<?php
require_once '../includes/auth.php';
requireAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Handle schedule form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lecturer_id = $_POST['lecturer_id'];
    $course_id = $_POST['course_id'];
    $day = $_POST['day'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $room = $_POST['room'];

    $stmt = $db->prepare("INSERT INTO schedules (lecturer_id, course_id, day_of_week, start_time, end_time, room) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$lecturer_id, $course_id, $day, $start, $end, $room]);
}

// Fetch lecturers with school information
$lecturers = $db->query("SELECT id, username, first_name, last_name, school FROM lecturers ORDER BY school, username")->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses with school information
$courses = $db->query("SELECT id, course_code, course_name, school FROM courses ORDER BY school, course_code")->fetchAll(PDO::FETCH_ASSOC);

// Fetch schedules with school information
$schedules = $db->query("
    SELECT s.*, l.username AS lecturer_name, l.first_name, l.last_name, l.school as lecturer_school, 
           c.course_code, c.course_name, c.school as course_school
    FROM schedules s
    JOIN lecturers l ON s.lecturer_id = l.id
    JOIN courses c ON s.course_id = c.id
    ORDER BY s.day_of_week, s.start_time
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lecture Schedule</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 999;
        }
        .modal-content {
        background-color: white;
        padding: 25px;
        border-radius: 8px;
        width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        }
        .close-btn {
        position: absolute;
        top: 12px;
        right: 16px;
        font-size: 22px;
        font-weight: bold;
        color: #888;
        cursor: pointer;
        }
        .form-group {
        margin-bottom: 15px;
        }
        .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        }
        .form-group input,
        .form-group select {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
        }
        .school-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 8px;
        }
    </style>

</head>
<body class="dashboard">>
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
        <h1 class="page-title">Add Lecturer Schedule</h1>
        <p class="page-subtitle">Schedule lectures for lecturers and students</p>
    </div>
       <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="lecturers.php" class="nav-link">Lecturers</a>
                <a href="courses.php" class="nav-link">Courses</a>
                <!-- <a href="students.php" class="nav-link">Students</a> -->
                <a href="allocations.php" class="nav-link ">Allocations</a>
                <a href="add_schedule.php" class="nav-link active">Schedule</a>
                <a href="reports.php" class="nav-link">Reports</a>
                <a href="settings.php" class="nav-link">Settings</a>
            </div>
        </nav>

        <div class="content-card">
            <div class="card-header">
                <!-- Add New Schedule Button -->
                <h2 class="card-title">Schedule </h2>
                <button id="openScheduleModal" class="btn btn-primary" style="margin-bottom: 20px;">Add New Schedule</button>
             </div>


    <hr style="margin: 40px 0; border-color: rgba(255,255,255,0.2);">

    <div class="content-card">
        <h2 class="card-title">Scheduled Lectures</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Lecturer</th>
                    <th>Course</th>
                    <th>School</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Room</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $sch): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($sch['first_name'] . ' ' . $sch['last_name']) ?>
                    </td>
                    <td><?= htmlspecialchars($sch['course_code']) ?></td>
                    <td>
                        <span class="school-badge"><?= htmlspecialchars($sch['course_school']) ?></span>
                    </td>
                    <td><?= $sch['day_of_week'] ?></td>
                    <td><?= date('H:i', strtotime($sch['start_time'])) ?> - <?= date('H:i', strtotime($sch['end_time'])) ?></td>
                    <td><?= htmlspecialchars($sch['room']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Schedule Modal -->
<div id="scheduleModal" class="modal-overlay" style="display: none;">
  <div class="modal-content">
    <span class="close-btn" onclick="document.getElementById('scheduleModal').style.display='none'">&times;</span>
    <h2>Add New Schedule</h2>
    <form method="POST">
      <div class="form-group">
        <label>Lecturer</label>
        <select name="lecturer_id" required>
          <option value="">Select Lecturer</option>
          <?php foreach ($lecturers as $lecturer): ?>
            <option value="<?= $lecturer['id'] ?>">
                <?= htmlspecialchars($lecturer['first_name'] . ' ' . $lecturer['last_name']) ?> 
                <small>(<?= htmlspecialchars($lecturer['school']) ?>)</small>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Course</label>
        <select name="course_id" required>
          <option value="">Select Course</option>
          <?php foreach ($courses as $course): ?>
            <option value="<?= $course['id'] ?>">
                <?= htmlspecialchars($course['course_code']) ?> 
                <small>(<?= htmlspecialchars($course['school']) ?>)</small>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Day</label>
        <select name="day" required>
          <option value="">Select Day</option>
          <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day): ?>
            <option value="<?= $day ?>"><?= $day ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Start Time</label>
        <input type="time" name="start_time" required>
      </div>

      <div class="form-group">
        <label>End Time</label>
        <input type="time" name="end_time" required>
      </div>

      <div class="form-group">
        <label>Room</label>
        <input type="text" name="room" placeholder="e.g. LT1 or Room A201">
      </div>

      <button type="submit" class="btn btn-primary">Add Schedule</button>
    </form>
  </div>
</div>



<script>
document.getElementById('openScheduleModal').addEventListener('click', function() {
  document.getElementById('scheduleModal').style.display = 'flex';
});
</script>
<script src="../assets/js/dashboard.js"></script>
</body>
</html>