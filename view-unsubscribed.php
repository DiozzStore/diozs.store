<?php
// Simple password protection for view-unsubscribed.php
session_start();

// Set a secure password (change this to your desired password)
$ADMIN_PASSWORD = 'dioz2024'; // Change this!

// Check if already logged in
$is_logged_in = isset($_SESSION['unsubscribed_admin_logged_in']) && $_SESSION['unsubscribed_admin_logged_in'] === true;

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $entered_password = $_POST['password'] ?? '';
    
    if ($entered_password === $ADMIN_PASSWORD) {
        $_SESSION['unsubscribed_admin_logged_in'] = true;
        $is_logged_in = true;
    } else {
        $login_error = 'Incorrect password. Please try again.';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// If not logged in, show login form
if (!$is_logged_in) {
    ?>
    <!doctype html>
    <html lang="en">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - OneSignal Subscribers</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .login-container {
        background: white;
        border-radius: 8px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        max-width: 400px;
        width: 90%;
    }

    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .login-header img {
        max-width: 100px;
        margin-bottom: 15px;
    }

    .login-header h1 {
        font-size: 24px;
        color: #333;
        margin-bottom: 8px;
    }

    .login-header p {
        color: #666;
        font-size: 14px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        color: #333;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 14px;
    }

    input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    input[type="password"]:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    button {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
    }

    button:hover {
        transform: translateY(-2px);
    }

    button:active {
        transform: translateY(0);
    }

    .error-message {
        background: #fee;
        border: 1px solid #fcc;
        color: #c33;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .info-message {
        background: #e8f4f8;
        border: 1px solid #b3d9ff;
        color: #004085;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 13px;
    }
    </style>
    </head>
    <body>

    <div class="login-container">
        <div class="login-header">
            <img src="https://dioz.com/wp-content/uploads/2024/07/logo.svg" alt="Dioz Logo">
            <h1>OneSignal Subscribers</h1>
            <p>Admin Access Required</p>
        </div>

        <?php if (isset($login_error)): ?>
            <div class="error-message">❌ <?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>

        <div class="info-message">
            🔒 Enter the admin password to view subscriber data.
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="password">Admin Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter password" 
                    required 
                    autofocus
                >
            </div>

            <button type="submit">🔓 Login</button>
        </form>
    </div>

    </body>
    </html>
    <?php
    exit;
}

// User is logged in - show the dashboard
// Reads from CSV, displays OneSignal-compatible data with IP and Country

$csv_file = __DIR__ . '/onesignal-import.csv';
$rows = [];
$error = null;

// Read from CSV (primary source for OneSignal data)
if (file_exists($csv_file)) {
    $handle = fopen($csv_file, 'r');
    $header = fgetcsv($handle); // Skip header
    
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) >= 13) {
            $rows[] = [
                'external_id' => $row[0] ?? '',
                'email' => $row[1] ?? '',
                'phone_number' => $row[2] ?? '',
                'subscribed' => $row[3] ?? 'yes',
                'timezone_id' => $row[4] ?? '',
                'country' => $row[5] ?? '',
                'country_name' => $row[6] ?? '',
                'city' => $row[7] ?? '',
                'language' => $row[8] ?? 'en',
                'ip' => $row[9] ?? '',
                'device_type' => $row[10] ?? '',
                'browser_name' => $row[11] ?? '',
                'suppressed' => $row[12] ?? 'FALSE',
            ];
        }
    }
    fclose($handle);
}

$total = count($rows);
// Reverse to show newest first
$rows = array_reverse($rows);

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    if (file_exists($csv_file)) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="onesignal-subscribers-' . date('Y-m-d-His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($csv_file);
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>OneSignal Subscribers | Dioz Group</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f5f5;
    padding: 30px 15px;
    color: #333;
}

.container {
    max-width: 1600px;
    margin: 0 auto;
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #eee;
}

.header h1 {
    font-size: 28px;
    color: #111;
}

.logout-btn {
    background: #dc3545;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    font-weight: 600;
    transition: background 0.3s;
}

.logout-btn:hover {
    background: #c82333;
}

.subtitle {
    color: #666;
    margin-bottom: 20px;
    font-size: 14px;
}

.stats {
    background: #f0f4ff;
    border-left: 4px solid #2563eb;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.info-box {
    background: #e7f3ff;
    border: 1px solid #b3d9ff;
    padding: 12px;
    margin-bottom: 16px;
    border-radius: 6px;
    color: #004085;
    font-size: 13px;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 12px;
}

thead {
    background: #1a1a1a;
    color: white;
}

th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 12px;
    white-space: nowrap;
}

td {
    padding: 10px 12px;
    border-bottom: 1px solid #eee;
    font-size: 12px;
}

tbody tr:hover {
    background: #f9f9f9;
}

.email {
    color: #2563eb;
    font-weight: 500;
    word-break: break-word;
}

.country {
    background: #e8f0ff;
    padding: 3px 6px;
    border-radius: 3px;
    font-weight: 600;
    text-align: center;
}

.language {
    background: #f0e8ff;
    padding: 3px 6px;
    border-radius: 3px;
    font-weight: 500;
    text-align: center;
}

.ip {
    font-family: monospace;
    font-size: 11px;
    color: #666;
}

.external-id {
    font-family: monospace;
    font-size: 10px;
    color: #999;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.btn {
    display: inline-block;
    background: #2563eb;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    text-align: center;
    border: none;
    cursor: pointer;
    margin-top: 20px;
    margin-right: 10px;
    transition: background 0.3s;
    font-size: 14px;
    font-weight: 600;
}

.btn:hover {
    background: #1e40af;
}

.btn-secondary {
    background: #666;
}

.btn-secondary:hover {
    background: #444;
}

.btn-success {
    background: #22c55e;
}

.btn-success:hover {
    background: #16a34a;
}

.empty {
    text-align: center;
    padding: 40px;
    color: #999;
}

.controls {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.notice {
    background: #fff3cd;
    border: 1px solid #ffeeba;
    padding: 12px;
    margin-bottom: 16px;
    border-radius: 6px;
    color: #856404;
    text-align: center;
}

@media (max-width: 768px) {
    th, td {
        padding: 8px;
        font-size: 11px;
    }
    
    .external-id {
        max-width: 80px;
    }
    
    .controls {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }

    .header {
        flex-direction: column;
        gap: 15px;
    }

    .header h1 {
        font-size: 22px;
    }

    .logout-btn {
        width: 100%;
    }
}
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>🔔 OneSignal Subscribers</h1>
            <p class="subtitle">Data collected for OneSignal push notification importing.</p>
        </div>
        <a href="?logout=1" class="logout-btn">🚪 Logout</a>
    </div>

    <div class="stats">
        <strong>Total Subscribers:</strong> <?= $total ?>
    </div>

    <?php if ($total > 0): ?>
    <div class="info-box">
        ✓ <strong>Ready for OneSignal Import:</strong> Download the CSV file below and upload it to OneSignal dashboard (Audience → Import Subscribers → CSV Upload)
    </div>
    <?php endif; ?>

    <?php if ($total === 0): ?>
        <div class="notice">
            ✅ No subscribers yet.
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 18%;">Email</th>
                        <th style="width: 12%;">Phone</th>
                        <th style="width: 6%;">Country</th>
                        <th style="width: 12%;">City</th>
                        <th style="width: 8%;">Language</th>
                        <th style="width: 12%;">IP Address</th>
                        <th style="width: 10%;">Device</th>
                        <th style="width: 10%;">Browser</th>
                        <th style="width: 12%;">External ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="email"><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= !empty($row['phone_number']) ? htmlspecialchars($row['phone_number']) : '—' ?></td>
                        <td class="country"><?= !empty($row['country']) ? htmlspecialchars($row['country']) : '—' ?></td>
                        <td><?= !empty($row['city']) ? htmlspecialchars($row['city']) : '—' ?></td>
                        <td class="language"><?= !empty($row['language']) ? strtoupper(htmlspecialchars($row['language'])) : '—' ?></td>
                        <td class="ip"><?= !empty($row['ip']) ? htmlspecialchars($row['ip']) : '—' ?></td>
                        <td><?= !empty($row['device_type']) ? htmlspecialchars($row['device_type']) : '—' ?></td>
                        <td><?= !empty($row['browser_name']) ? htmlspecialchars($row['browser_name']) : '—' ?></td>
                        <td class="external-id" title="<?= htmlspecialchars($row['external_id']) ?>"><?= htmlspecialchars($row['external_id']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="controls">
            <a href="/?export=csv" class="btn btn-success">📥 Download CSV for OneSignal</a>
            <a href="/" class="btn">← Back to Home</a>
            <button class="btn btn-secondary" onclick="window.print()">🖨️ Print</button>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
