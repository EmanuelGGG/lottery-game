<?php
require_once __DIR__ . '/controlador/controller.php';
$controller = new Controller();

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'jugar') {
        $controller->jugar();
    } elseif ($_GET['action'] === 'guardar') {
        $controller->guardar();
    }
}

require_once __DIR__ . '/view.php';
?>
