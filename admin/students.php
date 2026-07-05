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
            case 'add_student':
                $student_id = $_POST['student_id'] ?? '';
                $first_name = $_POST['first_name'] ?? '';
                $last_name = $_POST['last_name'] ?? '';
                $email = $_POST['email'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $school = $_POST['school'] ?? ''; // NEW: School field
                $department = $_POST['department'] ?? '';
                $year_of_study = $_POST['year_of_study'] ?? '';
                $gpa = $_POST['gpa'] ?? null;
                $password = $_POST['password'] ?? '';

                if ($student_id && $first_name && $last_name && $email && $school && $department && $year_of_study && $password) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $query = "INSERT INTO students (student_id, first_name, last_name, email, phone, school, department, year_of_study, gpa, password)
                            VALUES (:student_id, :first_name, :last_name, :email, :phone, :school, :department, :year_of_study, :gpa, :password)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':student_id', $student_id);
                    $stmt->bindParam(':first_name', $first_name);
                    $stmt->bindParam(':last_name', $last_name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':school', $school); // NEW
                    $stmt->bindParam(':department', $department);
                    $stmt->bindParam(':year_of_study', $year_of_study);
                    $stmt->bindParam(':gpa', $gpa);
                    $stmt->bindParam(':password', $hashed_password);

                    if ($stmt->execute()) {
                        $success_message = "Student added successfully!";
                    } else {
                        $error_message = "Error adding student.";
                    }
                } else {
                    $error_message = "Please fill in all required fields.";
                }
                break;

            case 'update_student':
                $id = $_POST['student_db_id'];
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                $school = $_POST['school']; // NEW: School field
                $department = $_POST['department'];
                $year_of_study = $_POST['year_of_study'];
                $gpa = $_POST['gpa'];
                
                $query = "UPDATE students SET first_name = :first_name, last_name = :last_name, 
                         email = :email, phone = :phone, school = :school, department = :department, 
                         year_of_study = :year_of_study, gpa = :gpa WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':last_name', $last_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':school', $school); // NEW
                $stmt->bindParam(':department', $department);
                $stmt->bindParam(':year_of_study', $year_of_study);
                $stmt->bindParam(':gpa', $gpa);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $success_message = "Student updated successfully!";
                } else {
                    $error_message = "Error updating student.";
                }
                break;
        }
    }
}

// Get students with enrollment counts
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$school_filter = isset($_GET['school']) ? $_GET['school'] : ''; // NEW: School filter
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';
$year_filter = isset($_GET['year']) ? $_GET['year'] : '';

$query = "
    SELECT s.*, 
    (SELECT COUNT(*) FROM student_course_allocations ca WHERE ca.student_id = s.id) AS enrollment_count
    FROM students s WHERE 1=1
";

$params = [];
if ($search) {
    $query .= " AND (s.first_name LIKE :search OR s.last_name LIKE :search OR s.student_id LIKE :search OR s.email LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($school_filter) {
    $query .= " AND s.school = :school"; // NEW: School filter
    $params[':school'] = $school_filter;
}
if ($department_filter) {
    $query .= " AND s.department = :department";
    $params[':department'] = $department_filter;
}
if ($year_filter) {
    $query .= " AND s.year_of_study = :year";
    $params[':year'] = $year_filter;
}

$query .= " ORDER BY s.first_name, s.last_name";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get schools for filter
$query = "SELECT DISTINCT school FROM students ORDER BY school";
$stmt = $db->prepare($query);
$stmt->execute();
$schools = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get departments for filter
$query = "SELECT DISTINCT department FROM students ORDER BY department";
$stmt = $db->prepare($query);
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Course Allocation System</title>
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
            <h1 class="page-title">Students Management</h1>
            <p class="page-subtitle">Manage student records and enrollments</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="lecturers.php" class="nav-link">Lecturers</a>
                <a href="courses.php" class="nav-link">Courses</a>
                <!-- <a href="students.php" class="nav-link active">Students</a> -->
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
                    <label>Search Students</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, ID, or email...">
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
            </form>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Students (<?php echo count($students); ?>)</h2>
                <button class="btn btn-primary" data-modal="add-student-modal">Add New Student</button>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>School</th>
                        <th>Department</th>
                        <th>Year</th>
                        <th>GPA</th>
                        <th>Courses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['school']); ?></td>
                        <td><?php echo htmlspecialchars($student['department']); ?></td>
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
                        <td>
                            <span class="status-badge status-active"><?php echo $student['enrollment_count']; ?> courses</span>
                        </td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Student Modal - UPDATED with School field -->
    <div id="add-student-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Student</h3>
                <button class="close-btn">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_student">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" id="student_id" name="student_id" required>
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    <!-- NEW: School Field -->
                    <div class="form-group">
                        <label for="school">School</label>
                        <select id="school" name="school" required>
                            <option value="School of Science">School of Science</option>
                            <option value="School of Engineering">School of Engineering</option>
                            <option value="School of Business">School of Business</option>
                            <option value="School of Arts">School of Arts</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" required>
                    </div>
                    <div class="form-group">
                        <label for="year_of_study">Year of Study</label>
                        <select id="year_of_study" name="year_of_study" required>
                            <option value="1">Year 1</option>
                            <option value="2">Year 2</option>
                            <option value="3">Year 3</option>
                            <option value="4">Year 4</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="gpa">GPA</label>
                        <input type="number" id="gpa" name="gpa" step="0.01" min="0" max="4.0">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Add Student</button>
                    <button type="button" class="btn btn-secondary close-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Student Modal - UPDATED with School field -->
    <div id="edit-student-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Student</h3>
                <button class="close-btn">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_student">
                <input type="hidden" id="edit_student_db_id" name="student_db_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_first_name">First Name</label>
                        <input type="text" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_last_name">Last Name</label>
                        <input type="text" id="edit_last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Phone</label>
                        <input type="tel" id="edit_phone" name="phone">
                    </div>
                    <!-- NEW: School Field -->
                    <div class="form-group">
                        <label for="edit_school">School</label>
                        <select id="edit_school" name="school" required>
                            <option value="School of Science">School of Science</option>
                            <option value="School of Engineering">School of Engineering</option>
                            <option value="School of Business">School of Business</option>
                            <option value="School of Arts">School of Arts</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_department">Department</label>
                        <input type="text" id="edit_department" name="department" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_year_of_study">Year of Study</label>
                        <select id="edit_year_of_study" name="year_of_study" required>
                            <option value="1">Year 1</option>
                            <option value="2">Year 2</option>
                            <option value="3">Year 3</option>
                            <option value="4">Year 4</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_gpa">GPA</label>
                        <input type="number" id="edit_gpa" name="gpa" step="0.01" min="0" max="4.0">
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Student</button>
                    <button type="button" class="btn btn-secondary close-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <script>
        function editStudent(student) {
            document.getElementById('edit_student_db_id').value = student.id;
            document.getElementById('edit_first_name').value = student.first_name;
            document.getElementById('edit_last_name').value = student.last_name;
            document.getElementById('edit_email').value = student.email;
            document.getElementById('edit_phone').value = student.phone || '';
            document.getElementById('edit_school').value = student.school; // NEW
            document.getElementById('edit_department').value = student.department;
            document.getElementById('edit_year_of_study').value = student.year_of_study;
            document.getElementById('edit_gpa').value = student.gpa || '';
            document.getElementById('edit-student-modal').classList.add('active');
        }
    </script>
</body>
</html>