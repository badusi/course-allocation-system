<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /index.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /lecturer/dashboard.php');
        exit();
    }
}

function requireLecturer() {
    requireLogin();
    if ($_SESSION['role'] !== 'lecturer') {
        header('Location: /admin/dashboard.php');
        exit();
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    require_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function requireLecturerAuth() {
    if (!isset($_SESSION)) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
        header("Location: ../index.php"); // or wherever your login page is
        exit();
    }
}


?>
