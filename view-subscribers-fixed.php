<?php
session_start();

// 🔐 Admin password
$ADMIN_PASSWORD = 'dioz2024';

// Check login
$is_logged_in = isset($_SESSION['unsubscribed_admin_logged_in']) && $_SESSION['unsubscribed_admin_logged_in'] === true;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['unsubscribed_admin_logged_in'] = true;
        $is_logged_in = true;
    } else {
        $login_error = 'Incorrect password.';
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Show login page if not logged in
if (!$is_logged_in) {
?>
<!doctype html>
<html>
<head>
<title>Admin Login</title>
<style>
    body {
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: #f5f5f5;
        margin: 0;
    }
    .login-box {
        background: white;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 300px;
    }
    h2 {
        text-align: center;
        color: #333;
    }
    input {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 14px;
    }
    button {
        width: 100%;
        padding: 10px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }
    button:hover {
        background: #0056b3;
    }
    .error {
        color: red;
        text-align: center;
        margin-bottom: 10px;
    }
</style>
</head>
<body>
<div class="login-box">
    <h2>🔐 Admin Login</h2>
    <?php if (isset($login_error)) echo "<p class='error'>$login_error</p>"; ?>
    <form method="POST">
        <input type="password" name="password" placeholder="Enter password" required autofocus>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
<?php
exit;
}

// ✅ CONNECT TO DATABASE
require 'db.php';

// ✅ FETCH DATA FROM MYSQL
$query = "SELECT * FROM unsubscribed ORDER BY id DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

$total = count($data);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Unsubscribed Users Dashboard</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background: #f5f5f5;
        padding: 20px;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 20px;
    }
    h2 {
        color: #333;
        font-size: 24px;
    }
    .stats {
        display: flex;
        gap: 30px;
        margin-bottom: 30px;
    }
    .stat-box {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }
    .stat-label {
        color: #666;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .stat-value {
        font-size: 32px;
        font-weight: bold;
        color: #007bff;
        margin-top: 5px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    thead {
        background: #222;
        color: white;
    }
    th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
    }
    td {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
    }
    tr:hover {
        background: #f9f9f9;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }
    .logout-btn {
        background: #dc3545;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .logout-btn:hover {
        background: #c82333;
    }
    .email-link {
        color: #007bff;
        text-decoration: none;
    }
    .email-link:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>📊 Unsubscribed Users Dashboard</h2>
        <a href="?logout=1" class="logout-btn">🚪 Logout</a>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="stat-label">Total Unsubscribers</div>
            <div class="stat-value"><?= $total ?></div>
        </div>
    </div>

    <?php if ($total === 0): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h3>No unsubscribers yet</h3>
            <p>Unsubscribe data will appear here.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Country</th>
                    <th>City</th>
                    <th>IP Address</th>
                    <th>Device</th>
                    <th>Browser</th>
                    <th>Date Added</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><a href="mailto:<?= htmlspecialchars($row['email']) ?>" class="email-link"><?= htmlspecialchars($row['email']) ?></a></td>
                    <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['country'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['city'] ?? '-') ?></td>
                    <td><small><?= htmlspecialchars($row['ip_address'] ?? '-') ?></small></td>
                    <td><?= htmlspecialchars($row['device'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['browser'] ?? '-') ?></td>
                    <td><?= isset($row['created_at']) ? date('M d, Y H:i', strtotime($row['created_at'])) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

</body>
</html>

<?php
mysqli_close($conn);
?>
