
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingreso - TecnoStock</title>
</head>
<body>

    <h2>Iniciar sesión - TecnoStock</h2>

    <?php
    if (isset($_GET["error"])) {
        echo "<p>Correo o contraseña incorrectos.</p>";
    }
    ?>

    <form action="validar_login.php" method="POST">

        <label>Correo electrónico:</label>
        <input type="email" name="correo" required>

        <br><br>

        <label>Contraseña:</label>
        <input type="password" name="clave" required>

        <br><br>

        <button type="submit">Ingresar</button>

    </form>

</body>
</html>

