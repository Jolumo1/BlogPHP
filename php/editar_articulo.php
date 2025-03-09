<?php
session_start();
require_once 'dbconnector.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

// Conectar a la base de datos
$conexion = conectarDB();

// Verificar si se ha pasado un ID de artículo
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de artículo no válido.";
    exit;
}

$idArticulo = intval($_GET['id']);

// Obtener el artículo, la imagen asociada y las categorías relacionadas
$sqlArticulo = "SELECT * FROM articulos WHERE id_articulo = :id";
$sqlImagen = "SELECT * FROM imagenes WHERE id_articulo = :id LIMIT 1";
$sqlCategorias = "SELECT c.id_categoria, c.nombre, 
                         ac.id_articulo AS relacionado 
                  FROM categorias c 
                  LEFT JOIN articulo_categoria ac 
                  ON c.id_categoria = ac.id_categoria 
                  AND ac.id_articulo = :id";

$stmtArticulo = $conexion->prepare($sqlArticulo);
$stmtArticulo->bindParam(':id', $idArticulo, PDO::PARAM_INT);
$stmtArticulo->execute();
$articulo = $stmtArticulo->fetch(PDO::FETCH_ASSOC);

$stmtImagen = $conexion->prepare($sqlImagen);
$stmtImagen->bindParam(':id', $idArticulo, PDO::PARAM_INT);
$stmtImagen->execute();
$imagen = $stmtImagen->fetch(PDO::FETCH_ASSOC);

$stmtCategorias = $conexion->prepare($sqlCategorias);
$stmtCategorias->bindParam(':id', $idArticulo, PDO::PARAM_INT);
$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

// Si no existe el artículo
if (!$articulo) {
    echo "El artículo no existe.";
    exit;
}

// Procesar la actualización del artículo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];
    $resumen = $_POST['resumen'];
    $estado = $_POST['estado'];
    $categoriasSeleccionadas = $_POST['categorias'] ?? [];
    $mensajeExito = '';

    // Manejar la subida de imagen
    $nombreImagen = $imagen['url'] ?? ''; // Imagen actual por defecto

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $directorioSubida = '../images/';
        $nombreTemporal = $_FILES['imagen']['tmp_name'];
        $nombreArchivo = basename($_FILES['imagen']['name']);
        $extensionArchivo = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        // Validar la extensión
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($extensionArchivo, $extensionesPermitidas)) {
            $nombreImagenNueva = $directorioSubida . uniqid('img_', true) . '.' . $extensionArchivo;
            if (move_uploaded_file($nombreTemporal, $nombreImagenNueva)) {
                // Eliminar la imagen anterior si existe
                if (!empty($nombreImagen) && file_exists("../" . $nombreImagen)) {
                    unlink("../" . $nombreImagen);
                }
                $nombreImagen = str_replace('../', '', $nombreImagenNueva); // Guardar ruta relativa
            } else {
                echo "Error al subir la nueva imagen.";
            }
        } else {
            echo "Extensión de archivo no permitida.";
        }
    }

    // Actualizar el artículo
    $sqlUpdateArticulo = "UPDATE articulos 
                          SET titulo = :titulo, contenido = :contenido, resumen = :resumen, estado = :estado 
                          WHERE id_articulo = :id";
    $stmtUpdateArticulo = $conexion->prepare($sqlUpdateArticulo);
    $stmtUpdateArticulo->bindParam(':titulo', $titulo);
    $stmtUpdateArticulo->bindParam(':contenido', $contenido);
    $stmtUpdateArticulo->bindParam(':resumen', $resumen);
    $stmtUpdateArticulo->bindParam(':estado', $estado);
    $stmtUpdateArticulo->bindParam(':id', $idArticulo, PDO::PARAM_INT);
    $stmtUpdateArticulo->execute();

    // Actualizar o insertar la imagen
    if (!empty($nombreImagen)) {
        if ($imagen) {
            $sqlUpdateImagen = "UPDATE imagenes SET url = :url WHERE id_articulo = :id";
        } else {
            $sqlUpdateImagen = "INSERT INTO imagenes (url, id_articulo) VALUES (:url, :id)";
        }
        $stmtUpdateImagen = $conexion->prepare($sqlUpdateImagen);
        $stmtUpdateImagen->bindParam(':url', $nombreImagen);
        $stmtUpdateImagen->bindParam(':id', $idArticulo, PDO::PARAM_INT);
        $stmtUpdateImagen->execute();
    }

    // Actualizar categorías relacionadas
    $sqlDeleteCategorias = "DELETE FROM articulo_categoria WHERE id_articulo = :id";
    $stmtDeleteCategorias = $conexion->prepare($sqlDeleteCategorias);
    $stmtDeleteCategorias->bindParam(':id', $idArticulo, PDO::PARAM_INT);
    $stmtDeleteCategorias->execute();

    if (!empty($categoriasSeleccionadas)) {
        $sqlInsertCategoria = "INSERT INTO articulo_categoria (id_articulo, id_categoria) VALUES (:id_articulo, :id_categoria)";
        $stmtInsertCategoria = $conexion->prepare($sqlInsertCategoria);

        foreach ($categoriasSeleccionadas as $idCategoria) {
            $stmtInsertCategoria->bindParam(':id_articulo', $idArticulo, PDO::PARAM_INT);
            $stmtInsertCategoria->bindParam(':id_categoria', $idCategoria, PDO::PARAM_INT);
            $stmtInsertCategoria->execute();
        }
    }

    $mensajeExito = "El artículo ha sido actualizado correctamente.";
}

// Desconectar la base de datos
desconectarDB($conexion);
?>




<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <title>Editar Artículo</title>
</head>

<body>
    <div class="header">
        <h3>Panel de administrador - Editar Artículo</h3>
    </div>

    <div class="row">
        <div class="leftcolumn">
            <div class="form-container">

                <?php if (!empty($mensajeExito)): ?>
                    <p class="exito"><?php echo $mensajeExito; ?></p>
                <?php endif; ?>


                <form method="POST" action="" enctype="multipart/form-data">


                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input type="text" id="titulo" name="titulo" required value="<?php echo htmlspecialchars($articulo['titulo']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="contenido">Contenido</label>
                        <textarea id="contenido" name="contenido" required><?php echo htmlspecialchars($articulo['contenido']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="resumen">Resumen</label>
                        <input type="text" id="resumen" name="resumen" value="<?php echo htmlspecialchars($articulo['resumen']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="borrador" <?php echo $articulo['estado'] === 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                            <option value="publicado" <?php echo $articulo['estado'] === 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="imagen">Subir nueva imagen</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Categorías:</label>
                        <?php foreach ($categorias as $categoria): ?>
                            <div class="checkbox-group">
                                <input type="checkbox" id="categoria-<?php echo $categoria['id_categoria']; ?>" name="categorias[]"
                                    value="<?php echo $categoria['id_categoria']; ?>"
                                    <?php echo in_array($categoria['id_categoria'], array_column($categorias, 'id_categoria')) ? 'checked' : ''; ?>>
                                <label for="categoria-<?php echo $categoria['id_categoria']; ?>">
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-group">
                        <input type="submit" value="Guardar cambios">
                    </div>
                </form>
            </div>
        </div>

        <div class="rightcolumn">
            <div class="card">
                <h3>Login administrador</h3>
                <?php
                if (isset($_SESSION['username'])) {
                ?>
                    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
                    <a href="./administrador.php"class="admin-btn">Volver</a><br>
                    <a href="./logout.php"class="admin-btn">Cerrar sesión</a>
                <?php
                }
                ?>
            </div>
        </div>
    </div>

</body>

</html>