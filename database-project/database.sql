-- ============================================
-- Portfolio Admin Dashboard Database Schema
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

-- ============================================
-- Admin Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user
-- Username: admin
-- Password: Admin@123 (CHANGE THIS AFTER FIRST LOGIN!)
INSERT INTO admin_users (username, email, password) VALUES 
('admin', 'admin@amindev.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================
-- Skills Table
-- ============================================
CREATE TABLE IF NOT EXISTS skills (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    level INT(11) NOT NULL CHECK (level >= 0 AND level <= 100),
    category VARCHAR(50) NOT NULL,
    icon_type VARCHAR(50) DEFAULT 'text',
    icon_value TEXT,
    color VARCHAR(7) DEFAULT '#3b82f6',
    display_order INT(11) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Skills Data
INSERT INTO skills (name, level, category, icon_type, icon_value, display_order) VALUES
('JavaScript', 95, 'Frontend', 'text', 'JS', 1),
('PHP', 95, 'Backend', 'text', 'PHP', 2),
('Laravel', 90, 'Backend', 'text', 'L', 3),
('Vue.js', 88, 'Frontend', 'text', 'V', 4),
('MySQL', 92, 'Database', 'text', 'SQL', 5),
('Three.js', 85, 'Frontend', 'text', '3D', 6),
('Docker', 85, 'DevOps', 'emoji', '🐳', 7),
('Git', 90, 'DevOps', 'text', 'G', 8);

-- ============================================
-- Projects Table
-- ============================================
CREATE TABLE IF NOT EXISTS projects (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    tech_stack JSON NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    live_url VARCHAR(255) DEFAULT NULL,
    github_url VARCHAR(255) DEFAULT NULL,
    display_order INT(11) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Projects Data
INSERT INTO projects (title, description, tech_stack, image_path, display_order, is_featured) VALUES
('E-Commerce Platform', 'Full-stack e-commerce solution with real-time inventory, payment processing, and admin dashboard.', 
 '["Laravel", "Vue.js", "MySQL"]', NULL, 1, 1),
('Healthcare Portal', 'Patient management system with appointment scheduling, medical records, and telemedicine features.', 
 '["Laravel", "JavaScript", "PostgreSQL"]', NULL, 2, 1),
('Real-Time Chat App', 'WebSocket-based messaging platform with end-to-end encryption and file sharing capabilities.', 
 '["Node.js", "Socket.io", "MongoDB"]', NULL, 3, 0);

-- ============================================
-- Contact Messages Table (Optional)
-- ============================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Site Analytics Table (Optional)
-- ============================================
CREATE TABLE IF NOT EXISTS site_analytics (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    page_views INT(11) DEFAULT 0,
    unique_visitors INT(11) DEFAULT 0,
    total_skills INT(11) DEFAULT 0,
    total_projects INT(11) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initialize analytics
INSERT INTO site_analytics (page_views, unique_visitors, total_skills, total_projects) 
VALUES (0, 0, 0, 0);

-- ============================================
-- Create Indexes for Performance
-- ============================================
CREATE INDEX idx_skills_category ON skills(category);
CREATE INDEX idx_skills_active ON skills(is_active);
CREATE INDEX idx_projects_featured ON projects(is_featured);
CREATE INDEX idx_projects_active ON projects(is_active);
CREATE INDEX idx_contact_read ON contact_messages(is_read);

-- ============================================
-- Views for Dashboard Analytics
-- ============================================
CREATE OR REPLACE VIEW dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM skills WHERE is_active = 1) as total_skills,
    (SELECT COUNT(*) FROM projects WHERE is_active = 1) as total_projects,
    (SELECT COUNT(*) FROM contact_messages WHERE is_read = 0) as unread_messages,
    (SELECT AVG(level) FROM skills WHERE is_active = 1) as avg_skill_level;

-- ============================================
-- Success Message
-- ============================================
SELECT 'Database schema created successfully!' as Status;
SELECT 'Default Admin Credentials:' as Info;
SELECT 'Username: admin' as Username;
SELECT 'Password: Admin@123' as Password;
SELECT 'IMPORTANT: Change the password after first login!' as Warning;
