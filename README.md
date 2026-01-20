# Elektron Datei Browser & Push App

Eine PWA (Progressive Web App) zur Verwaltung und Anzeige von Dateien mit Push-Benachrichtigungs-Funktion, im Design von elektron.ch.

## Anforderungen

*   PHP 8.3 oder 8.4
*   MySQL Datenbank
*   HTTPS (Zwingend erforderlich für Service Worker / PWA und Push Notifications)

## Installation

1.  Laden Sie den Inhalt des `backend/` Ordners auf Ihren Webserver hoch.
2.  Laden Sie den Inhalt des `vendor/` Ordners hoch (Führen Sie `composer install` lokal aus, wenn dieser fehlt).
    *   *Hinweis*: Wenn Sie keinen SSH Zugriff haben, installieren Sie `minishlink/web-push` lokal und laden den `vendor` Ordner mit hoch.
3.  Stellen Sie sicher, dass der Ordner `uploads/` existiert und beschreibbar ist (CHMOD 755 oder 777).
4.  Rufen Sie im Browser `https://ihre-domain.ch/install.php` auf.
5.  Füllen Sie die Datenbank-Zugangsdaten und das gewünschte Admin-Passwort aus.
6.  Nach erfolgreicher Installation werden Sie zur App weitergeleitet.
7.  **Wichtig**: Löschen Sie `install.php` nach der Installation aus Sicherheitsgründen.

## Funktionen

### Besucher
*   Durchsuchen von Ordnern und Dateien.
*   Anzeige von Ordner-Beschreibungen (Markdown).
*   Installation als App (PWA) auf dem Smartphone/Desktop.
*   Abonnieren von Push-Benachrichtigungen (via Burger Menu oder beim ersten Start).

### Admin
*   Login unter `/login` oder via Menu -> "Admin Bereich".
*   **Dateimanager**: Ordner erstellen, Dateien hochladen (Drag & Drop), Beschreibungen bearbeiten.
*   **Push Nachrichten**: Senden von Nachrichten an alle registrierten Geräte.
*   **Einstellungen**: Admin Passwort ändern.

## Entwicklung

### Frontend (Vue.js)
Das Frontend befindet sich im `frontend/` Ordner.
```bash
cd frontend
npm install
npm run dev
```

Zum Kompilieren:
```bash
npm run build
```
Die Dateien werden automatisch nach `backend/public/` exportiert.

### Backend (PHP)
Die API Endpoints befinden sich in `backend/api/`.
Datenbank-Verbindung via `backend/db.php` und `config.php`.

## Lizenz

MIT License

Copyright (c) 2024
