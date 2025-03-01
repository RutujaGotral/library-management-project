<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $book_id = $_POST['book_id'];

    $stmt = $conn->prepare("INSERT INTO requests (user_id, book_id, status, created_at) VALUES (?, ?, 'pending', NOW())");
    $stmt->bind_param("ii", $user_id, $book_id);
    if ($stmt->execute()) {
        echo "Request submitted!";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
header('Location: view_books.php');
?>