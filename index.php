<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="./css/styles.css" />
</head>

<body>
    <?php
    // HEADER
    include './php/headerMain.php';
    ?>

    <div class="row">
        <div class="leftcolumn">
            <?php
            // Obtener el ID de la categoría desde la URL
            $categoriaId = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
            // Obtener el número de página desde la URL (por defecto página 1)
            $paginaActual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
            $itemsPorPagina = 5; // Artículos por página

            include './php/articulos.php';
            ?>
        </div>

        <div class="rightcolumn">
            <div class="card">
                <h3>Sobre mí</h3>
                <div class="about-me">
                    <img src="./images/burns.jpg" alt="Foto de perfil" />
                    <h4>Jose Luján 2º DAW</h4>
                    <h4>Monkey Business Coordinator</h4>
                    <p>Blog sobre tecnología, smartphones, informática, ciencia, IA, videojuegos y todo lo que debe interesarte si eres una persona de bien.</p>
                </div>
            </div>

            <div class="card">
                <h3>Categorías</h3>
                <ul>
                    <?php include 'php/categorias.php'; ?>
                </ul>
            </div>

            <div class="card">
                <h3>Mis redes</h3>
                <div class="social-icons">
                    <a href="https://www.youtube.com" target="_blank">
                        <img src="./images/Youtube.png" alt="YouTube" />
                    </a>
                    <a href="https://www.instagram.com" target="_blank">
                        <img src="./images/Instagram.png" alt="Instagram" />
                    </a>
                    <a href="https://www.x.com" target="_blank">
                        <img src="./images/x.png" alt="X" />
                    </a>

                    <a href="https://www.facebook.com" target="_blank">
                        <img src="./images/facebook.png" alt="Facebook" />
                    </a>
                </div>
            </div>

            <div class="card">
                <h3>Panel de administrador</h3>
                <?php

                if (isset($_SESSION['username'])) {
                ?>
                    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
                    <a href="./php/administrador.php" class="admin-btn">Administración</a>
                    <a href="./php/logout.php" class="admin-btn">Cerrar sesión</a>
                <?php
                } elseif (isset($_SESSION['error'])) {
                    echo $_SESSION['error'];
                    include './php/formulario_login.php';
                } else {
                    include './php/formulario_login.php';
                }
                ?>
            </div>
        </div>
    </div>

    <?php
    // FOOTER
    include './php/footer.php';
    ?>
</body>

</html>