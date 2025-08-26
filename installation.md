## Localhost Installation Guide for the School Management Website

This document outlines the steps required to set up the School Management Website on a local development environment. This guide assumes you have a local web server environment (e.g., XAMPP, WAMP, MAMP, or a custom Apache/Nginx + PHP + MySQL installation) already installed on your system.

---

### **Prerequisites**

*   **Local Web Server:** A web server stack with Apache (or Nginx), PHP (version 7.4 or higher recommended), and MySQL (or MariaDB).
    *   **Recommended:** XAMPP (Windows, macOS, Linux), WAMPserver (Windows), MAMP (macOS, Windows), or Laragon (Windows).
*   **Web Browser:** A modern web browser (Chrome, Firefox, Edge, Safari).
*   **Text Editor:** A code editor (VS Code, Sublime Text, Notepad++).

---

### **Installation Steps**

#### **Step 1: Prepare Your Web Server Environment**

1.  **Start Web Server and Database:**
    *   If you are using XAMPP, WAMP, or MAMP, open its control panel and ensure that **Apache** (or your web server) and **MySQL** (or MariaDB) services are running.

#### **Step 2: Place the Project Files**

1.  **Locate Your Web Server's Document Root:**
    This is the directory where your web server serves files from. Common locations include:
    *   **XAMPP:** `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (macOS)
    *   **WAMPserver:** `C:\wamp\www\`
    *   **MAMP:** `/Applications/MAMP/htdocs/`
    *   *Note: Your specific path may vary based on your installation.*
2.  **Copy Project Directory:**
    *   Copy the entire `school` project directory (the one containing `config`, `db`, `public`, `src`, `templates` folders) into your web server's document root.
    *   **Example:** If your document root is `htdocs`, the path to your project should become `htdocs/school/`.
    *   **Access URL:** After copying, your project will typically be accessible via a URL like `http://localhost/school/` (or `http://localhost:8080/school/` if your web server uses a custom port).

#### **Step 3: Create and Populate the Database**

1.  **Access phpMyAdmin (or your preferred database management tool):**
    *   Open your web browser and navigate to `http://localhost/phpmyadmin` (or the URL provided by your local server stack for database management).
2.  **Create a New Database:**
    *   In phpMyAdmin, click on the "New" button or the "Databases" tab in the left sidebar.
    *   Enter a name for your new database (e.g., `school_db`).
    *   For "Collation", it is recommended to select `utf8mb4_general_ci` for broad character support.
    *   Click the "Create" button.
3.  **Import the Database Schema:**
    *   From the left sidebar in phpMyAdmin, select the newly created database (`school_db`).
    *   Click on the "Import" tab in the top navigation bar.
    *   Click the "Choose File" button and browse to your project directory: `[your_web_root]/school/db/schema.sql`.
    *   Click the "Go" button at the bottom of the page to start the import process. This will create all the necessary tables and initial data for the application.

#### **Step 4: Configure the Application**

1.  **Open Configuration File:**
    *   Using your text editor, open the file `[your_web_root]/school/config/config.php`.
2.  **Update Database Connection Details:**
    *   Locate the section for database credentials and update them to match your local MySQL/MariaDB setup:
        ```php
        <?php
        // Database credentials
        define('DB_HOST', 'localhost'); // Usually 'localhost'
        define('DB_USER', 'root');     // Your database username (e.g., 'root' for XAMPP/WAMP default)
        define('DB_PASS', '');         // Your database password (e.g., '' for XAMPP/WAMP default, no password)
        define('DB_NAME', 'school_db'); // The database name you created in Step 3

        // Base URL of the application
        define('BASE_URL', 'http://localhost/school/'); // IMPORTANT: Adjust this to your project's URL
        // ... other configurations
        ?>
        ```
    *   **`DB_HOST`**: Typically `'localhost'`.
    *   **`DB_USER`**: The username for your database. For default XAMPP/WAMP installations, this is often `'root'`.
    *   **`DB_PASS`**: The password for your database user. For default XAMPP/WAMP installations, this is often `''` (an empty string).
    *   **`DB_NAME`**: The name of the database you created in Step 3 (e.g., `'school_db'`).
3.  **Update `BASE_URL`:**
    *   Modify the `BASE_URL` constant to reflect the actual URL where your project is accessible.
    *   **Example:** If your project is in `htdocs/school/`, set it to `'http://localhost/school/'`. If you are using a custom port (e.g., MAMP's default 8888), it might be `'http://localhost:8888/school/'`.

#### **Step 5: Access the Application**

1.  **Open in Browser:**
    *   Open your web browser and navigate to the `BASE_URL` you configured in `config.php` (e.g., `http://localhost/school/`).
2.  **Login/Register:**
    *   You should now see the application's login page.
    *   **Initial Admin User:** To create an initial administrator account, you might need to directly access the registration page: `http://localhost/school/public/secure_admin_register.php`. Follow the prompts to register an admin user. The registration passcode is defined in `config.php` (e.g., `ADMIN_REGISTRATION_PASSCODE`).
    *   After successful registration, you can log in with your new admin credentials.

---

This completes the localhost installation process. You should now have a functional School Management Website running on your local machine.
