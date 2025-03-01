<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


if (isset($_SESSION['role']) && $_SESSION['role'] == 'user' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $message = $_POST['message'];
    
    
    if ($stmt = $conn->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)")) {
        $stmt->bind_param("is", $user_id, $message);
        if ($stmt->execute()) {
            echo "<p>Feedback submitted successfully!</p>";
        } else {
            echo "<p>Error submitting feedback: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Error preparing statement: " . $conn->error . "</p>";
    }
}

// Handle feedback deletion
if (isset($_GET['delete'])) {
    $feedback_id = $_GET['delete'];

    // Allow admin to delete any feedback, user can only delete their own feedback
    $canDelete = false;
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        $canDelete = true;
    } elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'user') {
        if ($stmt = $conn->prepare("SELECT user_id FROM feedback WHERE id = ? AND user_id = ?")) {
            $stmt->bind_param("ii", $feedback_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $canDelete = true;
            }
            $stmt->close();
        }
    }

    if ($canDelete) {
        if ($stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?")) {
            $stmt->bind_param("i", $feedback_id);
            if ($stmt->execute()) {
                echo "<p>Feedback deleted successfully!</p>";
            } else {
                echo "<p>Error deleting feedback: " . $stmt->error . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p>Error preparing statement: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Not authorized to delete this feedback.</p>";
    }
}

// Fetch all feedback( admin view)
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');
$allFeedbacks = $conn->query("SELECT feedback.id, users.username, feedback.message, feedback.created_at, feedback.user_id FROM feedback JOIN users ON feedback.user_id = users.id ORDER BY feedback.created_at DESC");
if ($allFeedbacks === false) {
    die("Query failed: " . $conn->error);
}

// Fetch feedback f
$isUser = (isset($_SESSION['role']) && $_SESSION['role'] == 'user');
$userFeedbacks = $conn->query("SELECT feedback.id, feedback.message, feedback.created_at FROM feedback WHERE user_id = " . $_SESSION['user_id'] . " ORDER BY feedback.created_at DESC");
if ($userFeedbacks === false) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <style>
        body { background-color: #f4f4f4; font-family: Arial, sans-serif; background-image: url('images/feedbackbg.jpg'); background-size: cover; background-position: center; background-attachment: fixed; }
        .feedback-form, .feedback-list { width: 80%; margin: 20px auto; background: rgba(8,7,9,0); padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0); }
        .feedback-form h2, .feedback-list h2 { margin-bottom: 20px; }
        .feedback-form textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .feedback-form button { padding: 10px 20px; background: #5cb85c; border: none; color: white; border-radius: 4px; cursor: pointer; }
        .feedback-form button:hover { background: #4cae4c; }
        .feedback-item { border-bottom: 1px solid #ddd; padding: 10px 0; }
        .feedback-item p { margin: 5px 0; }
        .delete-link { color: red; text-decoration: none; }
        .navbar {
            display: flex; justify-content: flex-start; align-items: center; background-color: rgba(255, 255, 255, 0); padding: 10px; border-radius: 8px; width: auto; font-size: 14px; top: 10px; left: 10px; }
        .navbar a { color: burlywood; text-decoration: none; padding: 10px 20px; margin: 0 10px; border-radius: 4px; transition: background-color 0.3s, transform 0.3s; }
        .navbar a:hover { background-color: #eaeaea; transform: translateY(-3px); }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php">Home</a>
        <a href="view_books.php">View Books</a>
        <a href="user_books.php">Requested and Granted</a>
        <a href="feedback.php">Send Feedback</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>

    <!-- Feedback Form  -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'user'): ?>
        <div class="feedback-form">
            <h2>Submit Your Feedback</h2>
            <form action="feedback.php" method="POST">
                <textarea name="message" rows="5" placeholder="Your feedback here..." required></textarea>
                <button type="submit">Submit</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- All Feedback List -->
    <div class="feedback-list">
        <h2>All User Feedback</h2>
        <?php while ($row = $allFeedbacks->fetch_assoc()): ?>
            <div class="feedback-item">
                <p><strong><?php echo htmlspecialchars($row['username']); ?></strong> (<?php echo $row['created_at']; ?>):</p>
                <p><?php echo htmlspecialchars($row['message']); ?></p>
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $row['user_id'] == $_SESSION['user_id'])): ?>
                    <a href="feedback.php?delete=<?php echo $row['id']; ?>" class="delete-link">Delete</a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- User Feedback List -->
    <?php if ($isUser): ?>
        <div class="feedback-list">
            <h2>Your Feedback</h2>
            <?php while ($row = $userFeedbacks->fetch_assoc()): ?>
                <div class="feedback-item">
                    <p><strong><?php echo $row['created_at']; ?></strong></p>
                    <p><?php echo htmlspecialchars($row['message']); ?></p>
                    <a href="feedback.php?delete=<?php echo $row['id']; ?>" class="delete-link">Delete</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</body>
</html>
