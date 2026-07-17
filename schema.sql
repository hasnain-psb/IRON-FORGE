-- Online Gym Membership Management System
-- Database Schema

CREATE DATABASE IF NOT EXISTS gym_db;
USE gym_db;

CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    plan VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: Insert initial sample data
INSERT INTO members (name, email, phone, plan, status) VALUES
('John Doe', 'john.doe@example.com', '123-456-7890', 'Premium', 'Active'),
('Jane Smith', 'jane.smith@example.com', '987-654-3210', 'Standard', 'Active'),
('Bob Johnson', 'bob.johnson@example.com', '555-555-5555', 'Basic', 'Inactive');
