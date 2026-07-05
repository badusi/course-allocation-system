<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($_SESSION['role'] === 'lecturer') {
        header('Location: lecturer/dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';

    $database = new Database();
    $db = $database->getConnection();

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check in lecturers table
    $query = "SELECT * FROM lecturers WHERE username = :username OR email = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lecturer && password_verify($password, $lecturer['password'])) {
        $_SESSION['user_id'] = $lecturer['id'];
        $_SESSION['role'] = 'lecturer';
        $_SESSION['username'] = $lecturer['username'];
        $_SESSION['first_name'] = $lecturer['first_name'];
        $_SESSION['last_name'] = $lecturer['last_name'];

        header('Location: lecturer/dashboard.php');
        exit();
    }

    // If not lecturer, check in users table (admin)
    $query = "SELECT * FROM users WHERE username = :username OR email = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];

        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: lecturer/dashboard.php');
        }
        exit();
    } else {
        $error = 'Invalid username or password';
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
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">Sign In</button>
            </form>
            
            <!-- <div class="demo-credentials">
                <h3>Demo Credentials:</h3>
                <div class="demo-accounts">
                    <div class="demo-account">
                        <strong>Admin:</strong>
                        <span>admin / password</span>
                    </div>
                    <div class="demo-account">
                        <strong>Lecturer:</strong>
                        <span>john.doe / password</span>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    
    <script src="assets/js/login.js"></script>
</body>
</html>
