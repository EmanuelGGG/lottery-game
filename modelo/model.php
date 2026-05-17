<?php
/**
 * model.php - Modelo de Acceso a Datos (Capa de Persistencia)
 *
 * Patrón MVC: Este archivo representa el Modelo.
 * Se encarga exclusivamente de:
 * - Establecer la conexión con la base de datos MySQL/MariaDB
 * - Ejecutar consultas SQL de forma segura usando prepared statements
 * - Abstraer la lógica de datos del controlador
 *
 * Configuración de conexión:
 * Las credenciales se leen de variables de entorno (getenv) para permitir
 * diferentes configuraciones en desarrollo local, Docker y producción.
 * Si la variable de entorno no existe, usa valores por defecto para XAMPP.
 *
 * @package LotteryGame
 * @version 1.1
 */

/**
 * Clase Model - Maneja la conexión y operaciones con la base de datos
 *
 * Propiedades:
 * - $conn: Objeto mysqli que representa la conexión activa a la BD
 *
 * Base de datos esperada:
 * - Database: 'juego'
 * - Tabla: 'puntajes' (id INT AUTO_INCREMENT, puntaje INT)
 */
class Model {
    private $conn;

    /**
     * Constructor: establece la conexión con la base de datos MySQL/MariaDB
     *
     * Lee las credenciales de variables de entorno en este orden de prioridad:
     * 1. Variables de entorno del sistema (getenv) - útil para Docker
     * 2. Valores por defecto - útil para desarrollo local con XAMPP
     *
     * Variables de entorno soportadas:
     * - DB_HOST: hostname del servidor MySQL (ej: 'localhost' o 'db' en Docker)
     * - DB_USER: usuario de la base de datos (ej: 'root')
     * - DB_PASS: contraseña del usuario (vacío por defecto en XAMPP)
     * - DB_NAME: nombre de la base de datos (ej: 'juego')
     *
     * Si la conexión falla, termina la ejecución con un mensaje de error.
     */
    public function __construct() {
        // Lee credenciales desde variables de entorno o usa valores por defecto
        // El operador ?: (null coalescing) devuelve el valor de getenv o el fallback
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $name = getenv('DB_NAME') ?: 'juego';

        // Establece conexión con MySQL usando la extensión mysqli
        // Parámetros: host, usuario, contraseña, nombre de la base de datos
        $this->conn = new mysqli($host, $user, $pass, $name);

        // Verifica si hubo error de conexión y detiene la ejecución si es así
        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        }
    }

    /**
     * Método guardarPuntaje() - Inserta un puntaje en la tabla 'puntajes'
     *
     * Usa prepared statements para prevenir inyección SQL:
     * 1. prepare(): compila la consulta SQL con un placeholder (?)
     * 2. bind_param(): vincula el valor del parámetro como entero ("i")
     * 3. execute(): ejecuta la consulta con el valor vinculado
     * 4. close(): libera los recursos del statement
     *
     * @param int $puntaje - El puntaje del jugador a guardar en la BD
     *
     * Nota: No retorna el ID generado ni verifica el resultado de la inserción.
     * En una versión futura podría retornar bool indicando éxito o fracaso.
     */
    public function guardarPuntaje($puntaje) {
        // Prepared statement: el '?' es un placeholder que se vincula de forma segura
        $stmt = $this->conn->prepare("INSERT INTO puntajes (puntaje) VALUES (?)");
        // Vincula el parámetro como entero ("i" = integer) para prevenir SQL injection
        $stmt->bind_param("i", $puntaje);
        // Ejecuta la consulta INSERT en la base de datos
        $stmt->execute();
        // Cierra el statement para liberar memoria
        $stmt->close();
    }
}
