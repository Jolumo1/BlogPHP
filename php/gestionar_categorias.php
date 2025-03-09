<?php
session_start();
require_once './dbconnector.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit();
}

// Conectar a la base de datos
$conexion = conectarDB();
$mensaje = "";
$error = "";

// Procesar la creación de una nueva categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_categoria'])) {
    $nuevaCategoria = trim($_POST['nueva_categoria']);

    if (!empty($nuevaCategoria)) {
        try {
            $sqlInsert = "INSERT INTO categorias (nombre) VALUES (:nombre)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->bindParam(':nombre', $nuevaCategoria, PDO::PARAM_STR);
            $stmtInsert->execute();
            $mensaje = "Categoría creada correctamente.";
        } catch (Exception $e) {
            $error = "Error al crear la categoría: " . $e->getMessage();
        }
    } else {
        $error = "El nombre de la categoría no puede estar vacío.";
    }
}

// Procesar el cambio de nombre de una categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_categoria_id'])) {
    $idCategoria = $_POST['editar_categoria_id'];
    $nuevoNombre = trim($_POST['nuevo_nombre_categoria']);

    if (!empty($nuevoNombre)) {
        try {
            $sqlUpdate = "UPDATE categorias SET nombre = :nombre WHERE id_categoria = :id_categoria";
            $stmtUpdate = $conexion->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':nombre', $nuevoNombre, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':id_categoria', $idCategoria, PDO::PARAM_INT);
            $stmtUpdate->execute();
            $mensaje = "Categoría actualizada correctamente.";
        } catch (Exception $e) {
            $error = "Error al actualizar la categoría: " . $e->getMessage();
        }
    } else {
        $error = "El nombre de la categoría no puede estar vacío.";
    }
}

// Obtener todas las categorías
$categorias = [];
try {
    $sqlSelect = "SELECT id_categoria, nombre FROM categorias";
    $stmtSelect = $conexion->prepare($sqlSelect);
    $stmtSelect->execute();
    $categorias = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error al recuperar las categorías: " . $e->getMessage();
}

// Desconectar de la base de datos
desconectarDB($conexion);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>

</head>

<body>
    <div class="header">
        <h3>Panel de administrador - Gestión de Categorías</h3>
    </div>
    <div class="leftcolumn">
        <div class="form-container">
            <h2>Gestión de Categorías</h2>

            <?php if ($mensaje): ?>
                <p class="exito"><?php echo $mensaje; ?></p>
            <?php elseif ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <!-- Formulario para crear una nueva categoría -->
            <form action="gestionar_categorias.php" method="POST">
                <div class="form-group">
                    <label for="nueva_categoria">Nueva Categoría</label>
                    <input type="text" id="nueva_categoria" name="nueva_categoria" placeholder="Nombre de la categoría" required>
                </div>
                <div class="form-group">
                    <input type="submit" value="Añadir Categoría">
                </div>
            </form>

            <!-- Listado de categorías existentes -->
            <h3>Categorías existentes</h3>
            <?php if (!empty($categorias)): ?>
                <ul>
                    <?php foreach ($categorias as $categoria): ?>
                        <li class="categoria-item">
                            <label><?php echo htmlspecialchars($categoria['nombre']); ?></label>
                            <!-- Formulario para editar el nombre de la categoría -->
                            <form action="gestionar_categorias.php" method="POST" style="display:inline;">
                                <input type="hidden" name="editar_categoria_id" value="<?php echo $categoria['id_categoria']; ?>">
                                <input type="text" name="nuevo_nombre_categoria" placeholder="Nuevo nombre" required>
                                <input type="submit" value="Editar">
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No hay categorías disponibles.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="rightcolumn">
        <div class="card">
            <h3>Login administrador</h3>
            <?php
            if (isset($_SESSION['username'])) {
            ?>
                <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
                <a href="./administrador.php" class="admin-btn">Volver</a><br>
            <?php
            }
            ?>
        </div>
    </div>
</body>

</html>