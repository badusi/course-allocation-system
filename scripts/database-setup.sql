-- Create database and tables for the course allocation system

-- Create database
CREATE DATABASE IF NOT EXISTS course_allocation_db;
USE course_allocation_db;

-- Admin table
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Lecturers table
CREATE TABLE IF NOT EXISTS lecturers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lecturer_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(100) NOT NULL,
    specialization VARCHAR(200),
    qualification VARCHAR(200),
    experience_years INT DEFAULT 0,
    max_courses INT DEFAULT 3,
    status ENUM('active', 'inactive') DEFAULT 'active',
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(200) NOT NULL,
    department VARCHAR(100) NOT NULL,
    credit_hours INT NOT NULL,
    semester VARCHAR(20) NOT NULL,
    academic_year VARCHAR(10) NOT NULL,
    course_type ENUM('core', 'elective', 'practical') DEFAULT 'core',
    prerequisites TEXT,
    description TEXT,
    max_students INT DEFAULT 50,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Course allocations table
CREATE TABLE IF NOT EXISTS course_allocations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lecturer_id INT NOT NULL,
    course_id INT NOT NULL,
    allocation_date DATE NOT NULL,
    academic_year VARCHAR(10) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    status ENUM('allocated', 'pending', 'cancelled') DEFAULT 'allocated',
    notes TEXT,
    allocated_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (allocated_by) REFERENCES admins(id),
    UNIQUE KEY unique_allocation (lecturer_id, course_id, academic_year, semester)
);

-- Insert default admin
INSERT INTO admins (username, email, password, full_name) VALUES 
('admin', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

-- Insert sample lecturers
INSERT INTO lecturers (lecturer_id, full_name, email, phone, department, specialization, qualification, experience_years, max_courses) VALUES
('LEC001', 'Dr. Sarah Johnson', 'sarah.johnson@university.edu', '+1234567890', 'Computer Science', 'Artificial Intelligence, Machine Learning', 'PhD in Computer Science', 8, 3),
('LEC002', 'Prof. Michael Chen', 'michael.chen@university.edu', '+1234567891', 'Computer Science', 'Software Engineering, Database Systems', 'PhD in Software Engineering', 12, 4),
('LEC003', 'Dr. Emily Rodriguez', 'emily.rodriguez@university.edu', '+1234567892', 'Mathematics', 'Applied Mathematics, Statistics', 'PhD in Mathematics', 6, 3),
('LEC004', 'Dr. James Wilson', 'james.wilson@university.edu', '+1234567893', 'Physics', 'Quantum Physics, Theoretical Physics', 'PhD in Physics', 10, 3),
('LEC005', 'Dr. Lisa Thompson', 'lisa.thompson@university.edu', '+1234567894', 'Chemistry', 'Organic Chemistry, Biochemistry', 'PhD in Chemistry', 7, 3);

-- Insert sample courses
INSERT INTO courses (course_code, course_name, department, credit_hours, semester, academic_year, course_type, description, max_students) VALUES
('CS101', 'Introduction to Programming', 'Computer Science', 3, 'Fall', '2024-25', 'core', 'Basic programming concepts using Python', 60),
('CS201', 'Data Structures and Algorithms', 'Computer Science', 4, 'Spring', '2024-25', 'core', 'Advanced data structures and algorithm design', 45),
('CS301', 'Database Management Systems', 'Computer Science', 3, 'Fall', '2024-25', 'core', 'Database design and SQL programming', 40),
('CS401', 'Machine Learning', 'Computer Science', 4, 'Spring', '2024-25', 'elective', 'Introduction to ML algorithms and applications', 30),
('MATH201', 'Calculus II', 'Mathematics', 4, 'Spring', '2024-25', 'core', 'Advanced calculus concepts', 50),
('PHYS101', 'General Physics I', 'Physics', 4, 'Fall', '2024-25', 'core', 'Mechanics and thermodynamics', 55),
('CHEM101', 'General Chemistry', 'Chemistry', 3, 'Fall', '2024-25', 'core', 'Basic chemistry principles', 50);



-- Add school column to courses table
ALTER TABLE courses ADD COLUMN school VARCHAR(100) DEFAULT 'School of Science';

-- Add school column to lecturers table  
ALTER TABLE lecturers ADD COLUMN school VARCHAR(100) DEFAULT 'School of Science';

-- Add school column to students table
ALTER TABLE students ADD COLUMN school VARCHAR(100) DEFAULT 'School of Science';

-- Update existing records to School of Science
UPDATE courses SET school = 'School of Science';
UPDATE lecturers SET school = 'School of Science';
UPDATE students SET school = 'School of Science';