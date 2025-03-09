<?php
session_start();

require_once 'dbconnector.php';
$conexion = conectarDB();

if ($conexion !== false) {
    $sql = "SELECT nombre, password FROM administrador";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $administradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    desconectarDB($conexion);
} else {
    echo "Error al conectar con la base de datos.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $usuarios_validos = [];
    foreach ($administradores as $admin) {
        $usuarios_validos[$admin['nombre']] = $admin['password'];
    }


    if (isset($usuarios_validos[$username]) && $usuarios_validos[$username] === $password) {
        $_SESSION['username'] = $username;
        header('Location: ../index.php');
        exit;
    } else {
        $_SESSION['error']='<p style="color: red;">Nombre de usuario o contraseña incorrectos</p>';
        header('Location: ../index.php');

    }
}
