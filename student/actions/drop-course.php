<?php
// Prevent any output before JSON
ob_start();

session_start();

// Clear any previous output
ob_clean();

// Set JSON header immediately
header('Content-Type: application/json');

// Check if database config exists
if (!file_exists('../../config/database.php')) {
    echo json_encode(['success' => false, 'message' => 'Database configuration not found']);
    exit();
}

try {
    require_once '../../config/database.php';
    $db = new Database();
    $pdo = $db->getConnection(); // ✅ Correct
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$student_id = $_SESSION['student_id'];

// Get input data
$input_raw = file_get_contents('php://input');
if (empty($input_raw)) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit();
}

$input = json_decode($input_raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

$course_id = $input['course_id'] ?? null;

if (!$course_id || !is_numeric($course_id)) {
    echo json_encode(['success' => false, 'message' => 'Valid Course ID is required']);
    exit();
}

try {
    // Check if enrolled
    $check_stmt = $pdo->prepare("SELECT id, status FROM student_courses WHERE student_id = ? AND course_id = ?");
    $check_stmt->execute([$student_id, $course_id]);
    $enrollment = $check_stmt->fetch();
    
    if (!$enrollment) {
        echo json_encode(['success' => false, 'message' => 'Not enrolled in this course']);
        exit();
    }
    
    if ($enrollment['status'] !== 'enrolled') {
        echo json_encode(['success' => false, 'message' => 'Course already dropped or completed']);
        exit();
    }
    
    // Update enrollment status to dropped
    $drop_stmt = $pdo->prepare("UPDATE student_courses SET status = 'dropped' WHERE student_id = ? AND course_id = ?");
    $drop_stmt->execute([$student_id, $course_id]);
    
    if ($drop_stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Successfully dropped course']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to drop course']);
    }
    
} catch (PDOException $e) {
    error_log("Database error in drop-course.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error in drop-course.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>