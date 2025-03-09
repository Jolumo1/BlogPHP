<?php
// Incluir el archivo de conexión a la base de datos
include './php/dbconnector.php';

// Conectar a la base de datos
$conexion = conectarDB();

$articuloId = isset($_GET['articulo']) ? intval($_GET['articulo']) : null;
$categoriaId = isset($categoriaId) ? $categoriaId : null; // Obtener el ID de la categoría
$paginaActual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$itemsPorPagina = 5; // Artículos por página
$offset = ($paginaActual - 1) * $itemsPorPagina;

if ($conexion !== false) {
    if ($articuloId) {
        // Consulta para obtener un único artículo
        $sql = "SELECT a.titulo, a.contenido, a.resumen, a.fecha_publicacion, 
                       COALESCE(i.url, 'images/placeholder.jpg') AS imagen_url
                FROM articulos a
                LEFT JOIN imagenes i ON a.id_articulo = i.id_articulo
                WHERE a.id_articulo = :articuloId AND a.estado = 'publicado'";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':articuloId', $articuloId, PDO::PARAM_INT);
    } else {
        // Consulta para obtener artículos con paginación
        $sql = "SELECT a.id_articulo, a.titulo, a.resumen, a.fecha_publicacion, 
                       COALESCE(i.url, 'images/placeholder.jpg') AS imagen_url
                FROM articulos a
                LEFT JOIN imagenes i ON a.id_articulo = i.id_articulo";

        if ($categoriaId) {
            $sql .= " INNER JOIN articulo_categoria ac ON a.id_articulo = ac.id_articulo
                      WHERE ac.id_categoria = :categoriaId AND a.estado = 'publicado'";
        } else {
            $sql .= " WHERE a.estado = 'publicado'";
        }

        $sql .= " ORDER BY a.fecha_publicacion DESC LIMIT :offset, :limit";
        $stmt = $conexion->prepare($sql);

        if ($categoriaId) {
            $stmt->bindParam(':categoriaId', $categoriaId, PDO::PARAM_INT);
        }

        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $itemsPorPagina, PDO::PARAM_INT);
    }

    // Ejecutar la consulta
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el número total de artículos para calcular el número total de páginas
    $sqlTotal = "SELECT COUNT(a.id_articulo) AS total_articulos
                 FROM articulos a
                 LEFT JOIN imagenes i ON a.id_articulo = i.id_articulo";
    if ($categoriaId) {
        $sqlTotal .= " INNER JOIN articulo_categoria ac ON a.id_articulo = ac.id_articulo
                       WHERE ac.id_categoria = :categoriaId AND a.estado = 'publicado'";
    } else {
        $sqlTotal .= " WHERE a.estado = 'publicado'";
    }

    $stmtTotal = $conexion->prepare($sqlTotal);
    if ($categoriaId) {
        $stmtTotal->bindParam(':categoriaId', $categoriaId, PDO::PARAM_INT);
    }
    $stmtTotal->execute();
    $totalArticulos = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total_articulos'];

    $totalPaginas = ceil($totalArticulos / $itemsPorPagina);

    // Cerrar la conexión
    desconectarDB($conexion);
} else {
    echo "Error al conectar con la base de datos.";
}
?>

<?php if ($articuloId && !empty($articulos)): ?>
    <?php $articulo = $articulos[0]; ?>
    <div class="card">
        <h2><?php echo htmlspecialchars($articulo['titulo']); ?></h2>
        <h5><?php echo date("F j, Y", strtotime($articulo['fecha_publicacion'])); ?></h5>
        <div class="fakeimg" style="height:400px;">
            <img src="<?php echo htmlspecialchars($articulo['imagen_url']); ?>" alt="Imagen del artículo" style="width:100%; height:100%; object-fit:cover;">
        </div>
        <br>
        <p><?php echo nl2br(htmlspecialchars($articulo['contenido'])); ?></p>
        <br>
        <a href="index.php" class="boton-leer-mas">Volver</a>
    </div>
<?php elseif (!empty($articulos)): ?>
    <?php foreach ($articulos as $articulo): ?>
        <div class="card preview">
            <div class="left">
                <div class="titulo">
                    <h2>
                        <a href="index.php?articulo=<?php echo htmlspecialchars($articulo['id_articulo']); ?>">
                            <?php echo htmlspecialchars($articulo['titulo']); ?>
                        </a>
                    </h2>
                </div>
                <div class="resumen">
                    <p><?php echo substr(htmlspecialchars($articulo['resumen']), 0, 600); ?>...</p>
                </div>
                <a href="index.php?articulo=<?php echo htmlspecialchars($articulo['id_articulo']); ?>" class="boton-leer-mas">
                    Leer más
                </a>
            </div>
            <div class="right">
                <a href="index.php?articulo=<?php echo htmlspecialchars($articulo['id_articulo']); ?>">
                    <img src="<?php echo htmlspecialchars($articulo['imagen_url']); ?>" alt="Imagen del artículo" style="width:100%; height:100%; object-fit:cover;">
                </a>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Paginación -->
    <div class="paginador">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <a href="index.php?pagina=<?php echo $i; ?>&categoria=<?php echo $categoriaId; ?>" class="<?php echo $i == $paginaActual ? 'activo' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>

<?php else: ?>
    <p>No se han encontrado artículos.</p>
<?php endif; ?>