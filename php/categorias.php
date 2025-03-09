<?php
require_once 'dbconnector.php';

$conexion = conectarDB();

if ($conexion !== false) {
    $sql = "SELECT id_categoria, nombre FROM categorias";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    desconectarDB($conexion);
} else {
    echo "Error al conectar con la base de datos.";
}
?>

<?php if (!empty($categorias)): ?>
    <ul>
        <!-- Opción para todas las categorías -->
        <li>
            <a href="index.php">Todas</a>
        </li>
        <?php foreach ($categorias as $categoria): ?>
            <li>
                <a href="index.php?categoria=<?php echo htmlspecialchars($categoria['id_categoria']); ?>">
                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No hay categorías disponibles.</p>
<?php endif; ?>