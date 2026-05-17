<?php
/**
 * controller.php - Controlador del Juego (Capa de Negocio)
 *
 * Patrón MVC: Este archivo representa el Controlador.
 * Se encarga de:
 * - Procesar las acciones del usuario (jugar, guardar)
 * - Aplicar la lógica de negocio del juego de lotería
 * - Calcular resultados y actualizar el puntaje
 * - Coordinar entre el Modelo (base de datos) y la Vista (interfaz)
 *
 * Reglas del juego:
 * - Se generan 3 números aleatorios entre 1 y 3
 * - Si los 3 son iguales: el jugador gana +200 puntos
 * - Si son diferentes: el jugador pierde -10 puntos
 * - Si el puntaje llega a 0: Game Over
 * - Puntaje inicial: 2000 puntos
 *
 * @package LotteryGame
 * @version 1.1
 */

// Carga el modelo que maneja la conexión y operaciones con la base de datos
require_once __DIR__ . '/../modelo/model.php';

/**
 * Clase Controller - Maneja la lógica principal del juego
 *
 * Propiedades:
 * - $model: Instancia del Model para acceso a base de datos
 * - $puntaje: Puntaje actual del jugador (almacenado también en sesión)
 */
class Controller {
    private $model;
    private $puntaje;

    /**
     * Constructor: inicializa el modelo y carga el puntaje desde la sesión
     *
     * Si no existe puntaje en sesión (primera visita), lo establece en 2000.
     * Esto garantiza que cada jugador nuevo comience con 2000 puntos.
     */
    public function __construct() {
        // Crea la conexión a la base de datos a través del modelo
        $this->model = new Model();
        // Recupera el puntaje de la sesión activa o usa el valor por defecto 2000
        $this->puntaje = isset($_SESSION['puntaje']) ? $_SESSION['puntaje'] : 2000;
    }

    /**
     * Método jugar() - Ejecuta una tirada de la máquina tragamonedas
     *
     * Proceso:
     * 1. Genera 3 números aleatorios entre 1 y 3 (mt_rand es más rápido que rand)
     * 2. Compara los 3 números para determinar si hay coincidencia
     * 3. Aplica la regla de puntuación correspondiente (+200 o -10)
     * 4. Verifica si el puntaje llegó a 0 (Game Over)
     * 5. Guarda todos los resultados en la sesión para que la vista los muestre
     *
     * Variables de sesión que actualiza:
     * - $_SESSION['puntaje']: Nuevo puntaje después de la tirada
     * - $_SESSION['resultado']: 'Ganaste', 'Perdiste' o 'Game Over'
     * - $_SESSION['numeros']: Array con los 3 números generados [n1, n2, n3]
     */
    public function jugar() {
        // Guardar puntaje anterior para que la vista lo muestre durante la animación
        // Esto evita que el jugador sepa el resultado antes de que paren los carretes
        $_SESSION['puntaje_anterior'] = $this->puntaje;

        // Genera 3 números aleatorios independientes entre 1 y 3
        // Cada número corresponde a un rodillo de la slot machine
        $numero1 = mt_rand(1, 3);
        $numero2 = mt_rand(1, 3);
        $numero3 = mt_rand(1, 3);

        // Evalúa si los 3 números son iguales (jackpot)
        if ($numero1 === $numero2 && $numero2 === $numero3) {
            // Jackpot: los 3 rodillos coinciden, bonificación de +200 puntos
            $this->puntaje += 200;
            $resultado = 'Ganaste';
        } else {
            // Sin coincidencia: penalización de -10 puntos
            $this->puntaje -= 10;
            $resultado = 'Perdiste';
        }

        // Verifica condición de Game Over: puntaje agotado
        if ($this->puntaje < 0) {
            $this->puntaje = 0;
            $resultado = 'Game Over';
        }

        // Persiste todos los resultados en la sesión HTTP
        // La vista (view.php) leerá estos valores para mostrarlos al usuario
        $_SESSION['puntaje'] = $this->puntaje;
        $_SESSION['resultado'] = $resultado;
        $_SESSION['numeros'] = [$numero1, $numero2, $numero3];
        
        // Bandera para indicar a la vista que debe ejecutar la animación de giro
        $_SESSION['animar'] = true;
    }

    /**
     * Método guardar() - Guarda el puntaje en la base de datos y reinicia el juego
     *
     * Proceso:
     * 1. Llama al modelo para insertar el puntaje actual en la tabla 'puntajes'
     * 2. Reinicia el puntaje a 2000 para una nueva partida
     * 3. Restablece el resultado y los números a sus valores iniciales
     *
     * Se usa cuando el jugador decide guardar su progreso antes de seguir jugando.
     */
    public function guardar() {
        // Inserta el puntaje actual en la tabla 'puntajes' de la base de datos
        $this->model->guardarPuntaje($this->puntaje);
        // Reinicia la sesión a los valores iniciales para una nueva partida
        $_SESSION['puntaje'] = 2000;
        $_SESSION['resultado'] = '¡A jugar!';
        $_SESSION['numeros'] = [1, 1, 1];
    }
}
