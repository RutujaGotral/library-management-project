<?php
require 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $message = $_POST['message'];

    // Insert the notice into the database
    $stmt = $conn->prepare("INSERT INTO notices (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    if ($stmt->execute()) {
        echo "Notice sent to user!";
    } else {
        echo "Error sending notice: " . $stmt->error;
    }
    $stmt->close();
}
?>
