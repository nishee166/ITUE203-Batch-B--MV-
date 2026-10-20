<?php
require_once __DIR__ . '/includes/helpers.php';

/* Intermediate extension: display stored CSV / JSON records on a web page */

$type   = ($_GET['type'] ?? 'contacts') === 'registrations' ? 'registrations' : 'contacts';
$source = ($_GET['source'] ?? 'json') === 'csv' ? 'csv' : 'json';

$path = STORAGE_DIR . '/' . $type . '.' . $source;
$rows = ($source === 'csv') ? read_csv_file($path) : read_json_file($path);
$readError = ($rows === null);
if ($readError) {
    $rows = [];
}

/* Column definitions: label => keys accepted from JSON or CSV (password is never shown) */
if ($type === 'contacts') {
    $title = 'Contact Messages';
    $columns = [
        'Name'         => ['name', 'Name'],
        'Email'        => ['email', 'Email'],
        'Subject'      => ['subject', 'Subject'],
        'Message'      => ['message', 'Message'],
        'Submitted At' => ['submitted_at', 'Submitted At']
    ];
} else {
    $title = 'Registered Students';
    $columns = [
        'Student ID'    => ['student_id', 'Student ID'],
        'Student Name'  => ['student_name', 'Student Name'],
        'Department'    => ['department', 'Department'],
        'Email'         => ['email', 'Email'],
        'Registered At' => ['registered_at', 'Registered At']
    ];
}

function pick($row, array $keys)
{
    foreach ($keys as $k) {
        if (isset($row[$k]) && $row[$k] !== '') {
            return $row[$k];
        }
    }
    return '';
}

$rows = array_reverse($rows);   // newest first
$qs = function ($t, $s) { return 'RECORDS.php?type=' . $t . '&source=' . $s; };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records - Student Hub Portal</title>
    <link rel="stylesheet" href="CONTACT.css">
    <link rel="stylesheet" href="php-forms.css">
</head>
<body>
<header>
    <a href="LOGIN.html" class="logout-btn">Log out</a>
    <h1>Student Hub Portal</h1>
    <p>Your One-Stop Learning Platform</p>
</header>
<nav>
    <a href="INDEX.html">Home</a> <a href="ABOUT.html">About</a> <a href="HACKATHON.html">Hackathons</a>
    <a href="STUDY.html">Study Materials</a> <a href="CODING.html">Coding Practice</a> <a href="EVENTS.html">Events</a>
    <a href="PORTFOLIO.html">Portfolio</a> <a href="CONTACT.php">Contact</a> <a href="FAQ.html">FAQ</a>
    <a href="PRACTICAL6.html">Live Data</a> <a href="RECORDS.php">Records</a>
</nav>

<section class="content">
<div class="box">

    <div class="contact-title">
        <div class="contact-icon">🗂️</div>
        <h2>Stored Records</h2>
        <p>Data saved by the PHP form processors (read from <?= e(strtoupper($source)) ?> file).</p>
    </div>

    <div class="records-tabs">
        <a class="<?= $type === 'contacts' ? 'active' : '' ?>" href="<?= e($qs('contacts', $source)) ?>">Contact Messages</a>
        <a class="<?= $type === 'registrations' ? 'active' : '' ?>" href="<?= e($qs('registrations', $source)) ?>">Registrations</a>
        <span class="records-sep"></span>
        <a class="<?= $source === 'json' ? 'active' : '' ?>" href="<?= e($qs($type, 'json')) ?>">JSON</a>
        <a class="<?= $source === 'csv' ? 'active' : '' ?>" href="<?= e($qs($type, 'csv')) ?>">CSV</a>
    </div>

    <h3><?= e($title) ?> (<?= count($rows) ?>)</h3>

    <?php if ($readError): ?>
        <div class="alert alert-error"><p>The <?= e(strtoupper($source)) ?> file could not be read or is damaged.</p></div>
    <?php elseif (empty($rows)): ?>
        <div class="alert alert-info"><p>No records have been submitted yet.</p></div>
    <?php else: ?>
        <div class="records-scroll">
            <table class="records-table">
                <tr>
                    <th>#</th>
                    <?php foreach (array_keys($columns) as $label): ?>
                        <th><?= e($label) ?></th>
                    <?php endforeach; ?>
                </tr>
                <?php foreach ($rows as $i => $row): ?>
                    <tr>
                        <td><?= count($rows) - $i ?></td>
                        <?php foreach ($columns as $label => $keys): ?>
                            <?php $val = pick($row, $keys); ?>
                            <td><?= $val === '' ? '&mdash;' : nl2br(e($val)) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>

</div>
</section>

<footer><p>&copy; 2026 Student Hub Portal | All Rights Reserved</p></footer>
</body>
</html>
