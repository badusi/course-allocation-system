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

// Handle form submission
if ($_POST) {
    $request_type = $_POST['request_type'];
    $course_id = !empty($_POST['course_id']) ? $_POST['course_id'] : null;
    $description = $_POST['description'];
    
    $stmt = $pdo->prepare("INSERT INTO course_requests (student_id, course_id, request_type, description, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([$student_id, $course_id, $request_type, $description]);
    
    $success_message = "Request submitted successfully!";
}

// Fetch student's requests
$requests_query = "
    SELECT cr.*, c.course_name as course_name, c.course_code as course_code
        FROM course_requests cr
        LEFT JOIN courses c ON cr.course_id = c.id
        WHERE cr.student_id = ?
        ORDER BY cr.created_at DESC

";
$stmt = $pdo->prepare($requests_query);
$stmt->execute([$student_id]);
$requests = $stmt->fetchAll();

// Fetch available courses for requests
$courses_query = "SELECT id, course_name, course_code FROM courses WHERE status = 'active' ORDER BY course_name";
$stmt = $pdo->prepare($courses_query);
$stmt->execute();
$available_courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests - Student Portal</title>
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
            <h2 class="dashboard-title">Course Requests</h2>
            <p class="dashboard-subtitle">Submit and track your course-related requests.</p>
        </div>

        <nav class="nav-tabs">
            <a href="student-dashboard.php" class="nav-tab">Dashboard</a>
            <a href="my-courses.php" class="nav-tab">My Courses</a>
            <a href="schedule.php" class="nav-tab">Schedule</a>
            <a href="grades.php" class="nav-tab">Grades</a>
            <a href="requests.php" class="nav-tab active">Requests</a>
            <a href="profile.php" class="nav-tab">Profile</a>
        </nav>

        <div class="dashboard-content">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <!-- New Request Form -->
            <div class="content-section full-width">
                <div class="section-header">
                    <h3 class="section-title">Submit New Request</h3>
                </div>
                
                <form method="POST" class="request-form">
                    <div class="form-group">
                        <label for="request_type">Request Type</label>
                        <select name="request_type" id="request_type" style="background-color: #000000;" required>
                            <option value="">Select request type</option>
                            <option value="enrollment">Course Enrollment</option>
                            <option value="drop">Drop Course</option>
                            <option value="schedule_change">Schedule Change</option>
                            <option value="grade_review">Grade Review</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group" id="course-select" style="display: none;">
                        <label for="course_id">Select Course</label>
                        <select name="course_id" id="course_id" style="background-color: #000000;">
                            <option value="">Select a course</option>
                            <?php foreach ($available_courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>">
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" rows="4" required 
                                placeholder="Please provide details about your request..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </form>
            </div>

            <!-- Request History -->
            <div class="content-section full-width">
                <div class="section-header">
                    <h3 class="section-title">Request History</h3>
                </div>
                
                <div class="requests-list">
                    <?php if (empty($requests)): ?>
                        <p class="no-data">No requests submitted yet.</p>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                            <div class="request-item">
                                <div class="request-header">
                                    <h4 class="request-type"><?php echo ucfirst(str_replace('_', ' ', $request['request_type'])); ?></h4>
                                    <span class="request-status status-<?php echo $request['status']; ?>">
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </div>
                                <div class="request-details">
                                    <?php if ($request['course_name']): ?>
                                        <p><strong>Course:</strong> <?php echo htmlspecialchars($request['course_code'] . ' - ' . $request['course_name']); ?></p>
                                    <?php endif; ?>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($request['description']); ?></p>
                                    <p><strong>Submitted:</strong> <?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?></p>
                                    <?php if ($request['admin_response']): ?>
                                        <p><strong>Response:</strong> <?php echo htmlspecialchars($request['admin_response']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/student-script.js"></script>
    <script>
        document.getElementById('request_type').addEventListener('change', function() {
            const courseSelect = document.getElementById('course-select');
            const selectedType = this.value;
            
            if (selectedType === 'enrollment' || selectedType === 'drop' || selectedType === 'schedule_change' || selectedType === 'grade_review') {
                courseSelect.style.display = 'block';
                document.getElementById('course_id').required = true;
            } else {
                courseSelect.style.display = 'none';
                document.getElementById('course_id').required = false;
            }
        });
    </script>
</body>
</html>
