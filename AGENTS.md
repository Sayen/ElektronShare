# Instructions for AI Agents

Welcome, Agent. This document contains critical context and guidelines for working on the Elektron File Browser PWA.

## 🏗️ Architecture Overview

*   **Frontend**: Vue 3 (Vite), TailwindCSS. Located in `frontend/`.
*   **Backend**: PHP 8.3/8.4 (Native, no framework). Located in `backend/`.
*   **Database**: MySQL.
*   **PWA**: Implemented using `vite-plugin-pwa` with `injectManifest` strategy.

## 🚀 Deployment Environment (CRITICAL)

The application runs on **Hostpoint Shared Hosting**.
*   **NO SSH Access**: You cannot run commands on the production server.
*   **NO Composer**: `composer install` cannot be run on the server. The `backend/vendor/` directory must be committed or uploaded manually via FTP.
*   **Build Artifacts**: The frontend **must** be built locally (`npm run build`). The build output is targeted to `backend/public/`.
*   **File Permissions**: The `backend/uploads/` folder must be writable.

## 🛠️ Development Guidelines

### 1. Build Process
**ALWAYS** compile the frontend after making changes to `frontend/`.
```bash
cd frontend
npm install
npm run build
```
This updates the assets in `backend/public/`, which are the actual files served by the backend. **You must commit these generated files.**

### 2. "Edit Source, Not Artifacts"
*   Never edit files in `backend/public/` (except `.htaccess` or static images that are not part of the build).
*   Always edit the source in `frontend/src/` and rebuild.

### 3. Language & Design
*   **Language**: The user interface (both Admin and Visitor views) must be in **German**.
*   **Design**: Follow the "Elektron" aesthetic.
    *   Primary Color: `#517c9f` (Elektron Blue).
    *   Use TailwindCSS for styling.

### 4. Database Changes
*   **New Installations**: Update `backend/install.php`.
*   **Existing Installations**: Create or update `backend/update.php` with idempotent SQL commands (check if column exists before adding).

### 5. Features
*   **WYSIWYG**: Use `@toast-ui/editor`.
*   **Push Notifications**:
    *   Backend: `minishlink/web-push`.
    *   Frontend: Custom Service Worker (`frontend/src/sw.js`).
    *   Support deep linking via `/?folder={id}`.

## 📂 Directory Structure

*   `backend/` - PHP API and Public Root
    *   `api/` - JSON API endpoints.
    *   `public/` - **Web Root**. Contains `index.html` (Vue entry) and assets.
    *   `uploads/` - Storage for user files.
    *   `vendor/` - Composer dependencies.
*   `frontend/` - Vue 3 Source
    *   `src/` - Vue components and logic.
    *   `vite.config.js` - Build configuration (proxies API to localhost:8000).

## 🧪 Testing
Since the environment is PHP/MySQL, use the provided tools to run a local PHP server for verification if needed:
`cd backend && php -S localhost:8000 -t public`
