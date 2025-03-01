<?php
require 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch books based on search query
$title = isset($_GET['title']) ? $_GET['title'] : '';
$query = "SELECT * FROM books WHERE title LIKE ?";
$stmt = $conn->prepare($query);
$search_title = "%" . $title . "%";
$stmt->bind_param("s", $search_title);
$stmt->execute();
$search_result = $stmt->get_result();

// Fetch all books
$result = $conn->query("SELECT * FROM books");
if ($result === false) {
    echo "Error: " . $conn->error;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playwrite+AU+SA:wght@100..400&display=swap');
        body {
            background-image: url('images/bg1.jpg'); /* Add your background image path */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Arial', sans-serif;
        }

        .navbar {
            display: flex;
            justify-content: space-between; /* Align items to the left */
            align-items: center; /* Center items vertically */
            background-color: rgba(255, 255, 255, 0); /* White with translucency */
            padding: 10px;
            border-radius: 8px;
            width: 100%; /* Full width */
            /* Fix the navbar at the top */
            top: 0; /* Align to the top */
            z-index: 1000; /* Ensure it is on top of other elements */
            font-size: 14px; /* Small font size */
        }

        .navbar a {
            color:burlywood;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .navbar a:hover {
            background-color: #eaeaea;
            transform: translateY(-3px);
        }

        .search-container {
            display:flex;
            align-items: center;
            padding-left: 0px;
            padding-right: 50px;
        }

        .search-container input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 8px;
        }

        .search-container button {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        .search-container button:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
        }

        .book-list {
            display: flex;
            flex-wrap: wrap;
            list-style-type: none;
            padding: 0;
            justify-content: center;
        }
        .book-item {
            background-color: rgba(249, 249, 249, 0.4); /* Add slight transparency */
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 16px;
            margin: 16px;
            transition: transform 0.3s, box-shadow 0.3s;
            width: 200px; /* Fixed width for the items */
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .book-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .book-image {
            max-width: 100px;
            max-height: 150px;
            border-radius: 4px;
        }
        .book-info {
            text-align: center;
            margin-top: 16px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        /* Button Styles */
        button {
            background-color: #4CAF50; /* Green */
            color: white;
            padding: 10px 20px;
            margin: 10px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }
        button:hover {
            background-color: #45a049;
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div>
            <?php 
            if ($_SESSION['role'] == 'admin') {
                echo '<a href="manage_books.php">Manage Books</a>';
                echo '<a href="manage_requests.php">Manage Requests</a>';
            } else {
                echo '<a href="index.php">Home</a>';
                echo '<a href="view_books.php">View Books</a>';
                echo '<a href="user_books.php">Requested & Granted Books</a>';
                echo '<a href="feedback.php">Feedback</a>';
            }
            ?>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" name="title" placeholder="Search by title">
                <button type="submit">Search</button>
            </form>
        </div>
    </nav>

    <div class="book-list-container">
        <h2 style="color:hotpink;"><i>Search Results</i></h2>
        <ul class="book-list">
        <?php if (!empty($title)): ?>
            <?php if ($search_result->num_rows > 0): ?>
                <?php while ($row = $search_result->fetch_assoc()): ?>
                    <li class="book-item">
                        <?php if (!empty($row['image'])): ?>
                            <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?> cover" class="book-image">
                        <?php endif; ?>
                        <div class="book-info">
                            <?php echo htmlspecialchars($row['title']) . ' by ' . htmlspecialchars($row['author']); ?>
                            <?php if (!empty($row['more_info_link'])): ?>
                                <p><a href="<?php echo htmlspecialchars($row['more_info_link']); ?>" target="_blank">More Info</a></p>
                            <?php endif; ?>
                            <form method="POST" action="request_book.php" style="display:inline;">
                                <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">
                                <button type="submit">Request Book</button>
                            </form>
                        </div>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No Result Found :(</p>
            <?php endif; ?>
        <?php endif; ?>
        </ul>

        <h2 style="color:khaki;"><i>Book List</i></h2>
        <ul class="book-list">
        <?php while ($row = $result->fetch_assoc()): ?>
            <li class="book-item">
                <?php if (!empty($row['image'])): ?>
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?> cover" class="book-image">
                <?php endif; ?>
                <div class="book-info">
                    <?php echo htmlspecialchars($row['title']) . ' by ' . htmlspecialchars($row['author']); ?>
                    <?php if (!empty($row['more_info_link'])): ?>
                        <p><a href="<?php echo htmlspecialchars($row['more_info_link']); ?>" target="_blank">More Info</a></p>
                    <?php endif; ?>
                    <form method="POST" action="request_book.php" style="display:inline;">
                        <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">
                        <button type="submit">Request Book</button>
                    </form>
                </div>
            </li>
        <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
