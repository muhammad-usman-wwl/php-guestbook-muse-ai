<?php
/**
 * SDCC Guestbook — a tiny PHP + MySQL guestbook.
 *
 * One self-contained file: form, validation, PDO storage, listing.
 * No frameworks, no JavaScript. Run it with PHP's built-in server:
 *
 *   php -S localhost:8095
 *
 * Requires the `v5_guestbook` database — see schema.sql.
 */

// --- Database connection (PDO) -------------------------------------------
$pdo = new PDO(
    'mysql:host=127.0.0.1;port=3307;dbname=v5_guestbook;charset=utf8mb4',
    'root',      // local dev only — never hard-code credentials in production
    '',
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

// --- Handle the form ------------------------------------------------------
$name    = '';
$message = '';
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Server-side validation
    if ($name === '') {
        $errors['name'] = 'Please enter your name.';
    } elseif (mb_strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters long.';
    } elseif (mb_strlen($name) > 50) {
        $errors['name'] = 'Name must be no more than 50 characters long.';
    }

    if ($message === '') {
        $errors['message'] = 'Please write a message.';
    } elseif (mb_strlen($message) > 500) {
        $errors['message'] = 'Message must be no more than 500 characters long.';
    }

    // Only save when everything is valid
    if ($errors === []) {
        // Prepared statement: user input never touches the SQL itself
        $stmt = $pdo->prepare(
            'INSERT INTO entries (name, message) VALUES (:name, :message)'
        );
        $stmt->execute(['name' => $name, 'message' => $message]);

        // Clear the form and show a confirmation (prevents double-submit)
        $name = $message = '';
        $success = true;
    }
}

// --- Load entries, newest first -------------------------------------------
$entries = $pdo->query(
    'SELECT name, message, created_at FROM entries ORDER BY created_at DESC, id DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDCC Guestbook</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #f4f6f8; color: #1f2937;
            margin: 0; line-height: 1.5;
        }
        header { background: #111827; color: #fff; padding: 2rem 1rem; text-align: center; }
        header h1 { margin: 0; font-size: 1.8rem; }
        header p  { margin: .4rem 0 0; color: #9ca3af; }
        main { max-width: 640px; margin: 2rem auto; padding: 0 1rem 3rem; }
        form, .entries { background: #fff; border-radius: 10px; padding: 1.5rem;
                         box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        label { display: block; font-weight: 600; margin: 1rem 0 .3rem; }
        label:first-child { margin-top: 0; }
        input, textarea {
            width: 100%; padding: .6rem .75rem; font: inherit;
            border: 1px solid #d1d5db; border-radius: 6px;
        }
        input:focus, textarea:focus { outline: 2px solid #2563eb; border-color: #2563eb; }
        textarea { min-height: 110px; resize: vertical; }
        .error { color: #b91c1c; font-size: .9rem; margin: .3rem 0 0; }
        .field-error input, .field-error textarea { border-color: #dc2626; }
        button {
            margin-top: 1.25rem; padding: .65rem 1.5rem; font: inherit; font-weight: 600;
            color: #fff; background: #2563eb; border: 0; border-radius: 6px; cursor: pointer;
        }
        button:hover { background: #1d4ed8; }
        .success {
            background: #dcfce7; color: #166534; border-radius: 6px;
            padding: .75rem 1rem; margin-bottom: 1rem;
        }
        .entries { margin-top: 2rem; }
        .entries h2 { margin-top: 0; }
        .entry { border-top: 1px solid #e5e7eb; padding: 1rem 0; }
        .entry:first-of-type { border-top: 0; padding-top: 0; }
        .entry:last-child { padding-bottom: 0; }
        .entry strong { color: #111827; }
        .entry time { color: #6b7280; font-size: .85rem; }
        .empty { color: #6b7280; }
    </style>
</head>
<body>
    <header>
        <h1>SDCC Guestbook</h1>
        <p>Leave a message — built with plain PHP &amp; MySQL</p>
    </header>

    <main>
        <?php if ($success): ?>
            <p class="success">Thanks! Your entry has been saved.</p>
        <?php endif; ?>

        <form method="post" action="guestbook.php" novalidate>
            <div class="<?= isset($errors['name']) ? 'field-error' : '' ?>">
                <label for="name">Your name</label>
                <input type="text" id="name" name="name" maxlength="50"
                       value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['name'])): ?>
                    <p class="error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <div class="<?= isset($errors['message']) ? 'field-error' : '' ?>">
                <label for="message">Your message</label>
                <textarea id="message" name="message" maxlength="500"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php if (isset($errors['message'])): ?>
                    <p class="error"><?= htmlspecialchars($errors['message'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <button type="submit">Sign the guestbook</button>
        </form>

        <section class="entries">
            <h2>Entries (<?= count($entries) ?>)</h2>

            <?php if ($entries === []): ?>
                <p class="empty">No entries yet — be the first to sign!</p>
            <?php endif; ?>

            <?php foreach ($entries as $entry): ?>
                <article class="entry">
                    <strong><?= htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <time><?= htmlspecialchars($entry['created_at'], ENT_QUOTES, 'UTF-8') ?></time>
                    <p><?= nl2br(htmlspecialchars($entry['message'], ENT_QUOTES, 'UTF-8')) ?></p>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>
