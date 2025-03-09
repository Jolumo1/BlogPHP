<?php
function conectarDB(){
    $dsn = 'mysql:host=localhost;dbname=blog';
    $username = 'root';
    $password = '';

    try {
        $conexionDB = new PDO($dsn, $username, $password);
        // Establecer el modo de error de PDO a excepción
        $conexionDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexionDB;
    } catch (PDOException $e) {
        echo "Conexión fallida: " . $e->getMessage();
    }
}

function desconectarDB($conexionDB)
{
    $conexionDB = null;
}
