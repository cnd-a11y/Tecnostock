
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

require_once "conexion/conexion.php";

$id_producto = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id_producto <= 0) {
    header("Location: listar_productos.php");
    exit;
}

$sql = "UPDATE producto
        SET estado = 0
        WHERE id_producto = :id";

$consulta = $conexion->prepare($sql);

$consulta->execute([
    "id" => $id_producto
]);

header("Location: listar_productos.php?desactivado=1");
exit;
?>
