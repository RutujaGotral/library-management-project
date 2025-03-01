<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


if ($_SESSION['role'] == 'admin') {
    $users_result = $conn->query("SELECT id, username FROM users WHERE role = 'user'");
    if (!$users_result) {
        die("Query failed: " . $conn->error);
    }
} else {
   
    $user_id = $_SESSION['user_id'];
    $notices_result = $conn->query("SELECT id, message, created_at FROM notices WHERE user_id = $user_id ORDER BY created_at DESC");
    if (!$notices_result) {
        die("Query failed: " . $conn->error);
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_notice_id'])) {
    $notice_id = $_POST['delete_notice_id'];
    $conn->query("DELETE FROM notices WHERE id = $notice_id");
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('images/bg2.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .navbar {
            display: flex;
            justify-content: flex-start; 
            align-items: center; 
            background-color: rgba(255, 255, 255, 0.8); 
            padding: 10px;
            border-radius: 8px;
            width: 100%;
            position: fixed; 
            top: 0; 
            z-index: 1000; 
            font-size: 14px; 
        }

        .navbar a {
            color:burlywood;
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

        .dashboard-container {
            background-color: rgba(255, 255, 255, 0.5);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-top: 60px; 
            width: 80%; 
        }

        .dashboard-container h1 {
            margin-bottom: 20px;
            color: #333;
        }

        .logout-link {
            margin-top: 20px;
        }

        .user-list {
            margin-top: 20px;
            text-align: left;
        }

        .user-list ul {
            list-style-type: none;
            padding: 0;
        }

        .user-list li {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 0.8);
        }

        .user-list form {
            display: inline;
        }

        .user-list button {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
            margin-left: 5px;
        }

        .user-list button:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
        }

        .remove-button {
            background-color: #dc3545; 
        }

        .remove-button:hover {
            background-color: #c82333;
        }
       
        .center-table {
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    border-collapse: collapse; 
    background-color: #f9f9f9; 
    width: 80%; 
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
}

.center-table th,
.center-table td {
    padding: 10px;
    border: 1px solid #ddd; 
}

.center-table th {
    background-color: #f2f2f2; 
    font-weight: bold;
 } 


    </style>
</head>
<body>
    <nav class="navbar">
        <?php 
        if ($_SESSION['role'] == 'admin') {
            echo '<a href="index.php">Home</a>';
            echo '<a href="manage_books.php">Manage Books</a>';
            echo '<a href="manage_requests.php">Manage Requests</a>';
            echo '<a href="feedback.php">feedbacks</a>';
        } else {
            echo '<a href="index.php">Home</a>';
            echo '<a href="view_books.php">View Books</a>';
            echo '<a href="user_books.php">Requested & Granted Books</a>';
            echo '<a href="feedback.php">send feedback</a>';
        }
        ?>
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>
    <div class="dashboard-container">
        <?php 
        if ($_SESSION['role'] == 'admin') {
            echo '<h1>Admin Page</h1>';
        } else {
            echo '<h1></h1>';
        }
        ?>

        <?php if ($_SESSION['role'] == 'admin'): ?>
            <div class="user-list">
                <h2>Registered Users</h2>
                <ul>
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <li>
                        <?php echo htmlspecialchars($user['username']); ?>
                        <form method="POST" action="send_notice.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="text" name="message" placeholder="Enter your message">
                            <button type="submit">Send Notice</button>
                        </form>
                        <form method="POST" action="remove_user.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="remove-button" onclick="return confirm('Are you sure you want to remove this user?')">Remove User</button>
                        </form>
                        <form method="GET" action="user_notices.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit">Notices</button>
                        </form>
                    </li>
                <?php endwhile; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="notices-container">
            
    <h2>Your Notices</h2>
    <table class="center-table" border="1">
        <tr>
            <th>Notice</th>
            <th>Timestamp</th>
        </tr>
        <?php while ($notice = $notices_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($notice['message']); ?></td>
            <td><?php echo $notice['created_at']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</div>

</div>

        <?php endif; ?>
    </div>
</body>
</html>
