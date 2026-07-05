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
            case 'add_allocation':
                $course_id = $_POST['course_id'];
                $lecturer_id = $_POST['lecturer_id'];
                $allocated_date = $_POST['allocated_date'];
                $notes = $_POST['notes'];
                
                $query = "INSERT INTO course_allocations (course_id, lecturer_id, allocated_date, notes, status) 
                         VALUES (:course_id, :lecturer_id, :allocated_date, :notes, 'pending')";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':course_id', $course_id);
                $stmt->bindParam(':lecturer_id', $lecturer_id);
                $stmt->bindParam(':allocated_date', $allocated_date);
                $stmt->bindParam(':notes', $notes);
                
                if ($stmt->execute()) {
                    $success_message = "Course allocation added successfully!";
                } else {
                    $error_message = "Error adding allocation.";
                }
                break;
                
            case 'update_status':
                $id = $_POST['allocation_id'];
                $status = $_POST['status'];
                
                $query = "UPDATE course_allocations SET status = :status WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $success_message = "Allocation status updated successfully!";
                } else {
                    $error_message = "Error updating status.";
                }
                break;
        }
    }
}

// Get allocations with course and lecturer details
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$school_filter = isset($_GET['school']) ? $_GET['school'] : ''; // NEW: School filter
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';

$query = "SELECT ca.*, c.course_code, c.course_name, c.school, c.department, 
          l.username as lecturer_name, l.first_name, l.last_name, l.email as lecturer_email
          FROM course_allocations ca 
          JOIN courses c ON ca.course_id = c.id 
          JOIN lecturers l ON ca.lecturer_id = l.id 
          WHERE 1=1";

$params = [];
if ($search) {
    $query .= " AND (c.course_code LIKE :search OR c.course_name LIKE :search OR l.username LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($status_filter) {
    $query .= " AND ca.status = :status";
    $params[':status'] = $status_filter;
}
if ($school_filter) {
    $query .= " AND c.school = :school"; // NEW: School filter
    $params[':school'] = $school_filter;
}
if ($department_filter) {
    $query .= " AND c.department = :department";
    $params[':department'] = $department_filter;
}

$query .= " ORDER BY ca.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$allocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get courses for dropdown
$query = "SELECT id, course_code, course_name, school FROM courses WHERE status = 'active' ORDER BY course_code";
$stmt = $db->prepare($query);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get lecturers for dropdown
$query = "SELECT id, username, first_name, last_name, school, department FROM lecturers ORDER BY username";
$stmt = $db->prepare($query);
$stmt->execute();
$lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Course Allocations - Course Allocation System</title>
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
            <h1 class="page-title">Course Allocations</h1>
            <p class="page-subtitle">Manage lecturer assignments to courses</p>
        </div>

        <nav class="nav-menu">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="lecturers.php" class="nav-link">Lecturers</a>
                <a href="courses.php" class="nav-link">Courses</a>
                <!-- <a href="students.php" class="nav-link">Students</a> -->
                <a href="allocations.php" class="nav-link active">Allocations</a>
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
                    <label>Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Course or lecturer...">
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
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
                    <a href="allocations.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Allocations (<?php echo count($allocations); ?>)</h2>
                <button class="btn btn-primary" data-modal="add-allocation-modal">New Allocation</button>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Lecturer</th>
                        <th>School</th>
                        <th>Department</th>
                        <th>Allocated Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allocations as $allocation): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($allocation['course_code']); ?></strong><br>
                            <small><?php echo htmlspecialchars($allocation['course_name']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($allocation['first_name'] . ' ' . $allocation['last_name']); ?><br>
                            <small><?php echo htmlspecialchars($allocation['lecturer_email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($allocation['school']); ?></td>
                        <td><?php echo htmlspecialchars($allocation['department']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($allocation['allocated_date'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $allocation['status']; ?>">
                                <?php echo ucfirst($allocation['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($allocation['status'] === 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="allocation_id" value="<?php echo $allocation['id']; ?>">
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-primary btn-sm">Confirm</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="allocation_id" value="<?php echo $allocation['id']; ?>">
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="btn btn-secondary btn-sm">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">No actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Allocation Modal -->
    <div id="add-allocation-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">New Course Allocation</h3>
                <button class="close-btn">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_allocation">
                <div class="form-group">
                    <label for="course_id">Course</label>
                    <select id="course_id" name="course_id" required>
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>">
                                <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name'] . ' (' . $course['school'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="lecturer_id">Lecturer</label>
                    <select id="lecturer_id" name="lecturer_id" required>
                        <option value="">Select Lecturer</option>
                        <?php foreach ($lecturers as $lecturer): ?>
                            <option value="<?php echo $lecturer['id']; ?>">
                                <?php echo htmlspecialchars($lecturer['first_name'] . ' ' . $lecturer['last_name'] . ' (' . $lecturer['school'] . ' - ' . $lecturer['department'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="allocated_date">Allocation Date</label>
                    <input type="date" id="allocated_date" name="allocated_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Optional notes..."></textarea>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Create Allocation</button>
                    <button type="button" class="btn btn-secondary close-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>