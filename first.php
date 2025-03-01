<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Background Page</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-family: Arial, sans-serif;
        }
        .background-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }
        .content {
            color: black;
            text-align: center;
            z-index: 1;
            background: linear-gradient(135deg, rgba(255, 183, 197, 0.8), rgba(173, 216, 230, 0.8)); 
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5); 
        }
        .content a {
            color: white;
            text-decoration: none;
            background-color: rgba(0, 0, 0, 0.9);
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
            transition: background-color 0.3s ease, color 0.3s ease; 
        }
        .content a:hover {
            background-image: linear-gradient(45deg, #ff6b6b, #f7c6c7, #fecd1a, #24fe41, #24cbff, #845ef7); 
            color: black; 
        }
    </style>
</head>
<body>
    <video autoplay muted loop class="background-video">
        <source src="videos/v5.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="content">
        <h1>Welcome to My LMS</h1>
        <p>Engage, Learn, Succeed</p>
        <a href="login.php">Login to Discover more reads.</a>
    </div>
</body>
</html>
