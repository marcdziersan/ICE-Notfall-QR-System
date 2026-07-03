# ICE Notfall QR System

> Privates Demo-Projekt: Ein QR-Code verweist auf eine geschützte ICE-Notfallseite, auf der medizinisch relevante Basisinformationen über einen serverseitigen Adminbereich gepflegt werden können.

![Status](https://img.shields.io/badge/status-private%20demo-blue)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4)
![Python](https://img.shields.io/badge/Python-3.x-3776AB)
![Storage](https://img.shields.io/badge/storage-JSON-lightgrey)
![License](https://img.shields.io/badge/license-private%20/%20demo-red)

---

## Worum geht es?

Dieses Projekt ist ein kleines **ICE-System** – **In Case of Emergency**.

Die Idee:

- eine Person trägt oder besitzt einen kleinen Ausdruck mit **Notfall-QR-Code**,
- der QR-Code enthält **nur einen Link mit Schlüssel**,
- die eigentlichen Notfalldaten liegen **nicht direkt im QR-Code**, sondern serverseitig als JSON-Datei,
- die Daten können später online gepflegt werden, ohne den QR-Code neu drucken zu müssen.

Beispiel:

```text
https://marcus-dziersan.net/ice/?key=YHtemngo5KhFCOcvtB-JDhaH
```

Der QR-Code kann auf ein Etikett, eine Karte, einen Anhänger, ein Portemonnaie-Blatt oder einen privaten Notfallzettel gedruckt werden.

---

## Warum ist das entstanden?

Das Projekt entstand privat aus einer sehr einfachen Frage:

> Was passiert, wenn im Notfall niemand sofort sagen kann, welche Vorerkrankungen, Medikamente, Allergien, Operationen, Impfungen, Implantate oder Notfallkontakte relevant sind?

Gerade bei Familien, Kindern, chronischen Erkrankungen, bekannten Allergien, Medikamentenplänen, Operationen oder Implantaten kann es helfen, wenn Ersthelfer oder Rettungskräfte schneller erkennen, dass strukturierte Notfalldaten vorhanden sind.

Ein wichtiger Auslöser war auch die Überlegung rund um Personen mit **implantiertem Defibrillator / ICD** oder anderen medizinischen Besonderheiten. Dabei ist wichtig:

> Ein implantierter Defibrillator bedeutet **nicht automatisch**, dass eine Person nicht reanimiert werden darf.

Eine Reanimation darf nicht einfach wegen eines ICD, Herzschrittmachers oder Implantats unterlassen werden. Ob bestimmte Maßnahmen gewünscht oder nicht gewünscht sind, ist eine Frage von medizinischer Lage, Patientenwille, Patientenverfügung, Vorsorgevollmacht oder ärztlicher Anordnung – nicht allein vom Vorhandensein eines Gerätes.

Dieses System entscheidet daher nichts medizinisch. Es soll nur helfen, relevante Informationen schneller verfügbar zu machen.

---

## Medizinischer Hinweis

Dieses Projekt ist **keine medizinische Empfehlung**, kein Medizinprodukt und kein Ersatz für:

- Notruf 112,
- Rettungsdienst,
- ärztliche Beurteilung,
- Patientenverfügung,
- Vorsorgevollmacht,
- offiziellen Notfalldatensatz,
- elektronische Patientenakte,
- Notfallausweis eines Arztes oder einer Klinik.

Im Zweifel gilt immer:

```text
112 anrufen.
```

Die gespeicherten Daten sollen nur eine zusätzliche Orientierung liefern.

---

## Recherchebasis / Einordnung

Für die Projektstory und die medizinische Einordnung wurden öffentlich zugängliche Informationen geprüft:

- Der Deutsche Rat für Wiederbelebung beschreibt, dass bei Herz-Kreislauf-Stillstand die Herzdruckmassage nicht unnötig unterbrochen werden soll und ein AED unterstützend eingesetzt werden kann.
- Die ERC-Reanimationsleitlinien nennen für Personen mit implantiertem Schrittmacher/Defibrillator, dass Defibrillationselektroden mit Abstand zum Gerät platziert werden sollen.
- Gesundheitsinformationen zu ICDs beschreiben, dass ein implantierter Defibrillator Rhythmusstörungen erkennt und bei Bedarf Schocks abgibt.
- Die Bundesärztekammer verweist darauf, dass Patienten für Einwilligungsunfähigkeit mit Patientenverfügung, Vorsorgevollmacht und Betreuungsverfügung vorsorgen können.
- Gesundheitsdaten gehören nach DSGVO zu besonders sensiblen personenbezogenen Daten und müssen entsprechend vorsichtig behandelt werden.

Quellen:

- https://www.grc-org.de/laien-schulen/39-44-Wie-funktioniert-eine-Reanimation
- https://www.resuscitationjournal.com/article/S0300-9572%25252825%25252900281-3/fulltext
- https://www.gesundheitsinformation.de/wie-funktioniert-ein-implantierbarer-defibrillator-icd.html
- https://www.bundesaerztekammer.de/bundesaerztekammer/patienten/patientenverfuegung
- https://www.edpb.europa.eu/sme/learn-the-basics/data-protection-basics_de

---

## Funktionen

### Öffentliche ICE-Seite

`index.php` zeigt anhand eines Keys die zugehörigen Notfalldaten an.

Aufruf:

```text
/ice/?key=DEIN_ICE_KEY
```

Angezeigt werden unter anderem:

- Name,
- Geburtsdatum,
- Blutgruppe,
- Notfallkontakt,
- Allergien / Unverträglichkeiten,
- Medikamente,
- Diagnosen,
- Operationen,
- Impfungen,
- weitere Hinweise,
- Datenstand.

Die öffentliche Seite setzt zusätzlich:

- `noindex`,
- `nofollow`,
- `noarchive`,
- `no-store`,
- HTML-Escaping gegen einfache Ausgabeprobleme/XSS,
- Key-Validierung.

---

### Adminbereich

`admin.php` bietet einen einfachen passwortgeschützten Adminbereich für JSON-Dateien.

Funktionen:

- Datensatz anlegen,
- Datensatz lesen,
- Datensatz bearbeiten,
- Datensatz löschen,
- Datensatz aktiv/deaktiv setzen,
- ICE-Key erzeugen,
- öffentlichen Link anzeigen,
- JSON-Dateien serverseitig verwalten.

Admin-Aufruf:

```text
/ice/admin.php
```

---

### QR-Printer

Das Python-Tool `ice_qr_printer_sync.py` erzeugt einen QR-Code und druckt ihn über einen einfachen YHK/TEDi-Thermodrucker.

Der Ausdruck enthält:

- `NOTFALL QR CODE`,
- `ICE / In Case of Emergency`,
- QR-Code mit Link,
- Name/Etikett-Titel,
- kurze sichtbare Info,
- ICE-Key.

Der QR-Code enthält bewusst **nicht** alle Gesundheitsdaten, sondern nur den Link:

```text
https://marcus-dziersan.net/ice/?key=DEIN_KEY
```

Dadurch bleiben Änderungen online möglich, ohne den QR-Code neu zu drucken.

---

## Projektstruktur

```text
ice_notfall_system/
├── ice_qr_printer_sync.py
└── ice_system_server/
    ├── config.php
    ├── index.php
    ├── admin.php
    ├── .htaccess
    └── ice_records/
        └── .htaccess
```

Empfohlener Serverpfad:

```text
/ice/
├── config.php
├── index.php
├── admin.php
├── .htaccess
└── ice_records/
    ├── .htaccess
    ├── KEY-1.json
    ├── KEY-2.json
    └── KEY-3.json
```

---

## Installation Server

### 1. Dateien hochladen

Den Inhalt des Ordners `ice_system_server/` nach `/ice/` auf dem Webserver laden.

Beispiel:

```text
marcus-dziersan.net/ice/
```

---

### 2. Schreibrechte setzen

Der Ordner `ice_records/` muss für PHP beschreibbar sein.

Beispiel je nach Hosting:

```bash
chmod 755 ice_records
```

Falls das nicht reicht:

```bash
chmod 775 ice_records
```

Keine pauschalen `777` setzen, wenn es nicht zwingend nötig ist.

---

### 3. Adminpasswort ändern

In `config.php` ist ein Standardhash hinterlegt.

Neuen Hash erzeugen:

```bash
php -r "echo password_hash('DEIN-NEUES-PASSWORT', PASSWORD_DEFAULT), PHP_EOL;"
```

Dann in `config.php` ersetzen:

```php
const ICE_ADMIN_PASSWORD_HASH = 'DEIN_NEUER_HASH';
```

---

### 4. JSON-Zugriff sperren

Der Ordner `ice_records/` enthält eine `.htaccess`, die direkten Zugriff auf JSON-Dateien verhindern soll.

Inhalt:

```apache
Require all denied
```

Damit sollen die JSON-Dateien nicht direkt über den Browser abrufbar sein.

Der Zugriff erfolgt nur über:

```text
/ice/?key=...
```

---

## Installation Printer

### Voraussetzungen

- Python 3.x
- Windows oder ein System mit serieller Verbindung zum Drucker
- YHK/TEDi Thermodrucker
- bekannter COM-Port, z. B. `COM11`

### Python-Abhängigkeiten

```bash
pip install pyserial pillow qrcode[pil]
```

### Start

```bash
python ice_qr_printer_sync.py
```

Standardwerte im Script:

```python
COM_PORT = "COM11"
BAUDRATE = 9600
PRINTER_WIDTH = 384
DEFAULT_BASE_URL = "https://marcus-dziersan.net/ice/"
```

---

## Bedienung

### Typischer Ablauf

```text
1. Adminbereich öffnen
2. Neuen Datensatz anlegen
3. Gesundheitsdaten eintragen
4. Speichern
5. ICE-Key übernehmen
6. Printer öffnen
7. Server-URL und ICE-Key eintragen
8. Name Etikett eintragen
9. Kurzinfo eintragen
10. Notfall QR Code drucken
```

---

### Beispiel: Name Etikett

```text
ICE – Marcus Dziersan
```

oder:

```text
ICE – Anna Dziersan
```

oder:

```text
ICE – Sam Dziersan
```

---

### Beispiel: Kurzinfo

```text
Notfalldaten online per QR.
```

oder bei einer wichtigen Sofortinformation:

```text
Allergie: Penicillin. Notfalldaten per QR.
```

oder:

```text
Kind / Notfalldaten per QR. Elternkontakt hinterlegt.
```

Die Kurzinfo sollte knapp bleiben. Ausführliche Daten gehören in den Adminbereich.

---

## Datensatzformat

Ein JSON-Datensatz kann ungefähr so aussehen:

```json
{
  "key": "YHtemngo5KhFCOcvtB-JDhaH",
  "active": "1",
  "name": "Max Mustermann",
  "date_of_birth": "01.01.1990",
  "blood_type": "0+",
  "emergency_contact": "Erika Mustermann, Ehefrau, 01xx / xxxxxx",
  "allergies": "Penicillin",
  "medications": "Medikament A, Dosierung nach Plan",
  "diagnoses": "Wichtige Diagnose / Implantat / Hinweis",
  "operations": "Operationen mit Jahr",
  "vaccinations": "Impfstatus / relevante Impfungen",
  "notes": "Weitere Hinweise",
  "created_at": "2026-07-03 18:00:00",
  "updated_at": "2026-07-03 18:00:00"
}
```

---

## Datenschutz, Sicherheit und Democharakter

Dieses Projekt verarbeitet potentiell **Gesundheitsdaten**. Das sind besonders sensible personenbezogene Daten. Genau deshalb ist dieses Repository ausdrücklich als **Demo, Lernprojekt und privater Technik-Prototyp** gedacht.

Die aktuelle Version speichert die Datensätze bewusst als einfache JSON-Dateien. Das ist für eine kleine Demo nachvollziehbar, aber für einen produktiven Betrieb mit echten Gesundheitsdaten nur eingeschränkt geeignet.

### Was die Demo aktuell macht

Aktuell vorhanden:

- Adminpasswort mit `password_hash()` / `password_verify()`,
- JSON-Dateien im Ordner `ice_records/`,
- Sperre des direkten JSON-Zugriffs per `.htaccess`,
- Key-Validierung,
- HTML-Ausgabe mit Escaping,
- öffentliche Seiten mit `noindex`, `nofollow`, `noarchive`,
- kein öffentliches Listing aller Datensätze,
- keine Gesundheitsdaten direkt im QR-Code,
- QR-Code enthält nur URL + Key.

Das reicht als einfache Technikdemo, weil man ohne Datenbank, Framework und komplexes Setup zeigen kann, wie ein QR-gestützter Notfallhinweis grundsätzlich funktionieren könnte.

### Was daran bewusst nicht produktionsreif ist

Die Demo ist **nicht** als fertiges Produkt zu verstehen.

Bewusste Grenzen:

- JSON-Dateien sind keine saubere Datenbanklösung für mehrere Nutzer, Rollen, Rechte und Audit-Logs.
- Ein Key im QR-Code ist praktisch ein Zugriffstoken. Wer den QR-Code hat, kann die öffentliche Notfallseite sehen.
- Die öffentlichen Notfalldaten sind absichtlich niedrigschwellig abrufbar, weil der Notfallzugriff schnell funktionieren soll.
- Es gibt keine echte Ende-zu-Ende-Verschlüsselung.
- Es gibt keine revisionssichere Änderungsverfolgung.
- Es gibt kein Rollenmodell für mehrere Admins.
- Es gibt keine granulare Freigabe nach Datenkategorie.
- Es gibt keine professionelle Datenschutz-Folgenabschätzung.
- Es gibt keine medizinische oder rechtliche Zertifizierung.

Für GitHub, Portfolio und Lernzwecke ist JSON okay. Für produktive Gesundheitsdaten wäre eine härtere Architektur notwendig.

---

## Wie man das System produktiv absichern könnte

Für einen echten Einsatz müsste das System deutlich erweitert werden. Die folgenden Punkte beschreiben eine mögliche Härtung.

### 1. Datenbank statt JSON

Für eine produktive Variante wäre eine relationale Datenbank sinnvoll, z. B. MySQL/MariaDB oder PostgreSQL.

Mögliche Tabellen:

```sql
users
ice_profiles
ice_contacts
ice_medications
ice_allergies
ice_operations
ice_vaccinations
ice_access_tokens
ice_access_logs
ice_audit_log
```

Vorteile gegenüber JSON:

- bessere Rechteverwaltung,
- saubere Beziehungen zwischen Personen, Kontakten und medizinischen Einträgen,
- Transaktionen,
- strukturierte Suche,
- Audit-Log,
- einfachere Backups,
- bessere Migrationen,
- weniger Risiko durch Dateirechte-Fehler.

Wichtig: Bei SQL müssten alle Datenbankzugriffe über **Prepared Statements** laufen. Keine SQL-Strings aus Benutzereingaben zusammensetzen.

Beispiel mit PDO:

```php
$stmt = $pdo->prepare('SELECT * FROM ice_profiles WHERE public_key = :key AND is_active = 1');
$stmt->execute(['key' => $key]);
$profile = $stmt->fetch();
```

### 2. CSRF-Schutz im Adminbereich

Alle schreibenden Aktionen im Adminbereich brauchen CSRF-Schutz:

- Anlegen,
- Speichern,
- Löschen,
- Aktivieren/Deaktivieren,
- Passwort ändern,
- Export auslösen.

Prinzip:

```php
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
```

Im Formular:

```html
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
```

Beim Absenden:

```php
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Ungültiger CSRF-Token');
}
```

Zusätzlich sinnvoll:

- nur `POST` für schreibende Aktionen,
- keine Löschaktionen per `GET`,
- SameSite-Cookies,
- erneute Bestätigung bei Löschen,
- Session nach Login erneuern.

### 3. Sichere Sessions und Cookies

Für den Adminbereich sollten Session-Cookies gehärtet werden:

```php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/ice/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);

session_start();
session_regenerate_id(true);
```

Ziele:

- Cookie nur über HTTPS,
- kein Zugriff per JavaScript,
- weniger Risiko durch Cross-Site-Anfragen,
- Schutz gegen Session-Fixation.

### 4. Login härten

Die Demo nutzt ein einzelnes Adminpasswort. Für produktive Nutzung besser:

- eigener Adminbenutzer,
- kein Standardpasswort,
- Passwort-Hashing mit Argon2id oder bcrypt,
- optional Pepper außerhalb des Webroots,
- Zwei-Faktor-Authentifizierung,
- Rate-Limiting,
- Login-Lockout nach Fehlversuchen,
- Login-Log,
- Session-Timeout,
- Adminbereich nicht öffentlich indexierbar,
- optional IP-Allowlist oder zusätzlicher HTTP-Basic-Schutz.

Beispiel Passwort-Hash:

```php
$hash = password_hash($password, PASSWORD_ARGON2ID);
```

Fallback, falls Argon2id nicht verfügbar ist:

```php
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
```

### 5. Verschlüsselung ruhender Daten

Für echte Gesundheitsdaten sollte man nicht nur den Zugriff sperren, sondern Daten auch ruhend verschlüsseln.

Mögliche Varianten:

#### Variante A: Anwendung verschlüsselt einzelne Felder

Die Anwendung verschlüsselt sensible Felder vor dem Speichern:

```php
$ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $key);
```

Vorteile:

- Datenbank sieht nur Ciphertext,
- auch bei Datenbankdump sind Inhalte nicht direkt lesbar.

Nachteile:

- Schlüsselmanagement wird kritisch,
- Suche/Filterung auf verschlüsselten Feldern wird schwerer,
- Backups brauchen ein Schlüsselkonzept.

#### Variante B: MySQL/MariaDB mit verschlüsselten Feldern

MySQL bietet Funktionen wie `AES_ENCRYPT()` und `AES_DECRYPT()`. Trotzdem sollte man genau prüfen, wo der Schlüssel liegt. Wenn der Schlüssel im selben PHP-Code oder direkt in SQL hart hinterlegt ist, ist der Schutz begrenzt.

#### Variante C: Server-/Volume-Verschlüsselung

Der Server oder das Hosting verschlüsselt Datenträger/Backups. Das schützt eher gegen Verlust von Datenträgern, aber nicht automatisch gegen kompromittierten Webspace oder auslesbaren PHP-Code.

Wichtig ist immer das **Schlüsselmanagement**:

- Schlüssel nicht im Webroot speichern,
- Schlüssel nicht in Git committen,
- `.env` oder Server-Secret verwenden,
- Rotation planen,
- Backup der Schlüssel getrennt sichern,
- Zugriff auf Secrets minimal halten.

### 6. Öffentliche Notfallseite: Minimaldaten und Stufenmodell

Der Notfallzugriff muss schnell sein. Gleichzeitig sind Gesundheitsdaten sensibel. Ein sinnvolles Modell wäre daher zweistufig:

#### Öffentlich per QR sichtbar

- Name,
- Geburtsjahr oder Alter statt vollständigem Geburtsdatum,
- Notfallkontakt,
- lebenswichtige Allergien,
- wichtige Implantate,
- Hinweis auf Patientenverfügung/Vorsorgevollmacht,
- Kurznotizen für Rettungskräfte.

#### Geschützt sichtbar

- vollständiger Medikamentenplan,
- Operationen,
- Impfungen,
- Diagnosen,
- Dokumente,
- Arztkontakte,
- detaillierte Historie.

Optional könnte der QR-Code nur ein Minimalprofil anzeigen und für Details eine zusätzliche PIN, TAN oder Freigabe verlangen. Für echte Rettungssituationen muss man aber sehr vorsichtig abwägen: Zu viel Schutz kann den Notfallnutzen zerstören.

### 7. Zugriffstokens statt sprechender Keys

Der QR-Key sollte kein Name, kein Geburtsdatum und kein erratbarer Wert sein.

Gut:

```text
u4Ww8pQW6vC7b5kM3hY9dA
```

Schlecht:

```text
marcus-1988
sam-kind
anna-notfall
```

Produktiv sinnvoll:

- mindestens 128 Bit Entropie,
- zufällig erzeugt mit `random_bytes()`,
- Token nur gehasht speichern,
- Token rotierbar machen,
- verlorene QR-Codes deaktivierbar machen,
- optional Ablaufdatum oder Versionierung.

Beispiel:

```php
$token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
$tokenHash = hash('sha256', $token);
```

Öffentlich wird der Token genutzt, gespeichert wird nur der Hash.

### 8. HTTP-Sicherheitsheader

Sinnvolle Header:

```php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
```

Für HTTPS:

```php
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
```

HSTS nur aktivieren, wenn HTTPS sauber läuft und Subdomains wirklich vorbereitet sind.

### 9. Dateisystem härten, falls JSON bleibt

Wenn man bei JSON bleibt, dann mindestens:

- `ice_records/` außerhalb des Webroots speichern,
- keine direkten Downloads erlauben,
- Dateinamen strikt validieren,
- keine Pfade aus Benutzereingaben übernehmen,
- Schreibrechte minimal halten,
- atomar schreiben: erst temporäre Datei, dann `rename()`,
- Datei-Locking mit `flock()`,
- Backups verschlüsseln,
- keine personenbezogenen Daten im Dateinamen.

Beispiel für atomares Schreiben:

```php
$tmp = $file . '.tmp';
file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
rename($tmp, $file);
```

### 10. Protokollierung ohne Datenschutz-Falle

Logs sind wichtig, aber bei Gesundheitsdaten heikel.

Sinnvoll loggen:

- Zeitpunkt,
- Aktion,
- Admin-Benutzer,
- Datensatz-ID,
- Erfolg/Fehler.

Nicht ungeprüft loggen:

- vollständige Gesundheitsdaten,
- Medikamente,
- Diagnosen,
- komplette JSON-Dumps,
- komplette Zugriffstokens,
- unnötige IP-Historien.

Produktiv braucht man ein Lösch- und Aufbewahrungskonzept.

### 11. Backups und Wiederherstellung

Produktiv notwendig:

- regelmäßige Backups,
- verschlüsselte Backups,
- getrennte Aufbewahrung von Daten und Schlüsseln,
- Wiederherstellung testen,
- Löschkonzept für alte Datensätze,
- Versionierung bei versehentlicher Änderung.

Ein Backup, das nie zurückgespielt getestet wurde, ist nur eine Hoffnung.

### 12. DSGVO und Einwilligung

Bei echten Gesundheitsdaten muss vorher geklärt werden:

- Wer ist Verantwortlicher?
- Welche Daten werden gespeichert?
- Wer darf sie sehen?
- Wie wird Einwilligung dokumentiert?
- Wie werden Daten gelöscht?
- Wie wird Auskunft erteilt?
- Was passiert bei Datenpanne?
- Wie wird der Zugriff im Notfall begründet?

Für Familien/Privatgebrauch ist die Lage anders als bei öffentlichem Dienst oder gewerblichem Betrieb. Trotzdem sollte man bei Gesundheitsdaten grundsätzlich sparsam und vorsichtig arbeiten.

---

## Mögliche produktive Architektur

Eine robustere Version könnte so aufgebaut sein:

```text
QR-Code
  ↓
öffentliche ICE-Seite mit Token
  ↓
Minimaldaten sichtbar
  ↓
optional: Detailfreigabe / PIN / Adminfreigabe
  ↓
MySQL/MariaDB mit verschlüsselten sensiblen Feldern
  ↓
Audit-Log + Backup + Rechtekonzept
```

Mögliche Komponenten:

- PHP 8.x,
- PDO,
- MySQL/MariaDB,
- CSRF-Token,
- Argon2id-Passwort-Hashes,
- 2FA für Admin,
- verschlüsselte Felder,
- Token-Hashing,
- Audit-Log,
- Backup-Export,
- getrennte Konfiguration außerhalb des Webroots.

---

## Security-Quellen für eine spätere Härtung

Hilfreiche Referenzen:

- OWASP CSRF Prevention Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
- OWASP SQL Injection Prevention Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html
- OWASP Password Storage Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html
- OWASP Authentication Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html
- OWASP Cryptographic Storage Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Cryptographic_Storage_Cheat_Sheet.html
- OWASP Key Management Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Key_Management_Cheat_Sheet.html
- PHP `setcookie()` / Cookie-Optionen: https://www.php.net/manual/en/function.setcookie.php
- MDN Content Security Policy: https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CSP
- MDN X-Frame-Options: https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Frame-Options
- MySQL Encryption Functions: https://dev.mysql.com/doc/refman/8.4/en/encryption-functions.html

## Grenzen des Projekts

Dieses System ist bewusst einfach gehalten.

Es ist:

- kein zertifiziertes Medizinprodukt,
- keine elektronische Patientenakte,
- keine rechtssichere Patientenverfügung,
- kein Ersatz für Notruf oder Rettungsdienst,
- kein Ersatz für ärztliche Dokumentation,
- kein Ersatz für einen offiziellen Notfallausweis.

Es ist eine technische Demo, wie man mit einfachen Mitteln einen QR-gestützten privaten Notfallhinweis bauen kann.

---

## Warum JSON statt Datenbank?

Für dieses private Demo-Projekt ist JSON bewusst gewählt. Es soll zeigen, wie die Grundidee funktioniert, ohne zuerst ein komplettes Datenbankmodell, Migrationen, Benutzerverwaltung und Deployment-Konzept bauen zu müssen.

Vorteile für die Demo:

- kein Datenbanksetup nötig,
- leicht zu verstehen,
- leicht auf einfachem Webspace zu betreiben,
- einfache Dateien pro Person,
- gut für wenige private Testdatensätze,
- gut als Lern- und Portfolio-Projekt.

Aber klar gesagt:

> JSON ist hier eine Demo-Entscheidung, keine Empfehlung für produktive Gesundheitsdaten.

Für echte Nutzung wären MySQL/MariaDB, PostgreSQL oder mindestens SQLite sinnvoller. Dann könnten Daten sauber normalisiert, Rechte abgebildet, Zugriffe protokolliert, Tokens gehasht und sensible Felder verschlüsselt werden.

Ein möglicher Datenbankansatz:

```sql
CREATE TABLE ice_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    public_token_hash CHAR(64) NOT NULL UNIQUE,
    display_name VARCHAR(120) NOT NULL,
    birth_date DATE NULL,
    blood_type VARCHAR(10) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE ice_medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    medication_name VARCHAR(255) NOT NULL,
    dosage VARCHAR(255) NULL,
    notes TEXT NULL,
    FOREIGN KEY (profile_id) REFERENCES ice_profiles(id) ON DELETE CASCADE
);
```

Sensible Detailfelder könnten entweder anwendungsseitig oder datenbankseitig verschlüsselt werden. Für Passwortspeicherung gilt weiterhin: Passwörter nicht verschlüsseln, sondern langsam und sicher hashen.

---

## Roadmap

Mögliche nächste Schritte:

- Export als PDF-Notfallkarte,
- Druckvorlagen für verschiedene Etikettgrößen,
- optionaler PIN-Schutz pro Datensatz,
- optionales öffentliches Minimalprofil + geschütztes Detailprofil,
- Admin-Login mit Benutzername statt nur Passwort,
- Änderungsverlauf,
- Backup-Download,
- Import/Export,
- mehrsprachige Ansicht Deutsch/Englisch,
- Notfallmodus mit besonders großer Darstellung,
- Feld für Implantate / ICD / Herzschrittmacher getrennt von Diagnosen,
- Feld für Patientenverfügung / Vorsorgevollmacht als reiner Hinweis ohne Rechtsprüfung.

---

## Beispiel-Szenario

Eine Person trägt einen kleinen Notfallzettel mit QR-Code im Portemonnaie.

Im Notfall kann der QR-Code gescannt werden. Die Seite zeigt schnell:

- wer die Person ist,
- wen man kontaktieren soll,
- ob Allergien bekannt sind,
- ob Medikamente relevant sind,
- ob wichtige Diagnosen oder Implantate bekannt sind,
- ob Operationen oder Hinweise hinterlegt wurden.

Der QR-Code selbst bleibt klein und stabil. Die Daten können später im Adminbereich geändert werden.

---

## Lizenz / Nutzung

Privates Lern- und Demo-Projekt.

Vor öffentlichem produktiven Einsatz müssen Datenschutz, Sicherheit, Haftung und medizinische/rechtliche Einordnung sauber geprüft werden.

---

## Haftungsausschluss

Dieses Repository stellt nur Beispielcode bereit.

Die Nutzung erfolgt auf eigene Verantwortung. Medizinische Entscheidungen dürfen nicht automatisiert aus den Daten dieses Systems abgeleitet werden. Im Notfall ist der Rettungsdienst zu alarmieren und medizinisches Fachpersonal entscheidet anhand der konkreten Lage.
