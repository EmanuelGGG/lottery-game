<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/style.css">
    <title>Casino Royale - Lotería</title>
</head>
<body>
    <div class="game-container">
        <?php
        // Eliminar la línea de session_start()
        $puntaje = isset($_SESSION['puntaje']) ? $_SESSION['puntaje'] : 2000;
        $resultado = $_SESSION['resultado'] ?? '¡A jugar!';
        $numeros = $_SESSION['numeros'] ?? [1, 1, 1];
        ?>
        
        <div class="score-board">
            <h2>Puntaje</h2>
            <h1 class="score-value"><?php echo $puntaje; ?></h1>
        </div>
        
        <div class="imagenes slot-machine">
            <div class="slot"><img src="public/imagenes/<?php echo $numeros[0]; ?>.jpg" alt="Slot 1" /></div>
            <div class="slot"><img src="public/imagenes/<?php echo $numeros[1]; ?>.jpg" alt="Slot 2" /></div>
            <div class="slot"><img src="public/imagenes/<?php echo $numeros[2]; ?>.jpg" alt="Slot 3" /></div>
        </div>
        
        <h2 class="result-message <?php echo strtolower(str_replace(' ', '-', $resultado)); ?>"><?php echo $resultado; ?></h2>
        
        <div class="controls">
            <?php if ($resultado === 'Game Over') { ?>
                <button class="btn btn-primary" onclick="location.href='index.php'">Vuelve a jugar</button>
            <?php } else { ?>
                <button class="btn btn-action" onclick="location.href='index.php?action=jugar'">Juguemos</button>
                <button class="btn btn-secondary" onclick="location.href='index.php?action=guardar'">Guardar</button>
            <?php } ?>
        </div>
    </div>
</body>
</html>
