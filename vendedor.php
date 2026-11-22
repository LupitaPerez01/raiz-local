<?php
session_start();
require_once "conexion.php";

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: acceso.php");
    exit;
}

// Verificar permisos
if (!in_array($_SESSION['rol'], ['vendedor', 'ambos'], true)) {
    header("Location: cliente.php");
    exit;
}

$id_usuario = (int) $_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre'];

// Token de seguridad para los formularios
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['token'];

$mensaje = "";
$tipo_mensaje = "";

// Función para mostrar texto de forma segura
function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// Valores iniciales del formulario
$datos_formulario = [
    'id_producto' => '',
    'nombre' => '',
    'categoria' => '',
    'precio' => '',
    'unidad' => 'pieza',
    'cantidad' => '',
    'disponibilidad' => 'Disponible',
    'descripcion' => '',
    'ubicacion' => '',
    'modalidad' => 'Venta',
    'fotografia' => ''
];

// Categorías permitidas
$categorias = [
    'Granos',
    'Frutas',
    'Animales',
    'Artesanías',
    'Otros'
];

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verificar token
    if (!hash_equals($token, $_POST['token'] ?? '')) {
        die("Solicitud no válida.");
    }

    $accion = $_POST['accion'] ?? '';

    // ELIMINAR PUBLICACIÓN
    if ($accion === 'eliminar') {

        $id_producto = (int) ($_POST['id_producto'] ?? 0);

        $sql = "UPDATE productos
                SET estado = 'eliminado'
                WHERE id_producto = :id_producto
                AND id_vendedor = :id_vendedor";

        $consulta = $conexion->prepare($sql);
        $consulta->execute([
            ':id_producto' => $id_producto,
            ':id_vendedor' => $id_usuario
        ]);

        $_SESSION['mensaje'] = $consulta->rowCount() > 0
            ? "La publicación se eliminó correctamente."
            : "No se encontró la publicación.";

        $_SESSION['tipo_mensaje'] = $consulta->rowCount() > 0
            ? "success"
            : "error";

        header("Location: vendedor.php#listing");
        exit;
    }

    // GUARDAR O EDITAR PUBLICACIÓN
    if ($accion === 'guardar') {

        $id_producto = (int) ($_POST['id_producto'] ?? 0);

        $datos_formulario = [
            'id_producto' => $id_producto,
            'nombre' => trim($_POST['nombre'] ?? ''),
            'categoria' => trim($_POST['categoria'] ?? ''),
            'precio' => trim($_POST['precio'] ?? ''),
            'unidad' => trim($_POST['unidad'] ?? ''),
            'cantidad' => trim($_POST['cantidad'] ?? ''),
            'disponibilidad' => trim($_POST['disponibilidad'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'ubicacion' => trim($_POST['ubicacion'] ?? ''),
            'modalidad' => trim($_POST['modalidad'] ?? ''),
            'fotografia' => ''
        ];

        // Validaciones
        if (
            $datos_formulario['nombre'] === '' ||
            $datos_formulario['descripcion'] === '' ||
            !in_array($datos_formulario['categoria'], $categorias, true)
        ) {
            $mensaje = "Completa los campos obligatorios y selecciona una categoría válida.";
            $tipo_mensaje = "error";

        } elseif (
            $datos_formulario['precio'] !== '' &&
            (!is_numeric($datos_formulario['precio']) || $datos_formulario['precio'] < 0)
        ) {
            $mensaje = "El precio debe ser un número válido.";
            $tipo_mensaje = "error";

        } elseif (
            $datos_formulario['cantidad'] !== '' &&
            (!is_numeric($datos_formulario['cantidad']) || $datos_formulario['cantidad'] < 0)
        ) {
            $mensaje = "La cantidad debe ser un número válido.";
            $tipo_mensaje = "error";

        } else {

            $precio = $datos_formulario['precio'] === ''
                ? null
                : $datos_formulario['precio'];

            $cantidad = $datos_formulario['cantidad'] === ''
                ? 0
                : $datos_formulario['cantidad'];

            // Conservar la fotografía anterior si estamos editando
            $fotografia = '';

            if ($id_producto > 0) {

                $consulta_foto = $conexion->prepare(
                    "SELECT fotografia FROM productos
                     WHERE id_producto = :id_producto
                     AND id_vendedor = :id_vendedor"
                );

                $consulta_foto->execute([
                    ':id_producto' => $id_producto,
                    ':id_vendedor' => $id_usuario
                ]);

                $fotografia = $consulta_foto->fetchColumn() ?: '';
            }

            // Procesar una nueva fotografía
            if (
                isset($_FILES['fotografia']) &&
                $_FILES['fotografia']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES['fotografia']['error'] !== UPLOAD_ERR_OK) {

                    $mensaje = "Ocurrió un error al subir la fotografía.";
                    $tipo_mensaje = "error";

                } elseif ($_FILES['fotografia']['size'] > 5 * 1024 * 1024) {

                    $mensaje = "La fotografía no debe superar los 5 MB.";
                    $tipo_mensaje = "error";

                } else {

                    $tipos_permitidos = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp'
                    ];

                    $archivo_temporal = $_FILES['fotografia']['tmp_name'];

                    $tipo_archivo = (new finfo(FILEINFO_MIME_TYPE))
                        ->file($archivo_temporal);

                    if (
                        !isset($tipos_permitidos[$tipo_archivo]) ||
                        getimagesize($archivo_temporal) === false
                    ) {

                        $mensaje = "Selecciona una imagen JPG, PNG o WEBP válida.";
                        $tipo_mensaje = "error";

                    } else {

                        $carpeta = __DIR__ . "/assets/img/productos/";

                        if (!is_dir($carpeta)) {
                            mkdir($carpeta, 0755, true);
                        }

                        $nombre_archivo = bin2hex(random_bytes(16))
                            . "." . $tipos_permitidos[$tipo_archivo];

                        if (move_uploaded_file(
                            $archivo_temporal,
                            $carpeta . $nombre_archivo
                        )) {

                            $fotografia = "assets/img/productos/" . $nombre_archivo;

                        } else {

                            $mensaje = "No se pudo guardar la fotografía.";
                            $tipo_mensaje = "error";
                        }
                    }
                }
            }

            // Guardar únicamente si no ocurrió ningún error con la fotografía
            if ($mensaje === '') {

                try {

                    if ($id_producto > 0) {

                        // Actualizar publicación existente
                        $sql = "UPDATE productos SET
                                    nombre = :nombre,
                                    categoria = :categoria,
                                    precio = :precio,
                                    unidad = :unidad,
                                    cantidad = :cantidad,
                                    disponibilidad = :disponibilidad,
                                    descripcion = :descripcion,
                                    ubicacion = :ubicacion,
                                    modalidad = :modalidad,
                                    fotografia = :fotografia
                                WHERE id_producto = :id_producto
                                AND id_vendedor = :id_vendedor
                                AND estado <> 'eliminado'";

                        $consulta = $conexion->prepare($sql);

                        $consulta->execute([
                            ':nombre' => $datos_formulario['nombre'],
                            ':categoria' => $datos_formulario['categoria'],
                            ':precio' => $precio,
                            ':unidad' => $datos_formulario['unidad'],
                            ':cantidad' => $cantidad,
                            ':disponibilidad' => $datos_formulario['disponibilidad'],
                            ':descripcion' => $datos_formulario['descripcion'],
                            ':ubicacion' => $datos_formulario['ubicacion'],
                            ':modalidad' => $datos_formulario['modalidad'],
                            ':fotografia' => $fotografia,
                            ':id_producto' => $id_producto,
                            ':id_vendedor' => $id_usuario
                        ]);

                        $_SESSION['mensaje'] = $consulta->rowCount() > 0
                            ? "La publicación se actualizó correctamente."
                            : "No hubo cambios o no se encontró la publicación.";

                    } else {

                        // Insertar nueva publicación
                        $sql = "INSERT INTO productos (
                                    nombre,
                                    categoria,
                                    precio,
                                    unidad,
                                    cantidad,
                                    disponibilidad,
                                    descripcion,
                                    ubicacion,
                                    modalidad,
                                    fotografia,
                                    id_vendedor
                                ) VALUES (
                                    :nombre,
                                    :categoria,
                                    :precio,
                                    :unidad,
                                    :cantidad,
                                    :disponibilidad,
                                    :descripcion,
                                    :ubicacion,
                                    :modalidad,
                                    :fotografia,
                                    :id_vendedor
                                )";

                        $consulta = $conexion->prepare($sql);

                        $consulta->execute([
                            ':nombre' => $datos_formulario['nombre'],
                            ':categoria' => $datos_formulario['categoria'],
                            ':precio' => $precio,
                            ':unidad' => $datos_formulario['unidad'],
                            ':cantidad' => $cantidad,
                            ':disponibilidad' => $datos_formulario['disponibilidad'],
                            ':descripcion' => $datos_formulario['descripcion'],
                            ':ubicacion' => $datos_formulario['ubicacion'],
                            ':modalidad' => $datos_formulario['modalidad'],
                            ':fotografia' => $fotografia,
                            ':id_vendedor' => $id_usuario
                        ]);

                        $_SESSION['mensaje'] = "Tu producto se publicó correctamente.";
                    }

                    $_SESSION['tipo_mensaje'] = "success";

                    header("Location: vendedor.php#listing");
                    exit;

                } catch (PDOException $e) {

                    $mensaje = "No fue posible guardar la publicación. Verifica los datos.";
                    $tipo_mensaje = "error";
                }
            }
        }
    }
}

// Recuperar mensaje después de guardar o eliminar
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'success';

    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
}

// Consultar producto para editar
if (isset($_GET['editar'])) {

    $id_editar = (int) $_GET['editar'];

    $sql = "SELECT *
            FROM productos
            WHERE id_producto = :id_producto
            AND id_vendedor = :id_vendedor
            AND estado <> 'eliminado'";

    $consulta = $conexion->prepare($sql);
    $consulta->execute([
        ':id_producto' => $id_editar,
        ':id_vendedor' => $id_usuario
    ]);

    $producto_editar = $consulta->fetch();

    if ($producto_editar) {
        $datos_formulario = $producto_editar;
    }
}

// Consultar publicaciones del vendedor
$sql = "SELECT *
        FROM productos
        WHERE id_vendedor = :id_vendedor
        AND estado <> 'eliminado'
        ORDER BY fecha_publicacion DESC";

$consulta = $conexion->prepare($sql);
$consulta->execute([
    ':id_vendedor' => $id_usuario
]);

$productos = $consulta->fetchAll();

// Contar publicaciones activas
$sql = "SELECT COUNT(*)
        FROM productos
        WHERE id_vendedor = :id_vendedor
        AND estado = 'activo'";

$consulta = $conexion->prepare($sql);
$consulta->execute([
    ':id_vendedor' => $id_usuario
]);

$total_activos = (int) $consulta->fetchColumn();

$total_publicaciones = count($productos);

// Iniciales del usuario
$partes_nombre = preg_split('/\s+/', trim($nombre_usuario));
$iniciales = '';

foreach (array_slice($partes_nombre, 0, 2) as $parte) {
    $iniciales .= strtoupper(substr($parte, 0, 1));
}

// Determinar si estamos editando
$editando = !empty($datos_formulario['id_producto']);
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Panel vendedor · Raíz Local</title>
    <link rel="stylesheet" href="assets/css/styles.css">

    <style>
    /* =====================================
       BANNER DEL VENDEDOR CON IMAGEN
    ===================================== */

    .seller-banner {
        background-image:
            linear-gradient(rgba(41, 63, 49, 0.55), rgba(41, 63, 49, 0.65)),
            url("https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=2200&q=90");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        color: #fffefa;
    }

    .seller-banner h2,
    .seller-banner p,
    .seller-banner small {
        color: #fffefa;
    }

    .seller-banner h2 em {
        color: #c5d3b9;
    }

    @media (max-width: 800px) {
        .seller-banner {
            background-position: center top;
        }
    }
    </style>
</head>

<body class="dashboard">

    <aside class="sidebar">

        <a class="brand" href="index.php">
            <img src="assets/img/raiz-logo.svg">
            <span>
                <b>raíz local</b>
                <small>PANEL DE PRODUCTOR</small>
            </span>
        </a>

        <label>GESTIÓN DE TIENDA</label>

        <a class="side active" href="vendedor.php">▦　Resumen</a>

        <a class="side" href="#listing">
            ▤　Mis publicaciones
            <i><?= $total_publicaciones ?></i>
        </a>

        <a class="side" href="#publish">＋　Publicar producto</a>

        <a class="side" href="perfil.php?tipo=vendedor">
            ⚙　Mi perfil y ubicación
        </a>

        <div class="side-bottom">

            <div class="side-promo">
                ♧
                <h3>Tu trabajo mueve<br>la comunidad.</h3>
                <p>Cuéntale a Tolimán lo que tienes para ofrecer.</p>
            </div>

            <?php if ($_SESSION['rol'] === 'ambos'): ?>
                <a class="side" href="cliente.php">⇥　Cambiar a modo cliente</a>
            <?php endif; ?>

        </div>

        <a href="cerrar_sesion.php" class="side">
            Cerrar sesión
        </a>

    </aside>

    <main class="dash-main">

        <header class="dashbar">

            <button class="mobile-menu" id="menuToggle">☰</button>

            <span>Mi espacio / <b>Resumen del vendedor</b></span>

            <a class="user" href="perfil.php?tipo=vendedor">
                <b class="avatar green"><?= escapar($iniciales) ?></b>
                <span>
                    <b><?= escapar($nombre_usuario) ?></b>
                    <small>Vendedor　⌄</small>
                </span>
            </a>

        </header>

        <section class="welcome">

            <span class="kicker">✳ TU ESPACIO DE TRABAJO</span>

            <h1>Buen día, <?= escapar($nombre_usuario) ?>. <em>🌱</em></h1>

            <p>Administra tus productos y haz que más personas descubran lo que nace de tu tierra.</p>

            <a class="btn green" href="#publish">＋ Publicar producto</a>

        </section>

        <section class="dash-banner seller-banner">

            <div>
                <small>TU TIENDA, TU HISTORIA</small>
                <h2>Lo que produces<br><em>merece ser visto.</em></h2>
                <p>Mantén tu catálogo al día para conectar con la comunidad.</p>
            </div>

            <span class="big-art">🌽　🌿</span>

        </section>

        <div class="stats">

            <article>
                <small>PUBLICACIONES ACTIVAS</small>
                <b id="activeCount"><?= $total_activos ?></b>
                <span>En tu catálogo</span>
            </article>

            <article>
                <small>TOTAL DE PUBLICACIONES</small>
                <b><?= $total_publicaciones ?></b>
                <span>Productos registrados</span>
            </article>

            <article>
                <small>UBICACIÓN</small>
                <b>Tolimán</b>
                <span>Querétaro, México</span>
            </article>

        </div>

        <?php if ($mensaje !== ''): ?>
            <div class="toast" style="display:block; position:relative; margin:15px 0; padding:12px; background:<?= $tipo_mensaje === 'success' ? '#e1f2e5' : '#f8dddd' ?>; color:#263b2c;">
                <?= escapar($mensaje) ?>
            </div>
        <?php endif; ?>

        <section class="panel" id="listing">

            <div class="heading">

                <div>
                    <span class="kicker">TU CATÁLOGO</span>
                    <h2>Mis publicaciones</h2>
                </div>

                <small><?= $total_publicaciones ?> REGISTROS</small>

            </div>

            <p class="muted">
                Desde aquí puedes editar tus productos o eliminarlos del catálogo.
            </p>

            <div id="sellerProducts">

                <?php if (empty($productos)): ?>

                    <p class="muted">
                        Todavía no tienes productos publicados. Utiliza el formulario para agregar tu primer producto.
                    </p>

                <?php else: ?>

                    <?php foreach ($productos as $producto): ?>

                        <article style="padding:18px; margin:15px 0; border:1px solid #e5e5e5; border-radius:12px;">

                            <?php if (!empty($producto['fotografia'])): ?>
                                <img
                                    src="<?= escapar($producto['fotografia']) ?>"
                                    alt="<?= escapar($producto['nombre']) ?>"
                                    style="width:120px;height:100px;object-fit:cover;border-radius:10px;"
                                >
                            <?php endif; ?>

                            <div style="display:flex; justify-content:space-between; gap:15px; flex-wrap:wrap;">

                                <div>

                                    <h3><?= escapar($producto['nombre']) ?></h3>

                                    <p>
                                        <b>Categoría:</b>
                                        <?= escapar($producto['categoria']) ?>
                                    </p>

                                    <p>
                                        <b>Precio:</b>
                                        <?= $producto['precio'] !== null
                                            ? '$' . number_format((float)$producto['precio'], 2) . ' MXN'
                                            : 'Por acordar' ?>
                                    </p>

                                    <p>
                                        <b>Cantidad:</b>
                                        <?= escapar($producto['cantidad']) ?>
                                        <?= escapar($producto['unidad']) ?>
                                    </p>

                                    <p>
                                        <b>Disponibilidad:</b>
                                        <?= escapar($producto['disponibilidad']) ?>
                                    </p>

                                    <p>
                                        <b>Estado:</b>
                                        <?= escapar($producto['estado']) ?>
                                    </p>

                                    <p><?= escapar($producto['descripcion']) ?></p>

                                </div>

                                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">

                                    <a
                                        href="vendedor.php?editar=<?= (int)$producto['id_producto'] ?>#publish"
                                        class="btn outline"
                                        style="text-decoration:none;"
                                    >
                                        Editar
                                    </a>

                                    <form method="POST" action="vendedor.php#listing"
                                          onsubmit="return confirm('¿Deseas eliminar esta publicación?');">

                                        <input type="hidden" name="token" value="<?= escapar($token) ?>">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_producto" value="<?= (int)$producto['id_producto'] ?>">

                                        <button type="submit" class="btn outline">
                                            Eliminar
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </section>

        <section class="panel" id="publish">

            <div class="heading">

                <div>
                    <span class="kicker">CATÁLOGO COMUNITARIO</span>

                    <h2 id="formTitle">
                        <?= $editando ? 'Editar publicación' : 'Publicar un producto' ?>
                    </h2>
                </div>

                <small>FORMULARIO</small>

            </div>

            <form method="POST" action="vendedor.php#publish"
            id="productForm" class="form-grid" enctype="multipart/form-data">

                <input type="hidden" name="token" value="<?= escapar($token) ?>">
                <input type="hidden" name="accion" value="guardar">

                <input
                    type="hidden"
                    name="id_producto"
                    value="<?= escapar($datos_formulario['id_producto']) ?>"
                >

                <label>
                    Nombre del producto *
                    <input
                        type="text"
                        name="nombre"
                        required
                        placeholder="Ej. Queso artesanal"
                        value="<?= escapar($datos_formulario['nombre']) ?>"
                    >
                </label>

                <label>
                    Categoría *
                    <select name="categoria" required>

                        <option value="">Selecciona</option>

                        <?php foreach ($categorias as $cat): ?>
                            <option
                                value="<?= escapar($cat) ?>"
                                <?= $datos_formulario['categoria'] === $cat ? 'selected' : '' ?>
                            >
                                <?= escapar($cat) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </label>

                <label>
                    Precio (MXN)
                    <input
                        type="number"
                        name="precio"
                        min="0"
                        step="0.01"
                        placeholder="Ej. 50"
                        value="<?= escapar($datos_formulario['precio']) ?>"
                    >
                </label>

                <label>
                    Unidad de venta
                    <select name="unidad">

                        <?php foreach (['pieza', 'kg', 'docena', 'litro', 'paquete', 'lote'] as $unidad): ?>

                            <option
                                value="<?= escapar($unidad) ?>"
                                <?= $datos_formulario['unidad'] === $unidad ? 'selected' : '' ?>
                            >
                                <?= escapar($unidad) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </label>

                <label>
                    Cantidad disponible
                    <input
                        type="number"
                        name="cantidad"
                        min="0"
                        step="0.01"
                        placeholder="Ej. 10"
                        value="<?= escapar($datos_formulario['cantidad']) ?>"
                    >
                </label>

                <label>
                    Disponibilidad
                    <select name="disponibilidad">

                        <?php foreach (['Disponible', 'Agotado', 'Por encargo'] as $opcion): ?>

                            <option
                                value="<?= escapar($opcion) ?>"
                                <?= $datos_formulario['disponibilidad'] === $opcion ? 'selected' : '' ?>
                            >
                                <?= escapar($opcion) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </label>

                <label class="wide">
                    Descripción *
                    <textarea
                        name="descripcion"
                        rows="3"
                        required
                        placeholder="Características, temporada, cómo lo produces..."
                    ><?= escapar($datos_formulario['descripcion']) ?></textarea>
                </label>

                <label>
                    Ubicación / entrega
                    <input
                        type="text"
                        name="ubicacion"
                        placeholder="Ej. Centro de Tolimán"
                        value="<?= escapar($datos_formulario['ubicacion']) ?>"
                    >
                </label>

                <label>
                    Modalidad
                    <select name="modalidad">

                        <?php foreach (['Venta', 'Trueque', 'Venta o trueque'] as $modo): ?>

                            <option
                                value="<?= escapar($modo) ?>"
                                <?= $datos_formulario['modalidad'] === $modo ? 'selected' : '' ?>
                            >
                                <?= escapar($modo) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </label>

                <label class="wide">
                    Fotografía del producto
                    <input
                        type="file"
                        name="fotografia"
                        accept="image/jpeg,image/png,image/webp"
                    >
                    <small>Formatos permitidos: JPG, PNG y WEBP. Máximo 5 MB.</small>
                </label>

                <div class="wide form-actions">

                    <a href="vendedor.php#publish" class="btn outline"
                       style="text-decoration:none;">
                        Limpiar
                    </a>

                    <button type="submit" class="btn green">
                        <?= $editando ? 'Actualizar publicación →' : 'Guardar publicación →' ?>
                    </button>

                </div>

            </form>

        </section>

        <footer class="dashfoot">
            ✳ Tu trabajo fortalece nuestra comunidad.
            <span>Raíz Local · Tolimán</span>
        </footer>

    </main>

</body>
</html>