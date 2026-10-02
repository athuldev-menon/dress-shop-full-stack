<?php
// reset_password.php — Run this ONCE to fix the admin password
// Then DELETE this file immediately after use!

require_once 'db.php';

$username    = 'admin';
$newPassword = 'Admin@1234';
$hash        = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $pdo->prepare("UPDATE Admin_Users SET password = ? WHERE username = ?");
$stmt->execute([$hash, $username]);

if ($stmt->rowCount() > 0) {
    echo '<p style="color:green;font-family:sans-serif;">
            ✅ Password reset successfully!<br>
            Username: <strong>' . $username . '</strong><br>
            Password: <strong>' . $newPassword . '</strong><br><br>
            <strong style="color:red;">⚠️ DELETE this file now for security!</strong><br><br>
            <a href="login.php">→ Go to Login</a>
          </p>';
} else {
    // Admin user might not exist at all — insert fresh
    $insert = $pdo->prepare(
        "INSERT INTO Admin_Users (username, password, full_name)
         VALUES (?, ?, 'Store Administrator')
         ON DUPLICATE KEY UPDATE password = VALUES(password)"
    );
    $insert->execute([$username, $hash]);
    echo '<p style="color:green;font-family:sans-serif;">
            ✅ Admin account created/fixed!<br>
            Username: <strong>' . $username . '</strong><br>
            Password: <strong>' . $newPassword . '</strong><br><br>
            <strong style="color:red;">⚠️ DELETE this file now for security!</strong><br><br>
            <a href="login.php">→ Go to Login</a>
          </p>';
}
