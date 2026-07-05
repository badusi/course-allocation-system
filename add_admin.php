<?php
$mysqli = new mysqli('localhost', 'root', '', 'course_allocation');


if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// New admin credentials
$username = 'admin';
$email = "admin@university.edu";  // Change this to the new admin's email
$password = "admin123";    // Change this to a strong password
$first_name = 'System';
$last_name = 'Administrator';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert into the database
$stmt = $mysqli->prepare("INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $email, $hashed_password, $first_name, $last_name);

if ($stmt->execute()) {
    echo "✅ New admin added successfully!";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$mysqli->close();
?>
