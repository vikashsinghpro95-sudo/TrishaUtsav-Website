<?php
require_once __DIR__ . '/config/database.php';
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    $tables = ['orders', 'payments', 'payment_logs'];
    foreach ($tables as $table) {
        echo "Table: $table\n";
        try {
            $stmt = $pdo->query("SHOW CREATE TABLE $table");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            echo $row[1] . "\n\n";
        } catch (Exception $e) {
            echo "Does not exist: " . $e->getMessage() . "\n\n";
        }
    }
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
