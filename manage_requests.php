<?php
require 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$result = $conn->query("SELECT r.id, r.status, b.title, u.username, r.created_at 
                        FROM requests r 
                        JOIN books b ON r.book_id = b.id 
                        JOIN users u ON r.user_id = u.id 
                        ORDER BY r.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Manage Book Requests</title>
    <style>
        body {
            background-image: url('images/bg1.jpg'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: Arial, sans-serif;
        }

        .navbar {
            display: flex;
            justify-content: flex-start; 
            align-items: center;
            background-color: rgba(255, 255, 255, 0); 
            padding: 10px;
            border-radius: 8px;
            width: auto; 
            font-size: 14px;
            position: absolute; 
            top: 10px;
            left: 10px;
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
            background-color: rgba(255, 255, 255, 0.5); 
            padding: 20px;
            border-radius: 8px;
            margin-top: 60px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .grant {
            background-color: #4CAF50;
            color: white;
        }

        .cancel {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php">Home</a>
        <a href="manage_books.php">Manage Books</a>
        <a href="manage_requests.php">Manage Requests</a>
        <a href="feedback.php">Feedback</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>

    <div class="section">
        <h2>Manage Book Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Requested Book</th>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                            <form method="POST" action="update_request.php" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="action" value="grant" class="btn grant">Grant</button>
                                <button type="submit" name="action" value="cancel" class="btn cancel">Cancel</button>
                            </form>
                        <?php else: ?>
                            <?php echo ucfirst($row['status']); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
