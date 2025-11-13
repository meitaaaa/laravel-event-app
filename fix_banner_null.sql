-- Fix banner fields that contain string "null" instead of actual NULL
-- Run this in phpMyAdmin or MySQL client

UPDATE banners 
SET title = NULL 
WHERE title = 'null' OR title = '' OR TRIM(title) = '';

UPDATE banners 
SET description = NULL 
WHERE description = 'null' OR description = '' OR TRIM(description) = '';

UPDATE banners 
SET button_text = NULL 
WHERE button_text = 'null' OR button_text = '' OR TRIM(button_text) = '';

UPDATE banners 
SET button_link = NULL 
WHERE button_link = 'null' OR button_link = '' OR TRIM(button_link) = '';

-- Verify the fix
SELECT id, title, description, button_text, is_active, `order` FROM banners;
