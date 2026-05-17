<?php
class Model {
    private $conn;

    public function __construct() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $name = getenv('DB_NAME') ?: 'juego';

        $this->conn = new mysqli($host, $user, $pass, $name);
        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        }
    }

    public function guardarPuntaje($puntaje) {
        $stmt = $this->conn->prepare("INSERT INTO puntajes (puntaje) VALUES (?)");
        $stmt->bind_param("i", $puntaje);
        $stmt->execute();
        $stmt->close();
    }
}
