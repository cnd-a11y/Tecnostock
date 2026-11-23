
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

require_once "conexion/conexion.php";

// Desactivar producto
if (isset($_POST["desactivar"])) {

    $id_producto = (int) $_POST["desactivar"];

    if ($id_producto > 0) {

        $sql = "UPDATE producto
                SET estado = 0
                WHERE id_producto = :id
                AND estado = 1";

        $consulta = $conexion->prepare($sql);

        $consulta->execute([
            "id" => $id_producto
        ]);
    }

    header("Location: listar_productos.php");
    exit;
}

// Buscar productos
$buscar = trim($_GET["buscar"] ?? "");
$filtro = "%" . $buscar . "%";

$sql = "SELECT p.*, c.nombre AS categoria
        FROM producto p
        INNER JOIN categoria c
        ON p.id_categoria = c.id_categoria
        WHERE p.nombre LIKE :nombre
        OR p.codigo LIKE :codigo
        OR c.nombre LIKE :categoria
        ORDER BY p.id_producto DESC";

$consulta = $conexion->prepare($sql);

$consulta->execute([
    "nombre" => $filtro,
    "codigo" => $filtro,
    "categoria" => $filtro
]);

$productos = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Productos - TecnoStock</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

    <h2>Listado de productos</h2>

    <form method="GET" class="mb-3">

        <div class="input-group">

            <input
                type="text"
                name="buscar"
                class="form-control"
                placeholder="Buscar por nombre, código o categoría"
                value="<?php echo htmlspecialchars($buscar); ?>"
            >

            <button type="submit" class="btn btn-primary">
                Buscar
            </button>

            <a href="listar_productos.php" class="btn btn-secondary">
                Limpiar
            </a>

        </div>

    </form>

    <a href="productos.php" class="btn btn-success mb-3">
        Registrar producto
    </a>

    <div class="table-responsive">

        <table class="table table-bordered table-striped bg-white">

            <thead class="table-dark">

                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock actual</th>
                    <th>Stock mínimo</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>

            </thead>

            <tbody>

                <?php if (count($productos) > 0): ?>

                    <?php foreach ($productos as $producto): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($producto["codigo"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($producto["nombre"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($producto["categoria"]); ?>
                            </td>

                            <td>
                                $<?php echo number_format($producto["precio"], 0, ",", "."); ?>
                            </td>

                            <td>
                                <?php echo $producto["stock_actual"]; ?>
                            </td>

                            <td>
                                <?php echo $producto["stock_minimo"]; ?>
                            </td>

                            <td>

                                <?php if ($producto["estado"] == 1): ?>

                                    <?php if ($producto["stock_actual"] < $producto["stock_minimo"]): ?>

                                        <span class="badge bg-warning text-dark">
                                            Stock bajo
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-success">
                                            Disponible
                                        </span>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        Inactivo
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <a
                                    href="editar_producto.php?id=<?php echo (int) $producto['id_producto']; ?>"
                                    class="btn btn-warning btn-sm mb-1"
                                >
                                    Modificar
                                </a>

                                <?php if ($producto["estado"] == 1): ?>

                                    <form method="POST" style="display:inline;">

                                        <button
                                            type="submit"
                                            name="desactivar"
                                            value="<?php echo (int) $producto['id_producto']; ?>"
                                            class="btn btn-danger btn-sm mb-1"
                                            onclick="return confirm('¿Deseas desactivar este producto?');"
                                        >
                                            Desactivar
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8" class="text-center">
                            No se encontraron productos.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <p>
        Productos encontrados: <?php echo count($productos); ?>
    </p>

</div>

</body>
</html>
