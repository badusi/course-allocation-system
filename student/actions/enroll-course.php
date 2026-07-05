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
    // Check if course exists
    $course_check = $pdo->prepare("SELECT id, course_name, status FROM courses WHERE id = ?");
    $course_check->execute([$course_id]);
    $course = $course_check->fetch();
    
    if (!$course) {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        exit();
    }
    
    if ($course['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Course is not active']);
        exit();
    }
    
    // Check if already enrolled
    $check_stmt = $pdo->prepare("SELECT id, status FROM student_courses WHERE student_id = ? AND course_id = ?");
    $check_stmt->execute([$student_id, $course_id]);
    $existing = $check_stmt->fetch();
    
    if ($existing) {
        if ($existing['status'] === 'enrolled') {
            echo json_encode(['success' => false, 'message' => 'Already enrolled in this course']);
            exit();
        } else {
            // Re-enroll if previously dropped
            $update_stmt = $pdo->prepare("UPDATE student_courses SET status = 'enrolled', enrollment_date = NOW() WHERE student_id = ? AND course_id = ?");
            $update_stmt->execute([$student_id, $course_id]);
            echo json_encode(['success' => true, 'message' => 'Successfully re-enrolled in course']);
            exit();
        }
    }
    
    // Check course capacity
    $capacity_stmt = $pdo->prepare("
        SELECT c.max_students, COUNT(sc.id) as current_enrollment 
        FROM courses c 
        LEFT JOIN student_courses sc ON c.id = sc.course_id AND sc.status = 'enrolled'
        WHERE c.id = ? 
        GROUP BY c.id
    ");
    $capacity_stmt->execute([$course_id]);
    $capacity_info = $capacity_stmt->fetch();
    
    if ($capacity_info && $capacity_info['current_enrollment'] >= $capacity_info['max_students']) {
        echo json_encode(['success' => false, 'message' => 'Course is full']);
        exit();
    }
    
    // Enroll student
    $enroll_stmt = $pdo->prepare("INSERT INTO student_courses (student_id, course_id, status, enrollment_date) VALUES (?, ?, 'enrolled', NOW())");
    $enroll_stmt->execute([$student_id, $course_id]);
    
    echo json_encode(['success' => true, 'message' => 'Successfully enrolled in course']);
    
} catch (PDOException $e) {
    error_log("Database error in enroll-course.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error in enroll-course.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>