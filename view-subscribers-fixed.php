<?php
session_start();
require 'db-connection.php';

$password = 'dioz2024';
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['logged_in'] = true;
        $logged_in = true;
    } else {
        $error = 'Incorrect password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: view-unsubscribed.php');
    exit;
}

// Handle delete
if ($logged_in && isset($_POST['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['delete_id']);
    $delete_query = "DELETE FROM unsubscribed WHERE id = '$id'";
    mysqli_query($conn, $delete_query);
}

// Get data from database
$query = "SELECT * FROM unsubscribed ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OneSignal Subscribers - Dioz Group</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 700;
        }
        .header-subtitle {
            font-size: 13px;
            opacity: 0.9;
        }
        .logout-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .logout-btn:hover {
            background: #ff5252;
        }
        .content {
            padding: 30px;
        }
        .stats {
            background: #f0f3f7;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        .stats h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .stats-value {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
        }
        .login-form {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
        }
        .login-form h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .error {
            color: #ff6b6b;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-submit:hover {
            background: #5568d3;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f5f7fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 13px;
            border-bottom: 2px solid #e0e0e0;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            color: #333;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .email-link {
            color: #667eea;
            text-decoration: none;
        }
        .email-link:hover {
            text-decoration: underline;
        }
        .flag {
            font-size: 18px;
            margin-right: 8px;
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
        .btn-download {
            background: #10b981;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .btn-download:hover {
            background: #059669;
        }
        .btn-delete {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-delete:hover {
            background: #ff5252;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🟡 OneSignal Subscribers</h1>
                <div class="header-subtitle">Data collected for OneSignal push notification importing.</div>
            </div>
            <?php if ($logged_in): ?>
                <a href="?logout=1"><button class="logout-btn">🚪 Logout</button></a>
            <?php endif; ?>
        </div>

        <div class="content">
            <?php if (!$logged_in): ?>
                <div class="login-form">
                    <h2>Admin Login</h2>
                    <?php if (isset($error)): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required autofocus>
                        </div>
                        <button type="submit" class="btn-submit">Login</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="stats">
                    <h3>Total Subscribers</h3>
                    <div class="stats-value"><?php echo $total; ?></div>
                </div>

                <?php if ($total > 0): ?>
                    <button class="btn-download" onclick="downloadCSV()">📥 Download CSV for OneSignal</button>
                    
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>SL No</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Country</th>
                                    <th>City</th>
                                    <th>IP Address</th>
                                    <th>Device</th>
                                    <th>Browser</th>
                                    <th>Date Added</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sl = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $sl . "</td>";
                                    echo "<td><a href='mailto:" . htmlspecialchars($row['email']) . "' class='email-link'>" . htmlspecialchars($row['email']) . "</a></td>";
                                    echo "<td>" . htmlspecialchars($row['phone'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['country'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['city'] ?? '-') . "</td>";
                                    echo "<td><small>" . htmlspecialchars($row['ip_address'] ?? '-') . "</small></td>";
                                    echo "<td><small>" . htmlspecialchars(substr($row['device'] ?? '-', 0, 30)) . "</small></td>";
                                    echo "<td><small>" . htmlspecialchars(substr($row['browser'] ?? '-', 0, 30)) . "</small></td>";
                                    echo "<td>" . date('M d, Y H:i', strtotime($row['created_at'])) . "</td>";
                                    echo "<td>
                                        <form method='POST' style='display:inline;' onsubmit=\"return confirm('Delete this entry?');\">
                                            <input type='hidden' name='delete_id' value='" . $row['id'] . "'>
                                            <button type='submit' class='btn-delete'>Delete</button>
                                        </form>
                                    </td>";
                                    echo "</tr>";
                                    $sl++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3>No subscribers yet</h3>
                        <p>Subscribers will appear here once they unsubscribe from emails.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function downloadCSV() {
            let csv = 'Email,Phone,Country,City,IP Address,Device,Browser,Date Added\n';
            <?php
            $result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                $email = addslashes($row['email']);
                $phone = addslashes($row['phone'] ?? '');
                $country = addslashes($row['country'] ?? '');
                $city = addslashes($row['city'] ?? '');
                $ip = addslashes($row['ip_address'] ?? '');
                $device = addslashes(substr($row['device'] ?? '', 0, 30));
                $browser = addslashes(substr($row['browser'] ?? '', 0, 30));
                $date = date('M d, Y H:i', strtotime($row['created_at']));
                echo "csv += \"$email,$phone,$country,$city,$ip,$device,$browser,$date\\n\";\n";
            }
            ?>
            
            let link = document.createElement('a');
            link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
            link.download = 'onesignal_subscribers_' + new Date().toISOString().split('T')[0] + '.csv';
            link.click();
        }
    </script>
</body>
</html>
<?php
mysqli_close($conn);
?>
