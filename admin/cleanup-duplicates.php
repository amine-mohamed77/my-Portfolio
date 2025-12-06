<?php
/**
 * Cleanup Duplicate Projects
 * Run this once to remove duplicate projects created by the bug
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cleanup Duplicates</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff; }
        .success { color: #4ade80; }
        .error { color: #f87171; }
        .info { color: #60a5fa; }
        pre { background: #2a2a2a; padding: 15px; border-radius: 5px; overflow-x: auto; }
        button { padding: 10px 20px; background: #3b82f6; color: #fff; border: none; cursor: pointer; border-radius: 5px; margin: 10px 5px; }
        button:hover { background: #2563eb; }
        button.danger { background: #ef4444; }
        button.danger:hover { background: #dc2626; }
    </style>
</head>
<body>
    <h1>🧹 Cleanup Duplicate Projects</h1>
    
    <?php
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Get all projects
        $query = "SELECT * FROM projects ORDER BY id ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Current Projects:</h2>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #333;'><th>ID</th><th>Title</th><th>Description</th><th>Created</th><th>Action</th></tr>";
        
        foreach ($projects as $project) {
            echo "<tr>";
            echo "<td>" . $project['id'] . "</td>";
            echo "<td>" . htmlspecialchars($project['title']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($project['description'], 0, 50)) . "...</td>";
            echo "<td>" . $project['created_at'] . "</td>";
            echo "<td><a href='?delete=" . $project['id'] . "' onclick='return confirm(\"Delete this project?\")' style='color: #ef4444;'>Delete</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Handle deletion
        if (isset($_GET['delete'])) {
            $deleteId = intval($_GET['delete']);
            $deleteQuery = "DELETE FROM projects WHERE id = :id";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
            
            if ($deleteStmt->execute()) {
                echo "<p class='success'>✅ Project ID $deleteId deleted successfully!</p>";
                echo "<p><a href='cleanup-duplicates.php'><button>Refresh</button></a></p>";
            } else {
                echo "<p class='error'>❌ Failed to delete project</p>";
            }
        }
        
        // Find duplicates
        echo "<h2>Duplicate Detection:</h2>";
        $dupQuery = "SELECT title, COUNT(*) as count FROM projects GROUP BY title HAVING count > 1";
        $dupStmt = $conn->prepare($dupQuery);
        $dupStmt->execute();
        $duplicates = $dupStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($duplicates) > 0) {
            echo "<p class='error'>⚠️ Found " . count($duplicates) . " duplicate project titles:</p>";
            foreach ($duplicates as $dup) {
                echo "<p class='info'>• " . htmlspecialchars($dup['title']) . " (" . $dup['count'] . " copies)</p>";
            }
            echo "<p>Review the table above and delete the duplicate entries manually.</p>";
        } else {
            echo "<p class='success'>✅ No duplicates found!</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <hr>
    <p><a href="dashboard.php"><button>← Back to Dashboard</button></a></p>
</body>
</html>
