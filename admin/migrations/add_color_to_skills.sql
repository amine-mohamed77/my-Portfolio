-- ============================================
-- Migration: Add color column to skills table
-- Date: 2025-12-05
-- ============================================

USE portfolio_db;

-- Add color column if it doesn't exist
ALTER TABLE skills 
ADD COLUMN IF NOT EXISTS color VARCHAR(7) DEFAULT '#3b82f6' AFTER icon_value;

-- Update existing skills with default colors based on category
UPDATE skills SET color = '#3b82f6' WHERE category = 'Frontend' AND color IS NULL;
UPDATE skills SET color = '#8b5cf6' WHERE category = 'Backend' AND color IS NULL;
UPDATE skills SET color = '#10b981' WHERE category = 'Database' AND color IS NULL;
UPDATE skills SET color = '#f97316' WHERE category = 'DevOps' AND color IS NULL;
UPDATE skills SET color = '#ec4899' WHERE category = 'Other' AND color IS NULL;

SELECT 'Color column added successfully!' as Status;
