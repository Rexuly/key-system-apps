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

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['message'] = "Please log in as admin.";
    header("Location: index.php");
    exit;
}

// Handle key deletion
if (isset($_GET['delete'])) {
    $keyId = (int)$_GET['delete'];
    $conn->query("DELETE FROM keys WHERE id=$keyId");
    $_SESSION['message'] = "Key deleted successfully.";
    header("Location: admin.php");
    exit;
}

// Fetch all keys with user info
$result = $conn->query("SELECT k.id, k.key_value, k.created_at, u.username, u.email 
                        FROM keys k 
                        JOIN users u ON k.user_id = u.id 
                        ORDER BY k.created_at DESC");
$keys = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Key System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f4f4f4;
        }
        h1 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .action {
            color: #dc3545;
            text-decoration: none;
        }
        .action:hover {
            text-decoration: underline;
        }
        .message {
            color: green;
            text-align: center;
        }
        .logout {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .logout:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Admin Panel - Manage Keys</h1>
    <?php
    if (isset($_SESSION['message'])) {
        echo "<p class='message'>" . $_SESSION['message'] . "</p>";
        unset($_SESSION['message']);
    }
    ?>
    <table>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Key</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
        <?php foreach ($keys as $key): ?>
            <tr>
                <td><?php echo htmlspecialchars($key['username']); ?></td>
                <td><?php echo htmlspecialchars($key['email']); ?></td>
                <td><?php echo htmlspecialchars($key['key_value']); ?></td>
                <td><?php echo $key['created_at']; ?></td>
                <td><a class="action" href="?delete=<?php echo $key['id']; ?>" 
                       onclick="return confirm('Are you sure you want to delete this key?');">Delete</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a class="logout" href="logout.php">Log Out</a>
</body>
</html>
