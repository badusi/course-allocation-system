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
    echo json_encode(['success' => false, 'message' => 'Database configuration not found', 'notifications' => []]);
    exit();
}

try {
    require_once '../config/database.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'notifications' => []]);
    exit();
}

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated', 'notifications' => []]);
    exit();
}

$student_id = $_SESSION['student_id'];

try {
    $notifications = [];
    
    // Fetch recent announcements
    $announcements_query = "
        SELECT 
            CONCAT('New announcement: ', title) as message,
            'info' as type
        FROM announcements 
        WHERE target_audience IN ('all', 'students') 
        AND status = 'active'
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
        ORDER BY created_at DESC
        LIMIT 3
    ";
    
    $stmt = $pdo->prepare($announcements_query);
    $stmt->execute();
    $announcement_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch course request updates
    $course_notifications_query = "
        SELECT 
            CONCAT('Your request for ', COALESCE(c.name, 'course'), ' has been ', cr.status) as message,
            CASE 
                WHEN cr.status = 'approved' THEN 'success'
                WHEN cr.status = 'rejected' THEN 'error'
                ELSE 'info'
            END as type
        FROM course_requests cr
        LEFT JOIN courses c ON cr.course_id = c.id
        WHERE cr.student_id = ?
        AND cr.updated_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
        AND cr.status != 'pending'
        ORDER BY cr.updated_at DESC
        LIMIT 2
    ";
    
    $stmt = $pdo->prepare($course_notifications_query);
    $stmt->execute([$student_id]);
    $request_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Merge notifications
    $all_notifications = array_merge($announcement_notifications, $request_notifications);
    
    echo json_encode([
        'success' => true,
        'notifications' => $all_notifications
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get-notifications.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'notifications' => []
    ]);
} catch (Exception $e) {
    error_log("General error in get-notifications.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'notifications' => []
    ]);
}
?>
