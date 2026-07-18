<?php

declare(strict_types=1);

if ($argc < 3) {
    fwrite(STDERR, "Usage: php scripts/copy-sqlite-to-mysql.php <sqlite-file> <mysql-dsn> [username]\n");
    exit(1);
}

$sqlitePath = realpath($argv[1]);
if ($sqlitePath === false) {
    fwrite(STDERR, "SQLite database not found: {$argv[1]}\n");
    exit(1);
}

$username = $argv[3] ?? 'root';
$password = getenv('MYSQL_PASSWORD') ?: '';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
$sqlite = new PDO('sqlite:'.$sqlitePath, null, null, $options);
$mysql = new PDO($argv[2], $username, $password, $options);
$tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
    ->fetchAll(PDO::FETCH_COLUMN);
$quote = static fn (string $identifier): string => '`'.str_replace('`', '``', $identifier).'`';

$mysql->exec('SET FOREIGN_KEY_CHECKS = 0');

try {
    foreach ($tables as $table) {
        $exists = $mysql->prepare('SHOW TABLES LIKE ?');
        $exists->execute([$table]);
        if (! $exists->fetchColumn()) {
            fwrite(STDOUT, "Skipped {$table} (missing in MySQL)\n");

            continue;
        }

        $quotedTable = $quote($table);
        $mysql->exec("DELETE FROM {$quotedTable}");
        $rows = $sqlite->query("SELECT * FROM {$quotedTable}")->fetchAll(PDO::FETCH_ASSOC);

        if ($rows !== []) {
            $columns = array_keys($rows[0]);
            $columnSql = implode(', ', array_map($quote, $columns));
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $insert = $mysql->prepare("INSERT INTO {$quotedTable} ({$columnSql}) VALUES ({$placeholders})");
            foreach ($rows as $row) {
                $insert->execute(array_values($row));
            }
        }

        fwrite(STDOUT, sprintf("%-28s %d rows\n", $table, count($rows)));
    }
} finally {
    $mysql->exec('SET FOREIGN_KEY_CHECKS = 1');
}
