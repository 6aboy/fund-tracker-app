<?php
class Database{
    private $host = "127.0.0.1";
    private $port = "3306";
    private $username = "root";
    private $password = "";
    private $database = "fund_tracker";
    private $conn;

    public function __construct(){
        try{
            $db = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4"; 
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false,
            ];  
            $this->conn = new PDO($db, $this->username, $this->password, $options);
        }
        catch (PDOException $e){
            die("Connection failed: " . $e->getMessage()); 
        }
    }

    public function getConnection(){
        return $this->conn;
    }
}
