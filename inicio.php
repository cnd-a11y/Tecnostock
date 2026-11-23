
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TecnoStock</title>
</head>
<body>

    <h2>Bienvenido a TecnoStock</h2>

    <p>Hola, <?php echo htmlspecialchars($_SESSION["nombre"]); ?></p>

    <a href="cerrar_sesion.php">Cerrar sesión</a>

</body>
</html>
