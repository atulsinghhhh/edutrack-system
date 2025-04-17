-- Add new columns if they don't exist
ALTER TABLE students
ADD COLUMN IF NOT EXISTS gender VARCHAR(10),
ADD COLUMN IF NOT EXISTS location_type VARCHAR(20),
ADD COLUMN IF NOT EXISTS date_flagged DATE,
ADD COLUMN IF NOT EXISTS risk_factors TEXT,
ADD COLUMN IF NOT EXISTS school_id INT,
ADD COLUMN IF NOT EXISTS school_name VARCHAR(100);

-- Insert sample data for gender-wise statistics
INSERT INTO students (name, gender, location_type, dropout_risk, date_flagged, risk_factors, school_id, school_name, intervention_status)
VALUES 
('John Doe', 'Male', 'Urban', 75, '2024-03-01', 'Poor attendance, Low grades', 1, 'Central High', 'In Progress'),
('Jane Smith', 'Female', 'Rural', 85, '2024-03-02', 'Family issues, Transportation', 2, 'Rural High', 'Pending'),
('Mike Johnson', 'Male', 'Urban', 65, '2024-03-03', 'Academic struggles', 1, 'Central High', 'In Progress'),
('Sarah Williams', 'Female', 'Urban', 70, '2024-03-04', 'Social issues', 1, 'Central High', 'Pending'),
('David Brown', 'Male', 'Rural', 80, '2024-03-05', 'Financial difficulties', 2, 'Rural High', 'In Progress'),
('Emily Davis', 'Female', 'Rural', 90, '2024-03-06', 'Family relocation', 2, 'Rural High', 'Pending'),
('Robert Wilson', 'Male', 'Urban', 60, '2024-03-07', 'Learning difficulties', 1, 'Central High', 'In Progress'),
('Lisa Anderson', 'Female', 'Urban', 75, '2024-03-08', 'Health issues', 1, 'Central High', 'Pending'),
('James Taylor', 'Male', 'Rural', 85, '2024-03-09', 'Work responsibilities', 2, 'Rural High', 'In Progress'),
('Maria Garcia', 'Female', 'Rural', 70, '2024-03-10', 'Language barrier', 2, 'Rural High', 'Pending');

-- Insert sample data for annual predictions
INSERT INTO students (name, gender, location_type, dropout_risk, date_flagged, risk_factors, school_id, school_name, intervention_status)
VALUES 
('Tom Wilson', 'Male', 'Urban', 75, '2023-01-15', 'Academic struggles', 1, 'Central High', 'Completed'),
('Anna Lee', 'Female', 'Rural', 80, '2023-02-20', 'Family issues', 2, 'Rural High', 'Completed'),
('Chris Evans', 'Male', 'Urban', 70, '2023-03-25', 'Social issues', 1, 'Central High', 'Completed'),
('Sophia Chen', 'Female', 'Urban', 85, '2023-04-30', 'Health issues', 1, 'Central High', 'Completed'),
('Daniel Kim', 'Male', 'Rural', 90, '2023-05-15', 'Financial difficulties', 2, 'Rural High', 'Completed'); 