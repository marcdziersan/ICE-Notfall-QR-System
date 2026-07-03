<?php
/**
 * ICE System Konfiguration
 * Pfad-Beispiel auf dem Server:
 *   /ice/config.php
 *   /ice/index.php
 *   /ice/admin.php
 *   /ice/ice_records/*.json
 */

declare(strict_types=1);

const ICE_APP_TITLE = 'ICE Notfalldaten';
const ICE_PUBLIC_BASE_URL = 'https://marcus-dziersan.net/ice/';
const ICE_DATA_DIR = __DIR__ . '/ice_records';

// Standard-Passwort: change-me-now
// Direkt nach Upload ändern. Neuen Hash erzeugen mit:
// php -r "echo password_hash('DEIN-NEUES-PASSWORT', PASSWORD_DEFAULT), PHP_EOL;"
const ICE_ADMIN_PASSWORD_HASH = '$2y$12$J.r3W.NC76LzICWJxSZzV.yoSkOe8aExV07eVGsc2/DhB8B4y5O2C';

// Optionaler Anzeigename im Adminbereich.
const ICE_ADMIN_NAME = 'ICE Admin';
