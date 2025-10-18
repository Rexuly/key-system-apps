<?php
session_start();

// Database connection
$dbHost = 'localhost';
$dbUser = 'your_db_user';
$dbPass = 'your_db_pass';
$dbName = 'key_system';
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle sign-up
if (isset($_POST['signup'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        $key = bin2hex(random_bytes(16)); // Generate random key
        $conn->query("INSERT INTO keys (user_id, key_value) VALUES ($userId, '$key')");
        $_SESSION['message'] = "Sign-up successful! Your key: $key";
    } else {
        $_SESSION['message'] = "Error during sign-up.";
    }
    $stmt->close();
}

// Handle login for admin panel
if (isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$username' AND is_admin=1");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            header("Location: admin.php");
            exit;
        } else {
            $_SESSION['message'] = "Invalid credentials.";
        }
    } else {
        $_SESSION['message'] = "Admin not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Key System - Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #f4f4f4;
        }
        h1, h2 {
            text-align: center;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #0056b3;
        }
        .message {
            color: green;
            text-align: center;
        }
        .error {
            color: red;
            text-align: center;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Key System for Game Menu</h1>
    <div class="form-container">
        <h2>Sign Up</h2>
        <?php
        if (isset($_SESSION['message'])) {
            $class = strpos($_SESSION['message'], 'Error') === false ? 'message' : 'error';
            echo "<p class='$class'>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']);
        }
        ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" name="signup" value="Sign Up">
        </form>
        <p>Admin? <a href="#login" onclick="document.getElementById('login-form').style.display='block';">Log in here</a></p>
        <form id="login-form" method="POST" action="" style="display:none;">
            <h2>Admin Login</h2>
            <input type="text" name="username" placeholder="Admin Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" name="login" value="Log In">
        </form>
    </div>
</body>
</html>
