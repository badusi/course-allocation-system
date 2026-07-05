<?php
// Prevent any output before JSON
ob_start();

session_start();

// Clear any previous output
ob_clean();

// Set JSON header immediately
header('Content-Type: application/json');

// Check if database config exists
if (!file_exists('../config/database.php')) {
    echo json_encode(['success' => false, 'message' => 'Database configuration not found']);
    exit();
}

try {
    require_once '../config/database.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$student_id = $_SESSION['student_id'];

try {
    // Fetch updated student statistics
    $stats_query = "
        SELECT 
            COUNT(DISTINCT sc.course_id) as enrolled_courses,
            COUNT(DISTINCT cr.id) as pending_requests,
            COALESCE(AVG(g.grade), 0) as current_gpa,
            COALESCE(SUM(DISTINCT c.credits), 0) as credits_earned
        FROM students s
        LEFT JOIN student_courses sc ON s.id = sc.student_id AND sc.status = 'enrolled'
        LEFT JOIN course_requests cr ON s.id = cr.student_id AND cr.status = 'pending'
        LEFT JOIN grades g ON s.id = g.student_id
        LEFT JOIN courses c ON sc.course_id = c.id
        WHERE s.id = ?
    ";
    
    $stmt = $pdo->prepare($stats_query);
    $stmt->execute([$student_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stats) {
        echo json_encode([
            'success' => true,
            'stats' => [
                'enrolled_courses' => 0,
                'pending_requests' => 0,
                'current_gpa' => 0.0,
                'credits_earned' => 0
            ]
        ]);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'enrolled_courses' => (int)($stats['enrolled_courses'] ?? 0),
            'pending_requests' => (int)($stats['pending_requests'] ?? 0),
            'current_gpa' => (float)($stats['current_gpa'] ?? 0),
            'credits_earned' => (int)($stats['credits_earned'] ?? 0)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get-student-stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("General error in get-student-stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
?>
