```php
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoStock</title>
</head>
<body>

    <h2>Bienvenido a TecnoStock</h2>

    <p>Hola, <?php echo htmlspecialchars($_SESSION["nombre"]); ?></p>

    <h3>Menú principal</h3>

    <p>
        <a href="listar_productos.php">Productos</a>
    </p>

    <p>
        <a href="movimientos.php">Movimientos</a>
    </p>

    <p>
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </p>

</body>
</html>

