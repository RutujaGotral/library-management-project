<?php
require 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $status = $action == 'grant' ? 'granted' : 'canceled';
    
    $stmt = $conn->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $request_id);
    if ($stmt->execute()) {
        echo "Request updated!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

header('Location: manage_requests.php');
?>
