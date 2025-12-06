<?php
/**
 * Admin Login Page
 */
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            // Try admin_users table first, then admin table
            $tableName = null;
            try {
                $checkTable = $conn->query("SHOW TABLES LIKE 'admin_users'");
                if ($checkTable->rowCount() > 0) {
                    $tableName = 'admin_users';
                } else {
                    $checkTable = $conn->query("SHOW TABLES LIKE 'admin'");
                    if ($checkTable->rowCount() > 0) {
                        $tableName = 'admin';
                    }
                }
                
                if ($tableName === null) {
                    $error = 'Admin table does not exist. Please run the database setup first.';
                } else {
                    // Check if table has any users
                    $countUsers = $conn->query("SELECT COUNT(*) FROM `$tableName`")->fetchColumn();
                    if ($countUsers == 0) {
                        // Add default admin user
                        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("INSERT INTO `$tableName` (username, password, email) VALUES (?, ?, ?)");
                        $stmt->execute(['admin', $defaultPassword, 'admin@example.com']);
                    }
                    
                    $query = "SELECT id, username, password FROM `$tableName` WHERE username = :username LIMIT 1";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':username', $username);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (password_verify($password, $row['password'])) {
                            // Update last login (skip if column doesn't exist)
                            try {
                                $updateQuery = "UPDATE `$tableName` SET updated_at = NOW() WHERE id = :id";
                                $updateStmt = $conn->prepare($updateQuery);
                                $updateStmt->bindParam(':id', $row['id']);
                                $updateStmt->execute();
                            } catch (Exception $e) {
                                // Column doesn't exist, skip
                            }
                            
                            // Login user
                            loginUser($row['id'], $row['username']);
                            
                            header('Location: dashboard.php');
                            exit();
                        } else {
                            $error = 'Wrong password. Try: admin123';
                        }
                    } else {
                        $error = 'User "' . htmlspecialchars($username) . '" not found. Try username: admin';
                    }
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Portfolio Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-black min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md animate-fade-in">
        <!-- Login Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 border border-gray-200 dark:border-gray-700">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    AMIN.DEV
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Admin Dashboard</p>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-red-600 dark:text-red-400 text-sm"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (!empty($success)): ?>
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <p class="text-green-600 dark:text-green-400 text-sm"><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Enter your username"
                        value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Enter your password"
                    >
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg"
                >
                    Sign In
                </button>
            </form>

            <!-- Info Message -->
            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <p class="text-blue-600 dark:text-blue-400 text-xs text-center">
                    <strong>Default Credentials:</strong><br>
                    Username: <code>admin</code> | Password: <code>admin123</code>
                </p>
            </div>

            <!-- Back to Portfolio -->
            <div class="mt-6 text-center">
                <a href="../index.html" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                    ← Back to Portfolio
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-gray-600 dark:text-gray-400 text-sm">
            <p>&copy; 2024 AMIN.DEV. All rights reserved.</p>
        </div>
    </div>

    <!-- Dark Mode Toggle Script -->
    <script>
        // Check for saved theme preference or default to light mode
        const theme = localStorage.getItem('admin_theme') || 'light';
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>

</body>
</html>
