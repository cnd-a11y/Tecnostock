
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

require_once "conexion/conexion.php";

// Obtener el ID del producto
$id_producto = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_producto || $id_producto <= 0) {
    header("Location: listar_productos.php");
    exit;
}

// Consultar el producto
$sql = "SELECT * FROM producto WHERE id_producto = :id";
$consulta = $conexion->prepare($sql);
$consulta->execute(["id" => $id_producto]);

$producto = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header("Location: listar_productos.php");
    exit;
}

// Consultar las categorías
$sql_categorias = "SELECT * FROM categoria ORDER BY nombre";
$consulta_categorias = $conexion->prepare($sql_categorias);
$consulta_categorias->execute();

$categorias = $consulta_categorias->fetchAll(PDO::FETCH_ASSOC);

$error = "";

// Guardar los cambios
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = $_POST["precio"] ?? "";
    $stock_actual = $_POST["stock_actual"] ?? "";
    $stock_minimo = $_POST["stock_minimo"] ?? "";
    $id_categoria = $_POST["id_categoria"] ?? "";

    if (
        !is_numeric($precio) ||
        !is_numeric($stock_actual) ||
        !is_numeric($stock_minimo) ||
        !filter_var($id_categoria, FILTER_VALIDATE_INT) ||
        $precio < 0 ||
        !ctype_digit((string)$stock_actual) ||
        !ctype_digit((string)$stock_minimo) ||
        $stock_actual < 0 ||
        $stock_minimo < 0
    ) {
        $error = "Verifica los datos. Los valores no pueden ser negativos.";
    } else {

        // Comprobar que la categoría exista
        $sql_categoria = "SELECT COUNT(*) FROM categoria WHERE id_categoria = :id";
        $consulta_categoria = $conexion->prepare($sql_categoria);
        $consulta_categoria->execute(["id" => $id_categoria]);

        if ($consulta_categoria->fetchColumn() == 0) {
            $error = "La categoría seleccionada no existe.";
        } else {

            $sql_update = "UPDATE producto
                           SET descripcion = :descripcion,
                               precio = :precio,
                               stock_actual = :stock_actual,
                               stock_minimo = :stock_minimo,
                               id_categoria = :id_categoria
                           WHERE id_producto = :id";

            $actualizar = $conexion->prepare($sql_update);

            $actualizar->execute([
                "descripcion" => $descripcion,
                "precio" => $precio,
                "stock_actual" => $stock_actual,
                "stock_minimo" => $stock_minimo,
                "id_categoria" => $id_categoria,
                "id" => $id_producto
            ]);

            header("Location: listar_productos.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar producto - TecnoStock</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

    <h2>Modificar producto</h2>

    <?php if ($error != ""): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Código</label>
            <input
                type="text"
                class="form-control"
                value="<?php echo htmlspecialchars($producto["codigo"]); ?>"
                readonly
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input
                type="text"
                class="form-control"
                value="<?php echo htmlspecialchars($producto["nombre"]); ?>"
                readonly
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea
                name="descripcion"
                class="form-control"
                rows="3"
            ><?php echo htmlspecialchars($producto["descripcion"] ?? ""); ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select name="id_categoria" class="form-select" required>

                <?php foreach ($categorias as $categoria): ?>

                    <option
                        value="<?php echo $categoria["id_categoria"]; ?>"
                        <?php echo ($categoria["id_categoria"] == $producto["id_categoria"]) ? "selected" : ""; ?>
                    >
                        <?php echo htmlspecialchars($categoria["nombre"]); ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio</label>
            <input
                type="number"
                name="precio"
                class="form-control"
                min="0"
                step="0.01"
                value="<?php echo htmlspecialchars($producto["precio"]); ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Stock actual</label>
            <input
                type="number"
                name="stock_actual"
                class="form-control"
                min="0"
                step="1"
                value="<?php echo htmlspecialchars($producto["stock_actual"]); ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Stock mínimo</label>
            <input
                type="number"
                name="stock_minimo"
                class="form-control"
                min="0"
                step="1"
                value="<?php echo htmlspecialchars($producto["stock_minimo"]); ?>"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Guardar cambios
        </button>

        <a href="listar_productos.php" class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

</body>
</html>
