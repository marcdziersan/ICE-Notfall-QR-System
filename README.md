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

## Datenschutz und Sicherheit

Dieses Projekt verarbeitet Gesundheitsdaten. Das sind besonders sensible Daten.

Für eine private Demo reicht eine einfache Lösung technisch aus, für produktive Nutzung aber nicht.

Aktuell vorhanden:

- Adminpasswort mit `password_hash()` / `password_verify()`,
- JSON-Dateien außerhalb direkter Anzeige über `.htaccess` geschützt,
- Key-Validierung,
- HTML-Ausgabe mit Escaping,
- öffentliche Seiten mit `noindex`, `nofollow`, `noarchive`,
- kein Listing aller Datensätze öffentlich,
- keine Gesundheitsdaten direkt im QR-Code.

Für echte produktive Nutzung wären zusätzlich sinnvoll:

- HTTPS erzwingen,
- stärkeres Rollen-/Benutzersystem,
- Zwei-Faktor-Login für Admin,
- Rate-Limiting,
- Login-Bruteforce-Schutz,
- serverseitige Logs mit Datenschutzkonzept,
- Verschlüsselung ruhender Daten,
- Backup-/Restore-Konzept,
- Exportfunktion,
- Einwilligungs- und Löschkonzept,
- klare Verantwortlichkeit nach DSGVO,
- Sicherheitsprüfung vor Veröffentlichung.

---

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

Für dieses private Demo-Projekt ist JSON bewusst gewählt:

- kein Datenbanksetup nötig,
- leicht zu sichern,
- leicht zu verstehen,
- leicht auf einfachem Webspace zu betreiben,
- passend für wenige Personen oder Familienmitglieder.

Für größere Nutzung wäre eine Datenbank sinnvoller, z. B. SQLite oder MySQL/MariaDB.

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
