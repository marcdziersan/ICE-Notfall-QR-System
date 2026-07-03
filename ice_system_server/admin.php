<?php
/**
 * ICE Adminbereich - Full CRUD für JSON-Datensätze
 * Aufruf:
 *   https://marcus-dziersan.net/ice/admin.php
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

session_name('ICEADMINSESSID');
session_start();

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

function now_iso(): string
{
    return date('c');
}

function data_dir(): string
{
    $dir = rtrim(ICE_DATA_DIR, DIRECTORY_SEPARATOR);
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    return $dir;
}

function record_path(string $key): string
{
    return data_dir() . DIRECTORY_SEPARATOR . $key . '.json';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function require_csrf(): void
{
    $token = (string)($_POST['csrf'] ?? '');
    if ($token === '' || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        exit('CSRF-Token ungültig.');
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['ice_admin_logged_in']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: admin.php');
        exit;
    }
}

function new_key(): string
{
    return rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
}

function load_record(string $key): array
{
    $path = record_path($key);
    if (!is_file($path)) {
        return [];
    }

    $raw = file_get_contents($path);
    $data = json_decode((string)$raw, true);
    return is_array($data) ? $data : [];
}

function save_record(array $record): void
{
    $key = clean_key($record['key'] ?? '');
    if ($key === '') {
        throw new RuntimeException('Ungültiger ICE-Key.');
    }

    $path = record_path($key);
    $tmp = $path . '.tmp';
    $json = json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('JSON konnte nicht erzeugt werden.');
    }
    if (file_put_contents($tmp, $json . PHP_EOL, LOCK_EX) === false) {
        throw new RuntimeException('Temporäre JSON-Datei konnte nicht geschrieben werden.');
    }
    if (!rename($tmp, $path)) {
        @unlink($tmp);
        throw new RuntimeException('JSON-Datei konnte nicht gespeichert werden.');
    }
    @chmod($path, 0640);
}

function delete_record(string $key): void
{
    $path = record_path($key);
    if (is_file($path) && !unlink($path)) {
        throw new RuntimeException('Datensatz konnte nicht gelöscht werden.');
    }
}

function public_url(string $key): string
{
    return rtrim(ICE_PUBLIC_BASE_URL, '/') . '/?key=' . rawurlencode($key);
}

function admin_edit_url(string $key): string
{
    return 'admin.php?action=edit&key=' . rawurlencode($key);
}

function list_records(): array
{
    $records = [];
    foreach (glob(data_dir() . DIRECTORY_SEPARATOR . '*.json') ?: [] as $file) {
        $key = basename($file, '.json');
        $data = json_decode((string)file_get_contents($file), true);
        if (!is_array($data)) {
            $data = [];
        }
        $records[] = [
            'key' => $key,
            'name' => trim((string)($data['name'] ?? '')),
            'date_of_birth' => trim((string)($data['date_of_birth'] ?? '')),
            'active' => (string)($data['active'] ?? '1'),
            'updated_at' => trim((string)($data['updated_at'] ?? $data['created_at'] ?? '')),
        ];
    }

    usort($records, static function (array $a, array $b): int {
        return strcmp($b['updated_at'], $a['updated_at']);
    });

    return $records;
}

function field(array $record, string $key, string $default = ''): string
{
    $value = $record[$key] ?? $default;
    if (is_array($value)) {
        return implode("\n", array_map('strval', $value));
    }
    return (string)$value;
}

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'login') {
        require_csrf();
        $password = (string)($_POST['password'] ?? '');
        if (password_verify($password, ICE_ADMIN_PASSWORD_HASH)) {
            session_regenerate_id(true);
            $_SESSION['ice_admin_logged_in'] = true;
            header('Location: admin.php');
            exit;
        }
        $error = 'Login fehlgeschlagen.';
    }

    if ($action === 'logout') {
        require_csrf();
        $_SESSION = [];
        session_destroy();
        header('Location: admin.php');
        exit;
    }

    if ($action === 'save') {
        require_login();
        require_csrf();

        try {
            $oldKey = clean_key($_POST['old_key'] ?? '');
            $key = clean_key($_POST['key'] ?? '');
            if ($key === '') {
                throw new RuntimeException('Der ICE-Key ist ungültig. Erlaubt sind A-Z, a-z, 0-9, - und _.');
            }

            $existing = load_record($key);
            $createdAt = (string)($existing['created_at'] ?? now_iso());
            if ($oldKey !== '' && $oldKey !== $key && is_file(record_path($oldKey))) {
                delete_record($oldKey);
            }

            $record = [
                'key' => $key,
                'active' => isset($_POST['active']) ? '1' : '0',
                'ice_url' => public_url($key),
                'name' => trim((string)($_POST['name'] ?? '')),
                'date_of_birth' => trim((string)($_POST['date_of_birth'] ?? '')),
                'blood_type' => trim((string)($_POST['blood_type'] ?? '')),
                'emergency_contact' => trim((string)($_POST['emergency_contact'] ?? '')),
                'allergies' => trim((string)($_POST['allergies'] ?? '')),
                'medications' => trim((string)($_POST['medications'] ?? '')),
                'diagnoses' => trim((string)($_POST['diagnoses'] ?? '')),
                'operations' => trim((string)($_POST['operations'] ?? '')),
                'vaccinations' => trim((string)($_POST['vaccinations'] ?? '')),
                'notes' => trim((string)($_POST['notes'] ?? '')),
                'created_at' => $createdAt,
                'updated_at' => now_iso(),
                'version' => '2.0.0',
            ];

            if ($record['name'] === '') {
                throw new RuntimeException('Name ist Pflicht.');
            }

            save_record($record);
            header('Location: admin.php?action=edit&key=' . rawurlencode($key) . '&saved=1');
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }

    if ($action === 'delete') {
        require_login();
        require_csrf();

        try {
            $key = clean_key($_POST['key'] ?? '');
            $confirm = (string)($_POST['confirm'] ?? '');
            if ($key === '' || $confirm !== $key) {
                throw new RuntimeException('Löschen nicht bestätigt.');
            }
            delete_record($key);
            header('Location: admin.php?deleted=1');
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

if (isset($_GET['saved'])) {
    $notice = 'Datensatz gespeichert.';
}
if (isset($_GET['deleted'])) {
    $notice = 'Datensatz gelöscht.';
}

$action = (string)($_GET['action'] ?? 'list');
$key = clean_key($_GET['key'] ?? '');
$record = [];
$isNew = false;

if (is_logged_in() && $action === 'new') {
    $isNew = true;
    $record = [
        'key' => new_key(),
        'active' => '1',
        'name' => '',
        'date_of_birth' => '',
        'blood_type' => '',
        'emergency_contact' => '',
        'allergies' => '',
        'medications' => '',
        'diagnoses' => '',
        'operations' => '',
        'vaccinations' => '',
        'notes' => '',
    ];
}

if (is_logged_in() && $action === 'edit') {
    $isNew = false;
    if ($key === '') {
        $key = new_key();
        $isNew = true;
    }
    $record = load_record($key);
    if ($record === []) {
        $isNew = true;
        $record = ['key' => $key, 'active' => '1'];
    }
}

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
    <title>ICE Admin</title>
    <style>
        :root{--bg:#111318;--panel:#fff;--panel2:#f6f8fc;--text:#111827;--muted:#657083;--border:#dfe4ee;--primary:#b7111b;--primary2:#840b12;--green:#166534;--danger:#b7111b;--shadow:0 20px 55px rgba(0,0,0,.20)}
        *{box-sizing:border-box}body{margin:0;background:linear-gradient(135deg,#111318,#252b37);color:var(--text);font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.wrap{width:min(1120px,100%);margin:0 auto;padding:14px}.top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;border-radius:0 0 26px 26px;padding:20px;box-shadow:var(--shadow)}h1{margin:0;font-size:clamp(1.6rem,6vw,3rem)}.top a,.top button{color:#fff}.panel{background:var(--panel);border:1px solid var(--border);border-radius:22px;padding:16px;margin-top:14px;box-shadow:var(--shadow)}.notice{background:#dcfce7;border:1px solid #86efac;color:#14532d;border-radius:14px;padding:12px;margin-top:14px}.error{background:#fee2e2;border:1px solid #fca5a5;color:#7f1d1d;border-radius:14px;padding:12px;margin-top:14px}.toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center}.btn,button{appearance:none;border:0;border-radius:13px;padding:10px 13px;font-weight:800;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#202633;color:#fff}.btn.primary,button.primary{background:var(--primary)}.btn.light,button.light{background:#eef2f7;color:#111827}.btn.danger,button.danger{background:var(--danger)}.grid{display:grid;grid-template-columns:1fr;gap:12px}.row{display:grid;grid-template-columns:1fr;gap:10px}.field label{display:block;font-weight:800;margin-bottom:5px;color:#1f2937}.field input,.field textarea{width:100%;border:1px solid var(--border);border-radius:14px;padding:11px 12px;font:inherit;background:#fff}.field textarea{min-height:92px;resize:vertical}.hint{color:var(--muted);font-size:.9rem;line-height:1.45}.records{width:100%;border-collapse:collapse}.records th,.records td{text-align:left;border-bottom:1px solid var(--border);padding:10px;vertical-align:top}.records th{font-size:.85rem;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}.key{font-family:ui-monospace,SFMono-Regular,Consolas,monospace;word-break:break-all}.pill{display:inline-flex;padding:4px 8px;border-radius:999px;font-weight:800;font-size:.82rem}.on{background:#dcfce7;color:#14532d}.off{background:#fee2e2;color:#7f1d1d}.login{min-height:100vh;display:grid;place-items:center;padding:18px}.login .panel{width:min(460px,100%)}.deletebox{border:2px solid #fecaca;background:#fff7f7}.inlineform{display:inline}.split{display:flex;gap:10px;flex-wrap:wrap}.mobile-table{overflow:auto}.linkbox{background:var(--panel2);border:1px dashed var(--border);border-radius:16px;padding:12px;word-break:break-all}
        @media (min-width:760px){.wrap{padding:24px}.grid{grid-template-columns:repeat(2,1fr)}.wide{grid-column:1/-1}.row{grid-template-columns:repeat(3,1fr)}}
    </style>
</head>
<body>
<?php if (!is_logged_in()): ?>
<div class="login">
    <form class="panel" method="post" autocomplete="off">
        <h1>ICE Admin</h1>
        <p class="hint">Login für Verwaltung der JSON-Notfalldaten.</p>
        <?php if ($error !== ''): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="login">
        <div class="field">
            <label for="password">Passwort</label>
            <input id="password" name="password" type="password" required autofocus>
        </div>
        <p><button class="primary" type="submit">Einloggen</button></p>
        <p class="hint">Standard nach Upload: <span class="key">change-me-now</span>. Danach in <span class="key">config.php</span> ändern.</p>
    </form>
</div>
<?php exit; endif; ?>

<div class="wrap">
    <header class="top">
        <div>
            <h1>ICE Admin</h1>
            <div><?= e(ICE_ADMIN_NAME) ?> · JSON CRUD · Serverdaten sind führend</div>
        </div>
        <div class="toolbar">
            <a class="btn light" href="admin.php">Übersicht</a>
            <a class="btn light" href="admin.php?action=new">Neuer Datensatz</a>
            <form class="inlineform" method="post">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="logout">
                <button type="submit">Logout</button>
            </form>
        </div>
    </header>

    <?php if ($notice !== ''): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

    <?php if ($action === 'new' || $action === 'edit'): ?>
        <?php
            $formKey = clean_key(field($record, 'key')) ?: ($key ?: new_key());
            $link = public_url($formKey);
        ?>
        <section class="panel">
            <h2><?= $isNew ? 'Neuen ICE-Datensatz anlegen' : 'ICE-Datensatz bearbeiten' ?></h2>
            <p class="hint">Der QR-Code enthält nur diesen öffentlichen Link. Alle Gesundheitsdaten bleiben serverseitig in der JSON-Datei.</p>
            <div class="linkbox"><b>QR-Link:</b><br><a href="<?= e($link) ?>" target="_blank" rel="noopener"><?= e($link) ?></a></div>

            <form method="post" autocomplete="off">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="old_key" value="<?= e($formKey) ?>">

                <div class="row" style="margin-top:14px">
                    <div class="field">
                        <label for="key">ICE-Key</label>
                        <input id="key" name="key" value="<?= e($formKey) ?>" required pattern="[A-Za-z0-9_-]{12,96}">
                    </div>
                    <div class="field">
                        <label for="name">Name *</label>
                        <input id="name" name="name" value="<?= e(field($record, 'name')) ?>" required>
                    </div>
                    <div class="field">
                        <label for="date_of_birth">Geburtsdatum</label>
                        <input id="date_of_birth" name="date_of_birth" value="<?= e(field($record, 'date_of_birth')) ?>" placeholder="TT.MM.JJJJ">
                    </div>
                </div>

                <div class="row">
                    <div class="field">
                        <label for="blood_type">Blutgruppe</label>
                        <input id="blood_type" name="blood_type" value="<?= e(field($record, 'blood_type')) ?>" placeholder="z. B. A+, 0-, unbekannt">
                    </div>
                    <div class="field wide">
                        <label for="emergency_contact">Notfallkontakt</label>
                        <input id="emergency_contact" name="emergency_contact" value="<?= e(field($record, 'emergency_contact')) ?>" placeholder="Name, Beziehung, Telefonnummer">
                    </div>
                </div>

                <div class="grid">
                    <div class="field">
                        <label for="allergies">Allergien / Unverträglichkeiten</label>
                        <textarea id="allergies" name="allergies"><?= e(field($record, 'allergies')) ?></textarea>
                    </div>
                    <div class="field">
                        <label for="medications">Aktuelle Medikamente</label>
                        <textarea id="medications" name="medications"><?= e(field($record, 'medications')) ?></textarea>
                    </div>
                    <div class="field">
                        <label for="diagnoses">Diagnosen / wichtige Erkrankungen</label>
                        <textarea id="diagnoses" name="diagnoses"><?= e(field($record, 'diagnoses')) ?></textarea>
                    </div>
                    <div class="field">
                        <label for="operations">Operationen</label>
                        <textarea id="operations" name="operations"><?= e(field($record, 'operations')) ?></textarea>
                    </div>
                    <div class="field">
                        <label for="vaccinations">Impfungen</label>
                        <textarea id="vaccinations" name="vaccinations"><?= e(field($record, 'vaccinations')) ?></textarea>
                    </div>
                    <div class="field">
                        <label for="notes">Weitere Hinweise</label>
                        <textarea id="notes" name="notes"><?= e(field($record, 'notes')) ?></textarea>
                    </div>
                </div>

                <p>
                    <label><input type="checkbox" name="active" value="1" <?= field($record, 'active', '1') === '1' ? 'checked' : '' ?>> Datensatz öffentlich über QR-Link freigeben</label>
                </p>

                <div class="toolbar">
                    <button class="primary" type="submit">Speichern</button>
                    <a class="btn light" href="<?= e($link) ?>" target="_blank" rel="noopener">Öffentliche Ansicht</a>
                    <a class="btn light" href="admin.php">Zurück</a>
                </div>
            </form>
        </section>

        <?php if (!$isNew): ?>
        <section class="panel deletebox">
            <h2>Datensatz löschen</h2>
            <p class="hint">Zum Löschen den ICE-Key exakt eintragen: <span class="key"><?= e($formKey) ?></span></p>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="key" value="<?= e($formKey) ?>">
                <div class="field">
                    <label for="confirm">Löschbestätigung</label>
                    <input id="confirm" name="confirm" placeholder="ICE-Key hier eintragen">
                </div>
                <p><button class="danger" type="submit">Endgültig löschen</button></p>
            </form>
        </section>
        <?php endif; ?>

    <?php else: ?>
        <section class="panel">
            <div class="toolbar" style="justify-content:space-between">
                <div>
                    <h2>Datensätze</h2>
                    <p class="hint">Jede Person hat eine eigene JSON-Datei. Der QR-Code zeigt nur auf den jeweiligen Key.</p>
                </div>
                <a class="btn primary" href="admin.php?action=new">+ Neuer Datensatz</a>
            </div>
            <div class="mobile-table">
                <table class="records">
                    <thead><tr><th>Name</th><th>Geburtsdatum</th><th>Status</th><th>Key</th><th>Geändert</th><th>Aktion</th></tr></thead>
                    <tbody>
                    <?php $records = list_records(); ?>
                    <?php if ($records === []): ?>
                        <tr><td colspan="6">Noch keine Datensätze vorhanden.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($records as $item): ?>
                        <tr>
                            <td><b><?= e($item['name'] !== '' ? $item['name'] : 'Ohne Name') ?></b></td>
                            <td><?= e($item['date_of_birth']) ?></td>
                            <td><span class="pill <?= $item['active'] === '1' ? 'on' : 'off' ?>"><?= $item['active'] === '1' ? 'aktiv' : 'inaktiv' ?></span></td>
                            <td class="key"><?= e($item['key']) ?></td>
                            <td><?= e($item['updated_at']) ?></td>
                            <td>
                                <div class="split">
                                    <a class="btn light" href="<?= e(admin_edit_url($item['key'])) ?>">Bearbeiten</a>
                                    <a class="btn light" href="<?= e(public_url($item['key'])) ?>" target="_blank" rel="noopener">QR-Link</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</div>
</body>
</html>
