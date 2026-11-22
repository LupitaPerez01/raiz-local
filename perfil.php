<?php
session_start();
require_once "conexion.php";
// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: acceso.php");
    exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];
$rol = $_SESSION['rol'];

// Determinar qué perfil se está consultando
$tipo = $_GET['tipo'] ?? 'cliente';

if (!in_array($tipo, ['cliente', 'vendedor'], true)) {
    $tipo = 'cliente';
}

// Verificar permisos según el rol
if ($rol === 'cliente') {
    $tipo = 'cliente';
} elseif ($rol === 'vendedor') {
    $tipo = 'vendedor';
} elseif (!in_array($rol, ['ambos'], true)) {
    header("Location: acceso.php");
    exit;
}

$seller = ($tipo === 'vendedor');

// Token de seguridad
if (empty($_SESSION['token_perfil'])) {
    $_SESSION['token_perfil'] = bin2hex(random_bytes(32));
}

// Función para proteger contenido HTML
function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$mensaje = '';
$tipo_mensaje = '';

// Obtener información del usuario
$sql_usuario = "SELECT
                    nombre,
                    nombre_publico,
                    correo,
                    telefono
                FROM usuarios
                WHERE id_usuario = :id_usuario";

$consulta_usuario = $conexion->prepare($sql_usuario);
$consulta_usuario->execute([
    ':id_usuario' => $id_usuario
]);

$usuario = $consulta_usuario->fetch();

if (!$usuario) {
    die("No se encontró la información del usuario.");
}

// Obtener información del perfil de vendedor
$perfil_vendedor = [
    'municipio' => '',
    'localidad' => '',
    'descripcion' => '',
    'tipo_productor' => '',
    'horario_atencion' => '',
    'punto_entrega' => '',
    'acepta_intercambio' => 0
];

if ($seller) {

    $consulta_perfil = $conexion->prepare(
        "SELECT municipio, localidad, descripcion,
                tipo_productor, horario_atencion,
                punto_entrega, acepta_intercambio
         FROM perfiles_vendedor
         WHERE id_usuario = :id_usuario"
    );

    $consulta_perfil->execute([
        ':id_usuario' => $id_usuario
    ]);

    $datos_perfil = $consulta_perfil->fetch();

    if ($datos_perfil) {
        $perfil_vendedor = array_merge(
            $perfil_vendedor,
            $datos_perfil
        );
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['token'] ?? '';

    if (!hash_equals($_SESSION['token_perfil'], $token)) {

        $mensaje = "La solicitud no es válida.";
        $tipo_mensaje = "error";

    } else {

        $nombre = trim($_POST['nombre'] ?? '');
        $nombre_publico = trim($_POST['nombre_publico'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        // Validar información general
        if ($nombre === '' || $correo === '') {

            $mensaje = "El nombre y el correo son obligatorios.";
            $tipo_mensaje = "error";

        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

            $mensaje = "Ingresa un correo electrónico válido.";
            $tipo_mensaje = "error";

        } else {

            // Comprobar que el correo no pertenezca a otro usuario
            $consulta_correo = $conexion->prepare(
                "SELECT id_usuario
                 FROM usuarios
                 WHERE correo = :correo
                 AND id_usuario <> :id_usuario"
            );

            $consulta_correo->execute([
                ':correo' => $correo,
                ':id_usuario' => $id_usuario
            ]);

            if ($consulta_correo->fetch()) {

                $mensaje = "Ese correo ya está registrado por otra cuenta.";
                $tipo_mensaje = "error";

            } else {

                try {

                    $conexion->beginTransaction();

                    // Actualizar datos generales
                    $actualizar_usuario = $conexion->prepare(
                        "UPDATE usuarios
                         SET nombre = :nombre,
                             nombre_publico = :nombre_publico,
                             correo = :correo,
                             telefono = :telefono
                         WHERE id_usuario = :id_usuario"
                    );

                    $actualizar_usuario->execute([
                        ':nombre' => $nombre,
                        ':nombre_publico' => $nombre_publico !== '' ? $nombre_publico : null,
                        ':correo' => $correo,
                        ':telefono' => $telefono !== '' ? $telefono : null,
                        ':id_usuario' => $id_usuario
                    ]);

                    // Actualizar datos adicionales del vendedor
                    if ($seller) {

                        $municipio = trim($_POST['municipio'] ?? '');
                        $localidad = trim($_POST['localidad'] ?? '');
                        $descripcion = trim($_POST['descripcion'] ?? '');
                        $tipo_productor = trim($_POST['tipo_productor'] ?? '');
                        $horario = trim($_POST['horario_atencion'] ?? '');
                        $punto_entrega = trim($_POST['punto_entrega'] ?? '');

                        $acepta_intercambio = isset($_POST['acepta_intercambio']) ? 1 : 0;

                        // Comprobar si ya existe el perfil
                        $comprobar_perfil = $conexion->prepare(
                            "SELECT id_perfil
                             FROM perfiles_vendedor
                             WHERE id_usuario = :id_usuario"
                        );

                        $comprobar_perfil->execute([
                            ':id_usuario' => $id_usuario
                        ]);

                        if ($comprobar_perfil->fetch()) {

                            // Actualizar perfil existente
                            $actualizar_perfil = $conexion->prepare(
                                "UPDATE perfiles_vendedor
                                 SET municipio = :municipio,
                                     localidad = :localidad,
                                     descripcion = :descripcion,
                                     tipo_productor = :tipo_productor,
                                     horario_atencion = :horario,
                                     punto_entrega = :punto_entrega,
                                     acepta_intercambio = :acepta_intercambio
                                 WHERE id_usuario = :id_usuario"
                            );

                        } else {

                            // Crear perfil si todavía no existe
                            $actualizar_perfil = $conexion->prepare(
                                "INSERT INTO perfiles_vendedor
                                 (municipio, localidad, descripcion,
                                  tipo_productor, horario_atencion,
                                  punto_entrega, acepta_intercambio,
                                  id_usuario)
                                 VALUES
                                 (:municipio, :localidad, :descripcion,
                                  :tipo_productor, :horario,
                                  :punto_entrega, :acepta_intercambio,
                                  :id_usuario)"
                            );
                        }

                        $actualizar_perfil->execute([
                            ':municipio' => $municipio,
                            ':localidad' => $localidad,
                            ':descripcion' => $descripcion,
                            ':tipo_productor' => $tipo_productor,
                            ':horario' => $horario,
                            ':punto_entrega' => $punto_entrega,
                            ':acepta_intercambio' => $acepta_intercambio,
                            ':id_usuario' => $id_usuario
                        ]);
                    }

                    $conexion->commit();

                    // Actualizar nombre en la sesión
                    $_SESSION['nombre'] = $nombre;

                    // Evitar reenviar el formulario al actualizar
                    $_SESSION['mensaje_perfil'] = "Tus datos se actualizaron correctamente.";

                    header("Location: perfil.php?tipo=" . $tipo);
                    exit;

                } catch (PDOException $e) {

                    if ($conexion->inTransaction()) {
                        $conexion->rollBack();
                    }

                    $mensaje = "No fue posible actualizar tus datos. Inténtalo nuevamente.";
                    $tipo_mensaje = "error";
                }
            }
        }
    }
}

// Recuperar mensaje después de guardar
if (!empty($_SESSION['mensaje_perfil'])) {
    $mensaje = $_SESSION['mensaje_perfil'];
    $tipo_mensaje = "success";
    unset($_SESSION['mensaje_perfil']);
}

// Volver a consultar los datos para mostrar los valores actuales
$consulta_usuario->execute([
    ':id_usuario' => $id_usuario
]);

$usuario = $consulta_usuario->fetch();

if ($seller) {
    $consulta_perfil->execute([
        ':id_usuario' => $id_usuario
    ]);

    $datos_perfil = $consulta_perfil->fetch();

    if ($datos_perfil) {
        $perfil_vendedor = array_merge(
            $perfil_vendedor,
            $datos_perfil
        );
    }
}

// Iniciales
$partes_nombre = explode(' ', trim($usuario['nombre']));
$iniciales = strtoupper(substr($partes_nombre[0], 0, 1));

if (count($partes_nombre) > 1) {
    $iniciales .= strtoupper(substr($partes_nombre[1], 0, 1));
}

$nombre_publico = $usuario['nombre_publico'] ?? '';
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mi perfil · Raíz Local</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body class="dashboard">

    <aside class="sidebar">

        <a class="brand" href="index.php">
            <img src="assets/img/raiz-logo.svg" alt="Raíz Local">
            <span>
                <b>raíz local</b>
                <small>MI PERFIL</small>
            </span>
        </a>

        <label>CUENTA</label>

        <a class="side" href="<?php echo $seller ? 'vendedor.php' : 'cliente.php'; ?>">
            ⌂　Volver al panel
        </a>

        <a class="side active" href="#">
            ⚙　Datos del perfil
        </a>

        <?php if ($rol === 'ambos'): ?>

            <?php if ($seller): ?>
                <a class="side" href="perfil.php?tipo=cliente">
                    ⇥　Ver perfil de cliente
                </a>
            <?php else: ?>
                <a class="side" href="perfil.php?tipo=vendedor">
                    ⇥　Ver perfil de vendedor
                </a>
            <?php endif; ?>

        <?php endif; ?>

        <div class="side-bottom">

            <div class="side-promo">
                ✳
                <h3>Tu perfil cuenta<br>tu historia.</h3>
                <p>Conecta con tu comunidad.</p>
            </div>

            <a href="cerrar_sesion.php" class="side">
                Cerrar sesión
            </a>

        </div>

    </aside>

    <main class="dash-main">

        <header class="dashbar">

            <button class="mobile-menu" id="menuToggle">☰</button>

            <span>Cuenta / <b>Editar perfil</b></span>

            <a class="user" href="#">
                <b class="avatar"><?php echo escapar($iniciales); ?></b>

                <span>
                    <b><?php echo escapar($usuario['nombre']); ?></b>
                    <small><?php echo $seller ? 'Vendedor' : 'Cliente'; ?></small>
                </span>
            </a>

        </header>

        <section class="welcome">

            <span class="kicker">✳ TU IDENTIDAD EN LA COMUNIDAD</span>

            <h1>Tu perfil, <em>a tu manera.</em></h1>

            <p>
                Actualiza tus datos y cómo te pueden encontrar
                las personas de Tolimán.
            </p>

        </section>

        <?php if ($mensaje !== ''): ?>

            <p class="muted">
                <?php echo escapar($mensaje); ?>
            </p>

        <?php endif; ?>

        <div class="profile-layout">

            <section class="profile-card">

                <div class="profile-cover"></div>

                <b class="profile-avatar-large">
                    <?php echo escapar($iniciales); ?>
                </b>

                <h2>
                    <?php echo escapar($usuario['nombre']); ?>
                </h2>

                <small>
                    <?php echo $seller ? 'Vendedor local' : 'Cliente de la comunidad'; ?>
                </small>

                <?php if ($seller): ?>

                    <p>
                        <?php echo escapar($perfil_vendedor['descripcion'] ?: 'Completa la descripción de tu perfil.'); ?>
                    </p>

                    <p>
                        ⌖
                        <?php
                        echo escapar(
                            trim($perfil_vendedor['municipio'] . ', ' . $perfil_vendedor['localidad'], ', ')
                            ?: 'Ubicación no registrada'
                        );
                        ?>
                    </p>

                <?php else: ?>

                    <p>
                        Miembro de la comunidad de Raíz Local.
                    </p>

                <?php endif; ?>

                <p>
                    ✉ <?php echo escapar($usuario['correo']); ?>
                </p>

                <?php if ($nombre_publico !== ''): ?>
                    <p>
                        <?php echo escapar($nombre_publico); ?>
                    </p>
                <?php endif; ?>

            </section>

            <section class="panel">

                <div class="heading">

                    <div>
                        <span class="kicker">INFORMACIÓN PERSONAL</span>
                        <h2>Editar mis datos</h2>
                    </div>

                    <small><?php echo $seller ? 'VENDEDOR' : 'CLIENTE'; ?></small>

                </div>

                <form method="POST" action="perfil.php?tipo=<?php echo escapar($tipo); ?>" class="form-grid">

                    <input
                        type="hidden"
                        name="token"
                        value="<?php echo escapar($_SESSION['token_perfil']); ?>"
                    >

                    <label>
                        Nombre *
                        <input
                            type="text"
                            name="nombre"
                            required
                            value="<?php echo escapar($usuario['nombre']); ?>"
                        >
                    </label>

                    <label>
                        Nombre público / negocio
                        <input
                            type="text"
                            name="nombre_publico"
                            value="<?php echo escapar($nombre_publico); ?>"
                        >
                    </label>

                    <label>
                        Correo electrónico *
                        <input
                            type="email"
                            name="correo"
                            required
                            value="<?php echo escapar($usuario['correo']); ?>"
                        >
                    </label>

                    <label>
                        Teléfono de contacto
                        <input
                            type="tel"
                            name="telefono"
                            value="<?php echo escapar($usuario['telefono'] ?? ''); ?>"
                        >
                    </label>

                    <?php if ($seller): ?>

                        <label>
                            Municipio
                            <input
                                type="text"
                                name="municipio"
                                value="<?php echo escapar($perfil_vendedor['municipio']); ?>"
                            >
                        </label>

                        <label>
                            Localidad / colonia
                            <input
                                type="text"
                                name="localidad"
                                value="<?php echo escapar($perfil_vendedor['localidad']); ?>"
                            >
                        </label>

                        <label class="wide">
                            Descripción breve
                            <textarea name="descripcion" rows="3"><?php echo escapar($perfil_vendedor['descripcion']); ?></textarea>
                        </label>

                        <label>
                            Tipo de productor
                            <select name="tipo_productor">
                                <option value="">Selecciona una opción</option>

                                <?php
                                $tipos_productor = [
                                    'Productor agrícola',
                                    'Artesano/a',
                                    'Ganadería / animales',
                                    'Productor de alimentos',
                                    'Otro'
                                ];

                                foreach ($tipos_productor as $opcion):
                                ?>

                                    <option
                                        value="<?php echo escapar($opcion); ?>"
                                        <?php echo $perfil_vendedor['tipo_productor'] === $opcion ? 'selected' : ''; ?>
                                    >
                                        <?php echo escapar($opcion); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </label>

                        <label>
                            Horario de atención
                            <input
                                type="text"
                                name="horario_atencion"
                                value="<?php echo escapar($perfil_vendedor['horario_atencion']); ?>"
                                placeholder="Lunes a sábado, 9:00–17:00"
                            >
                        </label>

                        <label class="wide">
                            Ubicación / punto de entrega
                            <input
                                type="text"
                                name="punto_entrega"
                                value="<?php echo escapar($perfil_vendedor['punto_entrega']); ?>"
                                placeholder="Describe dónde pueden recoger el producto"
                            >
                        </label>
                    <?php endif; ?>

                    <div class="wide form-actions">

                        <a
                            class="btn outline"
                            href="<?php echo $seller ? 'vendedor.php' : 'cliente.php'; ?>"
                        >
                            Cancelar
                        </a>

                        <button type="submit" class="btn green">
                            Guardar cambios ✓
                        </button>

                    </div>

                </form>

            </section>

        </div>

        <footer class="dashfoot">
            ✳ Apoya al campo, fortalece nuestra comunidad.
            <span>Raíz Local · Tolimán</span>
        </footer>

    </main>

</body>
</html>
