<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="style.css">
    <title>Juego de Imágenes</title>
</head>
<body>
    <?php
    // Eliminar la línea de session_start()
    $puntaje = isset($_SESSION['puntaje']) ? $_SESSION['puntaje'] : 1000;
    $resultado = $_SESSION['resultado'] ?? '';
    $numeros = $_SESSION['numeros'] ?? [1, 1, 1];
    ?>
    
    <h1>Puntaje: <?php echo $puntaje; ?></h1>
    
    <div class="imagenes">
        <img src="imagenes/<?php echo $numeros[0]; ?>.jpg" />
        <img src="imagenes/<?php echo $numeros[1]; ?>.jpg" />
        <img src="imagenes/<?php echo $numeros[2]; ?>.jpg" />
    </div>
    
    <h2><?php echo $resultado; ?></h2>
    
    <?php if ($resultado === 'Game Over') { ?>
        <button onclick="location.href='index.php'">Vuelve a jugar</button>
    <?php } else { ?>
        <input type="button" value="Juguemos" onclick="location.href='index.php?action=jugar'">
        <input type="button" value="Guardar" onclick="location.href='index.php?action=guardar'">
    <?php } ?>
</body>
</html>
