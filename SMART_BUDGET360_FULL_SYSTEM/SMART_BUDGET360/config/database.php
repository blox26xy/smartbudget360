<?php
class Database
{
    private string $host = '127.0.0.1';
    private string $database = 'smart_budget360';
    private string $username = 'root';
    private string $password = '';
    private string $charset = 'utf8mb4';
    private ?PDO $connection = null;

    public function connect(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database connection failed. Please import database/smart_budget360.sql and verify config/database.php.');
        }

        return $this->connection;
    }
}
