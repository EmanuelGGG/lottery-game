<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Casino Royale - Juego de Lotería">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/style.css">
    <title>Casino Royale - Lotería</title>
</head>
<body>
    <div class="game-wrapper">
        <header class="casino-header">
            <h1 class="casino-title">
                <span class="title-icon">&#9824;</span>
                Casino Royale
                <span class="title-icon">&#9829;</span>
            </h1>
            <p class="casino-subtitle">¡Gira y gana la grande!</p>
        </header>

        <main class="game-container" role="main" aria-label="Juego de lotería">
            <?php
                $puntaje = isset($_SESSION['puntaje']) ? $_SESSION['puntaje'] : 2000;
                $resultado = $_SESSION['resultado'] ?? '¡A jugar!';
                $numeros = $_SESSION['numeros'] ?? [1, 1, 1];
            ?>

            <div class="score-board" aria-live="polite">
                <div class="score-label">
                    <svg class="coin-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                        <text x="12" y="16" text-anchor="middle" font-size="12" fill="currentColor">$</text>
                    </svg>
                    <h2>Tu Puntaje</h2>
                </div>
                <h1 class="score-value" id="scoreDisplay"><?php echo number_format($puntaje); ?></h1>
            </div>

            <div class="slot-machine-frame">
                <div class="machine-top-bar">
                    <div class="lights">
                        <span class="light"></span><span class="light"></span><span class="light"></span>
                        <span class="light"></span><span class="light"></span><span class="light"></span>
                        <span class="light"></span><span class="light"></span><span class="light"></span>
                    </div>
                </div>

                <div class="slot-machine" id="slotMachine" aria-label="Máquina tragamonedas">
                    <div class="reel-container">
                        <div class="reel-overlay"></div>
                        <div class="reel" id="reel1">
                            <div class="reel-strip">
                                <img src="public/imagenes/<?php echo $numeros[0]; ?>.jpg" alt="Símbolo 1" class="reel-symbol" />
                            </div>
                        </div>
                    </div>
                    <div class="reel-container">
                        <div class="reel-overlay"></div>
                        <div class="reel" id="reel2">
                            <div class="reel-strip">
                                <img src="public/imagenes/<?php echo $numeros[1]; ?>.jpg" alt="Símbolo 2" class="reel-symbol" />
                            </div>
                        </div>
                    </div>
                    <div class="reel-container">
                        <div class="reel-overlay"></div>
                        <div class="reel" id="reel3">
                            <div class="reel-strip">
                                <img src="public/imagenes/<?php echo $numeros[2]; ?>.jpg" alt="Símbolo 3" class="reel-symbol" />
                            </div>
                        </div>
                    </div>
                    <div class="win-line"></div>
                </div>

                <div class="machine-bottom-bar"></div>
            </div>

            <div class="result-container" aria-live="assertive">
                <div class="result-message <?php echo strtolower(str_replace(' ', '-', $resultado)); ?>" id="resultMessage">
                    <?php
                        if ($resultado === 'Ganaste') {
                            echo '&#127881; ¡GANASTE! +200 &#127881;';
                        } elseif ($resultado === 'Perdiste') {
                            echo 'Perdiste -10';
                        } elseif ($resultado === 'Game Over') {
                            echo 'GAME OVER';
                        } else {
                            echo '¡A jugar!';
                        }
                    ?>
                </div>
            </div>

            <div class="controls">
                <?php if ($resultado === 'Game Over') { ?>
                    <button class="btn btn-primary btn-lg" onclick="resetGame()" aria-label="Volver a jugar">
                        <span class="btn-icon">&#128260;</span> Vuelve a Jugar
                    </button>
                <?php } else { ?>
                    <button class="btn btn-action btn-lg" id="spinBtn" onclick="spinSlots()" aria-label="Girar tragamonedas">
                        <span class="btn-icon">&#127922;</span> ¡GIRAR!
                    </button>
                    <button class="btn btn-secondary" onclick="location.href='index.php?action=guardar'" aria-label="Guardar puntaje">
                        <span class="btn-icon">&#128190;</span> Guardar
                    </button>
                <?php } ?>
            </div>

            <div class="paytable">
                <h3>Tabla de Premios</h3>
                <div class="paytable-row">
                    <span class="pay-combo">3 Iguales</span>
                    <span class="pay-win">+200 puntos</span>
                </div>
                <div class="paytable-row">
                    <span class="pay-combo">Sin coincidencia</span>
                    <span class="pay-loss">-10 puntos</span>
                </div>
            </div>
        </main>

        <footer class="game-footer">
            <p>Casino Royale &copy; 2026 | Juega responsablemente</p>
        </footer>
    </div>

    <script>
        const symbols = [1, 2, 3];
        let isSpinning = false;

        function getRandomSymbol() {
            return symbols[Math.floor(Math.random() * symbols.length)];
        }

        function spinSlots() {
            if (isSpinning) return;
            isSpinning = true;

            const btn = document.getElementById('spinBtn');
            btn.disabled = true;
            btn.classList.add('spinning');
            btn.innerHTML = '<span class="btn-icon">&#9203;</span> Girando...';

            const reels = [
                document.getElementById('reel1'),
                document.getElementById('reel2'),
                document.getElementById('reel3')
            ];

            const results = [getRandomSymbol(), getRandomSymbol(), getRandomSymbol()];

            reels.forEach((reel, index) => {
                reel.classList.add('spinning');
                const strip = reel.querySelector('.reel-strip');

                let spinCount = 0;
                const maxSpins = 15 + (index * 8);
                const spinInterval = setInterval(() => {
                    const randomSym = getRandomSymbol();
                    strip.innerHTML = '<img src="public/imagenes/' + randomSym + '.jpg" alt="Girando" class="reel-symbol blur" />';
                    spinCount++;

                    if (spinCount >= maxSpins) {
                        clearInterval(spinInterval);
                        const finalSymbol = results[index];
                        strip.innerHTML = '<img src="public/imagenes/' + finalSymbol + '.jpg" alt="Símbolo ' + finalSymbol + '" class="reel-symbol" />';
                        reel.classList.remove('spinning');

                        if (index === 2) {
                            setTimeout(() => {
                                window.location.href = 'index.php?action=jugar';
                            }, 500);
                        }
                    }
                }, 60 + (index * 20));
            });
        }

        function resetGame() {
            window.location.href = 'index.php';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const scoreValue = document.getElementById('scoreDisplay');
            if (scoreValue) {
                scoreValue.classList.add('score-pop');
                setTimeout(() => scoreValue.classList.remove('score-pop'), 600);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space' && !isSpinning) {
                e.preventDefault();
                spinSlots();
            }
        });
    </script>
</body>
</html>
