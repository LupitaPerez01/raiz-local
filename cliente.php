<?php
session_start();
require_once "conexion.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: acceso.php");
    exit;
}

// Solo pueden entrar clientes o usuarios con ambos roles
if (!in_array($_SESSION['rol'], ['cliente', 'ambos'], true)) {
    header("Location: vendedor.php");
    exit;
}

// Datos del usuario
$id_usuario = $_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre'];

// Crear token de seguridad para los formularios
if (empty($_SESSION['token_favoritos'])) {
    $_SESSION['token_favoritos'] = bin2hex(random_bytes(32));
}

// Procesar acción de guardar o quitar favorito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_favorito'])) {

    $token = $_POST['token'] ?? '';
    $id_producto = (int)($_POST['id_producto'] ?? 0);

    if (!hash_equals($_SESSION['token_favoritos'], $token)) {
        die("Solicitud no válida.");
    }

    // Verificar que el producto exista y esté activo
    $consulta_producto = $conexion->prepare(
        "SELECT id_producto FROM productos
         WHERE id_producto = :id_producto
         AND estado = 'activo'"
    );

    $consulta_producto->execute([
        ':id_producto' => $id_producto
    ]);

    if ($consulta_producto->fetch()) {

        // Comprobar si ya está guardado
        $consulta_existente = $conexion->prepare(
            "SELECT id_favorito FROM favoritos
             WHERE id_usuario = :id_usuario
             AND id_producto = :id_producto"
        );

        $consulta_existente->execute([
            ':id_usuario' => $id_usuario,
            ':id_producto' => $id_producto
        ]);

        if ($consulta_existente->fetch()) {

            // Quitar de favoritos
            $eliminar = $conexion->prepare(
                "DELETE FROM favoritos
                 WHERE id_usuario = :id_usuario
                 AND id_producto = :id_producto"
            );

            $eliminar->execute([
                ':id_usuario' => $id_usuario,
                ':id_producto' => $id_producto
            ]);

            $_SESSION['mensaje_favorito'] = "Producto eliminado de favoritos.";

        } else {

            // Guardar en favoritos
            $insertar = $conexion->prepare(
                "INSERT INTO favoritos (id_usuario, id_producto)
                 VALUES (:id_usuario, :id_producto)"
            );

            $insertar->execute([
                ':id_usuario' => $id_usuario,
                ':id_producto' => $id_producto
            ]);

            $_SESSION['mensaje_favorito'] = "Producto agregado a favoritos.";
        }

    } else {
        $_SESSION['mensaje_favorito'] = "El producto ya no está disponible.";
    }

    // Regresar a la misma búsqueda o categoría
    $destino = 'cliente.php';

    $filtros = $_GET;

    if (!empty($filtros)) {
        $destino .= '?' . http_build_query($filtros);
    }

    header("Location: " . $destino);
    exit;
}

// Iniciales para el avatar
$partes_nombre = explode(' ', trim($nombre_usuario));
$iniciales = strtoupper(substr($partes_nombre[0], 0, 1));

if (count($partes_nombre) > 1) {
    $iniciales .= strtoupper(substr($partes_nombre[1], 0, 1));
}

// Obtener filtros del buscador
$buscar = trim($_GET['buscar'] ?? '');
$categoria = trim($_GET['categoria'] ?? 'Todos');

// Contar favoritos del usuario
$sql_favoritos = "SELECT COUNT(*) FROM favoritos
                  WHERE id_usuario = :id_usuario";

$consulta_favoritos = $conexion->prepare($sql_favoritos);
$consulta_favoritos->execute([
    ':id_usuario' => $id_usuario
]);

$total_favoritos = $consulta_favoritos->fetchColumn();

// Consultar productos activos (incluye u.telefono)
$sql = "SELECT
            p.id_producto,
            p.nombre AS nombre_producto,
            p.categoria,
            p.precio,
            p.unidad,
            p.cantidad,
            p.disponibilidad,
            p.descripcion,
            p.ubicacion,
            p.modalidad,
            p.fotografia,
            u.nombre AS nombre_vendedor,
            u.nombre_publico,
            u.telefono
        FROM productos p
        INNER JOIN usuarios u
            ON p.id_vendedor = u.id_usuario
        WHERE p.estado = 'activo'";

$parametros = [];

// Aplicar búsqueda por producto o vendedor
if ($buscar !== '') {
    $sql .= " AND (
                p.nombre LIKE :buscar_producto
                OR u.nombre LIKE :buscar_nombre
                OR u.nombre_publico LIKE :buscar_publico
              )";

    $parametros[':buscar_producto'] = "%$buscar%";
    $parametros[':buscar_nombre'] = "%$buscar%";
    $parametros[':buscar_publico'] = "%$buscar%";
}

// Aplicar filtro de categoría
if ($categoria !== 'Todos') {
    $sql .= " AND p.categoria = :categoria";
    $parametros[':categoria'] = $categoria;
}

$sql .= " ORDER BY p.fecha_publicacion DESC";

$consulta = $conexion->prepare($sql);
$consulta->execute($parametros);
$productos = $consulta->fetchAll();

$total_productos = count($productos);

// Obtener los favoritos del usuario
$consulta_ids_favoritos = $conexion->prepare(
    "SELECT id_producto FROM favoritos
     WHERE id_usuario = :id_usuario"
);

$consulta_ids_favoritos->execute([
    ':id_usuario' => $id_usuario
]);

$favoritos_ids = $consulta_ids_favoritos->fetchAll(
    PDO::FETCH_COLUMN
);

// Función para proteger el contenido mostrado en HTML
function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// Obtener un símbolo según la categoría
function simboloCategoria($categoria) {
    $simbolos = [
        'Granos' => '🌽',
        'Frutas' => '🍊',
        'Animales' => '🐓',
        'Artesanías' => '🧺'
    ];

    return $simbolos[$categoria] ?? '🌱';
}

// Limpia el teléfono dejando solo dígitos (para usarlo en wa.me)
function telefonoParaWhatsapp($telefono) {
    $digitos = preg_replace('/\D+/', '', (string)$telefono);

    // Si son 10 dígitos (México), asumimos lada 52
    if (strlen($digitos) === 10) {
        $digitos = '52' . $digitos;
    }

    return $digitos;
}

// Detecta si el visitante es móvil (para abrir la app directo)
function esMovil() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return (bool) preg_match(
        '/Android|iPhone|iPad|iPod|Mobile|Windows Phone/i',
        $ua
    );
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Explorar · Raíz Local</title>
    <link rel="stylesheet" href="assets/css/styles.css">

    <style>
    /* =====================================
       BANNER DEL CLIENTE CON IMAGEN
    ===================================== */

    .cliente-banner {
        background-image:
            linear-gradient(rgba(41, 63, 49, 0.55), rgba(41, 63, 49, 0.65)),
            url("https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=2200&q=90");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        color: #fffefa;
    }

    .cliente-banner h2,
    .cliente-banner p,
    .cliente-banner small {
        color: #fffefa;
    }

    .cliente-banner h2 em {
        color: #c5d3b9;
    }

    @media (max-width: 800px) {
        .cliente-banner {
            background-position: center top;
        }
    }
    </style>
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

        <a class="side active" href="cliente.php">⌂　Explorar</a>

        <a class="side" href="favoritos.php">
            ♡　Mis favoritos
            <i><?php echo $total_favoritos; ?></i>
        </a>

        <a class="side" href="#categories">▦　Categorías</a>

        <div class="side-bottom">

            <div class="side-promo">
                ✳
                <h3>Compra cerca.<br>Impacta más.</h3>
                <p>Cada elección fortalece nuestra comunidad.</p>
            </div>

            <a class="side" href="perfil.php?tipo=cliente">⚙　Mi perfil</a>

            <?php if ($_SESSION['rol'] === 'ambos'): ?>
                <a class="side" href="vendedor.php">⇥　Cambiar a vendedor</a>
            <?php endif; ?>

            <a href="cerrar_sesion.php" class="side">
                Cerrar sesión
            </a>

        </div>

    </aside>

    <main class="dash-main">

        <header class="dashbar">

            <button class="mobile-menu" id="menuToggle">☰</button>

            <span>Comunidad / <b>Explorar</b></span>

            <a class="user" href="perfil.php?tipo=cliente">
                <b class="avatar"><?php echo escapar($iniciales); ?></b>

                <span>
                    <b><?php echo escapar($nombre_usuario); ?></b>
                    <small>Cliente　⌄</small>
                </span>
            </a>

        </header>

        <section class="welcome">

            <span class="kicker">✳ EL MERCADO DE NUESTRA TIERRA</span>

            <h1>
                Hola, <?php echo escapar($nombre_usuario); ?>.<br>
                <em>¿Qué se te antoja descubrir?</em>
            </h1>

            <p>
                Encuentra productos locales, conoce a sus productores
                y apoya lo que se hace cerca de ti.
            </p>

        </section>

        <section class="dash-banner cliente-banner">

            <div>
                <small>DE NUESTRA TIERRA A TU MESA</small>
                <h2>Lo local tiene<br><em>otro sabor.</em></h2>
                <p>Productos con origen, personas con historia.</p>
            </div>

            <span class="big-art">🌾　🧺</span>

        </section>

        <div class="heading">

            <div>
                <span class="kicker">EL MERCADO LOCAL</span>
                <h2>Encuentra algo <em>auténtico.</em></h2>
            </div>

            <small>
                <b><?php echo $total_productos; ?></b>
                productos encontrados
            </small>

        </div>

        <div class="tools">

            <!-- Buscador -->
            <form method="GET" action="cliente.php">
                <input
                    type="hidden"
                    name="categoria"
                    value="<?php echo escapar($categoria); ?>"
                >

                <input
                    type="search"
                    name="buscar"
                    id="search"
                    placeholder="⌕ Busca un producto o productor..."
                    value="<?php echo escapar($buscar); ?>"
                >

                <button type="submit" class="filter">Buscar</button>
            </form>

            <!-- Categorías -->
            <div id="categories">

                <?php
                $categorias = [
                    'Todos' => 'Todos',
                    'Granos' => '🌽 Granos',
                    'Frutas' => '🍊 Frutas',
                    'Animales' => '🐓 Animales',
                    'Artesanías' => '🧺 Artesanías'
                ];

                foreach ($categorias as $valor => $etiqueta):

                    $clase = ($categoria === $valor) ? 'filter active' : 'filter';

                    $url = 'cliente.php?categoria=' . urlencode($valor);

                    if ($buscar !== '') {
                        $url .= '&buscar=' . urlencode($buscar);
                    }
                ?>

                    <a
                        class="<?php echo $clase; ?>"
                        href="<?php echo escapar($url); ?>"
                    >
                        <?php echo escapar($etiqueta); ?>
                    </a>

                <?php endforeach; ?>

            </div>

        </div>

<?php if (!empty($_SESSION['mensaje_favorito'])): ?>

    <p class="muted">
        <?php
        echo escapar($_SESSION['mensaje_favorito']);
        unset($_SESSION['mensaje_favorito']);
        ?>
    </p>

<?php endif; ?>

        <!-- Productos obtenidos desde MySQL -->
        <div class="products" id="products">

            <?php if ($total_productos > 0): ?>

                <?php foreach ($productos as $producto): ?>

                    <?php
                    $nombre_vendedor = !empty($producto['nombre_publico'])
                        ? $producto['nombre_publico']
                        : $producto['nombre_vendedor'];

                    $simbolo = simboloCategoria($producto['categoria']);

                    // Datos para contacto / WhatsApp
                    $telefono = $producto['telefono'] ?? '';
                    $tel_limpio = telefonoParaWhatsapp($telefono);

                    $mensaje_whatsapp = 'Hola, me interesa más información sobre el producto "'
                        . $producto['nombre_producto'] . '" que vi en Raíz Local.';

                    $mensaje_codificado = rawurlencode($mensaje_whatsapp);

                    if (esMovil()) {
                        // Abre la app directamente (mucho más rápido)
                        $url_whatsapp = 'whatsapp://send?phone=' . $tel_limpio
                            . '&text=' . $mensaje_codificado;
                    } else {
                        // Escritorio: WhatsApp Web / Desktop
                        $url_whatsapp = 'https://api.whatsapp.com/send?phone=' . $tel_limpio
                            . '&text=' . $mensaje_codificado;
                    }
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

                            <small><?php echo escapar($producto['categoria']); ?></small>

                        </div>

                        <div class="product-info">

                            <h3><?php echo escapar($producto['nombre_producto']); ?></h3>

                            <?php if ($producto['precio'] !== null): ?>

                                <strong class="price">
                                    $<?php echo number_format((float)$producto['precio'], 2); ?>

                                    <?php if (!empty($producto['unidad'])): ?>
                                        <small>/ <?php echo escapar($producto['unidad']); ?></small>
                                    <?php endif; ?>
                                </strong>

                            <?php else: ?>

                                <strong class="price">Precio por acordar</strong>

                            <?php endif; ?>

                            <p>
                                Productor: <?php echo escapar($nombre_vendedor); ?>
                            </p>

                            <?php if (!empty($telefono)): ?>

                                <p class="productor-contacto">
                                    📞 <?php echo escapar($telefono); ?>
                                </p>

                                <a
                                    class="filter btn-whatsapp"
                                    href="<?php echo escapar($url_whatsapp); ?>"
                                >
                                    💬 Preguntar por WhatsApp
                                </a>

                            <?php endif; ?>

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

                            <?php
                            $es_favorito = in_array(
                                (int)$producto['id_producto'],
                                array_map('intval', $favoritos_ids),
                                true
                            );
                            ?>

                            <form method="POST" action="cliente.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . escapar($_SERVER['QUERY_STRING']) : ''; ?>">

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
                                    <?php echo $es_favorito ? '♥ Quitar de favoritos' : '♡ Guardar en favoritos'; ?>
                                </button>

                            </form>
                        </div>

                    </article>

                <?php endforeach; ?>

            <?php else: ?>

                <p class="muted">
                    <?php if ($buscar !== '' || $categoria !== 'Todos'): ?>
                        No encontramos productos que coincidan con tu búsqueda.
                        Intenta con otro nombre o categoría.
                    <?php else: ?>
                        Todavía no hay productos publicados.
                        ¡Pronto encontrarás productos de nuestra comunidad!
                    <?php endif; ?>
                </p>

            <?php endif; ?>

        </div>

        <footer class="dashfoot">
            ✳ Apoya al campo, fortalece nuestra comunidad.
            <span>Raíz Local · Tolimán</span>
        </footer>

    </main>

    <div id="toast" class="toast"></div>

</body>
</html>