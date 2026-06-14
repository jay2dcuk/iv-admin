<?php
require_once __DIR__ . '/config.php';

class DB {
    private static ?PDO $pdo = null;

    public static function connect(): PDO {
        if (self::$pdo) return self::$pdo;
        try {
            $host = defined('DB_HOST') && DB_HOST ? DB_HOST : 'mysql.iv.digital';
            $name = defined('DB_NAME') && DB_NAME ? DB_NAME : 'ivhew_demo';
            $user = defined('DB_USER') && DB_USER ? DB_USER : 'ivhew_user';
            $pass = defined('DB_PASS') ? DB_PASS : 'ivhew_demo';

            self::$pdo = new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user, $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('DB Error: ' . $e->getMessage());
            throw new RuntimeException('Database connection failed: ' . $e->getMessage());
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $p = []): array {
        try {
            $s = self::connect()->prepare($sql); $s->execute($p); return $s->fetchAll();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public static function one(string $sql, array $p = []): ?array {
        try {
            $s = self::connect()->prepare($sql); $s->execute($p); return $s->fetch() ?: null;
        } catch (Exception $e) { error_log($e->getMessage()); return null; }
    }

    public static function run(string $sql, array $p = []): int {
        try {
            $s = self::connect()->prepare($sql); $s->execute($p); return $s->rowCount();
        } catch (Exception $e) { error_log($e->getMessage()); return 0; }
    }

    public static function insert(string $sql, array $p = []): int {
        try {
            $s = self::connect()->prepare($sql); $s->execute($p);
            return (int) self::connect()->lastInsertId();
        } catch (Exception $e) { error_log($e->getMessage()); return 0; }
    }
}
