<?php
require_once '../includes/auth.php';
requireAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_course':
                $course_code = $_POST['course_code'];
                $course_name = $_POST['course_name'];
                $description = $_POST['description'];
                $credits = $_POST['credits'];
                $school = $_POST['school']; // NEW: School field
                $department = $_POST['department'];
                $semester = $_POST['semester'];
                $year = $_POST['year'];
                $max_students = $_POST['max_students'];
                
                $query = "INSERT INTO courses (course_code, course_name, description, credits, school, department, semester, year, max_students) 
                         VALUES (:course_code, :course_name, :description, :credits, :school, :department, :semester, :year, :max_students)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':course_code', $course_code);
                $stmt->bindParam(':course_name', $course_name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':credits', $credits);
                $stmt->bindParam(':school', $school); // NEW
                $stmt->bindParam(':department', $department);
                $stmt->bindParam(':semester', $semester);
                $stmt->bindParam(':year', $year);
                $stmt->bindParam(':max_students', $max_students);
                
                if ($stmt->execute()) {
                    $success_message = "Course added successfully!";
                } else {
                    $error_message = "Error adding course.";
                }
                break;
                
            case 'update_course':
                $id = $_POST['course_id'];
                $course_name = $_POST['course_name'];
                $description = $_POST['description'];
                $credits = $_POST['credits'];
                $school = $_POST['school']; // NEW: School field
                $department = $_POST['department'];
                $semester = $_POST['semester'];
                $year = $_POST['year'];
                $max_students = $_POST['max_students'];
                $status = $_POST['status'];
                
                $query = "UPDATE courses SET course_name = :course_name, description = :description, credits = :credits, 
                         school = :school, department = :department, semester = :semester, year = :year, max_students = :max_students, status = :status 
                         WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':course_name', $course_name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':credits', $credits);
                $stmt->bindParam(':school', $school); // NEW
                $stmt->bindParam(':department', $department);
                $stmt->bindParam(':semester', $semester);
                $stmt->bindParam(':year', $year);
                $stmt->bindParam(':max_students', $max_students);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $success_message = "Course updated successfully!";
                } else {
                    $error_message = "Error updating course.";
                }
                break;
        }
    }
}

// Get courses with enrollment counts
$search = isset($_GET['search']) ? $_GET['search'] : '';
$school_filter = isset($_GET['school']) ? $_GET['school'] : ''; // NEW: School filter
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT c.*, COUNT(se.id) as enrollment_count, 
          l.username as lecturer_name
          FROM courses c 
          LEFT JOIN student_enrollments se ON c.id = se.course_id AND se.status = 'enrolled'
          LEFT JOIN course_allocations ca ON c.id = ca.course_id AND ca.status = 'confirmed'
          LEFT JOIN lecturers l ON ca.lecturer_id = l.id
          WHERE 1=1";

$params = [];
if ($search) {
    $query .= " AND (c.course_code LIKE :search OR c.course_name LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($school_filter) {
    $query .= " AND c.school = :school"; // NEW: School filter
    $params[':school'] = $school_filter;
}
if ($department_filter) {
    $query .= " AND c.department = :department";
    $params[':department'] = $department_filter;
}
if ($status_filter) {
    $query .= " AND c.status = :status";
    $params[':status'] = $status_filter;
}

$query .= " GROUP BY c.id ORDER BY c.course_code";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get schools for filter
$query = "SELECT DISTINCT school FROM courses ORDER BY school";
$stmt = $db->prepare($query);
$stmt->execute();
$schools = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get departments for filter
$query = "SELECT DISTINCT department FROM courses ORDER BY department";
$stmt = $db->prepare($query);
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Course Allocation System</title>
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
            <h1 class="page-title">Courses Management</h1>
            <p class="page-subtitle">Manage course catalog and information</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="lecturers.php" class="nav-link">Lecturers</a>
                <a href="courses.php" class="nav-link active">Courses</a>
                <!-- <a href="students.php" class="nav-link">Students</a> -->
                <a href="allocations.php" class="nav-link">Allocations</a>
                <a href="add_schedule.php" class="nav-link">Schedule</a>
                <a href="reports.php" class="nav-link">Reports</a>
                <a href="settings.php" class="nav-link">Settings</a>
            </div>
        </nav>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="filters">
            <form method="GET" class="filter-grid">
                <div class="filter-group">
                    <label>Search Courses</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Course code or name...">
                </div>
                <!-- NEW: School Filter -->
                <div class="filter-group">
                    <label>School</label>
                    <select name="school">
                        <option value="">All Schools</option>
                        <?php foreach ($schools as $school): ?>
                            <option value="<?php echo htmlspecialchars($school); ?>" <?php echo $school_filter === $school ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($school); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Department</label>
                    <select name="department">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo $department_filter === $dept ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="courses.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Courses (<?php echo count($courses); ?>)</h2>
                <button class="btn btn-primary" data-modal="add-course-modal">Add New Course</button>
            </div>

            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <div class="course-header">
                        <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                        <span class="status-badge status-<?php echo $course['status']; ?>">
                            <?php echo ucfirst($course['status']); ?>
                        </span>
                    </div>
                    <h3 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                    <p class="course-description"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                    <div class="course-meta">
                        <!-- NEW: Display School -->
                        <span><strong>School:</strong> <?php echo htmlspecialchars($course['school']); ?></span>
                        <span><strong>Dept:</strong> <?php echo htmlspecialchars($course['department']); ?></span>
                    </div>
                    <div class="course-meta">
                        <span><?php echo $course['credits']; ?> Credits</span>
                        <span><?php echo $course['enrollment_count']; ?>/<?php echo $course['max_students']; ?> Students</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo ($course['enrollment_count'] / $course['max_students']) * 100; ?>%"></div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <button class="btn btn-secondary btn-sm" onclick="editCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)">Edit</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Add Course Modal - UPDATED with School field -->
    <div id="add-course-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Course</h3>
                <button class="close-btn">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_course">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="course_code">Course Code</label>
                        <input type="text" id="course_code" name="course_code" required>
                    </div>
                    <div class="form-group">
                        <label for="course_name">Course Name</label>
                        <input type="text" id="course_name" name="course_name" required>
                    </div>
                    <div class="form-group">
                        <label for="credits">Credits</label>
                        <input type="number" id="credits" name="credits" min="1" max="6" required>
                    </div>
                    <!-- NEW: School Field -->
                    <div class="form-group">
                        <label for="school">School</label>
                        <select id="school" name="school" required>
                            <option value="School of Science">School of Science</option>
                            <option value="School of Engineering">School of Engineering</option>
                            <option value="School of Business">School of Technology</option>
                            <option value="School of Arts">School of Management</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" required>
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester" required>
                            <option value="None">.............</option>
                            <option value="Fall">First Semester</option>
                            <option value="Spring">Second Semester</option>
                            <option value="Summer">Third Semester</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year" value="2024" min="2024" max="2150" required>
                    </div>
                    <div class="form-group">
                        <label for="max_students">Max Students</label>
                        <input type="number" id="max_students" name="max_students" min="1" max="100000000000000000000" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Add Course</button>
                    <button type="button" class="btn btn-secondary close-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Course Modal - UPDATED with School field -->
    <div id="edit-course-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Course</h3>
                <button class="close-btn">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_course">
                <input type="hidden" id="edit_course_id" name="course_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_course_name">Course Name</label>
                        <input type="text" id="edit_course_name" name="course_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_credits">Credits</label>
                        <input type="number" id="edit_credits" name="credits" min="1" max="6" required>
                    </div>
                    <!-- NEW: School Field -->
                    <div class="form-group">
                        <label for="edit_school">School</label>
                        <select id="edit_school" name="school" required>
                            <option value="School of Science">School of Science</option>
                            <option value="School of Engineering">School of Engineering</option>
                            <option value="School of Business">School of Technology</option>
                            <option value="School of Arts">School of Management</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_department">Department</label>
                        <input type="text" id="edit_department" name="department" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_semester">Semester</label>
                        <select id="edit_semester" name="semester" required>
                             <option value="None">.............</option>
                            <option value="Fall">First Semester</option>
                            <option value="Spring">Second Semester</option>
                            <option value="Summer">Third Semester</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_year">Year</label>
                        <input type="number" id="edit_year" name="year" min="2024" max="2030" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_max_students">Max Students</label>
                        <input type="number" id="edit_max_students" name="max_students" min="1" max="200" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Course</button>
                    <button type="button" class="btn btn-secondary close-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <script>
        function editCourse(course) {
            document.getElementById('edit_course_id').value = course.id;
            document.getElementById('edit_course_name').value = course.course_name;
            document.getElementById('edit_credits').value = course.credits;
            document.getElementById('edit_school').value = course.school; // NEW
            document.getElementById('edit_department').value = course.department;
            document.getElementById('edit_semester').value = course.semester;
            document.getElementById('edit_year').value = course.year;
            document.getElementById('edit_max_students').value = course.max_students;
            document.getElementById('edit_status').value = course.status;
            document.getElementById('edit_description').value = course.description;
            document.getElementById('edit-course-modal').classList.add('active');
        }
    </script>
</body>
</html>