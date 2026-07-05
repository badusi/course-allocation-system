-- Create database
CREATE DATABASE IF NOT EXISTS course_allocation_system;
USE course_allocation_system;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Lecturers table
CREATE TABLE IF NOT EXISTS lecturers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lecturer_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(100),
    phone VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    credits INT NOT NULL DEFAULT 3,
    lecturer_id INT,
    schedule VARCHAR(100),
    location VARCHAR(100),
    max_students INT DEFAULT 30,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE SET NULL
);

-- Student-Course enrollment table
CREATE TABLE IF NOT EXISTS student_courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('enrolled', 'dropped', 'completed') DEFAULT 'enrolled',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
);

-- Grades table
CREATE TABLE IF NOT EXISTS grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    grade DECIMAL(5,2) NOT NULL,
    grade_type ENUM('midterm', 'final', 'assignment', 'quiz') DEFAULT 'final',
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Course requests table
CREATE TABLE IF NOT EXISTS course_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT,
    request_type ENUM('enrollment', 'drop', 'schedule_change', 'grade_review', 'other') NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

-- Announcements table
CREATE TABLE IF NOT EXISTS announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    target_audience ENUM('all', 'students', 'lecturers') DEFAULT 'all',
    created_by INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES lecturers(id) ON DELETE SET NULL
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO students (student_id, name, email, password) VALUES
('STU001', 'John Smith', 'john.smith@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('STU002', 'Jane Doe', 'jane.doe@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('STU003', 'Mike Johnson', 'mike.johnson@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO lecturers (lecturer_id, name, email, password, department) VALUES
('LEC001', 'Dr. Sarah Johnson', 'sarah.johnson@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Computer Science'),
('LEC002', 'Prof. Michael Brown', 'michael.brown@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mathematics'),
('LEC003', 'Dr. Emily Davis', 'emily.davis@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Physics');

INSERT INTO courses (code, name, description, credits, lecturer_id, schedule, location) VALUES
('CS101', 'Computer Science 101', 'Introduction to Computer Science', 3, 1, 'Mon, Wed, Fri 10:00 AM', 'Room 101'),
('MATH201', 'Mathematics 201', 'Advanced Mathematics', 4, 2, 'Tue, Thu 2:00 PM', 'Room 201'),
('PHYS150', 'Physics 150', 'General Physics', 3, 3, 'Mon, Wed 1:00 PM', 'Lab 301'),
('CS102', 'Programming Fundamentals', 'Basic Programming Concepts', 3, 1, 'Tue, Thu 10:00 AM', 'Room 102'),
('MATH101', 'Calculus I', 'Introduction to Calculus', 4, 2, 'Mon, Wed, Fri 9:00 AM', 'Room 202');

INSERT INTO admin_users (username, name, email, password) VALUES
('admin', 'System Administrator', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO announcements (title, content, target_audience, created_by) VALUES
('Course Registration Deadline', 'Registration for next semester closes on March 15th', 'students', 1),
('Midterm Exam Schedule', 'Check your course pages for updated exam schedules', 'students', 1),
('Library Hours Extended', 'Library will be open 24/7 during finals week', 'all', 1);

-- Sample enrollments
INSERT INTO student_courses (student_id, course_id) VALUES
(1, 1), (1, 2), (1, 3),
(2, 1), (2, 4),
(3, 2), (3, 3), (3, 5);

-- Sample grades
INSERT INTO grades (student_id, course_id, grade) VALUES
(1, 1, 85.5), (1, 2, 92.0),
(2, 1, 78.5),
(3, 2, 88.0), (3, 3, 91.5);
