-- Insert sample schools
INSERT INTO schools (school_name) VALUES 
('Central High School'),
('Westside Academy'),
('East Valley High'),
('Northside Prep'),
('South High School');

-- Insert sample students
INSERT INTO students (student_name, school_id) VALUES 
('John Smith', 1),
('Maria Garcia', 2),
('David Chen', 3),
('Sarah Johnson', 4),
('Michael Brown', 5);

-- Insert sample actions
INSERT INTO actions (action_name) VALUES 
('Counseling'),
('Parent Meeting'),
('Academic Support'),
('Mentorship'),
('Attendance Monitoring'),
('Emergency Counseling');

-- Insert sample interventions
INSERT INTO interventions (student_id, risk_level, date_flagged, status) VALUES 
(1, 85, DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY), 'urgent'),
(2, 75, DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY), 'urgent'),
(3, 65, DATE_SUB(CURRENT_DATE, INTERVAL 8 DAY), 'urgent'),
(4, 90, DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY), 'urgent'),
(5, 70, DATE_SUB(CURRENT_DATE, INTERVAL 4 DAY), 'urgent');

-- Insert intervention actions
INSERT INTO intervention_actions (intervention_id, action_id) VALUES 
(1, 1), (1, 2), (1, 3),  -- John Smith: Counseling, Parent Meeting, Academic Support
(2, 3), (2, 4),          -- Maria Garcia: Academic Support, Mentorship
(3, 2), (3, 5),          -- David Chen: Parent Meeting, Attendance Monitoring
(4, 6), (4, 2), (4, 3),  -- Sarah Johnson: Emergency Counseling, Parent Meeting, Academic Support
(5, 3), (5, 5);          -- Michael Brown: Academic Support, Attendance Monitoring 