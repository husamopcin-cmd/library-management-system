LibraryMS - Library Management System
Web Programming Project | PHP + MySQL + Apache

========================================
SETUP (5 Steps)
========================================

1. Start XAMPP
   - Open XAMPP Control Panel
   - Click Start next to Apache
   - Click Start next to MySQL

2. Copy Project Folder
   - Copy the "kutuphane" folder into:
   C:\xampp\htdocs\kutuphane

3. Import the Database
   - Open http://localhost/phpmyadmin
   - Click "New" -> Database name: library_db -> Create
   - Click the "Import" tab
   - Choose database.sql -> Click Go

4. Open the App
   - Go to: http://localhost/kutuphane

5. Login
   - Email:    admin@library.com
   - Password: password

========================================
PROJECT STRUCTURE
========================================

kutuphane/
├── index.php               -> Dashboard (Homepage)
├── login.php               -> Login page
├── register.php            -> Registration page
├── logout.php              -> Logout
├── books.php               -> Books CRUD
├── authors.php             -> Authors CRUD
├── categories.php          -> Categories CRUD
├── members.php             -> Members CRUD
├── borrows.php             -> Borrow Records CRUD
├── database.sql            -> Database setup file
├── css/
│   └── style.css           -> All styles
├── js/
│   └── main.js             -> AJAX live search + interactions
├── includes/
│   ├── db.php              -> Database connection
│   ├── auth.php            -> Session & security functions
│   ├── header.php          -> Sidebar + page header
│   └── footer.php          -> JS imports + closing tags
└── ajax/
    ├── search_books.php    -> Live book search
    ├── search_authors.php  -> Live author search
    ├── search_members.php  -> Live member search
    └── search_borrows.php  -> Live borrow record search

========================================
TECHNOLOGIES USED
========================================

- Backend:   PHP 8.x
- Database:  MySQL (MySQLi with Prepared Statements)
- Server:    Apache (XAMPP)
- Frontend:  HTML5, CSS3, JavaScript (ES6 Fetch API)
- UI:        Bootstrap 5.3
- Security:  SQL Injection protection, XSS protection, bcrypt password hashing
