<?php
session_start();
require_once './dbconnector.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

// Verificar si se ha pasado un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de artículo no válido.");
}

$idArticulo = intval($_GET['id']);

// Conectar a la base de datos
$conexion = conectarDB();

if ($conexion !== false) {
    try {
        // Iniciar una transacción
        $conexion->beginTransaction();

        // Eliminar imágenes asociadas
        $sqlDeleteImagenes = "DELETE FROM imagenes WHERE id_articulo = :id";
        $stmtDeleteImagenes = $conexion->prepare($sqlDeleteImagenes);
        $stmtDeleteImagenes->bindParam(':id', $idArticulo, PDO::PARAM_INT);
        $stmtDeleteImagenes->execute();

        // Eliminar las categorías asociadas
        $sqlDeleteCategorias = "DELETE FROM articulo_categoria WHERE id_articulo = :id";
        $stmtDeleteCategorias = $conexion->prepare($sqlDeleteCategorias);
        $stmtDeleteCategorias->bindParam(':id', $idArticulo, PDO::PARAM_INT);
        $stmtDeleteCategorias->execute();

        // Eliminar el artículo
        $sqlDeleteArticulo = "DELETE FROM articulos WHERE id_articulo = :id";
        $stmtDeleteArticulo = $conexion->prepare($sqlDeleteArticulo);
        $stmtDeleteArticulo->bindParam(':id', $idArticulo, PDO::PARAM_INT);
        $stmtDeleteArticulo->execute();

        // Confirmar la transacción
        $conexion->commit();

        // Mensaje de éxito
        $_SESSION['success_message'] = "Artículo eliminado correctamente.";
        header('Location: administrador.php');
        exit;
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollBack();
        echo "Error al eliminar el artículo: " . $e->getMessage();
        exit;
    }

    // Desconectar la base de datos
    desconectarDB($conexion);
} else {
    die("Error al conectar con la base de datos.");
}
