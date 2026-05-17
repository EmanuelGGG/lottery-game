<?php
// test_jugar.php
// Script automatizado para probar el método jugar()

// Iniciamos la sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simulamos que el puntaje de la sesión inicia en 2000
$_SESSION['puntaje'] = 2000;

try {
    // Si la conexión a BD (Model) falla porque no hay MySQL corriendo,
    // capturamos el escenario para que no rompa el test visual.
    require_once 'controller.php';
    
    echo "========================================\n";
    echo "  EJECUTANDO PRUEBAS: método jugar()\n";
    echo "========================================\n\n";
    
    $controller = new Controller();
    
    echo "Estado Inicial: \n";
    echo "Puntaje: " . $_SESSION['puntaje'] . "\n";
    echo "----------------------------------------\n";
    
    // Prueba de 3 tiradas automáticas
    for ($i = 1; $i <= 3; $i++) {
        $controller->jugar();
        
        $nums = $_SESSION['numeros'];
        echo "Tirada $i:\n";
        echo "Imágenes: [ " . implode(", ", $nums) . " ]\n";
        echo "Resultado: " . $_SESSION['resultado'] . "\n";
        echo "Puntaje actual: " . $_SESSION['puntaje'] . "\n";
        echo "----------------------------------------\n";
    }

    echo "Pruebas finalizadas con éxito.\n";
    
} catch (Exception $e) {
    echo "Advertencia: Asegúrate de tener MySQL ejecutándose para evitar errores de conexión al probar el Controlador completo.\n";
    echo "Detalle de error: " . $e->getMessage() . "\n";
}
?>
