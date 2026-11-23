
<?php
session_start();

require_once "conexion/conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $correo = trim($_POST["correo"]);
    $clave = $_POST["clave"];

    $sql = "SELECT * FROM usuario WHERE correo = :correo AND estado = 1";
    $consulta = $conexion->prepare($sql);
    $consulta->execute(["correo" => $correo]);

    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($clave, $usuario["clave"])) {

        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["nombre"] = $usuario["nombre"];

        header("Location: inicio.php");
        exit;

    } else {

        header("Location: index.php?error=1");
        exit;
    }

} else {
    header("Location: index.php");
    exit;
}
?>
