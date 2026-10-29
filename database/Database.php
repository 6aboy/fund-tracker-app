<?php
class Database
{
    private $host = "localhost";
    private $port = "3307";
    private $username = "root";
    private $password = "";
    private $database = "fund_tracker";
    private $conn;

    public function __construct()
    {
        $local = is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];
        foreach (['host', 'port', 'username', 'password', 'database'] as $key) {
            $env = getenv('DB_' . strtoupper($key));
            $this->$key = $env !== false ? $env : ($local[$key] ?? $this->$key);
        }
        try {
            $db = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false,
            ];
            $this->conn = new PDO($db, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
