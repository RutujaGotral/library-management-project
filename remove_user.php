<?php
require 'db.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];

    // Delete the user from the database
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        echo "User removed successfully!";
    } else {
        echo "Error removing user: " . $stmt->error;
    }
    $stmt->close();
    header('Location: index.php');
    exit;
} else {
    echo "Invalid request method.";
}
?>
