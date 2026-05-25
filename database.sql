
CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE library_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff') DEFAULT 'staff',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    nationality VARCHAR(100),
    biography TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) UNIQUE,
    title VARCHAR(255) NOT NULL,
    author_id INT,
    category_id INT,
    publisher VARCHAR(150),
    publish_year YEAR,
    page_count INT,
    stock INT DEFAULT 1,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    membership_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS borrow_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    borrow_date DATE DEFAULT (CURRENT_DATE),
    return_date DATE,
    status ENUM('borrowed','returned') DEFAULT 'borrowed',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

INSERT INTO users (full_name, email, password, role) VALUES
('Admin User', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT INTO categories (name, description) VALUES
('Novel', 'Fiction and literary novels'),
('Science', 'Scientific research and popular science'),
('History', 'Historical events and biographies'),
('Technology', 'Software, hardware and the digital world'),
('Philosophy', 'Thought and philosophy works');

INSERT INTO authors (full_name, nationality) VALUES
('George Orwell', 'British'),
('Franz Kafka', 'Czech'),
('Yuval Noah Harari', 'Israeli'),
('Albert Camus', 'French'),
('J.R.R. Tolkien', 'British');

INSERT INTO books (isbn, title, author_id, category_id, publisher, publish_year, page_count, stock) VALUES
('9780451524935', '1984', 1, 1, 'Signet Classic', 1949, 328, 3),
('9780805209990', 'Animal Farm', 1, 1, 'Harcourt', 1945, 112, 4),
('9780805210576', 'The Trial', 2, 1, 'Schocken', 1925, 255, 2),
('9780062316110', 'Sapiens', 3, 2, 'Harper', 2011, 512, 5),
('9780679720201', 'The Stranger', 4, 1, 'Vintage', 1942, 123, 3);

INSERT INTO members (full_name, email, phone, membership_date) VALUES
('John Smith', 'john@example.com', '555-111-2233', '2024-01-15'),
('Emily Davis', 'emily@example.com', '555-222-3344', '2024-02-20'),
('Michael Brown', 'michael@example.com', '555-333-4455', '2024-03-10');
