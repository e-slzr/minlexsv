<?php
class Database {
    private $host = "localhost";
    private $database_name = "minlex_elsalvador";
    private $username = "root";
    private $password = "";
    private $port = 3306;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Intentar primero con socket
            try {
                $this->conn = new PDO(
                    "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=" . $this->database_name,
                    $this->username,
                    $this->password
                );
            } catch (PDOException $e) {
                // Si falla el socket, intentar con TCP
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->database_name,
                    $this->username,
                    $this->password
                );
            }

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("SET NAMES utf8");
            error_log("Conexión exitosa a la base de datos");
            return $this->conn;
        } catch (PDOException $exception) {
            error_log("Error de conexión a la base de datos: " . $exception->getMessage());
            throw new Exception("Error de conexión a la base de datos. Por favor verifica que XAMPP esté corriendo.");
        }
    }
}
?>
