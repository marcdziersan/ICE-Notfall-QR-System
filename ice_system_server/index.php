<?php
/**
 * Öffentliche ICE-Notfallseite
 * Aufruf:
 *   https://marcus-dziersan.net/ice/?key=DEIN_ICE_KEY
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function clean_key(mixed $key): string
{
    $key = trim((string)$key);
    if (!preg_match('/^[A-Za-z0-9_-]{12,96}$/', $key)) {
        return '';
    }
    return $key;
}

function record_path(string $key): string
{
    return rtrim(ICE_DATA_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $key . '.json';
}

function load_record(string $key): array
{
    $path = record_path($key);
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException('Zu diesem ICE-Key wurde kein Datensatz gefunden.');
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        throw new RuntimeException('Der ICE-Datensatz konnte nicht gelesen werden.');
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Der ICE-Datensatz ist beschädigt oder keine gültige JSON-Datei.');
    }

    return $data;
}

function val(array $record, string $key, string $fallback = ''): string
{
    $value = $record[$key] ?? $fallback;
    if (is_array($value)) {
        return implode("\n", array_map('strval', $value));
    }
    return trim((string)$value);
}

function section_card(string $title, string $content, string $class = ''): void
{
    $content = trim($content);
    if ($content === '') {
        return;
    }
    $classAttr = trim('card ' . $class);
    echo '<section class="' . e($classAttr) . '">';
    echo '<h2>' . e($title) . '</h2>';
    echo '<div class="content">' . nl2br(e($content)) . '</div>';
    echo '</section>';
}

function fail_page(string $title, string $message, int $status): never
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    header('X-Robots-Tag: noindex, nofollow, noarchive', true);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true);
    ?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title><?= e($title) ?></title>
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;background:#111318;color:#f8fafc;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;padding:18px}
        main{width:min(720px,100%);background:#1e232d;border:1px solid rgba(255,255,255,.12);border-radius:22px;padding:26px;box-shadow:0 24px 70px rgba(0,0,0,.35)}
        h1{margin:0 0 10px;font-size:clamp(1.8rem,7vw,2.8rem)}
        p{line-height:1.55;color:#d9dee8}code{background:rgba(255,255,255,.09);padding:.15rem .35rem;border-radius:.4rem}
    </style>
</head>
<body>
<main>
    <h1><?= e($title) ?></h1>
    <p><?= e($message) ?></p>
    <p>Erwarteter QR-Aufruf: <code>/ice/?key=DEIN_ICE_KEY</code></p>
</main>
</body>
</html>
<?php
    exit;
}

$key = clean_key($_GET['key'] ?? '');
if ($key === '') {
    fail_page('Ungültiger ICE-Key', 'Der QR-Code enthält keinen gültigen Schlüssel oder der Link wurde verändert.', 400);
}

try {
    $record = load_record($key);
} catch (Throwable $e) {
    fail_page('ICE-Datensatz nicht gefunden', $e->getMessage(), 404);
}

if (isset($record['active']) && (string)$record['active'] === '0') {
    fail_page('ICE-Datensatz deaktiviert', 'Dieser ICE-Datensatz wurde deaktiviert und ist aktuell nicht freigegeben.', 403);
}

$name = val($record, 'name', 'Unbekannte Person');
$dateOfBirth = val($record, 'date_of_birth');
$bloodType = val($record, 'blood_type');
$emergencyContact = val($record, 'emergency_contact');
$allergies = val($record, 'allergies');
$medications = val($record, 'medications');
$diagnoses = val($record, 'diagnoses');
$operations = val($record, 'operations');
$vaccinations = val($record, 'vaccinations');
$notes = val($record, 'notes');
$updatedAt = val($record, 'updated_at');
$createdAt = val($record, 'created_at');
$dataDate = $updatedAt !== '' ? $updatedAt : $createdAt;

header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow, noarchive', true);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true);
header('Pragma: no-cache', true);
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title><?= e(ICE_APP_TITLE) ?> - <?= e($name) ?></title>
    <style>
        :root{--bg:#111318;--card:#fff;--soft:#f5f7fb;--text:#10141d;--muted:#5f6675;--danger:#bd111b;--danger2:#870b12;--border:#dfe4ed;--green:#166534;--yellow:#fff6d8}
        *{box-sizing:border-box}body{margin:0;background:radial-gradient(circle at top left,rgba(189,17,27,.25),transparent 26rem),linear-gradient(135deg,#111318,#242a36);font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:var(--text)}
        .page{width:min(980px,100%);margin:0 auto;padding:14px}.hero{background:linear-gradient(135deg,var(--danger),var(--danger2));color:#fff;border-radius:0 0 26px 26px;padding:22px 18px;box-shadow:0 18px 45px rgba(0,0,0,.28)}
        .badge{display:inline-flex;gap:8px;align-items:center;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);padding:7px 10px;border-radius:999px;font-weight:800;letter-spacing:.04em}.badge span{background:#fff;color:var(--danger);border-radius:999px;padding:3px 7px}
        h1{font-size:clamp(2rem,8vw,4.2rem);line-height:.95;margin:18px 0 8px}.sub{font-size:1.1rem;opacity:.95;margin:0}.topgrid{display:grid;grid-template-columns:1fr;gap:12px;margin-top:16px}.fact{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.2);border-radius:18px;padding:13px}.fact b{display:block;font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;opacity:.8}.fact strong{display:block;font-size:1.25rem;margin-top:4px}
        .emergency{margin:14px 0;background:var(--yellow);border:2px solid #f4d36a;border-radius:20px;padding:14px;font-weight:800}.emergency a{color:#111;text-decoration:none}.grid{display:grid;grid-template-columns:1fr;gap:12px}.card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:16px;box-shadow:0 16px 45px rgba(0,0,0,.14)}.card h2{margin:0 0 8px;font-size:1.05rem;color:#202633}.content{white-space:normal;line-height:1.55}.critical{border-left:8px solid var(--danger)}.ok{border-left:8px solid var(--green)}.meta{color:var(--muted);font-size:.9rem;text-align:center;padding:20px 6px 10px}.key{font-family:ui-monospace,SFMono-Regular,Consolas,monospace;word-break:break-all}
        @media (min-width:760px){.page{padding:24px}.hero{border-radius:28px;padding:30px}.topgrid{grid-template-columns:repeat(3,1fr)}.grid{grid-template-columns:repeat(2,1fr)}.wide{grid-column:1/-1}}
        @media print{body{background:#fff}.page{padding:0}.hero,.card,.emergency{box-shadow:none}.hero{border-radius:0;color:#000;background:#fff;border:2px solid #000}.badge{color:#000;border-color:#000}.badge span{border:1px solid #000;color:#000}.card{break-inside:avoid}}
    </style>
</head>
<body>
<div class="page">
    <header class="hero">
        <div class="badge"><span>ICE</span> In Case of Emergency</div>
        <h1><?= e($name) ?></h1>
        <p class="sub">Notfalldaten für Ersthelfer, Rettungsdienst und medizinisches Personal.</p>
        <div class="topgrid">
            <div class="fact"><b>Geburtsdatum</b><strong><?= e($dateOfBirth !== '' ? $dateOfBirth : 'nicht angegeben') ?></strong></div>
            <div class="fact"><b>Blutgruppe</b><strong><?= e($bloodType !== '' ? $bloodType : 'nicht angegeben') ?></strong></div>
            <div class="fact"><b>Notfallkontakt</b><strong><?= e($emergencyContact !== '' ? $emergencyContact : 'nicht angegeben') ?></strong></div>
        </div>
    </header>

    <div class="emergency">Lebensbedrohlicher Notfall: <a href="tel:112">112 anrufen</a>. Diese Seite ersetzt keine medizinische Prüfung.</div>

    <main class="grid">
        <?php section_card('Allergien / Unverträglichkeiten', $allergies, 'critical'); ?>
        <?php section_card('Aktuelle Medikamente', $medications, 'critical'); ?>
        <?php section_card('Diagnosen / wichtige Erkrankungen', $diagnoses); ?>
        <?php section_card('Operationen', $operations); ?>
        <?php section_card('Impfungen', $vaccinations); ?>
        <?php section_card('Weitere Hinweise', $notes, 'wide'); ?>
    </main>

    <footer class="meta">
        <div>Datenstand: <?= e($dataDate !== '' ? $dataDate : 'nicht angegeben') ?></div>
        <div>ICE-Key: <span class="key"><?= e($key) ?></span></div>
    </footer>
</div>
</body>
</html>
