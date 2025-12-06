<?php
/**
 * Projects CRUD API
 * Handles Create, Read, Update, Delete operations for projects
 * Includes image upload functionality
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

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
    error_log("Projects API Error: " . $e->getMessage());
    sendError('An error occurred', 500);
}

/**
 * Get projects (all or by ID)
 */
function handleGet($conn) {
    $id = $_GET['id'] ?? null;
    $featured_only = isset($_GET['featured_only']) ? filter_var($_GET['featured_only'], FILTER_VALIDATE_BOOLEAN) : false;
    $active_only = isset($_GET['active_only']) ? filter_var($_GET['active_only'], FILTER_VALIDATE_BOOLEAN) : false;
    
    if ($id) {
        // Get single project
        $query = "SELECT * FROM projects WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($project) {
            // Decode JSON fields
            $project['tech_stack'] = json_decode($project['tech_stack'], true);
            sendSuccess('Project retrieved successfully', $project);
        } else {
            sendError('Project not found', 404);
        }
    } else {
        // Get all projects with optional filters
        $query = "SELECT * FROM projects WHERE 1=1";
        
        if ($active_only) {
            $query .= " AND is_active = 1";
        }
        
        if ($featured_only) {
            $query .= " AND is_featured = 1";
        }
        
        $query .= " ORDER BY display_order ASC, created_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSON fields
        foreach ($projects as &$project) {
            $project['tech_stack'] = json_decode($project['tech_stack'], true);
        }
        
        sendSuccess('Projects retrieved successfully', [
            'projects' => $projects,
            'total' => count($projects)
        ]);
    }
}

/**
 * Create or Update project
 */
function handlePost($conn) {
    // Handle multipart/form-data for file uploads
    $data = $_POST;
    
    // Check if this is an update (has ID)
    $isUpdate = !empty($data['id']);
    $id = $isUpdate ? intval($data['id']) : null;
    
    // Validate required fields
    $required = ['title', 'description'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendError("Field '$field' is required");
        }
    }
    
    // Sanitize inputs
    $title = sanitizeInput($data['title']);
    $description = sanitizeInput($data['description']);
    $live_url = !empty($data['live_url']) ? sanitizeInput($data['live_url']) : null;
    $github_url = !empty($data['github_url']) ? sanitizeInput($data['github_url']) : null;
    $display_order = intval($data['display_order'] ?? 0);
    $is_featured = isset($data['is_featured']) ? intval($data['is_featured']) : 0;
    $is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;
    
    // Handle tech stack (can be JSON string or array)
    $tech_stack = isset($data['tech_stack']) ? $data['tech_stack'] : '[]';
    if (is_string($tech_stack)) {
        $tech_stack_array = json_decode($tech_stack, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // If it's a comma-separated string
            $tech_stack_array = array_map('trim', explode(',', $tech_stack));
        }
    } else {
        $tech_stack_array = $tech_stack;
    }
    $tech_stack_json = json_encode($tech_stack_array);
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_path = handleImageUpload($_FILES['image']);
        if (!$image_path) {
            sendError('Failed to upload image');
        }
    }
    
    if ($isUpdate) {
        // UPDATE existing project
        if ($image_path) {
            // Update with new image
            $query = "UPDATE projects SET 
                      title = :title, 
                      description = :description, 
                      tech_stack = :tech_stack, 
                      image_path = :image_path, 
                      live_url = :live_url, 
                      github_url = :github_url, 
                      display_order = :display_order, 
                      is_featured = :is_featured, 
                      is_active = :is_active,
                      updated_at = CURRENT_TIMESTAMP
                      WHERE id = :id";
        } else {
            // Update without changing image
            $query = "UPDATE projects SET 
                      title = :title, 
                      description = :description, 
                      tech_stack = :tech_stack, 
                      live_url = :live_url, 
                      github_url = :github_url, 
                      display_order = :display_order, 
                      is_featured = :is_featured, 
                      is_active = :is_active,
                      updated_at = CURRENT_TIMESTAMP
                      WHERE id = :id";
        }
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':tech_stack', $tech_stack_json);
        if ($image_path) {
            $stmt->bindParam(':image_path', $image_path);
        }
        $stmt->bindParam(':live_url', $live_url);
        $stmt->bindParam(':github_url', $github_url);
        $stmt->bindParam(':display_order', $display_order, PDO::PARAM_INT);
        $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT);
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            sendSuccess('Project updated successfully', ['id' => $id]);
        } else {
            sendError('Failed to update project');
        }
    } else {
        // INSERT new project
        $query = "INSERT INTO projects (title, description, tech_stack, image_path, live_url, github_url, display_order, is_featured, is_active) 
                  VALUES (:title, :description, :tech_stack, :image_path, :live_url, :github_url, :display_order, :is_featured, :is_active)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':tech_stack', $tech_stack_json);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':live_url', $live_url);
        $stmt->bindParam(':github_url', $github_url);
        $stmt->bindParam(':display_order', $display_order, PDO::PARAM_INT);
        $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT);
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $newId = $conn->lastInsertId();
            sendSuccess('Project created successfully', ['id' => $newId]);
        } else {
            sendError('Failed to create project');
        }
    }
}

/**
 * Update project
 */
function handlePut($conn) {
    // Parse JSON or form data
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        $data = $_POST;
    }
    
    if (empty($data['id'])) {
        sendError('Project ID is required');
    }
    
    $id = intval($data['id']);
    
    // Check if project exists
    $checkQuery = "SELECT id, image_path FROM projects WHERE id = :id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        sendError('Project not found', 404);
    }
    
    $existingProject = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // Build update query dynamically
    $updates = [];
    $params = ['id' => $id];
    
    $allowed_fields = ['title', 'description', 'tech_stack', 'live_url', 'github_url', 'display_order', 'is_featured', 'is_active'];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = :$field";
            
            if ($field === 'tech_stack') {
                // Handle tech_stack JSON
                $tech_stack = $data[$field];
                if (is_string($tech_stack)) {
                    $tech_stack_array = json_decode($tech_stack, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $tech_stack_array = array_map('trim', explode(',', $tech_stack));
                    }
                } else {
                    $tech_stack_array = $tech_stack;
                }
                $params[$field] = json_encode($tech_stack_array);
            } elseif (in_array($field, ['display_order', 'is_featured', 'is_active'])) {
                $params[$field] = intval($data[$field]);
            } else {
                $params[$field] = !empty($data[$field]) ? sanitizeInput($data[$field]) : null;
            }
        }
    }
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $new_image_path = handleImageUpload($_FILES['image']);
        if ($new_image_path) {
            // Delete old image
            if ($existingProject['image_path']) {
                deleteImage($existingProject['image_path']);
            }
            $updates[] = "image_path = :image_path";
            $params['image_path'] = $new_image_path;
        }
    }
    
    if (empty($updates)) {
        sendError('No fields to update');
    }
    
    $query = "UPDATE projects SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    
    if ($stmt->execute()) {
        sendSuccess('Project updated successfully');
    } else {
        sendError('Failed to update project');
    }
}

/**
 * Delete project
 */
function handleDelete($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        sendError('Project ID is required');
    }
    
    $id = intval($id);
    
    // Get project to delete image
    $checkQuery = "SELECT image_path FROM projects WHERE id = :id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        sendError('Project not found', 404);
    }
    
    $project = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete project
    $query = "DELETE FROM projects WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Delete image file
        if ($project['image_path']) {
            deleteImage($project['image_path']);
        }
        sendSuccess('Project deleted successfully');
    } else {
        sendError('Failed to delete project');
    }
}

/**
 * Handle image upload
 * @param array $file
 * @return string|null
 */
function handleImageUpload($file) {
    $upload_dir = '../../uploads/projects/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        return null;
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('project_') . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/projects/' . $filename;
    }
    
    return null;
}

/**
 * Delete image file
 * @param string $path
 */
function deleteImage($path) {
    $filepath = '../../' . $path;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}
