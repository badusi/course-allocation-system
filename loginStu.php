<?php
session_start();

$error = '';


if ($_POST) {
    require_once 'config/database.php';

    $database = new Database();
    $db = $database->getConnection();

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check students table only
    $query = "SELECT * FROM students WHERE student_id = :username OR email = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student && password_verify($password, $student['password'])) {
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['role'] = 'student';
        $_SESSION['student_unique_id'] = $student['student_id'];  // If needed
        $_SESSION['first_name'] = $student['first_name'];
        $_SESSION['last_name'] = $student['last_name'];
        $_SESSION['name'] = $student['first_name'] . ' ' . $student['last_name'];

        header('Location: student/student-dashboard.php');
        exit();
    } else {
        $error = 'Invalid student ID/email or password';
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Course Allocation System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Course Allocation System</h1>
                <p>Sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Matric No</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">Sign In</button>
            </form>
        </div>
    </div>
    
    <script src="assets/js/login.js"></script>
</body>
</html>
