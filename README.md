# Elektron Datei Browser & Push App

Eine PWA (Progressive Web App) zur Verwaltung und Anzeige von Dateien mit Push-Benachrichtigungs-Funktion, im Design von elektron.ch.

## Anforderungen

*   PHP 8.3 oder 8.4
*   MySQL Datenbank
*   HTTPS (Zwingend erforderlich für Service Worker / PWA und Push Notifications)

## Vorbereitung (Wichtig!)

Da auf dem Hostpoint Server kein Composer (SSH) verfügbar ist, müssen Sie die Abhängigkeiten **lokal auf Ihrem Computer** vorbereiten:

1.  Installieren Sie [Composer](https://getcomposer.org/) auf Ihrem Computer.
2.  Laden Sie den Code herunter.
3.  Öffnen Sie ein Terminal / Kommandozeile im Ordner `backend/`.
4.  Führen Sie folgenden Befehl aus:
    ```bash
    composer install
    ```
5.  Dies erstellt einen Ordner `vendor/`. Dieser Ordner ist essenziell für die Push-Benachrichtigungen.

## Installation auf dem Server

1.  Laden Sie den **gesamten Inhalt** des `backend/` Ordners auf Ihren Webserver hoch (z.B. per FTP in `public_html` oder einen Unterordner).
    *   Vergessen Sie nicht den **`vendor/` Ordner**, den Sie im Schritt "Vorbereitung" erstellt haben.
    *   Vergessen Sie nicht die **`.htaccess` Datei** (manche FTP Programme verstecken Dateien mit Punkt am Anfang).
2.  Stellen Sie sicher, dass der Ordner `uploads/` auf dem Server existiert und beschreibbar ist (Berechtigungen auf 755 oder 777 setzen).
3.  Erstellen Sie beim Hoster (Hostpoint Panel) eine leere MySQL Datenbank und notieren Sie sich Host, Datenbankname, Benutzer und Passwort.
4.  Rufen Sie im Browser `https://ihre-domain.ch/install.php` auf.
5.  Füllen Sie die Datenbank-Zugangsdaten und das gewünschte Admin-Passwort aus.
6.  Nach erfolgreicher Installation werden Sie zur App weitergeleitet.
7.  **Sicherheit**: Löschen Sie die Datei `install.php` vom Server.

## Funktionen

### Besucher
*   Durchsuchen von Ordnern und Dateien.
*   Anzeige von Ordner-Beschreibungen (Markdown).
*   Installation als App (PWA) auf dem Smartphone/Desktop.
*   Abonnieren von Push-Benachrichtigungen (via Prompt beim Start oder Burger Menu).

### Admin
*   Login unter `/login` oder via Menu -> "Admin Bereich".
*   **Dateimanager**: Ordner erstellen, Dateien hochladen (Drag & Drop), Beschreibungen bearbeiten.
*   **Push Nachrichten**: Senden von Nachrichten an alle registrierten Geräte.
*   **Einstellungen**: Admin Passwort ändern.

## Entwicklung (Frontend)

Das Frontend (Vue.js) befindet sich im `frontend/` Ordner. Wenn Sie Änderungen am Design oder der Logik vornehmen wollen:

1.  Node.js installieren.
2.  Terminal in `frontend/` öffnen:
    ```bash
    npm install
    npm run dev  # Startet lokalen Entwicklungsserver
    ```
3.  Zum Erstellen der Produktions-Dateien:
    ```bash
    npm run build
    ```
    Die Dateien werden automatisch in den Ordner `backend/public/` exportiert. Laden Sie diesen Ordner danach erneut hoch.

## Lizenz

MIT License

Copyright (c) 2024
