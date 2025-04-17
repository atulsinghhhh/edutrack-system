-- Insert listeners if they don't exist
INSERT INTO users (name, email, password, role, status) 
SELECT 'Sarah Johnson', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'listener', 'Active'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'sarah@example.com');

INSERT INTO users (name, email, password, role, status) 
SELECT 'Michael Brown', 'michael@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'listener', 'Active'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'michael@example.com');

-- Get the user IDs of the listeners
SET @sarah_id = (SELECT id FROM users WHERE email = 'sarah@example.com');
SET @michael_id = (SELECT id FROM users WHERE email = 'michael@example.com');

-- Insert listener records
INSERT INTO listeners (user_id, name, specialization, is_available) 
SELECT @sarah_id, 'Sarah Johnson', 'Academic Counseling', TRUE
WHERE NOT EXISTS (SELECT 1 FROM listeners WHERE user_id = @sarah_id);

INSERT INTO listeners (user_id, name, specialization, is_available) 
SELECT @michael_id, 'Michael Brown', 'Mental Health Support', TRUE
WHERE NOT EXISTS (SELECT 1 FROM listeners WHERE user_id = @michael_id); 