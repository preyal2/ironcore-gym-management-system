<?php
/**
 * IRONCORE GYM MANAGEMENT SYSTEM
 * Database Connection & Auto-Bootstrap Configuration
 * Supports MySQL (Default for XAMPP) with seamless local SQLite fallback
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'ironcore_gym');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Returns 'mysql' or 'sqlite' based on active connection
 */
function db_driver(): string {
    static $driver = null;
    if ($driver !== null) return $driver;
    try {
        $pdo = get_db();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    } catch (Exception $e) {
        $driver = 'sqlite';
    }
    return $driver;
}

/**
 * Returns a SQL fragment for current date based on driver
 */
function sql_current_date(): string {
    return "date('now')";   // Works for both SQLite and MySQL (MySQL also accepts this)
}

/**
 * Returns SQL for date N days ago
 */
function sql_date_n_days_ago(int $days): string {
    if (db_driver() === 'sqlite') {
        return "date('now', '-{$days} days')";
    }
    return "DATE_SUB(CURRENT_DATE, INTERVAL {$days} DAY)";
}

/**
 * Returns SQL for date N months ago
 */
function sql_date_n_months_ago(int $months): string {
    if (db_driver() === 'sqlite') {
        return "date('now', '-{$months} months')";
    }
    return "DATE_SUB(CURRENT_DATE, INTERVAL {$months} MONTH)";
}

/**
 * Returns SQL for formatting date as YYYY-MM (month key)
 */
function sql_format_month(string $column): string {
    if (db_driver() === 'sqlite') {
        return "strftime('%Y-%m', {$column})";
    }
    return "DATE_FORMAT({$column}, '%Y-%m')";
}

/**
 * Returns SQL for DATEDIFF(end, start) giving days between
 */
function sql_datediff(string $end, string $start): string {
    if (db_driver() === 'sqlite') {
        return "CAST(julianday({$end}) - julianday({$start}) AS INTEGER)";
    }
    return "DATEDIFF({$end}, {$start})";
}

function get_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // 1. Try MySQL Connection
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // If database doesn't exist, attempt to create it on MySQL
        if (str_contains($e->getMessage(), 'Unknown database') || $e->getCode() == 1049) {
            try {
                $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
                $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                bootstrap_database($pdo, 'mysql');
                return $pdo;
            } catch (PDOException $inner) {
                // fall through to sqlite fallback
            }
        }

        // 2. Fallback to SQLite if MySQL is offline
        $sqliteFile = __DIR__ . '/ironcore_gym.sqlite';
        $needsBootstrap = !file_exists($sqliteFile) || filesize($sqliteFile) < 1024;
        $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec("PRAGMA foreign_keys = ON;");
        $pdo->exec("PRAGMA journal_mode = WAL;");
        if ($needsBootstrap) {
            bootstrap_database($pdo, 'sqlite');
        }
        return $pdo;
    }
}

function bootstrap_database(PDO $pdo, string $driver = 'mysql'): void {
    $sqlFile = dirname(__DIR__, 2) . '/database/ironcore_gym.sql';
    if (!file_exists($sqlFile)) {
        return;
    }

    $sqlContent = file_get_contents($sqlFile);

    if ($driver === 'sqlite') {
        // Transform MySQL-specific syntax for SQLite compatibility
        $sqlContent = preg_replace('/CREATE DATABASE[^;]*;/i', '', $sqlContent);
        $sqlContent = preg_replace('/USE\s+`?[^;`]+`?\s*;/i', '', $sqlContent);
        $sqlContent = preg_replace('/SET\s+FOREIGN_KEY_CHECKS[^;]*;/i', '', $sqlContent);
        $sqlContent = preg_replace('/ENGINE\s*=\s*\w+/i', '', $sqlContent);
        $sqlContent = preg_replace('/DEFAULT\s+CHARSET\s*=\s*\w+/i', '', $sqlContent);
        $sqlContent = preg_replace('/COLLATE\s*=\s*[\w_]+/i', '', $sqlContent);
        $sqlContent = preg_replace('/COLLATE\s+[\w_]+/i', '', $sqlContent);
        $sqlContent = preg_replace('/CHARACTER SET\s+\w+/i', '', $sqlContent);
        $sqlContent = preg_replace('/ON UPDATE CURRENT_TIMESTAMP/i', '', $sqlContent);
        $sqlContent = preg_replace('/COMMENT\s+\'[^\']*\'/i', '', $sqlContent);
        // Fix AUTO_INCREMENT -> AUTOINCREMENT (SQLite only allows INTEGER PRIMARY KEY AUTOINCREMENT)
        $sqlContent = preg_replace('/`(\w+)`\s+INT\s+AUTO_INCREMENT\s+PRIMARY KEY/i', '`$1` INTEGER PRIMARY KEY AUTOINCREMENT', $sqlContent);
        $sqlContent = preg_replace('/\bAUTO_INCREMENT\b/i', '', $sqlContent);
        // ENUM -> TEXT
        $sqlContent = preg_replace('/ENUM\([^)]+\)/i', 'TEXT', $sqlContent);
        // TINYINT(1) -> INTEGER
        $sqlContent = preg_replace('/TINYINT\(\d+\)/i', 'INTEGER', $sqlContent);
        // DECIMAL -> REAL
        $sqlContent = preg_replace('/DECIMAL\(\d+,\d+\)/i', 'REAL', $sqlContent);
        // Remove INDEX statements within CREATE TABLE (SQLite doesn't support inline KEY defs)
        $sqlContent = preg_replace('/,\s*(INDEX|KEY|UNIQUE KEY)\s+`[^`]*`\s*\([^)]*\)/i', '', $sqlContent);
        // Remove standalone CREATE INDEX calls if any
        $sqlContent = preg_replace('/CREATE\s+INDEX[^;]*;/i', '', $sqlContent);
        // Remove backticks for SQLite compatibility (optional but cleaner)
        // Keep backticks — SQLite supports them fine
    }

    // Execute statements one by one (required for SQLite, safe for MySQL too)
    execute_sql_statements($pdo, $sqlContent, $driver);
}

function execute_sql_statements(PDO $pdo, string $sqlContent, string $driver = 'mysql'): void {
    // Split on semicolons that are NOT inside string literals
    $statements = [];
    $current = '';
    $inString = false;
    $stringChar = '';
    $len = strlen($sqlContent);

    for ($i = 0; $i < $len; $i++) {
        $char = $sqlContent[$i];

        if ($inString) {
            $current .= $char;
            if ($char === $stringChar && ($i === 0 || $sqlContent[$i - 1] !== '\\')) {
                $inString = false;
            }
        } elseif ($char === "'" || $char === '"') {
            $inString = true;
            $stringChar = $char;
            $current .= $char;
        } elseif ($char === ';') {
            $stmt = trim($current);
            if (!empty($stmt) && $stmt !== '--') {
                $statements[] = $stmt;
            }
            $current = '';
        } elseif ($char === '-' && $i + 1 < $len && $sqlContent[$i + 1] === '-') {
            // Skip line comments
            while ($i < $len && $sqlContent[$i] !== "\n") {
                $i++;
            }
        } else {
            $current .= $char;
        }
    }
    $stmt = trim($current);
    if (!empty($stmt)) {
        $statements[] = $stmt;
    }

    foreach ($statements as $statement) {
        $clean = trim($statement);
        if (empty($clean) || substr($clean, 0, 2) === '--') {
            continue;
        }
        try {
            $pdo->exec($clean);
        } catch (PDOException $e) {
            // Ignore "already exists" and duplicate key errors during bootstrap
            $code = $e->getCode();
            $msg = $e->getMessage();
            if (
                str_contains($msg, 'already exists') ||
                str_contains($msg, 'duplicate') ||
                str_contains($msg, 'UNIQUE constraint') ||
                $code === '42S01' ||
                $code === '23000'
            ) {
                continue;
            }
            // Log but don't crash on other errors during bootstrap
            error_log("IronCore bootstrap warning [$driver]: " . $msg . " | SQL: " . substr($clean, 0, 80));
        }
    }
}
