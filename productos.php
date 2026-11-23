
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

require_once "conexion/conexion.php";

$mensaje = "";
$error = "";

$categorias = $conexion->query(
    "SELECT id_categoria, nombre
     FROM categoria
     WHERE estado = 1"
)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $codigo = trim($_POST["codigo"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = $_POST["precio"] ?? "";
    $stock_actual = $_POST["stock_actual"] ?? "";
    $stock_minimo = $_POST["stock_minimo"] ?? "";
    $id_categoria = $_POST["id_categoria"] ?? "";

    if ($codigo == "" || $nombre == "") {
        $error = "El código y el nombre son obligatorios.";
    } elseif (!is_numeric($precio) || $precio < 0) {
        $error = "El precio no puede ser negativo.";
    } elseif (
        filter_var($stock_actual, FILTER_VALIDATE_INT) === false ||
        $stock_actual < 0 ||
        filter_var($stock_minimo, FILTER_VALIDATE_INT) === false ||
        $stock_minimo < 0
    ) {
        $error = "Las cantidades deben ser números enteros no negativos.";
    } elseif (
        filter_var($id_categoria, FILTER_VALIDATE_INT) === false ||
        $id_categoria < 1
    ) {
        $error = "Selecciona una categoría válida.";
    } else {

        $consulta = $conexion->prepare(
            "SELECT id_categoria FROM categoria
             WHERE id_categoria = :id AND estado = 1"
        );

        $consulta->execute(["id" => $id_categoria]);

        if (!$consulta->fetch()) {
            $error = "La categoría seleccionada no existe.";
        } else {

            try {

                $sql = "INSERT INTO producto
                (codigo, nombre, descripcion, precio,
                 stock_actual, stock_minimo, id_categoria)
                VALUES
                (:codigo, :nombre, :descripcion, :precio,
                 :stock_actual, :stock_minimo, :id_categoria)";

                $consulta = $conexion->prepare($sql);

                $consulta->execute([
                    "codigo" => $codigo,
                    "nombre" => $nombre,
                    "descripcion" => $descripcion,
                    "precio" => $precio,
                    "stock_actual" => $stock_actual,
                    "stock_minimo" => $stock_minimo,
                    "id_categoria" => $id_categoria
                ]);

                $mensaje = "Producto registrado correctamente.";

            } catch (PDOException $e) {

                $error = "No se pudo registrar. Comprueba que el código no esté repetido.";

            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar producto - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">

    <h2>Registrar producto</h2>

    <?php if ($mensaje != ""): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <?php if ($error != ""): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="formProducto">

        <div class="mb-3">
            <label class="form-label">Código:</label>
            <input type="text" name="codigo" class="form-control"
                   maxlength="50" required
                   value="<?php echo htmlspecialchars($codigo ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Nombre:</label>
            <input type="text" name="nombre" class="form-control"
                   maxlength="100" required
                   value="<?php echo htmlspecialchars($nombre ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción:</label>
            <textarea name="descripcion" class="form-control"
                      maxlength="255"><?php echo htmlspecialchars($descripcion ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio:</label>
            <input type="number" name="precio" id="precio"
                   class="form-control" min="0" step="0.01"
                   required
                   value="<?php echo htmlspecialchars($precio ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Stock actual:</label>
            <input type="number" name="stock_actual" id="stock_actual"
                   class="form-control" min="0" step="1"
                   required
                   value="<?php echo htmlspecialchars($stock_actual ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Stock mínimo:</label>
            <input type="number" name="stock_minimo" id="stock_minimo"
                   class="form-control" min="0" step="1"
                   required
                   value="<?php echo htmlspecialchars($stock_minimo ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Categoría:</label>
            <select name="id_categoria" class="form-select" required>
                <option value="">Seleccionar categoría</option>

                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria["id_categoria"]; ?>"
                        <?php echo (isset($id_categoria) && $id_categoria == $categoria["id_categoria"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($categoria["nombre"]); ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            Registrar producto
        </button>

    </form>

</div>

<script>
document.getElementById("formProducto").addEventListener("submit", function(e) {

    const precio = Number(document.getElementById("precio").value);
    const stock = Number(document.getElementById("stock_actual").value);
    const minimo = Number(document.getElementById("stock_minimo").value);

    if (
        precio < 0 || stock < 0 || minimo < 0 ||
        !Number.isInteger(stock) || !Number.isInteger(minimo)
    ) {
        alert("Revisa el precio y las cantidades ingresadas.");
        e.preventDefault();
    }

});
</script>

</body>
</html>
