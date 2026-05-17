<?php
class Model {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli('localhost', 'root', '', 'juego');
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
?>


