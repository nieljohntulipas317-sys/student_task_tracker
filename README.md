# Student Task Tracker

A PHP and MySQL task tracker with account registration, login, and user-specific task management.

## Features

- **Sessions:** PHP sessions keep users signed in and protect task pages.
- **Three task transactions:** Add, Update, and Delete tasks. Each operation is restricted to the signed-in user's tasks.
- Search tasks by title, subject, description, priority, or status.
- Password hashing and CSRF protection for forms.

## Requirements

- PHP 8.0 or later with PDO MySQL enabled
- MySQL or MariaDB
- Apache (for the included `.htaccess` protection)

## Local setup (XAMPP)

1. Copy this project into `C:\xampp\htdocs\Tulipas_MIDETERM`.
2. Start Apache and MySQL in XAMPP.
3. Import `modals/schema.sql` using phpMyAdmin or the MySQL command line.
4. If your local database settings differ from the defaults, copy `modals/db_config.example.php` to `modals/db_config.local.php` and enter your local database settings. The local config file is ignored by Git.
5. Open `http://localhost/Tulipas_MIDETERM/` and register an account.

## Deploying to PHP/MySQL hosting

GitHub Pages does not run PHP. Use a host that provides PHP, MySQL, and HTTPS.

1. Create a MySQL database and database user in the hosting control panel.
2. Import `modals/schema.sql` into that database. If the host does not allow SQL to create databases, remove the `CREATE DATABASE` and `USE` statements first, then import it into the database you created.
3. Copy `modals/db_config.example.php` to `modals/db_config.local.php` and fill in the database host, database name, username, and password from the hosting control panel. Do not commit or share this local config.
4. Upload the project files and folders to the domain's web root. Keep `modals/.htaccess` in place.
5. Visit the site over HTTPS, register an account, and test login plus Add, Update, and Delete.

## Database

The schema is in [`modals/schema.sql`](modals/schema.sql). Existing installations may use [`modals/migration_auth_tasks.sql`](modals/migration_auth_tasks.sql) to add account ownership to an older tasks table.
