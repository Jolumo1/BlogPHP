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

// Procesar la creación de un nuevo administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_administrador']) && isset($_POST['password_administrador'])) {
    $nombreAdministrador = trim($_POST['nombre_administrador']);
    $passwordAdministrador = trim($_POST['password_administrador']);
    $emailAdministrador = trim($_POST['email_administrador']); // Nuevo campo para el email

    if (!empty($nombreAdministrador) && !empty($passwordAdministrador) && !empty($emailAdministrador)) {
        try {
            // Consulta para insertar un nuevo administrador
            $sqlInsert = "INSERT INTO administrador (nombre, password, email) VALUES (:nombre, :password, :email)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->bindParam(':nombre', $nombreAdministrador, PDO::PARAM_STR);
            $stmtInsert->bindParam(':password', $passwordAdministrador, PDO::PARAM_STR);
            $stmtInsert->bindParam(':email', $emailAdministrador, PDO::PARAM_STR);
            $stmtInsert->execute();
            $mensaje = "Administrador creado correctamente.";
        } catch (Exception $e) {
            $error = "Error al crear el administrador: " . $e->getMessage();
        }
    } else {
        $error = "El nombre de usuario, la contraseña y el email no pueden estar vacíos.";
    }
}

// Procesar el cambio de nombre, contraseña o email de un administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_administrador_id'])) {
    $idAdministrador = $_POST['editar_administrador_id'];
    $nuevoNombre = trim($_POST['nuevo_nombre_administrador']);
    $nuevaPassword = trim($_POST['nueva_password_administrador']);
    $nuevoEmail = trim($_POST['nuevo_email_administrador']); // Nuevo campo para el email

    if (!empty($nuevoNombre) || !empty($nuevoEmail)) { // Aseguramos que al menos uno de los campos se actualice
        try {
            // Actualizar el nombre del administrador
            $sqlUpdate = "UPDATE administrador SET nombre = :nombre WHERE id_administrador = :id_administrador";
            $stmtUpdate = $conexion->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':nombre', $nuevoNombre, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':id_administrador', $idAdministrador, PDO::PARAM_INT);
            $stmtUpdate->execute();

            // Si se proporciona una nueva contraseña, actualizarla
            if (!empty($nuevaPassword)) {
                $sqlUpdatePassword = "UPDATE administrador SET password = :password WHERE id_administrador = :id_administrador";
                $stmtUpdatePassword = $conexion->prepare($sqlUpdatePassword);
                $stmtUpdatePassword->bindParam(':password', $nuevaPassword, PDO::PARAM_STR);
                $stmtUpdatePassword->bindParam(':id_administrador', $idAdministrador, PDO::PARAM_INT);
                $stmtUpdatePassword->execute();
            }

            // Si se proporciona un nuevo email, actualizarlo
            if (!empty($nuevoEmail)) {
                $sqlUpdateEmail = "UPDATE administrador SET email = :email WHERE id_administrador = :id_administrador";
                $stmtUpdateEmail = $conexion->prepare($sqlUpdateEmail);
                $stmtUpdateEmail->bindParam(':email', $nuevoEmail, PDO::PARAM_STR);
                $stmtUpdateEmail->bindParam(':id_administrador', $idAdministrador, PDO::PARAM_INT);
                $stmtUpdateEmail->execute();
            }

            $mensaje = "Administrador actualizado correctamente.";
        } catch (Exception $e) {
            $error = "Error al actualizar el administrador: " . $e->getMessage();
        }
    } else {
        $error = "El nombre de usuario o el email no pueden estar vacíos.";
    }
}

// Eliminar un administrador
if (isset($_GET['eliminar_administrador_id'])) {
    $idAdministrador = $_GET['eliminar_administrador_id'];

    try {
        $sqlDelete = "DELETE FROM administrador WHERE id_administrador = :id_administrador";
        $stmtDelete = $conexion->prepare($sqlDelete);
        $stmtDelete->bindParam(':id_administrador', $idAdministrador, PDO::PARAM_INT);
        $stmtDelete->execute();
        $mensaje = "Administrador eliminado correctamente.";
    } catch (Exception $e) {
        $error = "Error al eliminar el administrador: " . $e->getMessage();
    }
}

// Obtener todos los administradores
$administradores = [];
try {
    $sqlSelect = "SELECT id_administrador, nombre, email FROM administrador"; // También traemos el email
    $stmtSelect = $conexion->prepare($sqlSelect);
    $stmtSelect->execute();
    $administradores = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error al recuperar los administradores: " . $e->getMessage();
}

// Desconectar de la base de datos
desconectarDB($conexion);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Administradores</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>

<body>
    <div class="header">
        <h3>Panel de administrador - Gestión de Administradores</h3>
    </div>

    <div class="leftcolumn">
        <div class="form-container">
            <h2>Nuevo administrador</h2>

            <?php if ($mensaje): ?>
                <p class="exito"><?php echo $mensaje; ?></p>
            <?php elseif ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <!-- Formulario para crear un nuevo administrador -->
            <form action="gestionar_administradores.php" method="POST">
                <div class="form-group-container">
                    <div class="form-group">
                        <label for="nombre_administrador">Nombre de Administrador</label>
                        <input type="text" id="nombre_administrador" name="nombre_administrador" placeholder="Nombre de usuario" required>
                    </div>

                    <div class="form-group">
                        <label for="password_administrador">Contraseña</label>
                        <input type="password" id="password_administrador" name="password_administrador" placeholder="Contraseña" required>
                    </div>

                    <div class="form-group">
                        <label for="email_administrador">Email</label>
                        <input type="email" id="email_administrador" name="email_administrador" placeholder="Email" required>
                    </div>
                </div>

                <div class="form-group">
                    <input type="submit" value="Añadir Administrador">
                </div>
            </form>

            <!-- Listado de administradores existentes -->
            <h3>Administradores existentes</h3>
            <?php if (!empty($administradores)): ?>
                <ul>
                    <?php foreach ($administradores as $administrador): ?>
                        <li class="administrador-item">
                            <label><?php echo htmlspecialchars($administrador['nombre']); ?></label>
                            <!-- Formulario para editar un administrador -->
                            <form action="gestionar_administradores.php" method="POST" style="display:inline;">
                                <input type="hidden" name="editar_administrador_id" value="<?php echo $administrador['id_administrador']; ?>">
                                <input type="text" name="nuevo_nombre_administrador" placeholder="Nuevo nombre" required>
                                <input type="password" name="nueva_password_administrador" placeholder="Nueva contraseña">
                                <input type="email" name="nuevo_email_administrador" placeholder="Nuevo email" value="<?php echo htmlspecialchars($administrador['email']); ?>">
                                <input type="submit" value="Editar">
                                <a href="gestionar_administradores.php?eliminar_administrador_id=<?php echo $administrador['id_administrador']; ?>" onclick="return confirm('¿Estás seguro de eliminar este administrador?');" class="boton-eliminar">Eliminar</a>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No hay administradores disponibles.</p>
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