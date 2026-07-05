first install all the necessary software's, like:
- chrome browser
- vs code
- xampp

then put the file in htdocs folder in your inside your xampp folder in your local disk. 

## ✨ Features

### Multi-Role Authentication System
- **Administrators**: Full system control, user management, course allocation
- **Lecturers**: Course management, scheduling, student enrollment
- **Students**: Course enrollment, schedule viewing, grade tracking

### Core Functionalities
- 🔐 Secure password hashing and session management
- 📊 Dynamic dashboard for each user role
- 📅 Course scheduling with time/day/room allocation
- 👥 Student enrollment and course assignment
- 📈 Academic performance tracking
- 📢 Announcement system for communications
- 🔄 Request management system

## 🛠️ Technology Stack

- **Backend**: PHP 8.2+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Security**: Password hashing, session management, prepared statements
- **Database**: PDO with parameterized queries

## 🗄️ Database Schema

The system uses the following main tables:

### Core Tables
- `users` - Admin accounts
- `lecturers` - Lecturer information and credentials
- `students` - Student information and credentials
- `courses` - Course details and specifications
- `course_allocations` - Lecturer-to-course assignments
- `schedules` - Course timetables
- `student_enrollments` - Student course registrations
- `grades` - Academic performance records

## 🚀 Installation & Setup

### 1. Prerequisites
- PHP 8.2 or higher
- MySQL/MariaDB
- Web server (Apache/Nginx)
- Composer (optional)

### 2. Database Setup
```sql
-- Import the database schema
mysql -u username -p database_name < course_allocation.sql


Create Admin Account
go to this php file "add_admin.php" and change the details inside the file and load it on the browser Or access via browser: http://localhost/course-allocation-system/add_admin.php


Note: the student has been deactivated when it is not need but it can be activate when need the file is still there but not linked to the system


Default admin credentials:

Username: admin

Email: admin@university.edu

Password: admin123


Access the System

Homepage: http://localhost/course-allocation-system/

Admin/Lecturer Login: http://localhost/course-allocation-system/login.php

Student Login: http://localhost/course-allocation-system/loginStu.php



User Roles & Permissions
Administrator
Manage all users (add/edit/delete)

Create and allocate courses

View system-wide reports

Manage announcements

Handle student requests

Lecturer
View assigned courses

Manage course schedules

Track student enrollment

Upload grades

View teaching timetable

Student
View available courses

Enroll in courses

Check personal schedule

View grades

Submit requests

🔒 Security Features
Password hashing using password_hash()

Prepared statements to prevent SQL injection

Session-based authentication

Role-based access control

Input sanitization and validation

Logout functionality with session destruction

📱 Pages Overview
Public Pages
index.php: Marketing landing page with features, testimonials, statistics

login.php: Admin and lecturer authentication portal

loginStu.php: Student-specific login portal

Protected Pages (Role-based)
admin/dashboard.php: Admin control panel

lecturer/dashboard.php: Lecturer interface

student/student-dashboard.php: Student portal


Utility Pages
logout.php: Session termination and redirect

add_admin.php: Admin account creation script


Design & UI
Modern, responsive design

Mobile-first approach

Clean, intuitive interface

Interactive elements with JavaScript

Professional color scheme and typography



Troubleshooting
Common Issues
Login Fails

Check database connection in config/database.php

Verify user exists in appropriate table

Check password hashing compatibility


Session Issues

Ensure session_start() is called on all protected pages

Check PHP session configuration


Database Errors

Verify MySQL service is running

Check database user permissions

Confirm table structure matches schema



Debug Mode
Add error reporting at the top of PHP files:
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ... rest of the code
?>


 Logging
The system maintains:

Login attempts in login_log.txt

Session tracking for security monitoring

Database transaction logs



Customization
Adding New Features
Create new PHP file in appropriate directory

Add database tables if needed

Update navigation menus

Test with different user roles



Styling Changes
Modify assets/css/style.css for global styles

Edit assets/css/landing.css for homepage

Update color variables in CSS files



Contributing
Fork the repository

Create a feature branch

Make your changes

Test thoroughly

Submit a pull request




License

This project is proprietary software. All rights reserved.




Support
For issues, questions, or feature requests:

Check the troubleshooting section

Review database logs

Contact system administrator



System Version: 1.0.0
Last Updated: December 2025
Developer: Badusi & co. Development Team
Institution: School of Science
