<?php
/**
 * Skills CRUD API
 * Handles Create, Read, Update, Delete operations for skills
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent caching of API responses
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../config/database.php';
require_once '../includes/auth.php';

// Check authentication for write operations
$method = $_SERVER['REQUEST_METHOD'];
if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
    if (!isLoggedIn()) {
        sendError('Unauthorized', 401);
    }
}

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    sendError('Database connection failed', 500);
}

try {
    switch ($method) {
        case 'GET':
            handleGet($conn);
            break;
        case 'POST':
            handlePost($conn);
            break;
        case 'PUT':
            handlePut($conn);
            break;
        case 'DELETE':
            handleDelete($conn);
            break;
        default:
            sendError('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Skills API Error: " . $e->getMessage());
    sendError('An error occurred', 500);
}

/**
 * Get skills (all or by ID)
 */
function handleGet($conn) {
    $id = $_GET['id'] ?? null;
    $category = $_GET['category'] ?? null;
    $active_only = isset($_GET['active_only']) ? filter_var($_GET['active_only'], FILTER_VALIDATE_BOOLEAN) : false;
    
    if ($id) {
        // Get single skill
        $query = "SELECT * FROM skills WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $skill = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($skill) {
            sendSuccess('Skill retrieved successfully', $skill);
        } else {
            sendError('Skill not found', 404);
        }
    } else {
        // Get all skills with optional filters
        $query = "SELECT * FROM skills WHERE 1=1";
        
        if ($active_only) {
            $query .= " AND is_active = 1";
        }
        
        if ($category) {
            $query .= " AND category = :category";
        }
        
        $query .= " ORDER BY display_order ASC, name ASC";
        
        $stmt = $conn->prepare($query);
        
        if ($category) {
            $stmt->bindParam(':category', $category);
        }
        
        $stmt->execute();
        $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by category
        $grouped = [];
        foreach ($skills as $skill) {
            $cat = $skill['category'];
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            $grouped[$cat][] = $skill;
        }
        
        sendSuccess('Skills retrieved successfully', [
            'skills' => $skills,
            'grouped' => $grouped,
            'total' => count($skills)
        ]);
    }
}

/**
 * Create or Update skill
 */
function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check if this is an update (has ID)
    $isUpdate = !empty($data['id']);
    $id = $isUpdate ? intval($data['id']) : null;
    
    // Validate required fields
    $required = ['name', 'level', 'category'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendError("Field '$field' is required");
        }
    }
    
    // Validate level
    $level = intval($data['level']);
    if ($level < 0 || $level > 100) {
        sendError('Level must be between 0 and 100');
    }
    
    // Sanitize inputs
    $name = sanitizeInput($data['name']);
    $category = sanitizeInput($data['category']);
    $icon_type = sanitizeInput($data['icon_type'] ?? 'text');
    $icon_value = sanitizeInput($data['icon_value'] ?? '');
    $color = sanitizeInput($data['color'] ?? '#3b82f6');
    $display_order = intval($data['display_order'] ?? 0);
    $is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;
    
    // Debug logging
    error_log("API RECEIVED COLOR: " . $color);
    error_log("FULL DATA: " . json_encode($data));
    
    if ($isUpdate) {
        // UPDATE existing skill
        $query = "UPDATE skills SET 
                  name = :name, 
                  level = :level, 
                  category = :category, 
                  icon_type = :icon_type, 
                  icon_value = :icon_value, 
                  color = :color, 
                  display_order = :display_order, 
                  is_active = :is_active,
                  updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':level', $level, PDO::PARAM_INT);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':icon_type', $icon_type);
        $stmt->bindParam(':icon_value', $icon_value);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':display_order', $display_order, PDO::PARAM_INT);
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $rowCount = $stmt->rowCount();
            error_log("✅ SQL UPDATE SUCCESS - ID: $id, Color: $color, Rows affected: $rowCount");
            
            // Verify the update
            $verifyQuery = "SELECT id, name, color FROM skills WHERE id = :id";
            $verifyStmt = $conn->prepare($verifyQuery);
            $verifyStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $verifyStmt->execute();
            $verifiedData = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            error_log("VERIFIED DATA AFTER UPDATE: " . json_encode($verifiedData));
            
            sendSuccess('Skill updated successfully', ['id' => $id, 'color' => $color]);
        } else {
            error_log("❌ SQL UPDATE FAILED: " . json_encode($stmt->errorInfo()));
            sendError('Failed to update skill');
        }
    } else {
        // INSERT new skill
        $query = "INSERT INTO skills (name, level, category, icon_type, icon_value, color, display_order, is_active) 
                  VALUES (:name, :level, :category, :icon_type, :icon_value, :color, :display_order, :is_active)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':level', $level, PDO::PARAM_INT);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':icon_type', $icon_type);
        $stmt->bindParam(':icon_value', $icon_value);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':display_order', $display_order, PDO::PARAM_INT);
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $newId = $conn->lastInsertId();
            sendSuccess('Skill created successfully', ['id' => $newId]);
        } else {
            sendError('Failed to create skill');
        }
    }
}

/**
 * Update skill
 */
function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        sendError('Skill ID is required');
    }
    
    $id = intval($data['id']);
    
    // Check if skill exists
    $checkQuery = "SELECT id FROM skills WHERE id = :id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        sendError('Skill not found', 404);
    }
    
    // Build update query dynamically
    $updates = [];
    $params = ['id' => $id];
    
    $allowed_fields = ['name', 'level', 'category', 'icon_type', 'icon_value', 'display_order', 'is_active'];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = :$field";
            
            if ($field === 'level' || $field === 'display_order' || $field === 'is_active') {
                $params[$field] = intval($data[$field]);
            } else {
                $params[$field] = sanitizeInput($data[$field]);
            }
        }
    }
    
    if (empty($updates)) {
        sendError('No fields to update');
    }
    
    // Validate level if provided
    if (isset($params['level']) && ($params['level'] < 0 || $params['level'] > 100)) {
        sendError('Level must be between 0 and 100');
    }
    
    $query = "UPDATE skills SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    
    if ($stmt->execute()) {
        sendSuccess('Skill updated successfully');
    } else {
        sendError('Failed to update skill');
    }
}

/**
 * Delete skill
 */
function handleDelete($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        sendError('Skill ID is required');
    }
    
    $id = intval($id);
    
    // Check if skill exists
    $checkQuery = "SELECT id FROM skills WHERE id = :id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        sendError('Skill not found', 404);
    }
    
    // Delete skill
    $query = "DELETE FROM skills WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        sendSuccess('Skill deleted successfully');
    } else {
        sendError('Failed to delete skill');
    }
}
