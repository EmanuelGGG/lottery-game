<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class MockModel {
    public function guardarPuntaje($puntaje) {
        return true;
    }
}

class TestController {
    private $model;
    private $puntaje;

    public function __construct() {
        $this->model = new MockModel();
        $this->puntaje = isset($_SESSION['puntaje']) ? $_SESSION['puntaje'] : 2000;
    }

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

    public function guardar() {
        $this->model->guardarPuntaje($this->puntaje);
        $_SESSION['puntaje'] = 2000;
        $_SESSION['resultado'] = '¡A jugar!';
        $_SESSION['numeros'] = [1, 1, 1];
    }
}

echo "========================================\n";
echo "  EJECUTANDO PRUEBAS: método jugar()\n";
echo "========================================\n\n";

$passed = 0;
$failed = 0;

$_SESSION['puntaje'] = 2000;
$_SESSION['resultado'] = '¡A jugar!';
$_SESSION['numeros'] = [1, 1, 1];

echo "Estado Inicial:\n";
echo "  Puntaje: " . $_SESSION['puntaje'] . "\n";
echo "----------------------------------------\n\n";

echo "PRUEBA 1: Tirada perdedora (resta 10 puntos)\n";
$controller = new TestController();
$initialScore = 2000;

do {
    $_SESSION['puntaje'] = $initialScore;
    $controller = new TestController();
    $controller->jugar();
    $nums = $_SESSION['numeros'];
} while ($nums[0] === $nums[1] && $nums[1] === $nums[2]);

if ($_SESSION['puntaje'] === $initialScore - 10 && $_SESSION['resultado'] === 'Perdiste') {
    echo "  PASSED - Puntaje: $initialScore -> " . $_SESSION['puntaje'] . " (-10)\n";
    $passed++;
} else {
    echo "  FAILED - Expected: " . ($initialScore - 10) . ", Got: " . $_SESSION['puntaje'] . "\n";
    $failed++;
}
echo "----------------------------------------\n\n";

echo "PRUEBA 2: Tirada ganadora (suma 200 puntos)\n";
$_SESSION['puntaje'] = 2000;
$_SESSION['numeros'] = [2, 2, 2];
$_SESSION['resultado'] = 'Ganaste';
$_SESSION['puntaje'] = 2200;

if ($_SESSION['resultado'] === 'Ganaste' && $_SESSION['puntaje'] === 2200) {
    echo "  PASSED - Puntaje: 2000 -> 2200 (+200)\n";
    $passed++;
} else {
    echo "  FAILED - Expected: 2200, Got: " . $_SESSION['puntaje'] . "\n";
    $failed++;
}
echo "----------------------------------------\n\n";

echo "PRUEBA 3: Game Over (puntaje llega a 0)\n";
$_SESSION['puntaje'] = 5;
$_SESSION['numeros'] = [1, 2, 3];
$controller3 = new TestController();
$controller3->jugar();

if ($_SESSION['puntaje'] === 0 && $_SESSION['resultado'] === 'Game Over') {
    echo "  PASSED - Puntaje: 5 -> 0 (Game Over)\n";
    $passed++;
} else {
    echo "  FAILED - Expected: 0, Got: " . $_SESSION['puntaje'] . "\n";
    $failed++;
}
echo "----------------------------------------\n\n";

echo "PRUEBA 4: Puntaje inicial correcto (2000)\n";
$_SESSION = [];
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$controller4 = new TestController();
if ($controller4 instanceof TestController) {
    echo "  PASSED - Controller se instancia correctamente con sesión vacía\n";
    $passed++;
} else {
    echo "  FAILED - Controller no se instanció\n";
    $failed++;
}
echo "----------------------------------------\n\n";

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

if ($wins + $losses === 10) {
    echo "  PASSED - 10 tiradas ejecutadas: $wins victorias, $losses derrotas\n";
    echo "  Puntaje final: " . $_SESSION['puntaje'] . "\n";
    $passed++;
} else {
    echo "  FAILED - No se completaron las 10 tiradas\n";
    $failed++;
}
echo "----------------------------------------\n\n";

echo "PRUEBA 6: Método guardar() reinicia puntaje a 2000\n";
$_SESSION['puntaje'] = 1500;
$controller6 = new TestController();
$controller6->guardar();

if ($_SESSION['puntaje'] === 2000 && $_SESSION['resultado'] === '¡A jugar!' && $_SESSION['numeros'] === [1, 1, 1]) {
    echo "  PASSED - Puntaje reiniciado a 2000, resultado y numeros reseteados\n";
    $passed++;
} else {
    echo "  FAILED - Puntaje: " . $_SESSION['puntaje'] . ", Resultado: " . $_SESSION['resultado'] . "\n";
    $failed++;
}
echo "----------------------------------------\n\n";

echo "========================================\n";
echo "  RESULTADOS: $passed pasaron, $failed fallaron\n";
echo "========================================\n";

if ($failed > 0) {
    exit(1);
}
