<?php

class Database {
    private static ?PDO $instance = null;

    // Prevent direct instantiation
    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {}

    /**
     * Get the PDO connection instance (Singleton)
     *
     * @return PDO
     * @throws Exception
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                // Set the database session timezone to IST
                self::$instance->exec("SET time_zone = '+05:30'");
            } catch (PDOException $e) {
                throw new Exception("Database Connection Error: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
