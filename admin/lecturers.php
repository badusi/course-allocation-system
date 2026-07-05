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
            case 'add_lecturer':
                $username = $_POST['username'];
                $email = $_POST['email'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $phone = $_POST['phone'];
                $school = $_POST['school']; // NEW: School field
                $department = $_POST['department'];
                
                $query = "INSERT INTO lecturers (username, email, password, first_name, last_name, phone, school, department) 
                         VALUES (:username, :email, :password, :first_name, :last_name, :phone, :school, :department)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':last_name', $last_name);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':school', $school); // NEW
                $stmt->bindParam(':department', $department);
                
                if ($stmt->execute()) {
                    $success_message = "Lecturer added successfully!";
                } else {
                    $error_message = "Error adding lecturer.";
                }
                break;
                
            case 'update_lecturer':
                $id = $_POST['lecturer_id'];
                $email = $_POST['email'];
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $phone = $_POST['phone'];
                $school = $_POST['school']; // NEW: School field
                $department = $_POST['department'];
                
                $query = "UPDATE lecturers SET email = :email, first_name = :first_name, last_name = :last_name, 
                         phone = :phone, school = :school, department = :department WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':last_name', $last_name);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':school', $school); // NEW
                $stmt->bindParam(':department', $department);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $success_message = "Lecturer updated successfully!";
                } else {
                    $error_message = "Error updating lecturer.";
                }
                break;
        }
    }
}

// Get lecturers with course counts
$search = isset($_GET['search']) ? $_GET['search'] : '';
$school_filter = isset($_GET['school']) ? $_GET['school'] : ''; // NEW: School filter
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';

$query = "SELECT l.*, COUNT(ca.id) as course_count 
          FROM lecturers l 
          LEFT JOIN course_allocations ca ON l.id = ca.lecturer_id AND ca.status = 'confirmed'
          WHERE 1=1";

$params = [];
if ($search) {
    $query .= " AND (l.first_name LIKE :search OR l.last_name LIKE :search OR l.email LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($school_filter) {
    $query .= " AND l.school = :school"; // NEW: School filter
    $params[':school'] = $school_filter;
}
if ($department_filter) {
    $query .= " AND l.department = :department";
    $params[':department'] = $department_filter;
}

$query .= " GROUP BY l.id ORDER BY l.last_name, l.first_name";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get schools for filter
$query = "SELECT DISTINCT school FROM lecturers ORDER BY school";
$stmt = $db->prepare($query);
$stmt->execute();
$schools = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get departments for filter
$query = "SELECT DISTINCT department FROM lecturers ORDER BY department";
$stmt = $db->prepare($query);
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturers - Course Allocation System</title>
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
            <h1 class="page-title">Lecturers Management</h1>
            <p class="page-subtitle">Manage faculty members and their information</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="lecturers.php" class="nav-link active">Lecturers</a>
                <a href="courses.php" class="nav-link">Courses</a>
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
                    <label>Search Lecturers</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name or email...">
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
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="lecturers.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Lecturers (<?php echo count($lecturers); ?>)</h2>
                <button class="btn btn-primary" data-modal="add-lecturer-modal">Add New Lecturer</button>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>School</th>
                        <th>Department</th>
                        <th>Phone</th>
                        <th>Courses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lecturers as $lecturer): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($lecturer['first_name'] . ' ' . $lecturer['last_name']); ?></strong><br>
                            <small>@<?php echo htmlspecialchars($lecturer['username']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($lecturer['email']); ?></td>
                        <td><?php echo htmlspecialchars($lecturer['school']); ?></td>
                        <td><?php echo htmlspecialchars($lecturer['department']); ?></td>
                        <td><?php echo htmlspecialchars($lecturer['phone'] ?: 'N/A'); ?></td>
                        <td>
                            <span class="status-badge status-active"><?php echo $lecturer['course_count']; ?> courses</span>
                        </td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="editLecturer(<?php echo htmlspecialchars(json_encode($lecturer)); ?>)">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Lecturer Modal - UPDATED with School field -->
    <div id="add-lecturer-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Lecturer</h3>
                <button class="close-btn">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_lecturer">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
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
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Add Lecturer</button>
                    <button type="button" class="btn btn-secondary close-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Lecturer Modal - UPDATED with School field -->
    <div id="edit-lecturer-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Lecturer</h3>
                <button class="close-btn">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_lecturer">
                <input type="hidden" id="edit_lecturer_id" name="lecturer_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_first_name">First Name</label>
                        <input type="text" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_last_name">Last Name</label>
                        <input type="text" id="edit_last_name" name="last_name" required>
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
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Lecturer</button>
                    <button type="button" class="btn btn-secondary close-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <script>
        function editLecturer(lecturer) {
            document.getElementById('edit_lecturer_id').value = lecturer.id;
            document.getElementById('edit_email').value = lecturer.email;
            document.getElementById('edit_first_name').value = lecturer.first_name;
            document.getElementById('edit_last_name').value = lecturer.last_name;
            document.getElementById('edit_phone').value = lecturer.phone || '';
            document.getElementById('edit_school').value = lecturer.school; // NEW
            document.getElementById('edit_department').value = lecturer.department;
            document.getElementById('edit-lecturer-modal').classList.add('active');
        }
    </script>
</body>
</html>