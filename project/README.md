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

## Installation

### 1. Upload Files
Upload the contents of the `public` directory to your web server's public root (e.g., `public_html` or `www`).
**Important:** Ensure the `vendor` folder is included in your upload. It contains the necessary backend libraries.

### 2. Setup Database
Open your browser and navigate to `http://yourdomain.com/install.php`.
Follow the instructions to:
- Connect to your MySQL database.
- Create the Admin account.
- Generate VAPID keys for Push Notifications.

### 3. Finalize
- The installer will create a `config.php` file.
- **Delete `install.php`** after successful installation for security.

## Admin Access
- Login at `http://yourdomain.com/admin/login` (or click "Admin Login" in the menu).
- Use the credentials you set during installation.

## Development (Optional)

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
