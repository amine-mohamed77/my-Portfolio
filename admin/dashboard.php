<?php
/**
 * Admin Dashboard
 * Main interface for managing skills and projects
 */
require_once 'config/database.php';
require_once 'includes/auth.php';

// Require authentication
requireAuth();

$adminUsername = getAdminUsername();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

$message = '';
$messageType = '';

// Handle account settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_account') {
    $newUsername = trim($_POST['new_username']);
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    $adminId = $_SESSION['admin_id'];
    $updateFields = [];
    $updateParams = [];
    
    // Update username if provided
    if (!empty($newUsername)) {
        $updateFields[] = "username = :username";
        $updateParams[':username'] = $newUsername;
    }
    
    // Update password if provided
    if (!empty($newPassword)) {
        if ($newPassword === $confirmPassword) {
            if (strlen($newPassword) >= 6) {
                $updateFields[] = "password = :password";
                $updateParams[':password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            } else {
                $message = 'Password must be at least 6 characters';
                $messageType = 'error';
            }
        } else {
            $message = 'New passwords do not match';
            $messageType = 'error';
        }
    }
    
    // Perform update if no errors
    if (empty($message) && !empty($updateFields)) {
        $updateQuery = "UPDATE admin_users SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bindParam(':id', $adminId);
        foreach ($updateParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($stmt->execute()) {
            header('Location: dashboard.php?settings_updated=1');
            exit;
        } else {
            $message = 'Failed to update account settings';
            $messageType = 'error';
        }
    } elseif (empty($message) && empty($updateFields)) {
        $message = 'No changes were made';
        $messageType = 'error';
    }
}

// Handle skill add/update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_skill') {
    $id = !empty($_POST['skill_id']) ? intval($_POST['skill_id']) : 0;
    $name = $_POST['skill_name'];
    $level = intval($_POST['skill_level']);
    $category = $_POST['skill_category'];
    $icon_value = $_POST['skill_icon'];
    $color = $_POST['skill_color'];
    
    try {
        if ($id > 0) {
            // Update existing skill
            $updateQuery = "UPDATE skills SET 
                            name = :name, 
                            level = :level, 
                            category = :category, 
                            icon_value = :icon_value, 
                            color = :color,
                            updated_at = CURRENT_TIMESTAMP
                            WHERE id = :id";
            
            $stmt = $conn->prepare($updateQuery);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':level', $level, PDO::PARAM_INT);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':icon_value', $icon_value);
            $stmt->bindParam(':color', $color);
            
            if ($stmt->execute()) {
                header('Location: dashboard.php?success=1&msg=updated');
                exit;
            }
        } else {
            // Insert new skill
            $insertQuery = "INSERT INTO skills (name, level, category, icon_value, color, is_active) 
                           VALUES (:name, :level, :category, :icon_value, :color, 1)";
            
            $stmt = $conn->prepare($insertQuery);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':level', $level, PDO::PARAM_INT);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':icon_value', $icon_value);
            $stmt->bindParam(':color', $color);
            
            if ($stmt->execute()) {
                header('Location: dashboard.php?success=1&msg=added');
                exit;
            }
        }
        
        $message = 'Failed to save skill';
        $messageType = 'error';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get dashboard stats
$stats = [
    'total_skills' => 0,
    'total_projects' => 0,
    'avg_skill_level' => 0
];

try {
    $statsQuery = "SELECT * FROM dashboard_stats";
    $statsStmt = $conn->query($statsQuery);
    $statsData = $statsStmt->fetch(PDO::FETCH_ASSOC);
    if ($statsData) {
        $stats = $statsData;
    }
} catch (Exception $e) {
    error_log("Stats error: " . $e->getMessage());
}

// Get skill for editing if requested
$editSkill = null;
if (isset($_GET['edit_skill'])) {
    $editId = intval($_GET['edit_skill']);
    $query = "SELECT * FROM skills WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $editId, PDO::PARAM_INT);
    $stmt->execute();
    $editSkill = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in',
                        'slide-in': 'slideIn 0.3s ease-out',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">

    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        AMIN.DEV
                    </h1>
                    <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Admin Dashboard</span>
                </div>

                <!-- Right Section -->
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <button id="theme-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg id="theme-icon-dark" class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-icon-light" class="hidden w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-3">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Welcome, <strong><?php echo htmlspecialchars($adminUsername); ?></strong></span>
                        <a href="logout.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium">
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Skills -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 animate-fade-in">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Skills</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_skills']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Projects -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 animate-fade-in" style="animation-delay: 0.1s">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Projects</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_projects']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Average Skill Level -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 animate-fade-in" style="animation-delay: 0.2s">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Avg Skill Level</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo round($stats['avg_skill_level'], 1); ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <!-- Tab Headers -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex -mb-px">
                    <button onclick="switchTab('skills')" id="tab-skills" class="tab-button active py-4 px-6 text-sm font-medium border-b-2 border-blue-600 text-blue-600 dark:text-blue-400">
                        Skills Management
                    </button>
                    <button onclick="switchTab('projects')" id="tab-projects" class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        Projects Management
                    </button>
                    <button onclick="switchTab('settings')" id="tab-settings" class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        ⚙️ Settings
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Success/Error Message -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="mb-6 p-4 rounded-lg bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                        <?php 
                        if (isset($_GET['msg']) && $_GET['msg'] === 'added') {
                            echo '✅ Skill added successfully!';
                        } else {
                            echo '✅ Skill updated successfully!';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['settings_updated'])): ?>
                    <div class="mb-6 p-4 rounded-lg bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                        ✅ Account settings updated successfully!
                    </div>
                <?php endif; ?>
                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Skills Tab -->
                <div id="content-skills" class="tab-content">
                    <!-- Add Skill Button -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Skills</h2>
                        <button onclick="openSkillModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium">
                            + Add New Skill
                        </button>
                    </div>

                    <!-- Skills Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="skills-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Projects Tab -->
                <div id="content-projects" class="tab-content hidden">
                    <!-- Add Project Button -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Projects</h2>
                        <button onclick="openProjectModal()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium">
                            + Add New Project
                        </button>
                    </div>

                    <!-- Projects Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tech Stack</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="projects-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

                <!-- Settings Tab -->
                <div id="content-settings" class="tab-content hidden">
                    <div class="max-w-2xl">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">⚙️ Account Settings</h2>
                        
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                            <form method="POST" action="" class="space-y-5">
                                <input type="hidden" name="action" value="update_account">
                                
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                                    <p class="text-sm text-blue-800 dark:text-blue-200">
                                        <strong>Current Username:</strong> <?php echo htmlspecialchars($adminUsername); ?>
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        New Username
                                    </label>
                                    <input type="text" name="new_username" 
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                           placeholder="Enter new username">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        New Password <span class="text-gray-400 text-xs">(leave empty to keep current)</span>
                                    </label>
                                    <input type="password" name="new_password" minlength="6"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                           placeholder="At least 6 characters">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Password must be at least 6 characters long
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Confirm New Password
                                    </label>
                                    <input type="password" name="confirm_password" minlength="6"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                           placeholder="Re-enter new password">
                                </div>
                                
                                <div class="flex gap-3 pt-4">
                                    <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                                        💾 Save Changes
                                    </button>
                                    <button type="reset" class="px-6 py-3 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg font-medium transition-colors">
                                        Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <h3 class="font-medium text-blue-800 dark:text-blue-200 mb-2">💡 Tips</h3>
                            <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                <li>• Leave fields empty to keep current values</li>
                                <li>• Use a strong, unique password (8+ characters)</li>
                                <li>• Changes take effect immediately</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Back to Portfolio Link -->
        <div class="mt-6 text-center">
            <a href="../index.html" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                View Live Portfolio →
            </a>
        </div>
    </div>

    <!-- Skill Modal -->
    <div id="skill-modal" class="<?php echo $editSkill ? '' : 'hidden'; ?> fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full p-5 my-8 shadow-2xl">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3" id="skill-modal-title">
                <?php echo $editSkill ? '✏️ Edit: ' . htmlspecialchars($editSkill['name']) : 'Add New Skill'; ?>
            </h3>
            <form id="skill-form" method="POST" action="" class="space-y-3">
                <input type="hidden" name="action" value="save_skill">
                <input type="hidden" name="skill_id" id="skill-id" value="<?php echo $editSkill ? $editSkill['id'] : ''; ?>">
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Skill Name</label>
                    <input type="text" name="skill_name" value="<?php echo $editSkill ? htmlspecialchars($editSkill['name']) : ''; ?>" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Level (%)</label>
                    <input type="number" name="skill_level" value="<?php echo $editSkill ? $editSkill['level'] : ''; ?>" min="0" max="100" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <select name="skill_category" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="Frontend" <?php echo $editSkill && $editSkill['category'] === 'Frontend' ? 'selected' : ''; ?>>Frontend</option>
                        <option value="Backend" <?php echo $editSkill && $editSkill['category'] === 'Backend' ? 'selected' : ''; ?>>Backend</option>
                        <option value="Database" <?php echo $editSkill && $editSkill['category'] === 'Database' ? 'selected' : ''; ?>>Database</option>
                        <option value="DevOps" <?php echo $editSkill && $editSkill['category'] === 'DevOps' ? 'selected' : ''; ?>>DevOps</option>
                        <option value="Other" <?php echo $editSkill && $editSkill['category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Icon <span class="text-gray-400 font-normal">(URL or text)</span></label>
                    <input type="text" name="skill_icon" value="<?php echo $editSkill ? htmlspecialchars($editSkill['icon_value']) : ''; ?>" placeholder="https://... or text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Card Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="skill_color" id="skill-color" value="<?php echo $editSkill ? $editSkill['color'] : '#3b82f6'; ?>" class="h-9 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                        <input type="text" id="skill-color-hex" value="<?php echo $editSkill ? $editSkill['color'] : '#3b82f6'; ?>" placeholder="#3b82f6" class="flex-1 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" readonly>
                        <button type="button" onclick="document.getElementById('skill-color').value='#3b82f6';document.getElementById('skill-color-hex').value='#3b82f6'" class="w-7 h-7 rounded-full bg-blue-500 border border-gray-300" title="Blue"></button>
                        <button type="button" onclick="document.getElementById('skill-color').value='#8b5cf6';document.getElementById('skill-color-hex').value='#8b5cf6'" class="w-7 h-7 rounded-full bg-purple-500 border border-gray-300" title="Purple"></button>
                        <button type="button" onclick="document.getElementById('skill-color').value='#ef4444';document.getElementById('skill-color-hex').value='#ef4444'" class="w-7 h-7 rounded-full bg-red-500 border border-gray-300" title="Red"></button>
                        <button type="button" onclick="document.getElementById('skill-color').value='#f97316';document.getElementById('skill-color-hex').value='#f97316'" class="w-7 h-7 rounded-full bg-orange-500 border border-gray-300" title="Orange"></button>
                        <button type="button" onclick="document.getElementById('skill-color').value='#10b981';document.getElementById('skill-color-hex').value='#10b981'" class="w-7 h-7 rounded-full bg-green-500 border border-gray-300" title="Green"></button>
                        <button type="button" onclick="document.getElementById('skill-color').value='#ec4899';document.getElementById('skill-color-hex').value='#ec4899'" class="w-7 h-7 rounded-full bg-pink-500 border border-gray-300" title="Pink"></button>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                        💾 Save
                    </button>
                    <a href="dashboard.php" class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg text-sm font-medium text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Project Modal -->
    <div id="project-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6 animate-slide-in max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4" id="project-modal-title">Add New Project</h3>
            <form id="project-form" class="space-y-4" enctype="multipart/form-data">
                <input type="hidden" id="project-id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project Title</label>
                    <input type="text" id="project-title" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea id="project-description" rows="3" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tech Stack (comma-separated)</label>
                    <input type="text" id="project-tech" placeholder="e.g., Laravel, Vue.js, MySQL" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Live URL (optional)</label>
                    <input type="url" id="project-live" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">GitHub URL (optional)</label>
                    <input type="url" id="project-github" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project Image (optional)</label>
                    <input type="file" id="project-image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="project-featured" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                    <label for="project-featured" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Featured Project</label>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium">
                        Save
                    </button>
                    <button type="button" onclick="closeProjectModal()" class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg transition-colors font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="js/dashboard.js?v=<?php echo time(); ?>"></script>
    <script>
        // Color picker sync
        document.addEventListener('DOMContentLoaded', function() {
            const colorPicker = document.getElementById('skill-color');
            const colorHex = document.getElementById('skill-color-hex');
            
            if (colorPicker && colorHex) {
                // Update hex display when color picker changes
                colorPicker.addEventListener('input', (e) => {
                    colorHex.value = e.target.value;
                });
            }
        });
    </script>

</body>
</html>
