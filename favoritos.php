
<?php
session_start();
require_once "conexion.php";

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: acceso.php");
    exit;
}

// Verificar permisos
if (!in_array($_SESSION['rol'], ['cliente', 'ambos'], true)) {
    header("Location: vendedor.php");
    exit;
}

// Datos del usuario
$id_usuario = (int)$_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre'];

// Generar token de seguridad si todavía no existe
if (empty($_SESSION['token_favoritos'])) {
    $_SESSION['token_favoritos'] = bin2hex(random_bytes(32));
}

// Función para proteger contenido HTML
function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// Obtener iniciales
$partes_nombre = explode(' ', trim($nombre_usuario));
$iniciales = strtoupper(substr($partes_nombre[0], 0, 1));

if (count($partes_nombre) > 1) {
    $iniciales .= strtoupper(substr($partes_nombre[1], 0, 1));
}

// Símbolo según categoría
function simboloCategoria($categoria) {
    $simbolos = [
        'Granos' => '🌽',
        'Frutas' => '🍊',
        'Animales' => '🐓',
        'Artesanías' => '🧺'
    ];

    return $simbolos[$categoria] ?? '🌱';
}

// Procesar eliminación de favoritos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_favorito'])) {

    $token = $_POST['token'] ?? '';
    $id_producto = (int)($_POST['id_producto'] ?? 0);

    if (!hash_equals($_SESSION['token_favoritos'], $token)) {
        die("Solicitud no válida.");
    }

    $eliminar = $conexion->prepare(
        "DELETE FROM favoritos
         WHERE id_usuario = :id_usuario
         AND id_producto = :id_producto"
    );

    $eliminar->execute([
        ':id_usuario' => $id_usuario,
        ':id_producto' => $id_producto
    ]);

    if ($eliminar->rowCount() > 0) {
        $_SESSION['mensaje_favorito'] = "Producto eliminado de favoritos.";
    } else {
        $_SESSION['mensaje_favorito'] = "El producto ya no estaba en tus favoritos.";
    }

    header("Location: favoritos.php");
    exit;
}

// Consultar productos guardados
$sql = "SELECT
            p.id_producto,
            p.nombre AS nombre_producto,
            p.categoria,
            p.precio,
            p.unidad,
            p.disponibilidad,
            p.descripcion,
            p.modalidad,
            p.fotografia,
            u.nombre AS nombre_vendedor,
            u.nombre_publico,
            f.fecha_guardado
        FROM favoritos f
        INNER JOIN productos p
            ON f.id_producto = p.id_producto
        INNER JOIN usuarios u
            ON p.id_vendedor = u.id_usuario
        WHERE f.id_usuario = :id_usuario
        AND p.estado = 'activo'
        ORDER BY f.fecha_guardado DESC";

$consulta = $conexion->prepare($sql);

$consulta->execute([
    ':id_usuario' => $id_usuario
]);

$productos = $consulta->fetchAll();

$total_favoritos = count($productos);
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mis favoritos · Raíz Local</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body class="dashboard">

    <aside class="sidebar">

        <a class="brand" href="index.php">
            <img src="assets/img/raiz-logo.svg" alt="Raíz Local">
            <span>
                <b>raíz local</b>
                <small>TOLIMÁN</small>
            </span>
        </a>

        <label>MENÚ PRINCIPAL</label>

        <a class="side" href="cliente.php">⌂　Explorar</a>

        <a class="side active" href="favoritos.php">
            ♡　Mis favoritos
            <i><?php echo $total_favoritos; ?></i>
        </a>

        <a class="side" href="cliente.php#categories">
            ▦　Categorías
        </a>

        <div class="side-bottom">

            <div class="side-promo">
                ✳
                <h3>Compra cerca.<br>Impacta más.</h3>
                <p>Cada elección fortalece nuestra comunidad.</p>
            </div>

            <a class="side" href="perfil.php?tipo=cliente">
                ⚙　Mi perfil
            </a>

            <?php if ($_SESSION['rol'] === 'ambos'): ?>
                <a class="side" href="vendedor.php">
                    ⇥　Cambiar a vendedor
                </a>
            <?php endif; ?>

            <a href="cerrar_sesion.php" class="side">
                Cerrar sesión
            </a>

        </div>

    </aside>

    <main class="dash-main">

        <header class="dashbar">

            <button class="mobile-menu" id="menuToggle">☰</button>

            <span>Comunidad / <b>Mis favoritos</b></span>

            <a class="user" href="perfil.php?tipo=cliente">

                <b class="avatar">
                    <?php echo escapar($iniciales); ?>
                </b>

                <span>
                    <b><?php echo escapar($nombre_usuario); ?></b>
                    <small>Cliente　⌄</small>
                </span>

            </a>

        </header>

        <section class="welcome">

            <span class="kicker">TUS PRODUCTOS GUARDADOS</span>

            <h1>
                Tus favoritos,<br>
                <em>siempre a tu alcance.</em>
            </h1>

            <p>
                Aquí puedes consultar los productos que te han interesado
                y volver a conocer a sus productores.
            </p>

        </section>

        <div class="heading">

            <div>
                <span class="kicker">TU SELECCIÓN</span>
                <h2>Mis <em>favoritos.</em></h2>
            </div>

            <small>
                <b><?php echo $total_favoritos; ?></b>
                productos guardados
            </small>

        </div>

        <?php if (!empty($_SESSION['mensaje_favorito'])): ?>

            <p class="muted">
                <?php
                echo escapar($_SESSION['mensaje_favorito']);
                unset($_SESSION['mensaje_favorito']);
                ?>
            </p>

        <?php endif; ?>

        <div class="products" id="products">

            <?php if ($total_favoritos > 0): ?>

                <?php foreach ($productos as $producto): ?>

                    <?php
                    $nombre_vendedor = !empty($producto['nombre_publico'])
                        ? $producto['nombre_publico']
                        : $producto['nombre_vendedor'];

                    $simbolo = simboloCategoria($producto['categoria']);
                    ?>

                    <article class="product">

                        <div class="product-art art-<?php echo escapar($producto['categoria']); ?>">

                            <?php if (!empty($producto['fotografia'])): ?>

                                <img
                                    src="<?php echo escapar($producto['fotografia']); ?>"
                                    alt="<?php echo escapar($producto['nombre_producto']); ?>"
                                    style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;"
                                >

                            <?php else: ?>

                                <span><?php echo $simbolo; ?></span>

                            <?php endif; ?>

                            <small>
                                <?php echo escapar($producto['categoria']); ?>
                            </small>

                        </div>

                        <div class="product-info">

                            <h3>
                                <?php echo escapar($producto['nombre_producto']); ?>
                            </h3>

                            <?php if ($producto['precio'] !== null): ?>

                                <strong class="price">

                                    $<?php echo number_format((float)$producto['precio'], 2); ?>

                                    <?php if (!empty($producto['unidad'])): ?>
                                        <small>
                                            / <?php echo escapar($producto['unidad']); ?>
                                        </small>
                                    <?php endif; ?>

                                </strong>

                            <?php else: ?>

                                <strong class="price">Precio por acordar</strong>

                            <?php endif; ?>

                            <p>
                                Productor: <?php echo escapar($nombre_vendedor); ?>
                            </p>

                            <p class="desc">
                                <?php echo escapar($producto['descripcion'] ?? 'Sin descripción disponible.'); ?>
                            </p>

                            <div class="product-bottom">

                                <small>
                                    <?php echo escapar($producto['disponibilidad'] ?? 'Consultar disponibilidad'); ?>
                                </small>

                                <span>
                                    <?php echo escapar($producto['modalidad'] ?? ''); ?>
                                </span>

                            </div>

                            <form method="POST" action="favoritos.php">

                                <input
                                    type="hidden"
                                    name="token"
                                    value="<?php echo escapar($_SESSION['token_favoritos']); ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id_producto"
                                    value="<?php echo (int)$producto['id_producto']; ?>"
                                >

                                <button
                                    type="submit"
                                    name="accion_favorito"
                                    value="1"
                                    class="filter"
                                >
                                    ♥ Quitar de favoritos
                                </button>

                            </form>

                        </div>

                    </article>

                <?php endforeach; ?>

            <?php else: ?>

                <p class="muted">
                    Todavía no tienes productos guardados en favoritos.
                    Explora el mercado local y guarda los productos que te interesen.
                </p>

                <a href="cliente.php" class="filter">
                    Explorar productos
                </a>

            <?php endif; ?>

        </div>

        <footer class="dashfoot">
            ✳ Apoya al campo, fortalece nuestra comunidad.
            <span>Raíz Local · Tolimán</span>
        </footer>

    </main>

</body>
</html>
