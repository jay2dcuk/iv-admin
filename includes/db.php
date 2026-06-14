<?php
require_once __DIR__ . '/config.php';

class DB {
    private static ?PDO $pdo = null;

    public static function connect(): PDO {
        if (self::$pdo) return self::$pdo;
        try {
            self::$pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('DB Error: ' . $e->getMessage());
            die(json_encode(['error' => 'Database connection failed']));
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $p = []): array {
        $s = self::connect()->prepare($sql);
        $s->execute($p);
        return $s->fetchAll();
    }

    public static function one(string $sql, array $p = []): ?array {
        $s = self::connect()->prepare($sql);
        $s->execute($p);
        return $s->fetch() ?: null;
    }

    public static function run(string $sql, array $p = []): int {
        $s = self::connect()->prepare($sql);
        $s->execute($p);
        return $s->rowCount();
    }

    public static function insert(string $sql, array $p = []): int {
        $s = self::connect()->prepare($sql);
        $s->execute($p);
        return (int) self::connect()->lastInsertId();
    }
}
