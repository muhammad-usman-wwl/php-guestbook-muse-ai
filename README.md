# SDCC Guestbook

A tiny guestbook in **one PHP file** — a form, server-side validation,
MySQL storage with PDO prepared statements, and a newest-first listing
with XSS-safe output. No frameworks, no JavaScript.

Built as the live demo for the Simplified Dev Code Camp video
"Muse AI by Meta — I Let an AI Run My YouTube Channel".

## What it demonstrates

- **Form handling** — `$_POST`, sticky form values after a failed submit
- **Server-side validation** — name required, 2–50 chars; message required, 1–500 chars; inline error messages
- **PDO + prepared statements** — user input never touches the SQL (SQL-injection safe)
- **XSS-safe output** — every value printed with `htmlspecialchars()`
- **Newest-first listing** — `ORDER BY created_at DESC, id DESC`

## How to run it

All commands use the portable tools in `~/workspace/tools/` — nothing
is installed system-wide.

### 1. Start MariaDB (port 3307)

```bash
~/workspace/tools/mariadb-10.11.14-linux-systemd-x86_64/bin/mariadbd \
  --no-defaults --user=root \
  --datadir=$HOME/workspace/tools/mariadb-data \
  --socket=$HOME/workspace/tools/mariadb.sock \
  --pid-file=$HOME/workspace/tools/mariadb.pid \
  --port=3307 --bind-address=127.0.0.1
```

(The `--no-defaults` flag skips `/etc/mysql/my.cnf`, which references
plugins this portable build does not have.)

### 2. Create the database and table

```bash
~/workspace/tools/mariadb-10.11.14-linux-systemd-x86_64/bin/mariadb \
  -h 127.0.0.1 -P 3307 -u root < schema.sql
```

### 3. Serve the app

```bash
cd ~/workspace/youtube-simplified-dev-code-camp/video-05-muse/demo
~/workspace/tools/php-8.4/php -S localhost:8095
```

### 4. Open it

http://localhost:8095/guestbook.php

## Files

| File | Purpose |
| --- | --- |
| `guestbook.php` | The whole app: form + validation + PDO storage + listing |
| `schema.sql` | Creates database `v5_guestbook` and table `entries` |
| `README.md` | This file |
