
<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

// Verificar si se recibió un ID de artículo
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de artículo no válido.");
}

require_once './dbconnector.php';

$idArticulo = intval($_GET['id']);
$conexion = conectarDB();

if ($conexion !== false) {
    // Obtener el estado actual del artículo
    $sql = "SELECT estado FROM articulos WHERE id_articulo = :id_articulo";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_articulo', $idArticulo, PDO::PARAM_INT);
    $stmt->execute();

    $articulo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($articulo) {
        // Alternar el estado del artículo
        $nuevoEstado = ($articulo['estado'] === 'publicado') ? 'borrador' : 'publicado';

        // Actualizar el estado en la base de datos
        $sql = "UPDATE articulos SET estado = :nuevo_estado WHERE id_articulo = :id_articulo";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nuevo_estado', $nuevoEstado, PDO::PARAM_STR);
        $stmt->bindParam(':id_articulo', $idArticulo, PDO::PARAM_INT);
        $stmt->execute();

        // Redirigir a la página de administración
        header('Location: ./administrador.php');
        exit;
    } else {
        echo "Artículo no encontrado.";
    }

    // Cerrar la conexión
    desconectarDB($conexion);
} else {
    echo "Error al conectar con la base de datos.";
}
?>
