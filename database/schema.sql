-- Course Allocation System Database Schema
CREATE DATABASE IF NOT EXISTS course_allocation;
USE course_allocation;

-- Users table (for both admins and lecturers)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'lecturer') NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(200) NOT NULL,
    description TEXT,
    credits INT NOT NULL DEFAULT 3,
    department VARCHAR(100) NOT NULL,
    lecturer_id INT NOT NULL;
    semester VARCHAR(20) NOT NULL,
    year INT NOT NULL,
    max_students INT DEFAULT 50,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(100) NOT NULL,
    year_of_study INT NOT NULL,
    gpa DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Course allocations (lecturer assignments)
CREATE TABLE course_allocations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    lecturer_id INT NOT NULL,
    allocated_date DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_course_lecturer (course_id, lecturer_id)
);


CREATE TABLE student_course_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    allocated_date DATE,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);



-- Student enrollments
CREATE TABLE student_enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    grade VARCHAR(5),
    status ENUM('enrolled', 'completed', 'dropped') DEFAULT 'enrolled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_course (student_id, course_id)
);

-- Schedule table
CREATE TABLE schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lecturer_id INT NOT NULL,
    course_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);



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
    lecturer_id VARCHAR(20) NOT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
     phone VARCHAR(20),
    department VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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



-- Insert sample data
INSERT INTO users (username, email, password, role, first_name, last_name, department) VALUES
('admin', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator', 'IT'),
('john.doe', 'john.doe@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer', 'John', 'Doe', 'Computer Science'),
('jane.smith', 'jane.smith@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer', 'Jane', 'Smith', 'Mathematics'),
('bob.wilson', 'bob.wilson@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer', 'Bob', 'Wilson', 'Physics');

INSERT INTO courses (course_code, course_name, description, credits, department, semester, year, max_students) VALUES
('CS101', 'Introduction to Programming', 'Basic programming concepts using Python', 3, 'Computer Science', 'Fall', 2024, 40),
('CS201', 'Data Structures', 'Advanced data structures and algorithms', 4, 'Computer Science', 'Spring', 2024, 35),
('MATH101', 'Calculus I', 'Differential and integral calculus', 4, 'Mathematics', 'Fall', 2024, 50),
('MATH201', 'Linear Algebra', 'Vector spaces and linear transformations', 3, 'Mathematics', 'Spring', 2024, 30),
('PHYS101', 'General Physics I', 'Mechanics and thermodynamics', 4, 'Physics', 'Fall', 2024, 45);

INSERT INTO students (student_id, first_name, last_name, email, department, year_of_study, gpa) VALUES
('S001', 'Alice', 'Johnson', 'alice.johnson@student.edu', 'Computer Science', 2, 3.75),
('S002', 'Charlie', 'Brown', 'charlie.brown@student.edu', 'Computer Science', 1, 3.50),
('S003', 'Diana', 'Davis', 'diana.davis@student.edu', 'Mathematics', 3, 3.90),
('S004', 'Edward', 'Miller', 'edward.miller@student.edu', 'Physics', 2, 3.25),
('S005', 'Fiona', 'Garcia', 'fiona.garcia@student.edu', 'Computer Science', 4, 3.85);

INSERT INTO course_allocations (course_id, lecturer_id, allocated_date, status) VALUES
(1, 2, '2024-01-15', 'confirmed'),
(2, 2, '2024-01-15', 'confirmed'),
(3, 3, '2024-01-15', 'confirmed'),
(4, 3, '2024-01-15', 'pending'),
(5, 4, '2024-01-15', 'confirmed');

INSERT INTO student_enrollments (student_id, course_id, enrollment_date, status) VALUES
(1, 1, '2024-01-20', 'enrolled'),
(2, 1, '2024-01-20', 'enrolled'),
(1, 3, '2024-01-20', 'enrolled'),
(3, 3, '2024-01-20', 'enrolled'),
(3, 4, '2024-01-20', 'enrolled'),
(4, 5, '2024-01-20', 'enrolled'),
(5, 2, '2024-01-20', 'enrolled');

INSERT INTO schedules (course_id, day_of_week, start_time, end_time, room) VALUES
(1, 'Monday', '09:00:00', '10:30:00', 'CS-101'),
(1, 'Wednesday', '09:00:00', '10:30:00', 'CS-101'),
(2, 'Tuesday', '14:00:00', '15:30:00', 'CS-201'),
(2, 'Thursday', '14:00:00', '15:30:00', 'CS-201'),
(3, 'Monday', '11:00:00', '12:30:00', 'MATH-101'),
(3, 'Friday', '11:00:00', '12:30:00', 'MATH-101'),
(4, 'Tuesday', '10:00:00', '11:30:00', 'MATH-201'),
(5, 'Wednesday', '15:00:00', '16:30:00', 'PHYS-101');



ALTER TABLE courses ADD lecturer_id INT;


ALTER TABLE courses ADD CONSTRAINT fk_lecturer
FOREIGN KEY (lecturer_id) REFERENCES lecturers(id)
ON DELETE SET NULL;



ALTER TABLE course_allocations 
DROP FOREIGN KEY course_allocations_ibfk_2;

ALTER TABLE course_allocations 
ADD CONSTRAINT course_allocations_ibfk_2 
FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE CASCADE;
