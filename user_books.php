<?php
require 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT b.title, b.author, r.timestamp, r.status 
                        FROM requests r 
                        JOIN books b ON r.book_id = b.id 
                        WHERE r.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Requested and Granted Books</title>
    <style>
        body {
            background-image: url('images/bg1.jpg'); /* Add your background image path */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Arial', sans-serif;
        }

        .navbar {
            display: flex;
            justify-content: flex-start; /* Align items to the left */
            align-items: center; /* Center items vertically */
            background-color: rgba(255, 255, 255, 0.4); /* White with translucency */
            padding: 10px;
            border-radius: 8px;
            width: 100%; /* Full width */
            position:fixed; /* Fix the navbar at the top */
            top: 0; /* Align to the top */
            z-index: 1000; /* Ensure it is on top of other elements */
            font-size: 14px; /* Small font size */
        }

        .navbar a {
            color: burlywood;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 4px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .navbar a:hover {
            background-color: #eaeaea;
            transform: translateY(-3px);
        }

        .section {
            text-align: center;
            margin: 20px;
            background-color: rgba(255, 255, 255, 0.5); /* Add slight transparency */
            padding: 20px;
            border-radius: 8px;
            margin-top: 60px; /* Add margin to accommodate fixed navbar */
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <?php 
        if ($_SESSION['role'] == 'admin') {
            echo '<a href="manage_books.php">Manage Books</a>';
            echo '<a href="manage_requests.php">Manage Requests</a>';
        } else {
            echo '<a href="index.php">Home</a>';
            echo '<a href="view_books.php">View Books</a>';
            echo '<a href="user_books.php">Requested and Granted</a>';
            echo '<a href="feedback.php">Feedback</a>';
        }
        ?>
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>
    <div class="section">
        <h2>Requested and Granted Books</h2>
        <table>
            <tr>
                <th>Book Title</th>
                <th>Author</th>
                <th>Requested On</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
