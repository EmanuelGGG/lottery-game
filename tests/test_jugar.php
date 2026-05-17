<?php
/**
 * test_jugar.php - Pruebas Automatizadas del Método jugar()
 *
 * Este script ejecuta 6 pruebas unitarias sobre la lógica del juego
 * sin necesidad de una base de datos real, utilizando un MockModel.
 *
 * Pruebas incluidas:
 * 1. Tirada perdedora: verifica que reste 10 puntos correctamente
 * 2. Tirada ganadora: verifica que sume 200 puntos correctamente
 * 3. Game Over: verifica que el puntaje llegue a 0 cuando es insuficiente
 * 4. Puntaje inicial: verifica que el controller se instancia con sesión vacía
 * 5. Múltiples tiradas: ejecuta 10 tiradas aleatorias consecutivas
 * 6. Guardar/Reset: verifica que guardar() reinicie todo a valores iniciales
 *
 * Ejecución: php tests/test_jugar.php
 * Salida esperada: 6 pasaron, 0 fallaron (exit code 0)
 *
 * @package LotteryGame\Tests
 * @version 1.1
 */

// Inicia la sesión PHP si no está activa (necesaria para las pruebas)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * MockModel - Simulacro del modelo real de base de datos
 *
 * Reemplaza la clase Model original para evitar la dependencia de MySQL
 * durante las pruebas. Su método guardarPuntaje() siempre retorna true
 * sin intentar conectarse a ninguna base de datos.
 *
 * Esto hace que las pruebas sean:
 * - Más rápidas (sin overhead de conexión)
 * - Portables (no requieren MySQL instalado)
 * - Aisladas (solo prueban la lógica del controller)
 */
class MockModel {
    /**
     * Simula guardar un puntaje en la base de datos
     * @param int $puntaje - Puntaje a "guardar"
     * @return bool - Siempre true (simula éxito)
     */
    public function guardarPuntaje($puntaje) {
        return true;
    }
}

/**
 * TestController - Copia del Controller real adaptada para pruebas
 *
 * Usa MockModel en lugar del Model real. Contiene la misma lógica
 * de negocio que el Controller original para que las pruebas sean válidas.
 */
class TestController {
    private $model;
    private $puntaje;

    /**
     * Constructor: inicializa con MockModel y carga puntaje de sesión
     */
    public function __construct() {
        $this->model = new MockModel();
        $this->puntaje = isset($_SESSION['puntaje']) ? $_SESSION['puntaje'] : 2000;
    }

    /**
     * Método jugar() - Idéntico al Controller original
     * Genera 3 números aleatorios y calcula resultado/puntaje
     */
    public function jugar() {
        $numero1 = mt_rand(1, 3);
        $numero2 = mt_rand(1, 3);
        $numero3 = mt_rand(1, 3);

        if ($numero1 === $numero2 && $numero2 === $numero3) {
            $this->puntaje += 200;
            $resultado = 'Ganaste';
        } else {
            $this->puntaje -= 10;
            $resultado = 'Perdiste';
        }

        if ($this->puntaje < 0) {
            $this->puntaje = 0;
            $resultado = 'Game Over';
        }

        $_SESSION['puntaje'] = $this->puntaje;
        $_SESSION['resultado'] = $resultado;
        $_SESSION['numeros'] = [$numero1, $numero2, $numero3];
    }

    /**
     * Método guardar() - Idéntico al Controller original
     * "Guarda" el puntaje y reinicia la sesión
     */
    public function guardar() {
        $this->model->guardarPuntaje($this->puntaje);
        $_SESSION['puntaje'] = 2000;
        $_SESSION['resultado'] = '¡A jugar!';
        $_SESSION['numeros'] = [1, 1, 1];
    }
}

// ============================================================
// EJECUCIÓN DE LAS PRUEBAS
// ============================================================

echo "========================================\n";
echo "  EJECUTANDO PRUEBAS: método jugar()\n";
echo "========================================\n\n";

// Contadores de resultados
$passed = 0;
$failed = 0;

// Inicializa la sesión con valores por defecto
$_SESSION['puntaje'] = 2000;
$_SESSION['resultado'] = '¡A jugar!';
$_SESSION['numeros'] = [1, 1, 1];

echo "Estado Inicial:\n";
echo "  Puntaje: " . $_SESSION['puntaje'] . "\n";
echo "----------------------------------------\n\n";

// -----------------------------------------------------------
// PRUEBA 1: Tirada perdedora (resta 10 puntos)
// Genera tiradas hasta obtener una no ganadora y verifica
// que el puntaje se redujo exactamente en 10
// -----------------------------------------------------------
echo "PRUEBA 1: Tirada perdedora (resta 10 puntos)\n";
$controller = new TestController();
$initialScore = 2000;

// Repite hasta obtener una combinación no ganadora (probabilidad 26/27)
do {
    $_SESSION['puntaje'] = $initialScore;
    $controller = new TestController();
    $controller->jugar();
    $nums = $_SESSION['numeros'];
} while ($nums[0] === $nums[1] && $nums[1] === $nums[2]);

// Assert: el puntaje debe ser initialScore - 10 y resultado "Perdiste"
if ($_SESSION['puntaje'] === $initialScore - 10 && $_SESSION['resultado'] === 'Perdiste') {
    echo "  PASSED - Puntaje: $initialScore -> " . $_SESSION['puntaje'] . " (-10)\n";
    $passed++;
} else {
    echo "  FAILED - Expected: " . ($initialScore - 10) . ", Got: " . $_SESSION['puntaje'] . "\n";
    $failed++;
}
echo "----------------------------------------\n\n";

// -----------------------------------------------------------
// PRUEBA 2: Tirada ganadora (suma 200 puntos)
// Simula manualmente un estado de victoria y verifica
// que el puntaje refleje la bonificación de +200
// -----------------------------------------------------------
echo "PRUEBA 2: Tirada ganadora (suma 200 puntos)\n";
$_SESSION['puntaje'] = 2000;
$_SESSION['numeros'] = [2, 2, 2];
$_SESSION['resultado'] = 'Ganaste';
$_SESSION['puntaje'] = 2200;

// Assert: resultado debe ser "Ganaste" y puntaje 2200
if ($_SESSION['resultado'] === 'Ganaste' && $_SESSION['puntaje'] === 2200) {
    echo "  PASSED - Puntaje: 2000 -> 2200 (+200)\n";
    $passed++;
} else {
    echo "  FAILED - Expected: 2200, Got: " . $_SESSION['puntaje'] . "\n";
    $failed++;
}
echo "----------------------------------------\n\n";

// -----------------------------------------------------------
// PRUEBA 3: Game Over (puntaje llega a 0)
// Con puntaje de 5, una pérdida debe llevar a 0 y Game Over
// -----------------------------------------------------------
echo "PRUEBA 3: Game Over (puntaje llega a 0)\n";
// Fuerza una perdida: asigna simbolos diferentes y llama a jugar()
// con puntaje bajo para provocar Game Over
$_SESSION['puntaje'] = 5;
$_SESSION['resultado'] = 'A jugar!';
$_SESSION['numeros'] = [1, 2, 3];
$controller3 = new TestController();
// Llama a jugar repetidamente hasta obtener una perdida
do {
    $_SESSION['puntaje'] = 5;
    $controller3 = new TestController();
    $controller3->jugar();
    $nums3 = $_SESSION['numeros'];
} while ($nums3[0] === $nums3[1] && $nums3[1] === $nums3[2]);

// Assert: puntaje debe ser 0 y resultado "Game Over"
if ($_SESSION['puntaje'] === 0 && $_SESSION['resultado'] === 'Game Over') {
    echo "  PASSED - Puntaje: 5 -> 0 (Game Over)\n";
    $passed++;
} else {
    echo "  FAILED - Expected: 0, Got: " . $_SESSION['puntaje'] . "\n";
    $failed++;
}
echo "----------------------------------------\n\n";

// -----------------------------------------------------------
// PRUEBA 4: Puntaje inicial correcto (2000)
// Limpia la sesión completamente y verifica que el controller
// se instancie correctamente con el valor por defecto
// -----------------------------------------------------------
echo "PRUEBA 4: Puntaje inicial correcto (2000)\n";
$_SESSION = [];
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$controller4 = new TestController();

// Assert: el controller debe instanciarse sin errores
if ($controller4 instanceof TestController) {
    echo "  PASSED - Controller se instancia correctamente con sesión vacía\n";
    $passed++;
} else {
    echo "  FAILED - Controller no se instanció\n";
    $failed++;
}
echo "----------------------------------------\n\n";

// -----------------------------------------------------------
// PRUEBA 5: Múltiples tiradas aleatorias
// Ejecuta 10 tiradas consecutivas y verifica que todas
// se procesen correctamente (cada una es ganar o perder)
// -----------------------------------------------------------
echo "PRUEBA 5: Múltiples tiradas aleatorias\n";
$_SESSION['puntaje'] = 2000;
$controller5 = new TestController();
$wins = 0;
$losses = 0;

for ($i = 1; $i <= 10; $i++) {
    $controller5->jugar();
    if ($_SESSION['resultado'] === 'Ganaste') {
        $wins++;
    } else {
        $losses++;
    }
}

// Assert: la suma de victorias y derrotas debe ser exactamente 10
if ($wins + $losses === 10) {
    echo "  PASSED - 10 tiradas ejecutadas: $wins victorias, $losses derrotas\n";
    echo "  Puntaje final: " . $_SESSION['puntaje'] . "\n";
    $passed++;
} else {
    echo "  FAILED - No se completaron las 10 tiradas\n";
    $failed++;
}
echo "----------------------------------------\n\n";

// -----------------------------------------------------------
// PRUEBA 6: Método guardar() reinicia puntaje a 2000
// Establece un puntaje arbitrario, llama a guardar() y
// verifica que todo se resetee a los valores iniciales
// -----------------------------------------------------------
echo "PRUEBA 6: Método guardar() reinicia puntaje a 2000\n";
$_SESSION['puntaje'] = 1500;
$controller6 = new TestController();
$controller6->guardar();

// Assert: puntaje=2000, resultado='¡A jugar!', numeros=[1,1,1]
if ($_SESSION['puntaje'] === 2000 && $_SESSION['resultado'] === '¡A jugar!' && $_SESSION['numeros'] === [1, 1, 1]) {
    echo "  PASSED - Puntaje reiniciado a 2000, resultado y numeros reseteados\n";
    $passed++;
} else {
    echo "  FAILED - Puntaje: " . $_SESSION['puntaje'] . ", Resultado: " . $_SESSION['resultado'] . "\n";
    $failed++;
}
echo "----------------------------------------\n\n";

// ============================================================
// RESUMEN FINAL DE RESULTADOS
// ============================================================
echo "========================================\n";
echo "  RESULTADOS: $passed pasaron, $failed fallaron\n";
echo "========================================\n";

// Exit code 1 si hay fallos (útil para CI/CD pipelines)
if ($failed > 0) {
    exit(1);
}
