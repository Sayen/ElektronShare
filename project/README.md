# Elektron Download Center & PWA

This is a complete Progressive Web App (PWA) for managing and downloading files, designed for shared hosting environments.

## Features

- **Public View**: Navigate folder structures, view PDF files, read Markdown descriptions. PWA installable.
- **Admin Panel**: Drag & Drop file upload, folder management, rich-text editor (Markdown) for folder descriptions.
- **Push Notifications**: Send broadcast messages to all subscribed users.

## Requirements

- PHP 8.4
- MySQL
- Apache or Nginx
- Composer (for installation of backend dependencies)

## Installation

### 1. Upload Files
Upload the contents of the `public` directory to your web server's public root (e.g., `public_html` or `www`).

### 2. Install Dependencies
Connect to your server via SSH and navigate to the directory where you uploaded the files. Run:

```bash
composer install
```

*Note: If you do not have SSH access, run `composer install` locally and upload the generated `vendor` folder to the server.*

### 3. Setup Database
Open your browser and navigate to `http://yourdomain.com/install.php`.
Follow the instructions to:
- Connect to your MySQL database.
- Create the Admin account.
- Generate VAPID keys for Push Notifications.

### 4. Finalize
- The installer will create a `config.php` file.
- **Delete `install.php`** after successful installation for security.

## Admin Access
- Login at `http://yourdomain.com/admin/login` (or click "Admin Login" in the menu).
- Use the credentials you set during installation.

## Development

### Frontend
The frontend source code is in `frontend/`.
```bash
cd frontend
npm install
npm run dev
```

### Build
To rebuild the frontend:
```bash
cd frontend
npm run build
```
This will update the files in `../public`.

## License
MIT
