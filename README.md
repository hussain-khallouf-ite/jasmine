# Al-Yasmin Apartment Booking System

Welcome to the Al-Yasmin Apartment Booking System codebase, built natively in PHP to support Syria's post-conflict reconstruction in the Wadi al-Jawz neighborhood of Hama City.

## Tech Stack
- Frontend: HTML5, CSS3, Bootstrap 5, Vanilla JS
- Backend: PHP 8.x (No Framework, MVC Pattern)
- Database: MySQL 8.x

## Project Structure
```text
/al-yasmin/
  ├── /public/           ← Web root (Static HTML, CSS, JS, Images)
  │   ├── /api/           ← JSON API endpoints for backend communication
  │   ├── index.html      ← Main landing page
  │   ├── properties.html ← Properties listing page
  │   ├── login.html      ← Login page
  │   ├── register.html   ← Register page
  │   └── profile.html    ← Profile page
  ├── /src/              ← Backend application logic
  │   ├── /controllers/  ← PHP request handlers
  │   ├── /models/       ← Database query classes
  │   └── /helpers/      ← Utility functions (auth, validation)
  ├── /config/           ← DB connection and environment constants
  ├── /admin/            ← Admin panel dashboard
  ├── /migrations/       ← SQL schema version and seed files
  └── /docs/             ← SRS, ERD, and related documents
```

## How to Run Locally with XAMPP

1. **Install XAMPP**
   Download and install [XAMPP](https://www.apachefriends.org/). Ensure it is running PHP 8.x or above.

2. **Clone the Repository**
   Move the project folder into your XAMPP `htdocs` directory.
   - For Windows: `C:\xampp\htdocs\jasmine`
   - For Mac: `/Applications/XAMPP/xamppfiles/htdocs/jasmine`

3. **Start Core Services**
   Open the XAMPP Control Panel and Start both **Apache** and **MySQL**.

4. **Set Up the Database**
   - Open your browser and navigate to `http://localhost/phpmyadmin`
   - Create a new, blank database named `alyasmin_db` (use utf8mb4_unicode_ci collation).
   - In phpMyAdmin, click on the new `alyasmin_db` database, then go to the "Import" tab.
   - Upload and import `migrations/001_create_tables.sql`
   - Upload and import `migrations/002_seed_data.sql`

5. **Credentials**
   By default, `config/database.php` assumes `root` user and an empty password to match typical XAMPP configurations.
   If you set a MySQL password for root, update `config/database.php`.

6. **View the Project**
   - Open your web browser and visit: `http://localhost/jasmine/public/`

## Default Users (Passwords = `password123`)
- **Admin**: `admin@alyasmin.sy`
- **Customer**: `ahmad@example.com`
