<?php
session_start();
require_once './dbconnector.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit();
}

// Recuperar categorías de la base de datos
$conexion = conectarDB();
$categorias = [];
if ($conexion) {
    $sqlCategorias = "SELECT id_categoria, nombre FROM categorias";
    $stmtCategorias = $conexion->prepare($sqlCategorias);
    $stmtCategorias->execute();
    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);
    desconectarDB($conexion);
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $contenido = $_POST['contenido'] ?? '';
    $resumen = $_POST['resumen'] ?? '';
    $categoriasSeleccionadas = $_POST['categorias'] ?? [];
    $imagenRuta = null; // Por defecto no hay imagen

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $directorioDestino = '../images/'; // Carpeta donde se guardarán las imágenes
        $nombreArchivo = uniqid() . '-' . basename($_FILES['imagen']['name']);
        $rutaCompleta = $directorioDestino . $nombreArchivo;

        // Validar el tipo de archivo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['imagen']['type'], $tiposPermitidos)) {
            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0755, true); // Crear el directorio si no existe
            }
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
                $imagenRuta = 'images/' . $nombreArchivo;
                // Guardar la ruta de la imagen
            } else {
                $error = "Error al subir la imagen.";
            }
        } else {
            $error = "Formato de imagen no permitido.";
        }
    }

    if (!empty($titulo) && !empty($contenido) && !empty($resumen)) {
        $conexion = conectarDB();
        if ($conexion) {
            try {
                // Insertar el artículo
                $sqlArticulo = "INSERT INTO articulos (titulo, contenido, resumen, estado) 
                                VALUES (:titulo, :contenido, :resumen, 'publicado')";
                $stmtArticulo = $conexion->prepare($sqlArticulo);
                $stmtArticulo->bindParam(':titulo', $titulo);
                $stmtArticulo->bindParam(':contenido', $contenido);
                $stmtArticulo->bindParam(':resumen', $resumen);
                $stmtArticulo->execute();
                $idArticulo = $conexion->lastInsertId();

                // Asociar categorías al artículo
                if (!empty($categoriasSeleccionadas)) {
                    $sqlRelacion = "INSERT INTO articulo_categoria (id_articulo, id_categoria) VALUES (:id_articulo, :id_categoria)";
                    $stmtRelacion = $conexion->prepare($sqlRelacion);
                    foreach ($categoriasSeleccionadas as $idCategoria) {
                        $stmtRelacion->bindParam(':id_articulo', $idArticulo, PDO::PARAM_INT);
                        $stmtRelacion->bindParam(':id_categoria', $idCategoria, PDO::PARAM_INT);
                        $stmtRelacion->execute();
                    }
                }

                // Asociar la URL de la imagen si está presente
                if (!empty($imagenRuta)) {
                    $sqlImagen = "INSERT INTO imagenes (id_articulo, url) VALUES (:id_articulo, :url)";
                    $stmtImagen = $conexion->prepare($sqlImagen);
                    $stmtImagen->bindParam(':id_articulo', $idArticulo, PDO::PARAM_INT);
                    $stmtImagen->bindParam(':url', $imagenRuta);
                    $stmtImagen->execute();
                }



                // Confirmación
                $mensajeExito = "Artículo creado correctamente.";

                // Limpiar variables del formulario
                $titulo = '';
                $contenido = '';
                $resumen = '';
                $imagen_url = '';
                $categoriasSeleccionadas = [];
            } catch (Exception $e) {
                $error = "Error al crear el artículo: " . $e->getMessage();
            }
            desconectarDB($conexion);
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Artículo</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>

<body>
    <div class="header">
        <h3>Panel de administrador - Crear Artículo</a></h3>
    </div>
    <div class="leftcolumn">
        <div class="form-container">
            <h2>Crear nuevo artículo</h2>
            <?php if (isset($mensajeExito)): ?>
                <p class="exito"><?php echo $mensajeExito; ?></p>
            <?php elseif (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="crear_articulo.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" name="titulo" required value="<?php echo isset($titulo) ? htmlspecialchars($titulo) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="contenido">Contenido</label>
                    <textarea id="contenido" name="contenido" required><?php echo isset($contenido) ? htmlspecialchars($contenido) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="resumen">Resumen</label>
                    <input type="text" id="resumen" name="resumen" required value="<?php echo isset($resumen) ? htmlspecialchars($resumen) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="imagen">Imagen del artículo</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Categorías</label>
                    <div class="checkbox-container">
                        <?php foreach ($categorias as $categoria): ?>
                            <div class="checkbox-group">
                                <input type="checkbox" id="categoria-<?php echo $categoria['id_categoria']; ?>" name="categorias[]"
                                    value="<?php echo $categoria['id_categoria']; ?>">
                                <label for="categoria-<?php echo $categoria['id_categoria']; ?>">
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="form-note">Selecciona una o más categorías para el artículo.</p>
                </div>

                <div class="form-group">
                    <input type="submit" value="Crear artículo">
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
                <a href="./administrador.php" class="admin-btn">Volver</a><br>
            <?php
            }
            ?>
        </div>
    </div>

</body>

</html>