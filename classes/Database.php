<?php

class Database
{
    private $username = "root";
    private $password = "";
    private $dbName = "ticketing-system";
    private $host = "localhost";
    private $conn = null;
    
    public function connect()
    {
      try {
          $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbName", $this->username, $this->password);
          // set the PDO error mode to exception
          $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
          require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'functions.php';
          logError("Database connection failed: ", [$e->getMessage()]);
          echo "Connection failed: " . $e->getMessage();
        }

      return $this->conn;
    }
}