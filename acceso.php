<?php
session_start();
require_once "conexion.php";

$mensaje = "";
$tipo_mensaje = "";

// =====================================
// FUNCIÓN PARA INICIAR SESIÓN
// =====================================
function ingresarUsuario($usuario) {
    session_regenerate_id(true);
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['rol'] = $usuario['rol'];

    // Cliente entra al catálogo.
    // Vendedor y ambos entran al panel de vendedor.
    if ($usuario['rol'] === 'cliente') {
        header("Location: cliente.php");
    } else {
        header("Location: vendedor.php");
    }
    exit;
}

// ==VERIFICAR SESIÓN EXISTENTE
if (isset($_SESSION['id_usuario'])) {
    if ($_SESSION['rol'] === 'cliente') {
        header("Location: cliente.php");
    } else {
        header("Location: vendedor.php");
    }
    exit;
}

// ==PROCESAR FORMULARIOS
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $accion = $_POST['accion'] ?? '';

    // == REGISTRO DE USUARIO
    if ($accion === "registro") {

        $nombre = trim($_POST['nombre'] ?? '');
        $nombre_publico = trim($_POST['nombre_publico'] ?? '');
        $correo = strtolower(trim($_POST['correo'] ?? ''));
        $contrasena = $_POST['contrasena'] ?? '';
        $telefono = trim($_POST['telefono'] ?? '');
        $rol = $_POST['rol'] ?? '';

        // Validar información recibida.
        if (
            $nombre === '' ||
            !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
            strlen($contrasena) < 8 ||
            !in_array($rol, ['cliente', 'vendedor', 'ambos'], true)
        ) {
            $mensaje = "Revisa tus datos. La contraseña debe tener al menos 8 caracteres.";
            $tipo_mensaje = "error";
        } else {
            try {
                // Proteger contraseña.
                $contrasena_segura = password_hash(
                    $contrasena,
                    PASSWORD_DEFAULT
                );

                // Iniciar transacción.
                $conexion->beginTransaction();

                // Crear usuario.
                $sql = "INSERT INTO usuarios
                        (nombre, nombre_publico, correo, contrasena, telefono, rol)
                        VALUES
                        (:nombre, :nombre_publico, :correo, :contrasena, :telefono, :rol)";

                $consulta = $conexion->prepare($sql);

                $consulta->execute([
                    ':nombre' => $nombre,
                    ':nombre_publico' => $nombre_publico,
                    ':correo' => $correo,
                    ':contrasena' => $contrasena_segura,
                    ':telefono' => $telefono,
                    ':rol' => $rol
                ]);

                // Obtener ID del nuevo usuario.
                $id_usuario = $conexion->lastInsertId();

                // Si es vendedor o ambos, crear su perfil de vendedor
                if ($rol === 'vendedor' || $rol === 'ambos') {

                    // Obtener la aceptación de intercambios
                    $acepta_intercambio = isset($_POST['acepta_intercambio']) ? 1 : 0;

                    $sql_perfil = "INSERT INTO perfiles_vendedor
                                (id_usuario, acepta_intercambio)
                                VALUES
                                (:id_usuario, :acepta_intercambio)";

                    $consulta_perfil = $conexion->prepare($sql_perfil);

                    $consulta_perfil->execute([
                        ':id_usuario' => $id_usuario,
                        ':acepta_intercambio' => $acepta_intercambio
                    ]);
                }

                // Confirmar ambos registros.
                $conexion->commit();

                // Iniciar sesión automáticamente.
                $nuevo_usuario = [
                    'id_usuario' => $id_usuario,
                    'nombre' => $nombre,
                    'rol' => $rol
                ];

                ingresarUsuario($nuevo_usuario);

            } catch (PDOException $e) {

                // Si algo falla, deshacer los cambios.
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }

                if ($e->getCode() == '23000') {
                    $mensaje = "Este correo ya está registrado.";
                } else {
                    $mensaje = "No fue posible crear tu cuenta. Intenta nuevamente.";
                }

                $tipo_mensaje = "error";
            }
        }
    }

    // =================================
    // INICIO DE SESIÓN
    // =================================

    if ($accion === "login") {

        $correo = strtolower(trim($_POST['correo'] ?? ''));
        $contrasena = $_POST['contrasena'] ?? '';

        $sql = "SELECT
                    id_usuario,
                    nombre,
                    nombre_publico,
                    correo,
                    contrasena,
                    rol,
                    activo
                FROM usuarios
                WHERE correo = :correo
                LIMIT 1";

        $consulta = $conexion->prepare($sql);

        $consulta->execute([
            ':correo' => $correo
        ]);

        $usuario = $consulta->fetch();

        if (
            $usuario &&
            (int)$usuario['activo'] === 1 &&
            password_verify($contrasena, $usuario['contrasena'])
        ) {

            ingresarUsuario($usuario);

        } else {

            $mensaje = "Correo o contraseña incorrectos, o cuenta inactiva.";
            $tipo_mensaje = "error";
        }
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ingresar · Raíz Local</title>
    <link rel="stylesheet" href="assets/css/styles.css">

<style>
/* =====================================
   DISEÑO DE ACCESO - RAÍZ LOCAL
===================================== */

body.access {
    background: #f4f1e8;
    min-height: 100vh;
    padding: 75px 5%;
    font-family: 'DM Sans', sans-serif;
}

/* Enlace para regresar */

.access .back {
    position: absolute;
    top: 25px;
    left: 5%;
    color: #405b45;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
}

.access .back:hover {
    color: #81936e;
}

/* Contenedor principal */

.access-wrap {
    max-width: 1100px;
    width: 100%;
    min-height: 650px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 0.9fr 1.1fr;
    background: #fffefa;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 12px 35px rgba(38, 63, 49, 0.09);
}

/* Panel izquierdo CON IMAGEN DE FONDO */

.access-story {
    background-image:
        linear-gradient(rgba(41, 63, 49, 0.55), rgba(41, 63, 49, 0.65)),
        url("https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=2200&q=90");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    color: #fffefa;
    padding: 45px 38px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.access-story .brand {
    display: flex;
    align-items: center;
    gap: 12px;
    color: inherit;
    text-decoration: none;
}

.access-story .brand img {
    width: 45px;
    height: 45px;
    object-fit: contain;
}

.access-story .brand span {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.access-story .brand b {
    font-family: 'Playfair Display', serif;
    font-size: 25px;
}

.access-story .brand small {
    font-size: 9px;
    letter-spacing: 2px;
    color: #cbd6c5;
}

.access-story .kicker {
    color: #c5d3b9;
    font-size: 10px;
    letter-spacing: 2px;
    font-weight: 700;
}

.access-story h1 {
    font-family: 'Playfair Display', serif;
    font-size: 43px;
    line-height: 1.2;
    font-weight: 500;
    margin: 18px 0;
}

.access-story h1 em {
    color: #c5d3b9;
}

.access-story p {
    max-width: 330px;
    color: #e0e6dc;
    font-size: 14px;
    line-height: 1.8;
}

.access-story > small {
    color: #c5d3b9;
    font-size: 10px;
    letter-spacing: 2px;
}

/* Panel de formularios */

.chooser {
    width: 100%;
    max-width: 600px;
    margin: auto;
    padding: 45px 55px;
}

.chooser > .kicker {
    color: #82936e;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 2px;
}

.chooser h2 {
    margin: 12px 0 25px;
    color: #293f31;
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    line-height: 1.25;
    font-weight: 500;
}

/* Títulos de formularios */

.chooser h3 {
    margin: 22px 0 15px;
    color: #344b39;
    font-size: 16px;
    font-weight: 700;
}

/* Formularios */

.chooser form {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
}

/* Etiquetas */

.chooser form label {
    margin-top: 8px;
    color: #526452;
    font-size: 12px;
    font-weight: 600;
}

/* Campos de texto */

.chooser form input,
.chooser form select {
    width: 100%;
    min-height: 43px;
    padding: 10px 12px;
    border: 1px solid #dce2d8;
    border-radius: 7px;
    background: #fff;
    color: #344b39;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    outline: none;
    transition: 0.2s;
}

.chooser form input::placeholder {
    color: #a5ada3;
}

.chooser form input:focus,
.chooser form select:focus {
    border-color: #81936e;
    box-shadow: 0 0 0 3px rgba(129, 147, 110, 0.12);
}

/* Botones */

.chooser form button {
    width: 100%;
    min-height: 44px;
    margin-top: 13px;
    border: none;
    border-radius: 7px;
    color: white;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
}

.chooser form button.green {
    background: #81936e;
}

.chooser form button.green:hover {
    background: #6c805b;
}

.chooser form button.dark {
    background: #293f31;
}

.chooser form button.dark:hover {
    background: #405b45;
}

/* Separador */

.chooser hr {
    margin: 27px 0;
    border: none;
    border-top: 1px solid #e7e9e2;
}

/* Mensajes */

.chooser .notice {
    padding: 12px 14px;
    margin: 12px 0;
    border-radius: 7px;
    background: #f9e9e5;
    color: #a34b3f;
    font-size: 13px;
}

/* =====================================
   ADAPTACIÓN A CELULARES
===================================== */

@media (max-width: 800px) {

    body.access {
        padding: 70px 20px 30px;
    }

    .access-wrap {
        grid-template-columns: 1fr;
    }

    .access-story {
        min-height: 280px;
        padding: 30px;
        gap: 25px;
        background-position: center top;
    }

    .access-story h1 {
        font-size: 34px;
    }

    .chooser {
        padding: 35px 30px;
    }
}

@media (max-width: 480px) {

    .access .back {
        left: 20px;
        top: 22px;
    }

    .access-story {
        padding: 25px 22px;
    }

    .chooser {
        padding: 30px 22px;
    }

    .chooser h2 {
        font-size: 28px;
    }
}

/* REGISTRO DESPLEGABLE */

.register-section {
    margin-top: 25px;
    border-top: 1px solid #e7e9e2;
    padding-top: 20px;
}

.register-toggle {
    width: 100%;
    padding: 15px;
    border: 1px solid #dce2d8;
    border-radius: 8px;
    background: #f8f8f3;
    color: #344b39;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 7px;
    font-family: 'DM Sans', sans-serif;
    text-align: left;
    transition: 0.2s;
}

.register-toggle:hover {
    background: #eef1e9;
    border-color: #81936e;
}

.register-toggle span {
    font-size: 12px;
    color: #718071;
}

.register-toggle strong {
    font-size: 14px;
    color: #405b45;
}

/* Contenido desplegable */

.register-content {
    display: none;
    padding-top: 15px;
    animation: aparecer 0.3s ease;
}

.register-content.active {
    display: block;
}

@keyframes aparecer {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* OPCIÓN DE INTERCAMBIOS */

.intercambio-opcion {
    display: none;
    margin-top: 12px;
    padding: 14px;
    background: #f4f6ef;
    border: 1px solid #dce2d8;
    border-radius: 8px;
}

.intercambio-opcion label {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #405b45;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.intercambio-opcion input[type="checkbox"] {
    width: 16px;
    min-height: 16px;
    margin: 0;
    accent-color: #81936e;
}

.intercambio-opcion small {
    display: block;
    margin: 8px 0 0 25px;
    color: #718071;
    font-size: 11px;
    line-height: 1.5;
}

</style>
</head>
<body class="access">
    <a class="back" href="index.php">← Volver a la portada</a>

    <main class="access-wrap">

        <section class="access-story">

            <a class="brand" href="index.php">
                <img src="assets/img/raiz-logo.svg">
                <span>
                    <b>raíz local</b>
                    <small>TOLIMÁN · QUERÉTARO</small>
                </span>
            </a>

            <div>
                <span class="kicker">QUÉ BUENO TENERTE AQUÍ</span>
                <h1>Tu comunidad.<br><em>Tu manera de sumar.</em></h1>
                <p>Descubre productos locales o administra tus publicaciones como productor.</p>
            </div>

            <small>HECHO CERCA. CON ORGULLO. ✳</small>

        </section>

        <section class="chooser">

            <span class="kicker">RAÍZ LOCAL</span>
            <h2>Bienvenido a<br>nuestra comunidad.</h2>

            <?php if ($mensaje !== ""): ?>
                <div class="notice">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <!-- INICIAR SESIÓN -->
            <h3>Iniciar sesión</h3>

            <form method="POST" action="acceso.php">

                <input type="hidden" name="accion" value="login">

                <label for="correo_login">Correo electrónico</label>
                <input
                    type="email"
                    id="correo_login"
                    name="correo"
                    placeholder="tucorreo@ejemplo.com"
                    required
                >

                <label for="contrasena_login">Contraseña</label>
                <input
                    type="password"
                    id="contrasena_login"
                    name="contrasena"
                    placeholder="Tu contraseña"
                    required
                >

                <button type="submit" class="btn green">
                    Ingresar
                </button>

            </form>

            <!-- REGISTRO DESPLEGABLE -->
            <div class="register-section">

                <button type="button"
                        class="register-toggle"
                        onclick="toggleRegistro()">
                    <span>¿Aún no tienes una cuenta?</span>
                    <strong id="textoRegistro">Crear una cuenta +</strong>
                </button>

                <div id="formularioRegistro" class="register-content">

                    <h3>Únete a Raíz Local</h3>

                    <form method="POST" action="acceso.php">

                        <input type="hidden" name="accion" value="registro">

                        <label for="nombre">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre"
                            placeholder="Escribe tu nombre"
                            maxlength="100" required>

                        <label for="nombre_publicp">Nombre público / negocio</label>
                        <input type="text" id="nombre_publicp"
                            name="nombre_publico"
                            placeholder="¿Cómo te reconocen?"
                            maxlength="200" required>

                        <label for="correo_registro">Correo electrónico</label>
                        <input type="email" id="correo_registro"
                            name="correo"
                            placeholder="tucorreo@ejemplo.com"
                            maxlength="120" required>

                        <label for="telefono">Teléfono / WhatsApp</label>
                        <input type="tel" id="telefono" name="telefono"
                            placeholder="Ej. 524421234567"
                            maxlength="20">

                        <label for="contrasena_registro">Contraseña</label>
                        <input type="password" id="contrasena_registro"
                            name="contrasena"
                            placeholder="Mínimo 8 caracteres"
                            minlength="8" required>

                        <label for="rol">¿Cómo deseas participar?</label>
                        <select id="rol" name="rol" required onchange="mostrarIntercambio()">
                            <option value="">Selecciona una opción</option>
                            <option value="cliente">Cliente</option>
                            <option value="vendedor">Vendedor / Productor</option>
                            <option value="ambos">Ambos</option>
                        </select>

                        <!-- ACEPTAR INTERCAMBIOS O TRUEQUES -->
                        <div id="campoIntercambio" class="intercambio-opcion">
                            <label>
                                <input
                                    type="checkbox"
                                    name="acepta_intercambio"
                                    id="acepta_intercambio"
                                    value="1"
                                >
                                Acepto intercambios o trueques.
                            </label>
                            <small>
                                Permite que otros usuarios sepan que estás dispuesto
                                a intercambiar tus productos.
                            </small>
                        </div>

                        <button type="submit" class="btn dark">
                            Crear mi cuenta
                        </button>

                    </form>

                </div>

            </div>

        </section>

    </main>

    <script>
    function toggleRegistro() {

        const formulario = document.getElementById('formularioRegistro');
        const texto = document.getElementById('textoRegistro');

        formulario.classList.toggle('active');

        if (formulario.classList.contains('active')) {
            texto.textContent = 'Cerrar formulario −';
        } else {
            texto.textContent = 'Crear una cuenta +';
        }
    }

    // Mostrar opción de intercambios según el tipo de usuario
    function mostrarIntercambio() {
        const rol = document.getElementById('rol').value;
        const campo = document.getElementById('campoIntercambio');
        const checkbox = document.getElementById('acepta_intercambio');

        if (rol === 'vendedor' || rol === 'ambos') {
            campo.style.display = 'block';
        } else {
            campo.style.display = 'none';
            checkbox.checked = false;
        }
    }
    </script>

</body>
</html>