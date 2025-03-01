<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Get the user ID from the query parameter
$user_id = $_GET['user_id'];

// Fetch notices for the specified user
$notices_result = $conn->query("SELECT id, message, created_at FROM notices WHERE user_id = $user_id ORDER BY created_at DESC");
if (!$notices_result) {
    die("Query failed: " . $conn->error);
}

// Fetch username for display
$user_result = $conn->query("SELECT username FROM users WHERE id = $user_id");
if (!$user_result) {
    die("Query failed: " . $conn->error);
}
$user = $user_result->fetch_assoc();

// Delete notice if requested
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_notice_id'])) {
    $notice_id = $_POST['delete_notice_id'];
    $conn->query("DELETE FROM notices WHERE id = $notice_id");
    header("Location: user_notices.php?user_id=$user_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Notices</title>
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
    justify-content: flex-start; /* Align items to the left */
    align-items: center; /* Center items vertically */
    background-color: rgba(255, 255, 255, 0.8); /* White with translucency */
    padding: 10px;
    border-radius: 8px;
    width: 100%; /* Full width */
    position: fixed; /* Fix the navbar at the top */
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

.dashboard-container {
    background-color: rgba(255, 255, 255, 0.5);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    margin-top: 60px; /* Add margin to accommodate fixed navbar */
    width: 80%; /* Adjust width as needed */
}

.dashboard-container h1 {
    margin-bottom: 20px;
    color: #333;
}

.notices-container {
    margin-top: 20px;
    text-align: left;
}

.notices-container ul {
    list-style-type: none;
    padding: 0;
}

.notices-container li {
    margin-bottom: 10px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: rgba(255, 255, 255, 0.8);
}

.notices-container form {
    display: inline;
}

.notices-container button {
    background-color: #dc3545; /* Red */
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.3s;
}

.notices-container button:hover {
    background-color: #c82333;
    transform: translateY(-3px);
}

    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php">Home</a>
        
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>
    <div class="dashboard-container">
        <h1>Notices for <?php echo htmlspecialchars($user['username']); ?></h1>
        <div class="notices-container">
            <ul>
            <?php while ($notice = $notices_result->fetch_assoc()): ?>
                <li>
                    <p><?php echo htmlspecialchars($notice['message']); ?></p>
                    <small>Sent on: <?php echo $notice['created_at']; ?></small>
                    <form method="POST" action="user_notices.php?user_id=<?php echo $user_id; ?>" style="display:inline;">
                        <input type="hidden" name="delete_notice_id" value="<?php echo $notice['id']; ?>">
                        <button type="submit" class="remove-button" onclick="return confirm('Are you sure you want to delete this notice?')">Delete Notice</button>
                    </form>
                </li>
            <?php endwhile; ?>
            </ul>
        </div>
    </div>
</body>
</html>
