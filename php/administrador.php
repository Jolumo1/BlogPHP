<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

require_once './dbconnector.php';
$conexion = conectarDB();

// Obtener todos los artículos
if ($conexion !== false) {
    $sql = "SELECT id_articulo, titulo, estado, fecha_publicacion FROM articulos ORDER BY fecha_publicacion DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    desconectarDB($conexion);
} else {
    die("Error al conectar con la base de datos.");
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Administrador</title>
</head>

<body>
    <div class="header">
        <h3>Panel de Administración</h3>
    </div>
    <?php
    if (isset($_SESSION['success_message'])) {
        echo "<p class='exito'>" . htmlspecialchars($_SESSION['success_message']) . "</p>";
        unset($_SESSION['success_message']);
    }
    ?>
    <div class="row">
        <div class="leftcolumn">
            <div class="card">
                <h3>Listado de Artículos</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Estado</th>
                            <th>Publicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($articulos)): ?>
                            <?php foreach ($articulos as $articulo): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($articulo['id_articulo']); ?></td>
                                    <td class="col-titulo"><?php echo htmlspecialchars($articulo['titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($articulo['estado']); ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($articulo['fecha_publicacion'])); ?></td>
                                    <td>
                                        <a href="editar_articulo.php?id=<?php echo htmlspecialchars($articulo['id_articulo']); ?>" class="btn btn-edit">Editar</a>
                                        <a href="cambiar_estado.php?id=<?php echo $articulo['id_articulo']; ?>" class="btn btn-status">Cambiar Estado</a>
                                        <a href="eliminar_articulo.php?id=<?php echo $articulo['id_articulo']; ?>" onclick="return confirm('¿Estás seguro de eliminar este artículo?');" class="btn btn-delete">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No hay artículos disponibles.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>

        <div class="rightcolumn">
            <div class="card">
                <h3>Login administrador</h3>
                <?php

                if (isset($_SESSION['username'])) {
                ?>
                    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
                    <a href="./crear_articulo.php" class="admin-btn">Crear Nuevo Artículo</a><br>
                    <a href="./gestionar_categorias.php" class="admin-btn">Gestionar categorías</a><br>
                    <br>
                    <a href="./gestionar_administradores.php" class="admin-btn">Gestionar Administradores</a><br>
                    <br>
                    <a href="../index.php" class="admin-btn">Volver</a><br>
                    <a href="./logout.php" class="admin-btn">Cerrar sesión</a>
                <?php
                }
                ?>
            </div>
        </div>

    </div>
</body>

</html>