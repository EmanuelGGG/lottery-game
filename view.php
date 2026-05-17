<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Casino Royale - Juego de Loteria">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="public/style.css">
    <title>Casino Royale - Loteria</title>
</head>

<body>
    <div class="neon-bg"></div>

    <div class="game-wrapper">
        <header class="casino-header">
            <h1 class="casino-title">
                <i class="fa-solid fa-crown"></i>
                CASINO ROYALE
                <i class="fa-solid fa-crown"></i>
            </h1>
            <p class="casino-subtitle">GIRA Y GANA LA GRANDE</p>
        </header>

        <main class="game-container">
            <?php
            // Recuperar datos de la sesión
            $puntaje = isset($_SESSION['puntaje']) ? $_SESSION['puntaje'] : 2000;
            $resultado = $_SESSION['resultado'] ?? 'A jugar!';
            $numeros = $_SESSION['numeros'] ?? [1, 1, 1];
            
            // Verificar si venimos de una jugada y debemos animar
            $animar = $_SESSION['animar'] ?? false;
            unset($_SESSION['animar']); // Consumir la bandera para que no se repita al refrescar

            // Recuperar puntaje anterior para el efecto de suspenso
            $puntaje_anterior = $_SESSION['puntaje_anterior'] ?? $puntaje;
            unset($_SESSION['puntaje_anterior']);

            // Si hay que animar, los carretes inician con imágenes aleatorias
            // para que no se vea el resultado final antes de la animación
            $num1 = $animar ? mt_rand(1, 3) : $numeros[0];
            $num2 = $animar ? mt_rand(1, 3) : $numeros[1];
            $num3 = $animar ? mt_rand(1, 3) : $numeros[2];
            ?>

            <div class="score-panel">
                <div class="score-header"><i class="fa-solid fa-trophy"></i> TU PUNTAJE</div>
                <div class="score-value" id="scoreDisplay"><?php echo number_format($animar ? $puntaje_anterior : $puntaje); ?></div>
            </div>

            <div class="slot-machine-frame">
                <div class="cylinder-left"></div>
                <div class="cylinder-right"></div>

                <div class="reels-container">
                    <div class="reel-frame">
                        <div class="reel" id="reel1">
                            <div class="reel-strip">
                                <img src="public/imagenes/<?php echo $num1; ?>.jpg" alt="" class="reel-symbol" />
                            </div>
                        </div>
                    </div>
                    <div class="reel-frame">
                        <div class="reel" id="reel2">
                            <div class="reel-strip">
                                <img src="public/imagenes/<?php echo $num2; ?>.jpg" alt="" class="reel-symbol" />
                            </div>
                        </div>
                    </div>
                    <div class="reel-frame">
                        <div class="reel" id="reel3">
                            <div class="reel-strip">
                                <img src="public/imagenes/<?php echo $num3; ?>.jpg" alt="" class="reel-symbol" />
                            </div>
                        </div>
                    </div>
                    <div class="win-line"></div>
                </div>
            </div>

            <div class="controls">
                <?php if ($resultado === 'Game Over') { ?>
                    <button class="btn btn-reset" onclick="resetGame()">
                        <i class="fa-solid fa-rotate"></i> REINTENTAR
                    </button>
                <?php } else { ?>
                    <button class="btn btn-spin" id="spinBtn" onclick="location.href='index.php?action=jugar'">
                        <i class="fa-solid fa-rotate"></i> GIRAR
                    </button>
                    <button class="btn btn-save" onclick="location.href='index.php?action=guardar'">
                        <i class="fa-solid fa-floppy-disk"></i> GUARDAR
                    </button>
                <?php } ?>
            </div>

            <div class="result-container" id="resultContainer" <?php if ($animar) echo 'style="display:none;"'; ?>>
                <div class="result-message <?php echo strtolower(str_replace(' ', '-', $resultado)); ?>" id="resultMessage">
                    <?php
                    if ($resultado === 'Ganaste') {
                        echo '<i class="fa-solid fa-trophy"></i> GANASTE! +200 <i class="fa-solid fa-trophy"></i>';
                    } elseif ($resultado === 'Perdiste') {
                        echo '<i class="fa-solid fa-face-frown"></i> Perdiste -10';
                    } elseif ($resultado === 'Game Over') {
                        echo '<i class="fa-solid fa-skull"></i> GAME OVER';
                    } else {
                        echo '<i class="fa-solid fa-hand-pointer"></i> Presiona GIRAR';
                    }
                    ?>
                </div>
            </div>

            <div class="paytable">
                <div class="paytable-title"><i class="fa-solid fa-trophy"></i> TABLA DE PREMIOS</div>
                <div class="paytable-row">
                    <span class="pay-combo">777 3 Iguales</span>
                    <span class="pay-win">+200 pts</span>
                </div>
                <div class="paytable-row">
                    <span class="pay-combo">X Sin coincidencia</span>
                    <span class="pay-loss">-10 pts</span>
                </div>
            </div>
        </main>

        <footer class="game-footer">
            <p>Casino Royale &copy; 2026 | Juego responsablemente</p>
        </footer>
    </div>

    <script>
        var symbols = [1, 2, 3]; // Símbolos disponibles (imágenes 1, 2, 3)
        var isSpinning = false;   // Estado para evitar múltiples clics
        
        // Variables pasadas desde PHP para controlar la animación
        var shouldAnimate = <?php echo $animar ? 'true' : 'false'; ?>;
        var targetNumbers = <?php echo json_encode($numeros); ?>; // Resultado real

        // Función para obtener un símbolo aleatorio durante el giro
        function getRandomSymbol() {
            return symbols[Math.floor(Math.random() * symbols.length)];
        }

        // Función principal que inicia la animación de los carretes
        // Recibe un array con los 3 números en los que debe detenerse
        function spinSlots(targets) {
            if (isSpinning) return;
            isSpinning = true;

            var btn = document.getElementById('spinBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> GIRANDO...';
            }

            for (var i = 0; i < 3; i++) {
                document.getElementById('reel' + (i + 1)).classList.add('spinning');
                animateReel(i, 1500 + i * 1000, targets[i]); // Parando uno por uno
            }

            setTimeout(function() {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-rotate"></i> GIRAR';
                }
                isSpinning = false;

                // Mostrar mensaje de resultado
                var resultCont = document.getElementById('resultContainer');
                if (resultCont) {
                    resultCont.style.display = 'flex';
                }

                // Actualizar puntaje visualmente
                var scoreDisp = document.getElementById('scoreDisplay');
                if (scoreDisp) {
                    scoreDisp.innerHTML = '<?php echo number_format($puntaje); ?>';
                    scoreDisp.classList.add('pop');
                    setTimeout(function() {
                        scoreDisp.classList.remove('pop');
                    }, 600);
                }
            }, 4000);
        }

        // Función que anima un carrete individual cambiando imágenes al azar
        // y se detiene en el targetSymbol al completar la duración
        function animateReel(index, duration, targetSymbol) {
            var reel = document.getElementById('reel' + (index + 1));
            var strip = reel.querySelector('.reel-strip');
            var start = Date.now();

            function tick() {
                var elapsed = Date.now() - start;
                if (elapsed >= duration) {
                    // Fijar el símbolo real al final
                    strip.innerHTML = '<img src="public/imagenes/' + targetSymbol + '.jpg" alt="" class="reel-symbol" />';
                    reel.classList.remove('spinning');
                    return;
                }
                var sym = getRandomSymbol();
                strip.innerHTML = '<img src="public/imagenes/' + sym + '.jpg" alt="" class="reel-symbol blur" />';
                setTimeout(tick, 50 + (elapsed / duration) * 80);
            }
            tick();
        }

        function resetGame() {
            window.location.href = 'index.php';
        }

        document.addEventListener('DOMContentLoaded', function() {
            var sv = document.getElementById('scoreDisplay');
            if (sv) {
                sv.classList.add('pop');
                setTimeout(function() {
                    sv.classList.remove('pop');
                }, 600);
            }

            // Si viene de una jugada, arranca la animación
            if (shouldAnimate) {
                spinSlots(targetNumbers);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.code === 'Space' && !isSpinning && !document.getElementById('spinBtn').disabled) {
                e.preventDefault();
                location.href = 'index.php?action=jugar';
            }
        });
    </script>
</body>

</html>