<?php
/**
 * index.php - Punto de entrada principal (Router)
 *
 * Soporta dos formatos de respuesta:
 *   - HTML (por defecto): Renderiza la vista completa del juego
 *   - JSON (?format=json): Retorna datos de sesion SIN modificar estado (READ ONLY)
 *
 * Flujo de juego:
 *   1. Usuario hace clic en GIRAR
 *   2. JS anima visualmente (puro frontend, sin llamadas al servidor)
 *   3. JS redirige a ?action=jugar
 *   4. Servidor procesa la tirada y renderiza la vista con el resultado real
 *
 * @package LotteryGame
 * @version 1.1
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/controlador/controller.php';
$controller = new Controller();

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'jugar') {
        $controller->jugar();
    } elseif ($_GET['action'] === 'guardar') {
        $controller->guardar();
    }
} else {
    $_SESSION['resultado'] = '¡A jugar!';
    $_SESSION['numeros'] = [1, 1, 1];
}

// Endpoint JSON: SOLO LECTURA, no modifica el estado del juego
// Esto permite que el frontend lea datos sin riesgo de perder puntos al refrescar
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'puntaje'  => intval($_SESSION['puntaje'] ?? 2000),
        'resultado' => $_SESSION['resultado'] ?? '¡A jugar!',
        'numeros'  => $_SESSION['numeros'] ?? [1, 1, 1]
    ]);
    exit;
}

require_once __DIR__ . '/view.php';
