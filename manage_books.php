<?php
require 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $year = $_POST['year'];
    $isbn = $_POST['isbn'];
    $more_info_link = $_POST['more_info_link'];

    //image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'uploads/';
        $image_path = $upload_dir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = $image_path;
        } else {
            echo "<p>Error uploading image file.</p>";
        }
    }

    if ($stmt = $conn->prepare("INSERT INTO books (title, author, year, isbn, image, more_info_link) VALUES (?, ?, ?, ?, ?, ?)")) {
        $stmt->bind_param("ssisss", $title, $author, $year, $isbn, $image, $more_info_link);
        if ($stmt->execute()) {
            echo "<p>Book added successfully!</p>";
        } else {
            echo "<p>Error adding book: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Error preparing statement: " . $conn->error . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_book'])) {
    $book_id = $_POST['book_id'];

    // Delete associated requests 
    $stmt = $conn->prepare("DELETE FROM requests WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    if ($stmt->execute()) {
        // delete the book
        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        if ($stmt->execute()) {
            echo "<p>Book and associated requests deleted successfully!</p>";
        } else {
            echo "<p>Error deleting book: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p>Error deleting associated requests: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

$result = $conn->query("SELECT * FROM books");
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management</title>
    <style>
        body {
            background-image: url('images/managebooksbg1.jpg'); 
            background-size: cover;
            background-repeat: no-repeat;
            font-family: Arial, sans-serif;
        }

        .navbar {
            display: flex;
            justify-content: flex-start; 
            align-items: center; 
            background-color: rgba(255, 255, 255, 0); 
            padding: 5px;
            color:white;
            border-radius: 8px;
            width: 100%;
            
            top: 0; 
            z-index: 1000; 
            font-size: 12px; 
        }

        .navbar a {
            color:burlywood;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 5px;
            border-radius: 4px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .navbar a:hover {
            background-color: #eaeaea;
            transform: translateY(-3px);
        }

        .admin-panel {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.4); 
            padding: 20px;
            border-radius: 8px;
            margin: 20px;
            margin-top: 60px; 
        }
        .book-list {
            display: flex;
            flex-wrap: wrap;
            list-style-type: none;
            padding: 0;
            justify-content: center;
        }
        .book-item {
            background-color: rgba(249, 249, 249, 0.4); 
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 16px;
            margin: 16px;
            transition: transform 0.3s, box-shadow 0.3s;
            width: 200px; 
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
       
        button {
            background-color: #4CAF50; 
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
        input[type="file"] {
            margin: 10px 0;
            padding: 6px;
            cursor: pointer;
        }
        input[type="file"]:hover {
            background-color: #eaeaea;
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

    <div class="admin-panel">
        <h1>Library Management - Admin Panel</h1>

        <form method="POST" enctype="multipart/form-data">
            <h2>Add a New Book</h2>
            Title: <input type="text" name="title" required><br>
            Author: <input type="text" name="author" required><br>
            Year: <input type="number" name="year" required><br>
            ISBN: <input type="text" name="isbn" required><br>
            More Info Link: <input type="url" name="more_info_link"><br>
            Image: <input type="file" name="image" accept="image/*"><br>
            <button type="submit" name="add_book">Add Book</button>
        </form>

        <h2>Book List</h2>
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
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>"><br>
                        <button type="submit" name="delete_book" onclick="return confirm('Are you sure you want to delete this book?')">Delete</button>
                    </form>
                </div>
            </li>
        <?php endwhile; ?>
        </ul>

        <?php
        $result->free();
        $conn->close();
        ?>
    </div>
</body>
</html>
