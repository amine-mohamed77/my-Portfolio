<?php
/**
 * Setup Admin Table
 * Run this once to create the admin table and default user
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Admin Table</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff; }
        .success { color: #4ade80; }
        .error { color: #f87171; }
        .info { color: #60a5fa; }
        pre { background: #2a2a2a; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #60a5fa; }
    </style>
</head>
<body>
    <h1>🔧 Admin Table Setup</h1>

<?php
try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<h2>Creating admin table...</h2>";
    
    // Create admin table
    $createTable = "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->exec($createTable);
    echo "<p class='success'>✅ Admin table created successfully!</p>";
    
    // Check if admin user exists
    $checkQuery = "SELECT COUNT(*) as count FROM admin WHERE username = 'admin'";
    $stmt = $conn->query($checkQuery);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        // Insert default admin user
        // Username: admin
        // Password: admin123
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $insertQuery = "INSERT INTO admin (username, password, email) 
                       VALUES (:username, :password, :email)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bindValue(':username', 'admin');
        $stmt->bindValue(':password', $defaultPassword);
        $stmt->bindValue(':email', 'admin@example.com');
        $stmt->execute();
        
        echo "<p class='success'>✅ Default admin user created!</p>";
        echo "<div class='info' style='background:#2a2a2a;padding:15px;border-radius:5px;margin:20px 0;'>";
        echo "<h3>Default Credentials:</h3>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p style='color:#fbbf24;'>⚠️ Please change these credentials in the Settings tab!</p>";
        echo "</div>";
    } else {
        echo "<p class='info'>ℹ️ Admin user already exists</p>";
    }
    
    // Show all admin users
    echo "<h2>Current Admin Users:</h2>";
    $query = "SELECT id, username, email, created_at FROM admin";
    $stmt = $conn->query($query);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($admins);
    echo "</pre>";
    
    echo "<hr>";
    echo "<h2>✅ Setup Complete!</h2>";
    echo "<p><a href='login.php' style='color:#60a5fa;'>→ Go to Login Page</a></p>";
    echo "<p><a href='dashboard.php' style='color:#60a5fa;'>→ Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre class='error'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

</body>
</html>
