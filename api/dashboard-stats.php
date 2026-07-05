<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

try {
    if ($_SESSION['role'] === 'admin') {
        // Admin statistics
        $stats = [];
        
        // Total lecturers
        $query = "SELECT COUNT(*) as count FROM users WHERE role = 'lecturer'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['lecturers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total courses
        $query = "SELECT COUNT(*) as count FROM courses WHERE status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total students
        $query = "SELECT COUNT(*) as count FROM students";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total allocations
        $query = "SELECT COUNT(*) as count FROM course_allocations WHERE status = 'confirmed'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['allocations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
    } else {
        // Lecturer statistics
        $lecturer_id = $_SESSION['user_id'];
        $stats = [];
        
        // My courses
        $query = "SELECT COUNT(*) as count FROM course_allocations WHERE lecturer_id = :lecturer_id AND status = 'confirmed'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':lecturer_id', $lecturer_id);
        $stmt->execute();
        $stats['my_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total students
        $query = "SELECT COUNT(DISTINCT se.student_id) as count 
                  FROM course_allocations ca 
                  JOIN student_enrollments se ON ca.course_id = se.course_id 
                  WHERE ca.lecturer_id = :lecturer_id AND ca.status = 'confirmed' AND se.status = 'enrolled'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':lecturer_id', $lecturer_id);
        $stmt->execute();
        $stats['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Pending allocations
        $query = "SELECT COUNT(*) as count FROM course_allocations WHERE lecturer_id = :lecturer_id AND status = 'pending'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':lecturer_id', $lecturer_id);
        $stmt->execute();
        $stats['pending_allocations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching statistics',
        'error' => $e->getMessage()
    ]);
}
?>
