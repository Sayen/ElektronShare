# Elektron File Browser PWA

![Elektron App](https://via.placeholder.com/800x400?text=Elektron+File+Browser)

A modern, progressive web application (PWA) for managing files and folders, designed with the Elektron corporate identity. Built with Vue 3 and PHP.

## ✨ Features

*   **📂 File Management**: Create folders, upload files, and organize content in a hierarchical structure.
*   **📝 WYSIWYG Editor**: Rich text editing for folder descriptions using Markdown (powered by Toast UI).
*   **🚀 Progressive Web App**: Installable on devices, offline-capable (basic shell), and optimized for performance.
*   **🔔 Push Notifications**:
    *   Send notifications to all subscribers.
    *   **Deep Linking**: Direct users to specific folders from a notification.
    *   **Technical Insights**: View subscriber details (IP, User Agent).
*   **☁️ Multi-Upload**: Drag and drop support for uploading multiple files simultaneously.

## 🛠️ Tech Stack

*   **Frontend**: Vue 3, Vite, TailwindCSS, Toast UI Editor.
*   **Backend**: PHP 8.3+, MySQL.
*   **Push**: Web Push (VAPID), Service Worker.

## 🚀 Installation & Deployment

### Prerequisites
*   PHP 8.3 or higher.
*   MySQL Database.
*   Web Server (Apache/Nginx).

### Local Development

1.  **Backend Setup**:
    ```bash
    cd backend
    composer install
    # Start local PHP server
    php -S localhost:8000 -t public
    ```
2.  **Frontend Setup**:
    ```bash
    cd frontend
    npm install
    npm run dev
    ```
3.  **Database**:
    *   Navigate to `http://localhost:8000/install.php` to set up the database and create the admin account.

### Production Deployment (Hostpoint / FTP)

1.  **Build Frontend**:
    *   The backend relies on compiled frontend assets. You **must** build them locally first.
    *   Run: `cd frontend && npm install && npm run build`
    *   This generates files in `backend/public/`.

2.  **Upload Files**:
    *   Upload the entire `backend/` directory to your web server's `public_html` (or equivalent).
    *   **Important**: Ensure the `backend/vendor/` directory is included (run `composer install` locally if needed).

3.  **Permissions**:
    *   Ensure the `uploads/` directory is writable (`chmod 755` or `777`).

4.  **Database Setup**:
    *   **Fresh Install**: Go to `your-site.com/install.php`.
    *   **Update**: If updating an existing instance, upload and run `update.php` (e.g., `your-site.com/update.php`) to migrate the schema.

## 🤖 For AI Agents

See [AGENTS.md](./AGENTS.md) for detailed instructions on codebase conventions and development workflows.
