-- Name: Srijan Shandilya | Roll Number: 20231BCA0248

-- Create the database only if it does not already exist; utf8mb4 supports all characters
CREATE DATABASE IF NOT EXISTS student_management
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

-- Select the database so the following statements apply to it
USE student_management;

-- Table 1: users (people who can log in to the system)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- unique id, MySQL fills it automatically
    username VARCHAR(50) NOT NULL UNIQUE,       -- login name, no two users can share it
    password VARCHAR(255) NOT NULL              -- stores the HASH of the password, never the plain text
) ENGINE=InnoDB;                                -- InnoDB supports keys and safe transactions

-- Table 2: students (the records we manage with CRUD)
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- unique id for each row
    register_number VARCHAR(20) NOT NULL UNIQUE,-- university register number, must be unique
    student_name VARCHAR(100) NOT NULL,         -- full name of the student
    course VARCHAR(100) NOT NULL,               -- e.g. BCA
    semester TINYINT UNSIGNED NOT NULL,         -- small whole number, e.g. 5
    email VARCHAR(100) NOT NULL,                -- email address
    contact_number VARCHAR(10) NOT NULL         -- 10-digit phone number stored as text
) ENGINE=InnoDB;