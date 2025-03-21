<?php
class Database {
    private $host = "localhost";
    private $db_name = "minlex_elsalvador";
    private $username = "root";
    private $password = "";
    private $conn = null;

    public function connect() {
        try {
            if ($this->conn === null) {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("SET NAMES utf8");
                error_log("Conexión exitosa a la base de datos");
            }
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }

    public function getConnection() {
        return $this->connect();
    }
}
?>
