
<?php


define('STORAGE_DIR', dirname(__DIR__) . '/storage');
define('SESSION_TIMEOUT', 900);


if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax'
    ]);

    session_start();
}


function check_session_timeout()
{
    if (empty($_SESSION['student_id'])) {
        return;
    }

    if (
        isset($_SESSION['last_activity']) &&
        time() - (int) $_SESSION['last_activity'] > SESSION_TIMEOUT
    ) {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?: '/',
                'domain' => $params['domain'] ?? '',
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]);
        }

        session_destroy();

        header('Location: LOGIN.html?timeout=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
}


function regenerate_session_after_login()
{
    session_regenerate_id(true);
    $_SESSION['last_activity'] = time();
}

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}


function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' .
        e(csrf_token()) . '">';
}

function csrf_verify($submitted)
{
    return is_string($submitted)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $submitted);
}

function csrf_rotate()
{
    unset($_SESSION['csrf_token']);
}


function flash_set($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function flash_get()
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function clean_text($value, $multiline = false)
{
    $value = (string) $value;

    if (!mb_check_encoding($value, 'UTF-8')) {
        return '';
    }

    $value = strip_tags($value);
    $value = preg_replace('/[^\PC\s]/u', '', $value);

    if ($value === null) {
        return '';
    }

    if ($multiline) {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/[ \t]+/u', ' ', $value);
        $value = preg_replace("/\n{3,}/", "\n\n", $value);
    } else {
        $value = preg_replace('/\s+/u', ' ', $value);
    }

    return trim($value);
}

function csv_safe($value)
{
    $value = (string) $value;

    if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value)) {
        return "'" . $value;
    }

    return $value;
}


function save_record($base, array $record, array $columns)
{
    if (!is_dir(STORAGE_DIR) && !@mkdir(STORAGE_DIR, 0755, true)) {
        return 'The storage folder could not be created.';
    }

    if (!is_writable(STORAGE_DIR)) {
        return 'The storage folder is not writable.';
    }

    $lock = fopen(STORAGE_DIR . '/.lock', 'c');

    if ($lock === false) {
        return 'Could not open the storage lock file.';
    }

    if (!flock($lock, LOCK_EX)) {
        fclose($lock);
        return 'Could not lock the storage files. Please try again.';
    }

    $error = null;

    try {
       
        $jsonPath = STORAGE_DIR . '/' . $base . '.json';
        $rows = read_json_file($jsonPath);

        if ($rows === null) {
            $error = 'The existing JSON file is damaged and was not modified.';
        } else {
            $rows[] = $record;

            $encoded = json_encode(
                $rows,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            $tmpPath = $jsonPath . '.tmp';

            if (
                $encoded === false ||
                file_put_contents($tmpPath, $encoded, LOCK_EX) === false ||
                !rename($tmpPath, $jsonPath)
            ) {
                $error = 'Could not write the JSON file.';

                if (file_exists($tmpPath)) {
                    @unlink($tmpPath);
                }
            }
        }

       
        if ($error === null) {
            $csvPath = STORAGE_DIR . '/' . $base . '.csv';
            $handle = fopen($csvPath, 'a');

            if ($handle === false) {
                $error = 'Could not open the CSV file.';
            } else {
                $fileSize = filesize($csvPath);
                $fresh = ($fileSize === false || $fileSize === 0);
                $ok = true;

                if ($fresh) {
                    $ok = fputcsv($handle, array_values($columns)) !== false;
                }

                $line = [];

                foreach (array_keys($columns) as $key) {
                    $line[] = csv_safe($record[$key] ?? '');
                }

                $ok = $ok && fputcsv($handle, $line) !== false;
                fclose($handle);

                if (!$ok) {
                    $error = 'Could not write the CSV file.';
                }
            }
        }
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    return $error;
}

function read_json_file($path)
{
    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);

    if ($content === false) {
        return null;
    }

    if (trim($content) === '') {
        return [];
    }

    $data = json_decode($content, true);

    return is_array($data) ? $data : null;
}

function read_csv_file($path)
{
    if (!file_exists($path)) {
        return [];
    }

    $handle = fopen($path, 'r');

    if ($handle === false) {
        return null;
    }

    if (!flock($handle, LOCK_SH)) {
        fclose($handle);
        return null;
    }

    $header = fgetcsv($handle);
    $rows = [];

    if ($header !== false && $header !== null) {
        while (($line = fgetcsv($handle)) !== false) {
            if ($line === [null]) {
                continue;
            }

            $line = array_pad($line, count($header), '');

            $rows[] = array_combine(
                $header,
                array_slice($line, 0, count($header))
            );
        }
    }

    flock($handle, LOCK_UN);
    fclose($handle);

    return $rows;
}

function render_result_page(
    $title,
    $type,
    array $messages,
    $linkHref = '',
    $linkText = ''
) {
    $cls = ($type === 'success') ? 'alert-success' : 'alert-error';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?> - Student Hub Portal</title>
        <link rel="stylesheet" href="php-forms.css">
    </head>
    <body class="result-body">
    <header class="result-header">
        <h1>Student Hub Portal</h1>
        <p>Your One-Stop Learning Platform</p>
    </header>

    <main class="result-card">
        <div class="alert <?= $cls ?>">
            <h2><?= e($title) ?></h2>

            <?php foreach ($messages as $m): ?>
                <p><?= e($m) ?></p>
            <?php endforeach; ?>
        </div>

        <?php if ($linkHref !== ''): ?>
            <p class="result-link">
                <a href="<?= e($linkHref) ?>">
                    <?= e($linkText) ?>
                </a>
            </p>
        <?php endif; ?>
    </main>
    </body>
    </html>
    <?php
}
