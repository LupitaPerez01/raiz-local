<?php declare(strict_types=1);
/* CONEXIÓN A LA BASE DE DATOS */
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'raiz_local';

$productos = [];      
$hayBD     = false;   
try {
    $conexion = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conexion->set_charset('utf8mb4');

    if (!$conexion->connect_error) {
        $hayBD = true;

        $sql = "SELECT
                    id_producto,
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
                    id_vendedor,
                    fecha_publicacion
                FROM productos
                WHERE estado = 'activo'
                ORDER BY fecha_publicacion DESC, id_producto DESC
                LIMIT 6";

        if ($resultado = $conexion->query($sql)) {
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = [
                    'id'          => (int)$fila['id_producto'],
                    'nombre'      => $fila['nombre'],
                    'categoria'   => $fila['categoria'] ?? '',
                    'precio'      => (float)($fila['precio'] ?? 0),
                    'unidad'      => $fila['unidad'] ?? '',
                    'cantidad'    => $fila['cantidad'] ?? 0,
                    'disponibilidad' => $fila['disponibilidad'] ?? '',
                    'descripcion' => $fila['descripcion'] ?? '',
                    'ubicacion'   => $fila['ubicacion'] ?? '',
                    'modalidad'   => $fila['modalidad'] ?? '',
                    'fotografia'  => $fila['fotografia'] ?? '',
                    'id_vendedor' => (int)($fila['id_vendedor'] ?? 0),
                    'fecha'       => $fila['fecha_publicacion'] ?? ''
                ];
            }
            $resultado->free();
        }

        $conexion->close();
    }
} catch (Throwable $e) {
    // Si falla la conexión, se usará el fallback de demo
    $hayBD = false;
}
if (empty($productos)) {
    $productos = [
        [
            'id' => 1, 'nombre' => 'Maíz criollo de temporal',
            'categoria' => 'Granos', 'precio' => 480, 'unidad' => 'costal de 25 kg',
            'cantidad' => 25, 'disponibilidad' => '25 kg disponibles',
            'descripcion' => 'Maíz criollo seleccionado de cosecha local.',
            'ubicacion' => 'Tolimán, Querétaro', 'modalidad' => 'Venta',
            'fotografia' => 'https://images.unsplash.com/photo-1601593768790-6f1e4d6a9c9c?auto=format&fit=crop&w=1200&q=85',
            'id_vendedor' => 1, 'fecha' => ''
        ],
        [
            'id' => 2, 'nombre' => 'Miel multifloral artesanal',
            'categoria' => 'Granos', 'precio' => 135, 'unidad' => 'frasco de 500 g',
            'cantidad' => 12, 'disponibilidad' => '12 frascos disponibles',
            'descripcion' => 'Miel artesanal de producción local.',
            'ubicacion' => 'Tolimán, Querétaro', 'modalidad' => 'Venta',
            'fotografia' => 'https://images.unsplash.com/photo-1587049352851-8d4e89133924?auto=format&fit=crop&w=1200&q=85',
            'id_vendedor' => 2, 'fecha' => ''
        ],
        [
            'id' => 3, 'nombre' => 'Canasta de frutas de temporada',
            'categoria' => 'Frutas', 'precio' => 0, 'unidad' => 'canasta',
            'cantidad' => 4, 'disponibilidad' => '4 canastas disponibles',
            'descripcion' => 'Canasta con frutas de temporada.',
            'ubicacion' => 'Zona centro, Tolimán', 'modalidad' => 'Trueque',
            'fotografia' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=1200&q=85',
            'id_vendedor' => 3, 'fecha' => ''
        ],
        [
            'id' => 4, 'nombre' => 'Gallinas de traspatio',
            'categoria' => 'Animales', 'precio' => 250, 'unidad' => 'por pieza',
            'cantidad' => 6, 'disponibilidad' => '6 disponibles',
            'descripcion' => 'Gallinas criadas en un entorno familiar.',
            'ubicacion' => 'Tolimán, Querétaro', 'modalidad' => 'Venta',
            'fotografia' => 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=1200&q=85',
            'id_vendedor' => 4, 'fecha' => ''
        ],
        [
            'id' => 5, 'nombre' => 'Canasta artesanal de palma',
            'categoria' => 'Artesanías', 'precio' => 320, 'unidad' => 'por pieza',
            'cantidad' => 3, 'disponibilidad' => '3 piezas disponibles',
            'descripcion' => 'Pieza artesanal elaborada a mano.',
            'ubicacion' => 'Tolimán, Querétaro', 'modalidad' => 'Venta',
            'fotografia' => 'https://images.unsplash.com/photo-1590736969955-71cc94901144?auto=format&fit=crop&w=1200&q=85',
            'id_vendedor' => 5, 'fecha' => ''
        ],
        [
            'id' => 6, 'nombre' => 'Frijol bayo de la región',
            'categoria' => 'Granos', 'precio' => 0, 'unidad' => 'lote de 5 kg',
            'cantidad' => 15, 'disponibilidad' => '15 kg disponibles',
            'descripcion' => 'Frijol bayo de producción regional.',
            'ubicacion' => 'Tolimán, Querétaro', 'modalidad' => 'Trueque',
            'fotografia' => 'https://images.unsplash.com/photo-1515543904379-3d757afe72e4?auto=format&fit=crop&w=1200&q=85',
            'id_vendedor' => 6, 'fecha' => ''
        ]
    ];
}

$categorias = ['Todos', 'Granos', 'Frutas', 'Animales', 'Artesanías'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#183d2d">
    <meta name="description" content="Raíz Local Tolimán: descubre productos, cosechas y artesanías de productores locales.">
    <title>Raíz Local Tolimán — Lo nuestro, más cerca</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================
           RAÍZ LOCAL TOLIMÁN 
        ========================================================== */

        :root {
            --forest: #183d2d;
            --forest-deep: #102c20;
            --forest-light: #2e6145;
            --sage: #dce5d7;
            --sage-deep: #9db69a;
            --cream: #f7f5ef;
            --paper: #fffefa;
            --ink: #202b23;
            --muted: #777e72;
            --line: #e5e6dc;
            --gold: #c6a86a;
            --clay: #b97455;
            --radius: 20px;
            --shadow: 0 24px 70px rgba(29, 51, 34, .09);
            --ease: cubic-bezier(.22, 1, .36, 1);
            --font-display: "Manrope", sans-serif;
            --font-body: "DM Sans", sans-serif;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 90px; }
        body {
            margin: 0;
            background: var(--cream);
            color: var(--ink);
            font-family: var(--font-body);
            font-size: 14px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        body.lock-scroll { overflow: hidden; }

        button, input, select, textarea { font: inherit; }
        button { color: inherit; cursor: pointer; }
        button:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 3px solid #b8ce9e; outline-offset: 4px;
        }
        button:disabled { opacity: .5; cursor: not-allowed; }
        button, a { -webkit-tap-highlight-color: transparent; }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
        [hidden] { display: none !important; }
        ::selection { background: #c8d9bd; color: var(--forest-deep); }

        button, input, .product-card, .brand, .nav-link {
            transition:
                color .25s ease,
                background-color .25s ease,
                border-color .25s ease,
                box-shadow .3s ease,
                transform .35s var(--ease),
                opacity .25s ease;
        }

        .site-noise {
            position: fixed; inset: 0; z-index: 1000; pointer-events: none; opacity: .025;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.35'/%3E%3C/svg%3E");
        }

        /* ---------- PORTADA ---------- */
        .welcome-screen {
            position: fixed; z-index: 2000; inset: 0; overflow: hidden;
            display: flex; flex-direction: column; justify-content: space-between;
            min-height: 620px; color: white; background: #182e20;
            transition: opacity .9s ease, visibility .9s ease, transform 1s var(--ease);
        }
        .welcome-screen.is-leaving {
            opacity: 0; visibility: hidden; transform: scale(1.025); pointer-events: none;
        }
        .welcome-image, .welcome-overlay { position: absolute; inset: 0; }
        .welcome-image {
            background:
                url("https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=2200&q=90")
                center 54% / cover no-repeat;
            transform: scale(1.06);
            animation: welcomeZoom 16s ease-out forwards;
        }
        .welcome-overlay {
            background:
                linear-gradient(90deg, rgba(10,31,20,.88) 0%, rgba(15,39,24,.63) 48%, rgba(12,29,20,.16) 100%),
                linear-gradient(0deg, rgba(9,28,19,.54), transparent 35%);
        }
        .welcome-top, .welcome-bottom {
            position: relative; z-index: 1;
            display: flex; align-items: center; justify-content: space-between;
            padding: 30px clamp(24px, 6vw, 96px);
        }
        .welcome-edition, .welcome-bottom, .scroll-indicator {
            font-size: 9px; font-weight: 700; letter-spacing: .18em;
        }
        .welcome-edition { color: rgba(255,255,255,.65); }
        .welcome-content {
            position: relative; z-index: 1;
            width: min(680px, 100%);
            padding: 30px clamp(24px, 9vw, 150px) 65px;
            animation: fadeUp .9s .15s both;
        }
        .welcome-content h1 {
            margin: 24px 0;
            font-family: var(--font-display);
            font-size: clamp(54px, 8.5vw, 108px);
            line-height: .99; letter-spacing: -.075em; font-weight: 600;
        }
        .welcome-content h1 em, .hero-copy h2 em, .section-heading h2 em,
        .community-banner h2 em, .detail-copy h1 em {
            color: #b9cba6; font-family: Georgia, serif; font-weight: 400; letter-spacing: -.055em;
        }
        .welcome-content > p {
            max-width: 380px; margin: 0 0 32px;
            color: rgba(255,255,255,.78); font-size: 15px; line-height: 1.9;
        }
        .welcome-note {
            display: flex; align-items: center; gap: 12px;
            margin-top: 34px; color: rgba(255,255,255,.7); font-size: 11px;
        }
        .welcome-line { width: 30px; height: 1px; background: var(--gold); }
        .welcome-bottom { color: rgba(255,255,255,.62); font-size: 8px; }
        .scroll-indicator { display: flex; align-items: center; gap: 12px; }
        .scroll-indicator span { width: 30px; height: 1px; background: white; }

        /* ---------- IDENTIDAD ---------- */
        .brand { display: inline-flex; align-items: center; gap: 10px; width: max-content; }
        .brand-mark {
            display: grid; width: 42px; height: 42px;
            place-items: center; border-radius: 50%;
        }
        .brand-mark svg {
            width: 34px; height: 34px; fill: none;
            stroke: currentColor; stroke-width: 1.7;
            stroke-linecap: round; stroke-linejoin: round;
        }
        .brand-copy { display: flex; flex-direction: column; line-height: 1.15; }
        .brand-copy strong {
            font-family: var(--font-display); font-size: 19px; font-weight: 800; letter-spacing: -.065em;
        }
        .brand-copy small { margin-top: 5px; font-size: 8px; font-weight: 700; letter-spacing: .13em; }
        .brand-light { color: #fff; }
        .brand-dark { color: var(--forest); }

        /* ---------- BOTONES ---------- */
        .button {
            min-height: 49px; display: inline-flex; align-items: center; justify-content: center;
            gap: 24px; padding: 0 21px; border: 1px solid transparent; border-radius: 4px;
            font-size: 12px; font-weight: 700; letter-spacing: .01em;
        }
        .button:hover { transform: translateY(-3px); }
        .button-primary { color: #fff; background: var(--forest); }
        .button-primary:hover { background: var(--forest-light); box-shadow: 0 10px 25px rgba(24,61,45,.17); }
        .button-light { color: var(--forest-deep); background: var(--paper); }
        .button-light:hover { background: #e9efdf; }
        .button-large { min-height: 57px; padding: 0 24px; }
        .button-outline { border-color: #d7ddcf; color: var(--forest); background: transparent; }
        .button-outline:hover { border-color: var(--forest); background: #f0f3eb; }
        .button-arrow { font-size: 18px; line-height: 1; }
        .text-button {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 0; border: 0; background: transparent;
            color: var(--forest); font-size: 12px; font-weight: 700;
        }
        .text-button span { font-size: 18px; transition: transform .3s var(--ease); }
        .text-button:hover span { transform: translateX(5px); }

        /* ---------- HEADER ---------- */
        .main-header {
            position: sticky; z-index: 100; top: 0;
            display: flex; align-items: center; justify-content: space-between;
            min-height: 84px; padding: 0 clamp(22px, 6vw, 92px);
            border-bottom: 1px solid rgba(30,59,41,.08);
            background: rgba(250,249,244,.94);
            backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
        }
        .header-nav {
            display: flex; align-items: center; gap: clamp(16px, 2.4vw, 32px);
            height: 84px; margin-left: auto;
        }
        .header-nav a {
            position: relative; align-self: stretch;
            display: inline-flex; align-items: center;
            padding: 0 2px;
            color: #777c72; font-size: 11px; font-weight: 600;
        }
        .header-nav a::after {
            position: absolute; right: 0; bottom: 0; left: 0; height: 2px;
            background: var(--forest); content: "";
            transform: scaleX(0); transform-origin: left;
            transition: transform .35s var(--ease);
        }
        .header-nav a:hover { color: var(--forest); }
        .header-nav a:hover::after { transform: scaleX(1); }

        .header-actions { display: flex; align-items: center; gap: 14px; margin-left: 22px; }
        .header-access-btn {
            min-height: 42px;
            padding: 0 16px;
            font-size: 11px;
            gap: 12px;
        }
        .mobile-menu-button { display: none; }
        .mobile-nav { display: none; }
        .mobile-bottom-nav { display: none; }

        /* ---------- APP SHELL ---------- */
        .app-shell {
            opacity: 0; transform: translateY(12px);
            transition: opacity .8s ease, transform .8s var(--ease);
        }
        .app-shell.is-visible { opacity: 1; transform: none; }

        /* ---------- ETIQUETAS ---------- */
        .eyebrow {
            display: flex; align-items: center; gap: 10px;
            color: #7a826f; font-size: 9px; font-weight: 800;
            letter-spacing: .16em; line-height: 1.6;
        }
        .eyebrow-light { color: rgba(255,255,255,.78); }
        .eyebrow-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #9cba85; box-shadow: 0 0 0 4px rgba(156,186,133,.12);
        }

        /* ---------- HERO ---------- */
        .hero-section {
            display: grid; grid-template-columns: .95fr 1.05fr;
            align-items: center; gap: clamp(35px, 6vw, 90px);
            max-width: 1600px; min-height: 620px; margin: auto;
            padding: 75px clamp(24px, 7.5vw, 120px) 90px;
        }
        .hero-copy { position: relative; z-index: 2; animation: fadeUp .75s both; }
        .hero-copy h2 {
            margin: 26px 0 20px;
            font-family: var(--font-display);
            font-size: clamp(48px, 6.1vw, 77px);
            font-weight: 600; letter-spacing: -.08em; line-height: 1.06;
        }
        .hero-copy h2 em { color: #75916e; }
        .hero-copy > p { max-width: 405px; color: var(--muted); font-size: 13px; line-height: 2; }
        .hero-buttons { display: flex; align-items: center; flex-wrap: wrap; gap: 24px; margin-top: 30px; }
        .hero-proof { display: flex; align-items: center; gap: 14px; margin-top: 46px; }
        .avatar-stack { display: flex; padding-left: 2px; }
        .avatar-stack span {
            display: grid; width: 33px; height: 33px; margin-left: -3px;
            place-items: center; border: 2px solid var(--cream); border-radius: 50%;
            background: #d5dfc9; color: var(--forest);
            font-size: 8px; font-weight: 800;
        }
        .avatar-stack span:nth-child(2) { background: #e7c8a6; }
        .avatar-stack span:nth-child(3) { background: #d7b8a7; }
        .hero-proof > div:last-child { display: flex; flex-direction: column; gap: 3px; }
        .hero-proof strong { color: #374337; font-size: 10px; }
        .hero-proof small { color: #8a8d82; font-size: 9px; }

        .hero-visual { position: relative; min-height: 465px; animation: fadeUp .9s .15s both; }
        .hero-photo {
            position: absolute; overflow: hidden;
            border-radius: 150px 150px 5px 5px; background: #d5ddce;
        }
        .hero-photo img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform 1.2s var(--ease);
        }
        .hero-photo:hover img { transform: scale(1.05); }
        .hero-photo-main { top: 0; right: 7%; bottom: 20px; left: 17%; }
        .hero-photo-small {
            z-index: 2; bottom: 45px; left: 0;
            width: 36%; height: 205px;
            border: 7px solid var(--cream);
            border-radius: 100px 100px 4px 4px;
        }
        .floating-note {
            position: absolute; z-index: 3;
            display: flex; align-items: center; gap: 12px;
            padding: 15px 18px;
            border: 1px solid rgba(255,255,255,.65);
            border-radius: 8px;
            background: rgba(255,254,250,.94);
            box-shadow: var(--shadow);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            animation: float 6s ease-in-out infinite;
        }
        .floating-note > span:last-child { display: flex; flex-direction: column; gap: 3px; }
        .floating-note strong { color: var(--forest); font-size: 10px; }
        .floating-note small { color: #82877c; font-size: 9px; }
        .floating-note-icon {
            display: grid; width: 35px; height: 35px;
            place-items: center; border-radius: 50%;
            background: #e8eddf; color: var(--forest); font-size: 19px;
        }
        .floating-note-top { top: 14%; right: -2%; }
        .floating-note-bottom { bottom: 1%; left: 17%; animation-delay: -2.5s; }
        .floating-leaf {
            display: grid; width: 36px; height: 36px;
            place-items: center; border-radius: 50%;
            background: var(--forest); color: white;
        }
        .hero-image-caption {
            position: absolute; right: 8%; bottom: 0; left: 53%;
            display: flex; justify-content: space-between;
            color: #7d8478; font-size: 8px; font-weight: 800; letter-spacing: .1em;
        }

        /* ---------- VALORES ---------- */
        .values-strip {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 20px; padding: 32px clamp(24px, 8vw, 120px);
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            background: #f0f0e8;
        }
        .value-item { display: flex; align-items: center; justify-content: center; gap: 16px; }
        .value-number {
            color: #98a78d; font-family: Georgia, serif;
            font-size: 25px; font-style: italic;
        }
        .value-item > div { display: flex; flex-direction: column; gap: 3px; }
        .value-item strong { color: #354337; font-size: 11px; }
        .value-item small { color: #888d81; font-size: 10px; }

        /* ---------- SECCIÓN ORIGEN ---------- */
        .origin-section {
            max-width: 1600px; margin: auto;
            padding: 100px clamp(24px, 7.5vw, 120px) 60px;
            border-top: 1px solid var(--line);
        }
        .origin-intro {
            display: grid; grid-template-columns: 1fr .8fr;
            gap: 10%; align-items: end; margin: 24px 0 54px;
        }
        .origin-intro h2,
        .origin-section > h2 {
            margin: 0;
            color: var(--ink);
            font-family: var(--font-display);
            font-size: clamp(32px, 4vw, 48px);
            font-weight: 600; letter-spacing: -.065em; line-height: 1.18;
        }
        .origin-intro h2 em,
        .origin-section > h2 em {
            color: #77906c; font-family: Georgia, serif;
            font-weight: 400; letter-spacing: -.055em;
        }
        .origin-intro p {
            margin: 0; color: var(--muted); font-size: 12px; line-height: 2;
        }
        .origin-section .kicker,
        .manifesto .kicker {
            display: inline-flex; align-items: center; gap: 10px;
            color: #7a826f; font-size: 9px; font-weight: 800;
            letter-spacing: .16em; line-height: 1.6;
            margin-bottom: 26px;
        }
        .origin-section .kicker::before,
        .manifesto .kicker::before {
            content: ""; width: 7px; height: 7px; border-radius: 50%;
            background: #9cba85; box-shadow: 0 0 0 4px rgba(156,186,133,.12);
        }

        /* ---------- VALORES (tarjetas) ---------- */
        .values {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;
        }
        .values article {
            position: relative; min-height: 225px;
            padding: 32px 28px;
            border: 1px solid #e8e7de; border-radius: 7px;
            background: var(--paper);
            overflow: hidden;
        }
        .values article:hover {
            transform: translateY(-6px); border-color: #d6dece;
            box-shadow: 0 22px 55px rgba(25,55,34,.09);
        }
        .values article > span { display: inline-block; color: var(--forest); font-size: 30px; }
        .values article > small {
            position: absolute; right: 26px; top: 26px;
            color: #929789; font-size: 8px; font-weight: 800; letter-spacing: .13em;
        }
        .values h3 {
            margin: 26px 0 10px; color: var(--forest);
            font-family: var(--font-display);
            font-size: 22px; font-weight: 700; letter-spacing: -.045em; line-height: 1.3;
        }
        .values p { margin: 0; color: var(--muted); font-size: 12px; line-height: 1.9; }

        /* ---------- MANIFIESTO ---------- */
        .manifesto {
            position: relative; overflow: hidden;
            padding: 90px 24px; background: var(--forest);
            color: #f8f0dc; text-align: center;
        }
        .manifesto::before {
            content: ""; position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(198,168,106,.15), transparent 55%),
                radial-gradient(ellipse at 85% 80%, rgba(198,168,106,.1), transparent 50%);
            pointer-events: none;
        }
        .manifesto .kicker { color: rgba(255,255,255,.7); }
        .manifesto .kicker::before { background: var(--gold); box-shadow: 0 0 0 4px rgba(198,168,106,.15); }
        .manifesto > span:not(.kicker) {
            display: inline-block; margin-bottom: 10px;
            color: var(--gold); font-family: Georgia, serif; font-size: 110px; line-height: .7;
        }
        .manifesto h2 {
            position: relative; z-index: 1;
            max-width: 800px; margin: 15px auto 36px;
            font-family: var(--font-display);
            font-size: clamp(32px, 4.6vw, 58px);
            font-weight: 600; letter-spacing: -.07em; line-height: 1.12;
        }
        .manifesto em {
            color: #b9cba6; font-family: Georgia, serif;
            font-weight: 400; letter-spacing: -.055em;
        }
        .manifesto .button { position: relative; z-index: 1; }

        /* ---------- CATÁLOGO ---------- */
        .catalog-section {
            max-width: 1600px; margin: auto;
            padding: 100px clamp(24px, 7.5vw, 120px) 90px;
        }
        .section-heading {
            display: flex; align-items: flex-end; justify-content: space-between; gap: 30px;
        }
        .section-heading h2 {
            margin: 13px 0 8px;
            font-family: var(--font-display);
            font-size: clamp(32px, 4vw, 48px);
            font-weight: 600; letter-spacing: -.065em; line-height: 1.18;
        }
        .section-heading h2 em { color: #77906c; }
        .section-heading p { margin: 0; color: var(--muted); font-size: 12px; }

        /* ---------- GRID SIMPLE (imagen + nombre) ---------- */
        .simple-products {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 34px 22px;
            margin-top: 43px;
        }
        .simple-card {
            min-width: 0;
            border: 1px solid #e8e7de;
            border-radius: 7px;
            background: var(--paper);
            overflow: hidden;
            cursor: pointer;
            animation: cardEnter .65s var(--ease) both;
            animation-delay: calc(var(--card-index, 0) * 55ms);
        }
        .simple-card:hover {
            transform: translateY(-6px);
            border-color: #d6dece;
            box-shadow: 0 22px 55px rgba(25,55,34,.09);
        }
        .simple-card-image {
            position: relative;
            width: 100%;
            height: clamp(220px, 26vw, 320px);
            overflow: hidden;
            background: #e4e6db;
        }
        .simple-card-image img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform .9s var(--ease), filter .5s ease;
        }
        .simple-card:hover .simple-card-image img {
            transform: scale(1.055);
            filter: saturate(1.06);
        }
        .simple-card-name {
            margin: 0;
            padding: 18px 18px 20px;
            color: var(--forest);
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -.045em;
            line-height: 1.35;
        }

        .empty-state { padding: 75px 20px; text-align: center; }
        .empty-state > span { color: #91a187; font-size: 40px; }
        .empty-state h3 {
            color: var(--forest); font-family: var(--font-display);
            font-size: 23px; letter-spacing: -.04em;
        }
        .empty-state p { margin-bottom: 25px; color: var(--muted); font-size: 12px; }
        .catalog-bottom {
            display: flex; align-items: center; justify-content: space-between;
            gap: 20px; margin-top: 43px; padding-top: 20px;
            border-top: 1px solid var(--line);
        }
        .catalog-bottom > span {
            color: #8d9285; font-size: 8px; font-weight: 800; letter-spacing: .14em;
        }

        /* ---------- MUESTRAS ---------- */
        .samples {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 20px; margin-top: 30px;
        }
        .samples article {
            position: relative; min-height: 240px;
            padding: 28px;
            border: 1px solid #e8e7de; border-radius: 7px;
            background: var(--paper);
            overflow: hidden;
        }
        .samples article:hover {
            transform: translateY(-6px); border-color: #d6dece;
            box-shadow: 0 22px 55px rgba(25,55,34,.09);
        }
        .samples article > span {
            position: absolute; right: 20px; top: 18px;
            font-size: 78px;
            filter: drop-shadow(0 8px 8px rgba(40,60,35,.15));
            transition: transform .35s var(--ease);
        }
        .samples article:hover > span { transform: scale(1.08) rotate(-4deg); }
        .samples article > small {
            display: inline-block; padding: 7px 10px; border-radius: 3px;
            background: #e8ecdf; color: var(--forest);
            font-size: 9px; font-weight: 800; letter-spacing: .06em;
        }
        .samples h3 {
            position: relative; z-index: 1; margin: 92px 0 0;
            color: var(--forest); font-family: var(--font-display);
            font-size: 22px; font-weight: 700; letter-spacing: -.045em; line-height: 1.3;
        }

        /* ---------- BANNER COMUNIDAD ---------- */
        .community-banner {
            position: relative; display: flex; align-items: center;
            min-height: 420px; margin: 0 clamp(16px, 4vw, 58px) 75px;
            overflow: hidden; border-radius: 7px;
            background: var(--forest); color: white;
        }
        .community-banner-image, .community-banner::after {
            position: absolute; inset: 0;
        }
        .community-banner-image {
            background: url("https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=1900&q=85") center / cover no-repeat;
            opacity: .4; transition: transform 1.2s var(--ease);
        }
        .community-banner:hover .community-banner-image { transform: scale(1.035); }
        .community-banner::after {
            content: "";
            background: linear-gradient(90deg, rgba(15,43,29,.96), rgba(19,50,32,.72) 58%, rgba(18,46,30,.25));
        }
        .community-banner-content {
            position: relative; z-index: 1; max-width: 650px;
            padding: 65px clamp(26px, 7vw, 100px);
        }
        .community-banner h2 {
            margin: 20px 0 15px;
            font-family: var(--font-display);
            font-size: clamp(36px, 5vw, 58px);
            font-weight: 600; letter-spacing: -.07em; line-height: 1.12;
        }
        .community-banner p {
            max-width: 380px; margin: 0 0 25px;
            color: rgba(255,255,255,.72); font-size: 12px; line-height: 1.9;
        }
        .banner-decoration {
            position: absolute; right: 4%; bottom: -150px;
            color: rgba(255,255,255,.055); font-family: Georgia, serif;
            font-size: 480px; line-height: 1; pointer-events: none;
        }

        /* ---------- DETALLE ---------- */
        .page-detail {
            min-height: 70vh; padding: 30px clamp(24px, 7.5vw, 120px) 80px;
        }
        .detail-topbar {
            display: flex; align-items: center; justify-content: space-between;
            gap: 20px; padding: 0 0 25px; border-bottom: 1px solid var(--line);
        }
        .back-button {
            display: inline-flex; align-items: center; gap: 9px;
            padding: 8px 0; border: 0; background: transparent;
            color: #687263; font-size: 11px; font-weight: 700;
        }
        .back-button:hover { color: var(--forest); }
        .detail-layout {
            display: grid; grid-template-columns: minmax(0, 1.08fr) minmax(320px, .92fr);
            gap: clamp(35px, 6vw, 90px); padding-top: 45px;
            animation: fadeUp .55s var(--ease) both;
        }
        .detail-gallery { position: relative; }
        .detail-main-image {
            height: clamp(380px, 48vw, 650px); overflow: hidden;
            border-radius: 5px; background: #e6e5d9;
        }
        .detail-main-image img { width: 100%; height: 100%; object-fit: cover; }
        .detail-image-caption {
            display: flex; justify-content: space-between;
            margin-top: 12px; color: #8a8f82;
            font-size: 8px; font-weight: 800; letter-spacing: .13em;
        }
        .detail-copy { align-self: center; padding: 25px 0; }
        .detail-type {
            display: inline-flex; padding: 7px 11px; border-radius: 3px;
            background: #e8ecdf; color: var(--forest);
            font-size: 9px; font-weight: 800; letter-spacing: .06em;
        }
        .detail-type.trade { background: #f2e7d4; color: #866b3b; }
        .detail-copy h1 {
            margin: 20px 0 12px; color: var(--forest);
            font-family: var(--font-display);
            font-size: clamp(32px, 4vw, 51px);
            font-weight: 600; letter-spacing: -.075em; line-height: 1.12;
        }
        .detail-copy h1 em { color: #78926e; }
        .detail-quantity { color: #7b8175; font-size: 11px; }
        .detail-price {
            display: flex; align-items: baseline; gap: 10px;
            margin: 26px 0; padding-bottom: 25px;
            border-bottom: 1px solid var(--line);
        }
        .detail-price strong {
            color: var(--forest); font-family: var(--font-display);
            font-size: 34px; letter-spacing: -.07em;
        }
        .detail-price span { color: #858b7f; font-size: 10px; }
        .detail-description-label {
            margin: 0 0 9px; color: #65715e;
            font-size: 9px; font-weight: 800; letter-spacing: .14em;
        }
        .detail-description { color: #737a6e; font-size: 12px; line-height: 2; }
        .detail-facts {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
            margin-top: 28px; padding: 22px 0;
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
        }
        .detail-fact { display: flex; align-items: flex-start; gap: 10px; }
        .detail-fact-icon {
            display: grid; width: 34px; height: 34px; flex: 0 0 auto;
            place-items: center; border-radius: 50%;
            background: #e9ede2; color: var(--forest); font-size: 15px;
        }
        .detail-fact > div { display: flex; flex-direction: column; gap: 3px; }
        .detail-fact small {
            color: #96998e; font-size: 8px; font-weight: 800; letter-spacing: .08em;
        }
        .detail-fact strong { color: #485446; font-size: 10px; }
        .detail-producer {
            display: flex; align-items: center; gap: 12px; margin: 24px 0;
        }
        .detail-producer-avatar {
            display: grid; width: 48px; height: 48px;
            place-items: center; border-radius: 50%;
            background: #dfe7d6; color: var(--forest);
            font-size: 12px; font-weight: 800;
        }
        .detail-producer > div:last-child { display: flex; flex-direction: column; gap: 3px; }
        .detail-producer small {
            color: #92978a; font-size: 8px; font-weight: 800; letter-spacing: .1em;
        }
        .detail-producer strong { color: var(--forest); font-size: 12px; }
        .detail-contact-note {
            margin: 12px 0 0; color: #93988c; font-size: 9px; line-height: 1.8;
        }
        .detail-contact-button { width: 100%; }
        .related-section { margin-top: 90px; padding-top: 40px; border-top: 1px solid var(--line); }
        .section-heading-small h2 { font-size: clamp(28px, 3.5vw, 39px); }
        .related-grid {
            display: grid; grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px; margin-top: 25px;
        }

        /* ---------- FOOTER ---------- */
        .main-footer { background: var(--forest-deep); color: white; }
        .footer-main {
            display: grid; grid-template-columns: 1.35fr .75fr .95fr 1fr;
            gap: 35px; padding: 60px clamp(24px, 7.5vw, 120px) 50px;
        }
        .footer-brand-column > p {
            margin-top: 22px; color: rgba(255,255,255,.62);
            font-size: 11px; line-height: 1.9;
        }
        .footer-links {
            display: flex; flex-direction: column;
            align-items: flex-start; gap: 13px;
        }
        .footer-label {
            margin: 8px 0; color: #a9bd9b;
            font-size: 8px; font-weight: 800; letter-spacing: .14em;
        }
        .footer-links a, .footer-links > span:not(.footer-label) {
            padding: 0; border: 0; background: transparent;
            color: rgba(255,255,255,.7); font-size: 10px; text-align: left;
        }
        .footer-links a:hover { color: white; transform: translateX(3px); }
        .footer-signoff {
            position: relative; display: flex; flex-direction: column; gap: 13px;
            padding-left: 24px; border-left: 1px solid rgba(255,255,255,.15);
        }
        .footer-signoff > span:first-child {
            color: #a9bd9b; font-size: 8px; font-weight: 800; letter-spacing: .14em;
        }
        .footer-signoff strong {
            font-family: var(--font-display); font-size: 26px;
            font-weight: 500; letter-spacing: -.06em; line-height: 1.3;
        }
        .footer-sun { margin-top: 3px; color: #b8cba8; font-size: 27px; }
        .footer-bottom {
            display: flex; align-items: center; justify-content: space-between;
            gap: 20px; padding: 20px clamp(24px, 7.5vw, 120px);
            border-top: 1px solid rgba(255,255,255,.12);
            color: rgba(255,255,255,.5);
            font-size: 8px; font-weight: 700; letter-spacing: .1em;
        }
        .footer-bottom button {
            padding: 5px 0; border: 0; background: transparent;
            color: #c0ceb6; font-size: 8px; font-weight: 800; letter-spacing: .1em;
        }

        /* ---------- TOAST ---------- */
        .toast-message {
            position: fixed; z-index: 3000; right: 24px; bottom: 24px;
            max-width: min(380px, calc(100% - 48px)); padding: 14px 18px;
            border: 1px solid rgba(255,255,255,.15); border-radius: 6px;
            background: var(--forest-deep); color: white;
            box-shadow: var(--shadow); font-size: 11px; line-height: 1.7;
            opacity: 0; pointer-events: none; transform: translateY(12px);
            transition: opacity .3s ease, transform .3s var(--ease);
        }
        .toast-message.visible { opacity: 1; transform: none; }

        /* ---------- ANIMACIONES ---------- */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes cardEnter {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }
        @keyframes welcomeZoom {
            from { transform: scale(1.06); }
            to   { transform: scale(1); }
        }

        /* ---------- TABLET ---------- */
        @media (max-width: 1050px) {
            .hero-section { gap: 35px; padding-top: 60px; padding-bottom: 65px; }
            .hero-copy h2 { font-size: clamp(45px, 6vw, 65px); }
            .hero-visual { min-height: 420px; }
            .simple-products { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 25px 18px; }
            .footer-main { grid-template-columns: 1fr 1fr; row-gap: 45px; }
            .footer-signoff { min-height: 140px; }
            .header-nav { display: none; }
        }

        /* ---------- MÓVIL ---------- */
        @media (max-width: 700px) {
            body { font-size: 13px; }
            .welcome-screen { min-height: 570px; }
            .welcome-image { background-position: 58% center; }
            .welcome-overlay {
                background:
                    linear-gradient(90deg, rgba(10,31,20,.79), rgba(15,39,24,.37)),
                    linear-gradient(0deg, rgba(9,28,19,.7), transparent 75%);
            }
            .welcome-top { padding: 20px 20px; }
            .welcome-top .brand-mark { width: 36px; height: 36px; }
            .welcome-top .brand-copy strong { font-size: 17px; }
            .welcome-edition { max-width: 120px; font-size: 7px; text-align: right; }
            .welcome-content { padding: 25px 25px 48px; }
            .welcome-content h1 { font-size: clamp(53px, 14vw, 82px); }
            .welcome-content > p { max-width: 310px; font-size: 13px; }
            .welcome-bottom { padding: 18px 22px; font-size: 7px; }
            .welcome-bottom > span:first-child { max-width: 190px; line-height: 1.8; }

            .main-header { min-height: 70px; padding: 0 19px; }
            .brand-mark { width: 35px; height: 35px; }
            .brand-mark svg { width: 30px; height: 30px; }
            .brand-copy strong { font-size: 17px; }
            .brand-copy small { font-size: 7px; }
            .header-nav, .header-access-btn { display: none; }
            .mobile-menu-button {
                display: flex; width: 40px; height: 40px;
                flex-direction: column; align-items: center; justify-content: center;
                gap: 6px; border: 1px solid #e2e5da; border-radius: 50%;
                background: transparent;
            }
            .mobile-menu-button span {
                width: 16px; height: 1.5px; background: var(--forest);
                transition: transform .25s ease;
            }
            .mobile-menu-button.active span:first-child {
                transform: translateY(3.75px) rotate(45deg);
            }
            .mobile-menu-button.active span:last-child {
                transform: translateY(-3.75px) rotate(-45deg);
            }
            .mobile-nav {
                position: absolute; z-index: 99; top: 70px; right: 0; left: 0;
                display: flex; flex-direction: column;
                padding: 8px 20px 17px;
                border-bottom: 1px solid var(--line);
                background: #fbfaf5;
                box-shadow: 0 20px 35px rgba(20,40,25,.07);
                opacity: 0; pointer-events: none; transform: translateY(-8px);
                transition: opacity .25s ease, transform .25s var(--ease);
            }
            .mobile-nav.open { opacity: 1; pointer-events: auto; transform: none; }
            .mobile-nav a {
                display: flex; align-items: center; justify-content: space-between;
                padding: 15px 0; border: 0; border-bottom: 1px solid #edeee6;
                background: transparent; color: var(--forest);
                font-size: 12px; text-align: left; text-decoration: none;
            }
            .mobile-nav a span { color: #98a18f; font-size: 9px; }

            .hero-section { grid-template-columns: 1fr; gap: 35px; padding: 55px 24px 50px; }
            .hero-copy h2 { margin-top: 20px; font-size: clamp(49px, 12vw, 72px); }
            .hero-copy > p { max-width: 370px; }
            .hero-buttons { gap: 18px; }
            .hero-proof { margin-top: 30px; }
            .hero-visual { min-height: 390px; margin: 0 2px; }
            .hero-photo-main { right: 8%; left: 15%; }
            .hero-photo-small { bottom: 35px; height: 160px; }
            .floating-note { gap: 8px; padding: 11px 12px; }
            .floating-note-top { right: -2px; }
            .floating-note-bottom { bottom: 0; left: 13%; }
            .floating-note-icon, .floating-leaf { width: 30px; height: 30px; }
            .floating-note strong { font-size: 9px; }
            .floating-note small { font-size: 8px; }
            .hero-image-caption { right: 8%; left: 53%; font-size: 7px; }

            .values-strip { grid-template-columns: 1fr; gap: 0; padding: 8px 24px; }
            .value-item { justify-content: flex-start; padding: 17px 0; border-bottom: 1px solid #e1e4d9; }
            .value-item:last-child { border-bottom: 0; }
            .value-number { width: 30px; }

            .origin-section { padding: 65px 20px 45px; }
            .origin-intro { grid-template-columns: 1fr; gap: 22px; }
            .origin-intro h2, .origin-section > h2 { font-size: clamp(30px, 8vw, 40px); }
            .values, .samples { grid-template-columns: 1fr; gap: 16px; }
            .values article { min-height: 190px; padding: 26px 24px; }
            .values h3 { margin-top: 20px; font-size: 21px; }
            .manifesto { padding: 70px 20px; }
            .manifesto h2 { font-size: 32px; }
            .manifesto > span:not(.kicker) { font-size: 90px; }
            .samples article { min-height: 200px; padding: 24px; }
            .samples h3 { margin-top: 78px; font-size: 21px; }

            .catalog-section { padding: 65px 18px 55px; }
            .section-heading { align-items: flex-start; }
            .section-heading h2 { font-size: clamp(31px, 8vw, 43px); }
            .section-heading p { max-width: 310px; line-height: 1.8; }

            .simple-products { grid-template-columns: 1fr; gap: 22px; margin-top: 30px; }
            .simple-card-image { height: clamp(240px, 74vw, 390px); }
            .simple-card-name { font-size: 17px; }

            .community-banner { min-height: 430px; margin: 0 12px 45px; }
            .community-banner-content { padding: 45px 25px; }
            .community-banner h2 { font-size: clamp(36px, 10vw, 48px); }
            .banner-decoration { right: -20px; bottom: -70px; font-size: 300px; }

            .page-detail { padding: 20px 18px 55px; }
            .detail-topbar { align-items: flex-start; }
            .detail-topbar .eyebrow { max-width: 120px; text-align: right; }
            .detail-layout { grid-template-columns: 1fr; gap: 24px; padding-top: 25px; }
            .detail-main-image { height: clamp(290px, 90vw, 470px); }
            .detail-copy { padding: 0; }
            .detail-copy h1 { font-size: 38px; }
            .detail-facts { gap: 17px 10px; }
            .related-section { margin-top: 55px; padding-top: 30px; }
            .related-grid { grid-template-columns: 1fr; }

            .footer-main { grid-template-columns: 1fr 1fr; gap: 38px 20px; padding: 42px 24px 35px; }
            .footer-brand-column { grid-column: 1 / -1; }
            .footer-signoff {
                grid-column: 1 / -1; min-height: auto;
                padding: 22px 0 0;
                border-top: 1px solid rgba(255,255,255,.15); border-left: 0;
            }
            .footer-signoff strong { font-size: 25px; }
            .footer-bottom {
                align-items: flex-start; flex-direction: column; gap: 10px;
                padding: 18px 24px 40px; font-size: 7px;
            }

            .toast-message {
                right: 15px; bottom: 15px; left: 15px; max-width: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body>

<div class="site-noise" aria-hidden="true"></div>

<!-- =========================================================
     PORTADA / BIENVENIDA (se cierra sola a los 4 segundos
     o al pulsar "Explorar la comunidad")
========================================================== -->
<section class="welcome-screen" id="welcomeScreen">
    <div class="welcome-image"></div>
    <div class="welcome-overlay"></div>

    <header class="welcome-top">
        <a href="#" class="brand brand-light" aria-label="Raíz Local Tolimán">
            <span class="brand-mark">
                <svg viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M24 40V18"></path>
                    <path d="M24 27C11 27 8 18 8 10C19 10 25 16 24 27Z"></path>
                    <path d="M24 21C24 10 31 5 41 5C41 17 34 23 24 21Z"></path>
                    <path d="M24 34C17 34 13 30 12 25"></path>
                </svg>
            </span>
            <span class="brand-copy">
                <strong>raíz local</strong>
                <small>TOLIMÁN · QUERÉTARO</small>
            </span>
        </a>
        <span class="welcome-edition">UNA COMUNIDAD, MUCHAS RAÍCES</span>
    </header>

    <div class="welcome-content">
        <span class="eyebrow eyebrow-light">
            <span class="eyebrow-dot"></span>
            DEL CAMPO A TU COMUNIDAD
        </span>

        <h1>Lo nuestro,<br><em>más cerca.</em></h1>

        <p>
            Descubre lo que nace de nuestra tierra.
            Conecta con quienes lo hacen posible.
        </p>

        <button class="button button-light button-large" id="enterSiteButton" type="button">
            <span>Explorar la comunidad</span>
            <span class="button-arrow">↗</span>
        </button>

        <div class="welcome-note">
            <span class="welcome-line"></span>
            <span>Apoya al campo, fortalece nuestra comunidad</span>
        </div>
    </div>

    <div class="welcome-bottom">
        <span>PRODUCTORES LOCALES · TRADICIÓN · COMUNIDAD</span>
        <span class="scroll-indicator"><span></span> DESCUBRE</span>
    </div>
</section>

<!-- =========================================================
     APLICACIÓN PRINCIPAL
========================================================== -->
<div class="app-shell" id="appShell" aria-hidden="true">

    <header class="main-header">
        <a href="#page-home" class="brand brand-dark" data-page="home">
            <span class="brand-mark">
                <svg viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M24 40V18"></path>
                    <path d="M24 27C11 27 8 18 8 10C19 10 25 16 24 27Z"></path>
                    <path d="M24 21C24 10 31 5 41 5C41 17 34 23 24 21Z"></path>
                    <path d="M24 34C17 34 13 30 12 25"></path>
                </svg>
            </span>
            <span class="brand-copy">
                <strong>raíz local</strong>
                <small>TOLIMÁN · QRO.</small>
            </span>
        </a>

        <nav class="header-nav" aria-label="Enlaces de la portada">
            <a href="#origen">Nuestra raíz</a>
            <a href="#valores">Lo que nos mueve</a>
        </nav>

        <div class="header-actions">
            <a class="button button-primary header-access-btn" href="acceso.php">
                Ingresar ↗
            </a>
            <button class="mobile-menu-button" id="mobileMenuButton" aria-label="Abrir menú" type="button">
                <span></span><span></span>
            </button>
        </div>
    </header>

    <nav class="mobile-nav" id="mobileNav" aria-label="Navegación móvil">
        <a href="#origen"><span>Nuestra raíz</span></a>
        <a href="#valores"><span>Lo que nos mueve</span></a>
        <a href="acceso.php"><span>Ingresar ↗</span></a>
    </nav>

    <main id="mainContent">

        <!-- ===================== INICIO ===================== -->
        <section class="page-section page-home" id="page-home">

            <section class="hero-section">
                <div class="hero-copy">
                    <div class="eyebrow">
                        <span class="eyebrow-dot"></span>
                        HECHO AQUÍ. COMPARTIDO AQUÍ.
                    </div>

                    <h2>La riqueza de<br>nuestra tierra,<br><em>en tus manos.</em></h2>

                    <p>
                        Encuentra productos de nuestra región y conoce
                        a las personas que los cultivan, crean y comparten.
                    </p>

                    <div class="hero-buttons">
                        <button class="button button-primary" id="heroExploreButton" type="button">
                            Descubrir productos <span class="button-arrow">↗</span>
                        </button>
                    </div>

                    <div class="hero-proof">
                        <div class="avatar-stack" aria-hidden="true">
                            <span>MH</span><span>MG</span><span>PS</span>
                        </div>
                        <div>
                            <strong>La comunidad empieza contigo.</strong>
                            <small>Conoce el trabajo detrás de cada producto.</small>
                        </div>
                    </div>
                </div>

                <div class="hero-visual">
                    <div class="hero-photo hero-photo-main">
                        <img
                            src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1400&q=90"
                            alt="Campos agrícolas iluminados por el sol"
                        >
                    </div>
                    <div class="hero-photo hero-photo-small">
                        <img
                            src="https://images.unsplash.com/photo-1499529112087-3cb3b73cec95?auto=format&fit=crop&w=700&q=85"
                            alt="Cosecha de productos frescos"
                        >
                    </div>

                    <div class="floating-note floating-note-top">
                        <span class="floating-note-icon">✳</span>
                        <span><strong>Origen local</strong><small>Historias que nos conectan</small></span>
                    </div>

                    <div class="floating-note floating-note-bottom">
                        <span class="floating-leaf">↗</span>
                        <span><strong>Compra diferente.</strong><small>Apoya a quienes producen.</small></span>
                    </div>

                    <div class="hero-image-caption">
                        <span>01 / 04</span>
                        <span>LA TIERRA QUE NOS UNE</span>
                    </div>
                </div>
            </section>

            <section class="values-strip" aria-label="Nuestros valores">
                <div class="value-item">
                    <span class="value-number">01</span>
                    <div><strong>Origen cercano</strong><small>Productos de nuestra región</small></div>
                </div>
                <div class="value-item">
                    <span class="value-number">02</span>
                    <div><strong>Trato directo</strong><small>Conecta con quien produce</small></div>
                </div>
                <div class="value-item">
                    <span class="value-number">03</span>
                    <div><strong>Comunidad viva</strong><small>El valor de compartir</small></div>
                </div>
            </section>

            <!-- ===================== SECCIÓN ORIGEN ===================== -->
            <section class="origin-section" id="origen">

                <span class="kicker">01 / NUESTRA RAÍZ</span>

                <div class="origin-intro">

                    <h2>El valor de lo local<br>se siente <em>de cerca.</em></h2>

                    <p>Raíz Local Tolimán conecta a quienes producen con quienes buscan productos de su comunidad. Sin perder lo más importante: la confianza, el origen y el trato entre personas.</p>

                </div>

                <div class="values" id="valores">

                    <article>
                        <span>⌂</span>
                        <small>01</small>
                        <h3>Compra local</h3>
                        <p>Descubre alimentos, artesanías y productos de productores cercanos.</p>
                    </article>

                    <article>
                        <span>⇄</span>
                        <small>02</small>
                        <h3>Intercambia</h3>
                        <p>Propón trueques y encuentra nuevas formas de dar valor a lo que tienes.</p>
                    </article>

                    <article>
                        <span>♡</span>
                        <small>03</small>
                        <h3>Apoya personas</h3>
                        <p>Conoce quién está detrás de cada producto y fortalece la economía local.</p>
                    </article>

                </div>

            </section>

            <!-- ===================== MANIFIESTO ===================== -->
            <section class="manifesto">
                <span class="kicker">UN PROPÓSITO COMÚN</span>
                <span>“</span>
                <h2>Apoya al campo,<br><em>fortalece nuestra comunidad.</em></h2>
                <a class="button button-light" href="acceso.php">
                    Encuentra tu lugar aquí <span class="button-arrow">→</span>
                </a>
            </section>

            <!-- ===================== CATÁLOGO (SOLO IMAGEN + NOMBRE) ===================== -->
            <section class="catalog-section" id="catalogSection">
                <div class="section-heading">
                    <div>
                        <div class="eyebrow">EL MERCADO DE NUESTRA TIERRA</div>
                        <h2>Encuentra algo <em>auténtico.</em></h2>
                        <p>Los 6 productos más recientes publicados por nuestros productores.</p>
                    </div>
                </div>

                <?php if (!empty($productos)): ?>
                    <div class="simple-products" id="productGrid">
                        <?php foreach ($productos as $index => $p): ?>
                            <article
                                class="simple-card"
                                style="--card-index: <?= (int)$index ?>;"
                                data-open-product="<?= (int)$p['id'] ?>"
                                role="button"
                                tabindex="0"
                                aria-label="Ver detalles de <?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <div class="simple-card-image">
                                    <img
                                        src="<?= htmlspecialchars(
                                            $p['fotografia'] !== ''
                                                ? $p['fotografia']
                                                : 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=800&q=80',
                                            ENT_QUOTES, 'UTF-8'
                                        ) ?>"
                                        alt="<?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                        loading="lazy"
                                    >
                                </div>
                                <h3 class="simple-card-name"><?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?></h3>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <span>⌕</span>
                        <h3>Todavía no hay productos publicados.</h3>
                        <p>Vuelve pronto para descubrir lo que nuestros productores están compartiendo.</p>
                    </div>
                <?php endif; ?>

                <div class="catalog-bottom">
                    <span>LO LOCAL TIENE MUCHO QUE CONTAR.</span>
                    <button class="text-button" id="backToTopButton" type="button">Volver arriba ↑</button>
                </div>
            </section>

            <!-- ===================== MUESTRAS ===================== -->
            <section class="origin-section" style="border-top:0;padding-top:0;">
                <span class="kicker">02 / LO QUE NOS RODEA</span>
                <h2>Un vistazo a lo <em>nuestro.</em></h2>

                <div class="samples">
                    <article class="s-corn">
                        <span>🌽</span>
                        <small>DEL CAMPO</small>
                        <h3>Granos y semillas</h3>
                    </article>
                    <article class="s-fruit">
                        <span>🍊</span>
                        <small>FRESCO Y LOCAL</small>
                        <h3>Frutas de temporada</h3>
                    </article>
                    <article class="s-craft">
                        <span>🧺</span>
                        <small>HECHO A MANO</small>
                        <h3>Artesanías</h3>
                    </article>
                </div>
            </section>

            <!-- ===================== BANNER COMUNIDAD ===================== -->
            <section class="community-banner">
                <div class="community-banner-image"></div>
                <div class="community-banner-content">
                    <span class="eyebrow eyebrow-light">UNA RED QUE CRECE DESDE LA RAÍZ</span>
                    <h2>Detrás de cada producto,<br>hay una <em>historia.</em></h2>
                    <p>Conoce a quienes mantienen viva la producción y la tradición de nuestra región.</p>
                    <a class="button button-light" href="acceso.php">
                        Formar parte <span class="button-arrow">↗</span>
                    </a>
                </div>
                <span class="banner-decoration" aria-hidden="true">R.</span>
            </section>

            <!-- ===================== CTA ORIGINAL ===================== -->
            <section class="origin-section" style="border-top:0;padding-top:0;padding-bottom:80px;">
                <div style="display:flex;flex-wrap:wrap;gap:24px;align-items:center;justify-content:space-between;">
                    <div>
                        <span class="kicker">HECHO CERCA. CON ORGULLO.</span>
                        <h2 style="margin:16px 0 0;">Compra, vende o<br><em>intercambia localmente.</em></h2>
                    </div>
                    <div style="display:flex;gap:18px;flex-wrap:wrap;">
                        <a class="button button-primary" href="acceso.php">Explorar la comunidad →</a>
                        <a class="button button-outline" href="#origen">Conoce nuestra raíz ↓</a>
                    </div>
                </div>
            </section>

        </section>

        <!-- ===================== DETALLE ===================== -->
        <section class="page-section page-detail" id="page-detail" hidden>
            <div class="detail-topbar">
                <button class="back-button" id="backToCatalogButton" type="button">← <span>Volver al catálogo</span></button>
                <span class="eyebrow">CONOCE SU HISTORIA</span>
            </div>

            <div class="detail-layout" id="detailContent"></div>

            <section class="related-section">
                <div class="section-heading section-heading-small">
                    <div>
                        <div class="eyebrow">SIGUE DESCUBRIENDO</div>
                        <h2>También podría <em>interesarte.</em></h2>
                    </div>
                </div>
                <div class="related-grid" id="relatedGrid"></div>
            </section>
        </section>
    </main>

    <footer class="main-footer">
        <div class="footer-main">
            <div class="footer-brand-column">
                <a href="#page-home" class="brand brand-light" data-page="home">
                    <span class="brand-mark">
                        <svg viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M24 40V18"></path>
                            <path d="M24 27C11 27 8 18 8 10C19 10 25 16 24 27Z"></path>
                            <path d="M24 21C24 10 31 5 41 5C41 17 34 23 24 21Z"></path>
                            <path d="M24 34C17 34 13 30 12 25"></path>
                        </svg>
                    </span>
                    <span class="brand-copy">
                        <strong>raíz local</strong>
                        <small>TOLIMÁN · QUERÉTARO</small>
                    </span>
                </a>
                <p>Apoya al campo,<br>fortalece nuestra comunidad.</p>
            </div>

            <div class="footer-links">
                <span class="footer-label">EXPLORA</span>
                <a href="#catalogSection">Catálogo</a>
                <a href="#origen">Nuestra raíz</a>
                <a href="#valores">Lo que nos mueve</a>
            </div>

            <div class="footer-links">
                <span class="footer-label">NUESTRA COMUNIDAD</span>
                <span>Productores locales</span>
                <span>Venta e intercambio</span>
                <span>Tradición regional</span>
            </div>

            <div class="footer-signoff">
                <span>HECHO CON RAÍCES.</span>
                <strong>Creado para<br>estar más cerca.</strong>
                <span class="footer-sun">✳</span>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© <?= date('Y') ?> RAÍZ LOCAL TOLIMÁN</span>
            <span>UNA PROPUESTA PARA CONECTAR NUESTRA COMUNIDAD</span>
            <button id="footerTopButton" type="button">VOLVER ARRIBA ↑</button>
        </div>
    </footer>
</div>

<div class="toast-message" id="toastMessage" role="status" aria-live="polite"></div>

<script>
    window.RAIZ_LOCAL_PRODUCTS = <?= json_encode(
        $productos,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES |
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ) ?>;
</script>
<script>
/* ==========================================================
   RAÍZ LOCAL TOLIMÁN — App JS (autocontenido)
========================================================== */
(function () {
    'use strict';

    const PRODUCTS = Array.isArray(window.RAIZ_LOCAL_PRODUCTS) ? window.RAIZ_LOCAL_PRODUCTS : [];
    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

    const welcomeScreen     = $('#welcomeScreen');
    const enterSiteButton   = $('#enterSiteButton');
    const appShell          = $('#appShell');
    const toastMessage      = $('#toastMessage');

    const detailContent     = $('#detailContent');
    const relatedGrid       = $('#relatedGrid');
    const backToCatalogBtn  = $('#backToCatalogButton');

    const mobileMenuButton  = $('#mobileMenuButton');
    const mobileNav         = $('#mobileNav');

    const pages = {
        home:     $('#page-home'),
        detail:   $('#page-detail')
    };

    let currentProductId = null;
    let welcomeTimer    = null;
    let hasEntered      = false;

    /* ---------- TOAST ---------- */
    let toastTimer = null;
    function showToast(message) {
        if (!toastMessage) return;
        toastMessage.textContent = message;
        toastMessage.classList.add('visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toastMessage.classList.remove('visible'), 2800);
    }

    /* ---------- NAVEGACIÓN ENTRE PÁGINAS ---------- */
    function goToPage(pageName) {
        Object.values(pages).forEach(p => { if (p) p.hidden = true; });
        const target = pages[pageName] || pages.home;
        if (target) target.hidden = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (pageName !== 'detail') {
            currentProductId = null;
        }
        closeMobileNav();
    }

    function closeMobileNav() {
        if (mobileNav) mobileNav.classList.remove('open');
        if (mobileMenuButton) mobileMenuButton.classList.remove('active');
    }

    document.addEventListener('click', (event) => {
        const pageBtn = event.target.closest('[data-page]');
        if (pageBtn) {
            event.preventDefault();
            goToPage(pageBtn.dataset.page);
            return;
        }
        const openBtn = event.target.closest('[data-open-product]');
        if (openBtn) {
            event.preventDefault();
            openProduct(Number(openBtn.dataset.openProduct));
        }
    });

    /* ---------- DETALLE ---------- */
    function findProduct(id) {
        return PRODUCTS.find(p => Number(p.id) === Number(id)) || null;
    }

    function productPriceText(product) {
        if (product.modalidad === 'Trueque' || (Number(product.precio) === 0 && product.modalidad !== 'Venta')) {
            return { big: 'Intercambio', small: 'Propuesta abierta' };
        }
        const price = Number(product.precio).toLocaleString('es-MX');
        return { big: `$${price}`, small: `MXN / ${product.unidad || ''}` };
    }

    function openProduct(id) {
        const product = findProduct(id);
        if (!product || !detailContent) return;
        currentProductId = id;

        const price = productPriceText(product);
        const isTrade = product.modalidad === 'Trueque';

        detailContent.innerHTML = `
            <div class="detail-gallery">
                <div class="detail-main-image">
                    <img src="${escapeAttr(product.fotografia || '')}" alt="${escapeAttr(product.nombre)}">
                </div>
                <div class="detail-image-caption">
                    <span>PRODUCTO LOCAL</span>
                    <span>${escapeHtml(String(product.categoria || '').toUpperCase())}</span>
                </div>
            </div>

            <div class="detail-copy">
                <span class="detail-type ${isTrade ? 'trade' : ''}">${escapeHtml(String(product.modalidad || 'Producto').toUpperCase())}</span>
                <h1>${escapeHtml(product.nombre)} <em>local.</em></h1>
                <p class="detail-quantity">${escapeHtml(product.disponibilidad || '')}</p>

                <div class="detail-price">
                    <strong>${escapeHtml(price.big)}</strong>
                    <span>${escapeHtml(price.small)}</span>
                </div>

                <p class="detail-description-label">SOBRE EL PRODUCTO</p>
                <p class="detail-description">${escapeHtml(product.descripcion || '')}</p>

                <div class="detail-facts">
                    <div class="detail-fact">
                        <span class="detail-fact-icon">⌂</span>
                        <div><small>UBICACIÓN</small><strong>${escapeHtml(product.ubicacion || '')}</strong></div>
                    </div>
                    <div class="detail-fact">
                        <span class="detail-fact-icon">✳</span>
                        <div><small>MODALIDAD</small><strong>${escapeHtml(product.modalidad || '')}</strong></div>
                    </div>
                </div>

                <div class="detail-producer">
                    <span class="detail-producer-avatar">PROD</span>
                    <div>
                        <small>PRODUCTOR</small>
                        <strong>Vendedor #${Number(product.id_vendedor)}</strong>
                    </div>
                </div>

                <button class="button button-primary detail-contact-button" type="button" data-contact="${Number(product.id)}">
                    Contactar al productor <span class="button-arrow">↗</span>
                </button>
                <p class="detail-contact-note">
                    La integración de WhatsApp y autenticación se conectará en la siguiente fase del proyecto.
                </p>
            </div>
        `;

        const contactBtn = detailContent.querySelector('[data-contact]');
        if (contactBtn) {
            contactBtn.addEventListener('click', () => {
                showToast('Próximamente: contacto directo por WhatsApp.');
            });
        }

        renderRelated(id);
        goToPage('detail');
    }

    function renderRelated(currentId) {
        if (!relatedGrid) return;
        const related = PRODUCTS
            .filter(p => Number(p.id) !== Number(currentId))
            .slice(0, 3);

        relatedGrid.innerHTML = related.map((product, index) => `
            <article class="simple-card" data-open-product="${Number(product.id)}" style="--card-index:${index}">
                <div class="simple-card-image">
                    <img src="${escapeAttr(product.fotografia || '')}" alt="${escapeAttr(product.nombre)}">
                </div>
                <h3 class="simple-card-name">${escapeHtml(product.nombre)}</h3>
            </article>
        `).join('');
    }

    if (backToCatalogBtn) {
        backToCatalogBtn.addEventListener('click', () => goToPage('home'));
    }

    /* ---------- PORTADA ---------- */
    function enterSite() {
        if (hasEntered || !welcomeScreen) return;
        hasEntered = true;
        clearTimeout(welcomeTimer);

        welcomeScreen.classList.add('is-leaving');
        document.body.classList.remove('lock-scroll');
        if (appShell) {
            appShell.classList.add('is-visible');
            appShell.setAttribute('aria-hidden', 'false');
        }
        setTimeout(() => {
            welcomeScreen.style.display = 'none';
        }, 900);
    }

    if (enterSiteButton) enterSiteButton.addEventListener('click', enterSite);
    document.body.classList.add('lock-scroll');

    /* Portada automática: se cierra a los 4 segundos */
    welcomeTimer = setTimeout(enterSite, 4000);

    /* ---------- MOBILE MENU ---------- */
    if (mobileMenuButton && mobileNav) {
        mobileMenuButton.addEventListener('click', () => {
            const open = mobileNav.classList.toggle('open');
            mobileMenuButton.classList.toggle('active', open);
        });
    }

    /* ---------- BOTONES VARIOS ---------- */
    const heroExploreButton = $('#heroExploreButton');
    if (heroExploreButton) {
        heroExploreButton.addEventListener('click', () => {
            const catalog = $('#catalogSection');
            if (catalog) catalog.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    const backToTopButton = $('#backToTopButton');
    if (backToTopButton) {
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    const footerTopButton = $('#footerTopButton');
    if (footerTopButton) {
        footerTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ---------- HELPERS ---------- */
    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
    function escapeAttr(text) { return escapeHtml(text); }

    if (appShell) appShell.setAttribute('aria-hidden', 'true');
})();
</script>
</body>
</html>