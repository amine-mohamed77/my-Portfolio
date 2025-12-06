<?php
/**
 * Export Static HTML for GitHub Pages
 * This will generate a static index.html from your index.php with database content
 */

require_once 'config/database.php';

// Start output buffering
ob_start();

// Load skills and projects from database
$skills = [];
$projects = [];

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn !== null) {
        // Fetch active skills
        $query = "SELECT * FROM skills WHERE is_active = 1 ORDER BY display_order";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch active projects
        $query = "SELECT * FROM projects WHERE is_active = 1 ORDER BY display_order";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Continue with empty arrays
}

// Include the index.php file to render it
include '../index.php';

// Get the rendered content
$htmlContent = ob_get_clean();

// Save to index.html in the root directory
$outputFile = '../index.html';
file_put_contents($outputFile, $htmlContent);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Export Complete</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #1a1a1a; color: #fff; text-align: center; }
        .success { color: #4ade80; padding: 30px; background: #166534; border-radius: 10px; margin: 20px auto; max-width: 600px; }
        h1 { color: #60a5fa; font-size: 36px; }
        code { background: #2a2a2a; padding: 5px 10px; border-radius: 5px; color: #4ade80; }
        a { color: #60a5fa; font-size: 18px; font-weight: bold; text-decoration: none; }
        .button { display: inline-block; padding: 15px 30px; background: #3b82f6; color: #fff; border-radius: 10px; margin: 10px; }
        .button:hover { background: #2563eb; }
    </style>
</head>
<body>
    <h1>✅ Export Complete!</h1>
    
    <div class="success">
        <h2>Static HTML Generated Successfully!</h2>
        <p>Your <code>index.html</code> has been created with all your current skills and projects.</p>
        <p>File size: <?php echo number_format(strlen($htmlContent)); ?> bytes</p>
        <p>Skills included: <?php echo count($skills); ?></p>
        <p>Projects included: <?php echo count($projects); ?></p>
    </div>
    
    <div style="margin: 40px 0;">
        <a href="../index.html" class="button" target="_blank">📄 View Static HTML</a>
        <a href="export_static.php" class="button">🔄 Re-Export</a>
    </div>
    
    <div class="success">
        <h3>📤 Next Steps for GitHub Pages:</h3>
        <ol style="text-align: left; max-width: 500px; margin: 20px auto; line-height: 2;">
            <li>Commit <code>index.html</code> to your repository</li>
            <li>Push to GitHub</li>
            <li>Your portfolio will work on GitHub Pages!</li>
            <li><strong>Re-run this export</strong> whenever you update skills/projects</li>
        </ol>
    </div>
    
    <div style="margin-top: 40px; padding: 20px; background: #2a2a2a; border-radius: 10px; max-width: 600px; margin: 40px auto;">
        <h3 style="color: #fbbf24;">⚠️ Important Notes:</h3>
        <ul style="text-align: left; line-height: 2;">
            <li>The exported HTML is a <strong>snapshot</strong> of your current data</li>
            <li>Run this export again after adding/editing projects or skills</li>
            <li><code>index.php</code> still works locally with live database</li>
            <li><code>index.html</code> is the static version for GitHub Pages</li>
        </ul>
    </div>
    
</body>
</html>
