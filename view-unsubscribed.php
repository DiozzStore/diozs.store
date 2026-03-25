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
<title>Login</title>
</head>
<body style="font-family: Arial; text-align:center; margin-top:100px;">
<h2>Admin Login</h2>
<?php if (isset($login_error)) echo "<p style='color:red;'>$login_error</p>"; ?>
<form method="POST">
<input type="password" name="password" placeholder="Password" required>
<br><br>
<button type="submit">Login</button>
</form>
</body>
</html>
<?php
exit;
}

// ✅ CONNECT DATABASE
require 'db.php';

// ✅ FETCH DATA FROM MYSQL
$stmt = $pdo->query("SELECT * FROM unsubscribers ORDER BY id DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($data);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Unsubscribed Users</title>
<style>
body {
    font-family: Arial;
    background: #f5f5f5;
    padding: 20px;
}
.container {
    background: white;
    padding: 20px;
    border-radius: 8px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    border-bottom: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}
th {
    background: #222;
    color: white;
}
</style>
</head>
<body>

<div class="container">
    <h2>📊 Unsubscribers</h2>
    <p><strong>Total:</strong> <?= $total ?></p>

    <?php if ($total === 0): ?>
        <p>No data found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Country</th>
                    <th>City</th>
                    <th>IP</th>
                    <th>Device</th>
                    <th>Browser</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['country']) ?></td>
                    <td><?= htmlspecialchars($row['city']) ?></td>
                    <td><?= htmlspecialchars($row['ip']) ?></td>
                    <td><?= htmlspecialchars($row['device']) ?></td>
                    <td><?= htmlspecialchars($row['browser']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <br>
    <a href="?logout=1">Logout</a>
</div>

</body>
</html>
