
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

require_once "conexion/conexion.php";

$mensaje = "";

// Registrar movimiento
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_producto = $_POST["id_producto"] ?? "";
    $tipo = $_POST["tipo"] ?? "";
    $cantidad = filter_var($_POST["cantidad"] ?? "", FILTER_VALIDATE_INT);
    $observacion = trim($_POST["observacion"] ?? "");

    if (
        !filter_var($id_producto, FILTER_VALIDATE_INT) ||
        $id_producto <= 0 ||
        !in_array($tipo, ["entrada", "salida"]) ||
        $cantidad === false ||
        $cantidad <= 0
    ) {
        $mensaje = "Verifique los datos ingresados.";

    } else {

        try {
            $conexion->beginTransaction();

            $sql = "SELECT stock_actual FROM producto
                    WHERE id_producto = :id AND estado = 1
                    FOR UPDATE";

            $consulta = $conexion->prepare($sql);
            $consulta->execute(["id" => $id_producto]);

            $producto = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception("Producto no encontrado.");

            } else {

                $stock = $producto["stock_actual"];

                if ($tipo == "entrada") {
                    $nuevo_stock = $stock + $cantidad;
                } else {
                    $nuevo_stock = $stock - $cantidad;
                }

                if ($nuevo_stock < 0) {
                    throw new Exception("No hay suficiente stock para realizar la salida.");
                }

                $sql = "UPDATE producto
                        SET stock_actual = :stock
                        WHERE id_producto = :id";

                $consulta = $conexion->prepare($sql);
                $consulta->execute([
                    "stock" => $nuevo_stock,
                    "id" => $id_producto
                ]);

                $sql = "INSERT INTO movimiento
                        (tipo, cantidad, observacion, id_producto, id_usuario)
                        VALUES
                        (:tipo, :cantidad, :observacion, :producto, :usuario)";

                $consulta = $conexion->prepare($sql);
                $consulta->execute([
                    "tipo" => $tipo,
                    "cantidad" => $cantidad,
                    "observacion" => $observacion,
                    "producto" => $id_producto,
                    "usuario" => $_SESSION["id_usuario"]
                ]);

                $conexion->commit();

                $mensaje = "Movimiento registrado correctamente.";
            }

        } catch (Exception $e) {

            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $mensaje = $e->getMessage();
        }
    }
}

// Consultar productos
$sql = "SELECT id_producto, codigo, nombre, stock_actual
        FROM producto
        WHERE estado = 1
        ORDER BY nombre";

$productos = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Consultar movimientos
$sql = "SELECT m.*, p.nombre AS producto, u.nombre AS usuario
        FROM movimiento m
        INNER JOIN producto p ON m.id_producto = p.id_producto
        INNER JOIN usuario u ON m.id_usuario = u.id_usuario
        ORDER BY m.id_movimiento DESC";

$movimientos = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos - TecnoStock</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

    <h2>Movimientos de inventario</h2>

    <a href="inicio.php" class="btn btn-secondary mb-3">
        Volver al inicio
    </a>

    <a href="listar_productos.php" class="btn btn-primary mb-3">
        Ver productos
    </a>

    <?php if ($mensaje != ""): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <div class="card p-3 mb-4">

        <h4>Registrar movimiento</h4>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Producto</label>

                <select name="id_producto" class="form-select" required>
                    <option value="">Seleccione un producto</option>

                    <?php foreach ($productos as $producto): ?>
                        <option value="<?php echo $producto["id_producto"]; ?>">
                            <?php echo htmlspecialchars($producto["nombre"]); ?>
                            - Stock: <?php echo $producto["stock_actual"]; ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo de movimiento</label>

                <select name="tipo" class="form-select" required>
                    <option value="">Seleccione</option>
                    <option value="entrada">Entrada</option>
                    <option value="salida">Salida</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Cantidad</label>

                <input
                    type="number"
                    name="cantidad"
                    class="form-control"
                    min="1"
                    step="1"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Observación</label>

                <input
                    type="text"
                    name="observacion"
                    class="form-control"
                    maxlength="255"
                >
            </div>

            <button type="submit" class="btn btn-success">
                Registrar movimiento
            </button>

        </form>

    </div>

    <h4>Historial de movimientos</h4>

    <div class="table-responsive">

        <table class="table table-bordered table-striped bg-white">

            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Observación</th>
                    <th>Usuario</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($movimientos as $movimiento): ?>

                    <tr>
                        <td><?php echo htmlspecialchars($movimiento["fecha_hora"]); ?></td>
                        <td><?php echo htmlspecialchars($movimiento["producto"]); ?></td>
                        <td><?php echo htmlspecialchars($movimiento["tipo"]); ?></td>
                        <td><?php echo $movimiento["cantidad"]; ?></td>
                        <td><?php echo htmlspecialchars($movimiento["observacion"] ?? ""); ?></td>
                        <td><?php echo htmlspecialchars($movimiento["usuario"]); ?></td>
                    </tr>

                <?php endforeach; ?>

                <?php if (count($movimientos) == 0): ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            No hay movimientos registrados.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
