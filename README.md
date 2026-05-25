# LibraryMS - Library Management System

A full-stack web application for managing library operations built with PHP, MySQL, and Bootstrap 5.

> **Educational Project** — TED University, Web Programming Course

---

## Features

- **Book Management** — Add, edit, delete, and search books with ISBN, author, category, publisher, and stock tracking
- **Author & Category Management** — Full CRUD for authors (with nationality/biography) and categories
- **Member Management** — Library member registration with active/inactive status
- **Borrow Records** — Track book loans and returns with date management
- **Live Search** — Real-time AJAX search on all management pages (300ms debounce)
- **User Authentication** — Session-based login system with role support (admin/staff)
- **Security** — SQL injection protection via prepared statements, XSS prevention via `htmlspecialchars`, bcrypt password hashing
- **Responsive UI** — Bootstrap 5.3 sidebar layout, works on desktop and mobile

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.x |
| Database | MySQL (MySQLi with Prepared Statements) |
| Server | Apache (XAMPP) |
| Frontend | HTML5, CSS3, JavaScript (ES6 Fetch API) |
| UI Framework | Bootstrap 5.3 |
| Security | bcrypt, prepared statements, XSS escaping |

## Project Structure

```
kutuphane/
├── index.php               # Dashboard (stats overview)
├── login.php               # Login page
├── register.php            # Staff registration
├── logout.php              # Session termination
├── books.php               # Books CRUD
├── authors.php             # Authors CRUD
├── categories.php          # Categories CRUD
├── members.php             # Members CRUD
├── borrows.php             # Borrow Records CRUD
├── database.sql            # Database schema + seed data
├── css/
│   └── style.css           # Custom styles
├── js/
│   └── main.js             # AJAX live search + interactions
├── includes/
│   ├── db.php              # Database connection
│   ├── auth.php            # Session & security helpers
│   ├── header.php          # Sidebar navigation
│   └── footer.php          # JS imports + closing tags
└── ajax/
    ├── search_books.php    # Live book search endpoint
    ├── search_authors.php  # Live author search endpoint
    ├── search_members.php  # Live member search endpoint
    └── search_borrows.php  # Live borrow record search endpoint
```

## Database Schema

```
users           — system users (admin/staff)
categories      — book categories
authors         — author records
books           — book inventory with stock
members         — library members
borrow_records  — loan history (borrowed/returned)
```

## Local Setup

**Prerequisites:** XAMPP (Apache + MySQL)

**1. Start XAMPP**
Open XAMPP Control Panel and start Apache and MySQL.

**2. Copy project**
```
C:\xampp\htdocs\kutuphane\
```

**3. Import the database**
- Go to `http://localhost/phpmyadmin`
- Create a new database: `library_db`
- Import `database.sql`

**4. Open the app**
```
http://localhost/kutuphane
```

**5. Login with the default admin account**
```
Email:    admin@library.com
Password: password
```

> Change the default password after first login.

## Security Implementation

- **SQL Injection** — All queries use MySQLi prepared statements with bound parameters
- **XSS Prevention** — All output is escaped with `htmlspecialchars()` and `strip_tags()`
- **Password Hashing** — Passwords stored with PHP `password_hash()` (bcrypt, cost 10)
- **Session Security** — Session-based authentication with `requireLogin()` guard on every protected page
- **Input Sanitization** — Custom `clean()` helper trims and escapes all user input

## Screenshots

> Dashboard, Books management, and Live Search in action.

*(Add screenshots here)*

## License

MIT License — free to use for educational purposes.

---

*Developed as part of the Web Programming course at TED University.*
