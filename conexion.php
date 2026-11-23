
<?php
$host = "localhost";
$db = "tecnostock";
$user = "root";
$pass = "";

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

