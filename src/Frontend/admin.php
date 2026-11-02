<?php
declare(strict_types=1);

// Verificar la sesion antes de mostrar cualquier contenido.
require_once __DIR__ . '/../Backend/verificarsesion.php';

// Mantener la distribucion de roles que ya tiene el proyecto.
if ($_SESSION['usuario_rol'] !== 'admin') {
    header('Location: bodega.php');
    exit;
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Administrador';

function escapar($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// Esta lista contiene enlaces, no nuevas operaciones de base de datos.
$accesos = [
    [
        'titulo' => 'Inventario de repuestos',
        'descripcion' => 'Consulta existencias, registra repuestos y modifica o elimina productos mediante modales.',
        'ruta' => 'bodega.php',
        'icono' => 'bi-box-seam',
        'tipo' => 'Gestión privada',
        'boton' => 'Gestionar inventario'
    ],
    [
        'titulo' => 'Facturación',
        'descripcion' => 'Accede al formulario de compra y facturación disponible en el proyecto.',
        'ruta' => 'facturacion.php',
        'icono' => 'bi-receipt',
        'tipo' => 'Formulario del portal',
        'boton' => 'Abrir facturación'
    ],
    [
        'titulo' => 'Kit Tune-Up',
        'descripcion' => 'Abre el cotizador de mantenimiento y revisa las opciones de aceite, filtros y adicionales.',
        'ruta' => 'kits.php',
        'icono' => 'bi-tools',
        'tipo' => 'Cotizador del portal',
        'boton' => 'Abrir cotizador'
    ],
    [
        'titulo' => 'Catálogo de repuestos',
        'descripcion' => 'Revisa cómo se muestran los productos, sus fotografías y precios a los clientes.',
        'ruta' => 'repuestos.php',
        'icono' => 'bi-shop',
        'tipo' => 'Consulta del portal',
        'boton' => 'Ver catálogo'
    ],
    [
        'titulo' => 'Categorías',
        'descripcion' => 'Consulta la agrupación de repuestos por líneas de productos.',
        'ruta' => 'categorias.php',
        'icono' => 'bi-grid',
        'tipo' => 'Consulta del portal',
        'boton' => 'Ver categorías'
    ],
    [
        'titulo' => 'Sucursales e historial',
        'descripcion' => 'Abre el directorio de sucursales y la consulta de compras existente.',
        'ruta' => 'sucursales.php',
        'icono' => 'bi-geo-alt',
        'tipo' => 'Consulta existente',
        'boton' => 'Consultar sucursales'
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Don Pancho | Panel administrativo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="bg-light">
    <header id="inicio" class="bg-dark text-light">
        <div class="container py-4">
            <div class="d-flex justify-content-center align-items-center gap-3">
                <img src="../imagenes/logo.jpg" alt="Logo Autopartes Don Pancho" width="100" class="img-fluid">
                <div>
                    <h1 class="display-4 fw-bold mb-2">Don Pancho</h1>
                    <p class="lead mb-0">Autopartes y Repuestos</p>
                    <p class="text-white-50 mb-0">Calidad, confianza y soluciones para tu vehículo</p>
                </div>
            </div>
        </div>
    </header>
    <nav class="bg-white border-bottom" aria-label="Navegación administrativa">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3 py-3">
            <a href="admin.php" class="fw-bold text-dark text-decoration-none" aria-current="page">
                <i class="bi bi-grid-1x2 me-2" aria-hidden="true"></i>Administración
            </a>

            <div class="d-flex flex-wrap align-items-center gap-3">
                <a href="index.php" class="text-dark text-decoration-none">Ver portal</a>

                <span>
                    <i class="bi bi-person-circle me-1" aria-hidden="true"></i>
                    <?= escapar($nombreUsuario) ?>
                    <span class="badge bg-dark ms-1">Admin</span>
                </span>

                <form method="post" action="../Backend/cerrarsesion.php" class="m-0">
                    <button type="submit" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="panel-encabezado">
            <p class="panel-etiqueta mb-2">Área privada / Don Pancho</p>
            <h2 class="display-6 fw-bold mb-2">Panel administrativo</h2>
            <p class="text-secondary mb-0">
                Gestiona el inventario y accede a los módulos del portal desde un solo lugar.
            </p>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($accesos as $acceso): ?>
                <div class="col">
                    <article class="card h-100 panel-tarjeta <?= $acceso['ruta'] === 'bodega.php' ? 'panel-destacada' : '' ?>">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
                                <span class="panel-icono" aria-hidden="true">
                                    <i class="bi <?= escapar($acceso['icono']) ?>"></i>
                                </span>
                                <span class="panel-tipo"><?= escapar($acceso['tipo']) ?></span>
                            </div>

                            <h3 class="h5 fw-bold mb-3"><?= escapar($acceso['titulo']) ?></h3>
                            <p class="panel-descripcion mb-4"><?= escapar($acceso['descripcion']) ?></p>

                            <a href="<?= escapar($acceso['ruta']) ?>" class="panel-enlace mt-auto">
                                <?= escapar($acceso['boton']) ?>
                                <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="small text-secondary mt-4 mb-0">
            Las modificaciones de repuestos se realizan en Inventario.
            Los demás accesos abren las páginas existentes del portal.
        </p>
    </main>
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="text-center mb-4">
                <h5 class="fw-bold mb-3">Síguenos en nuestras redes sociales</h5>
                <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" class="text-light me-3 text-decoration-none"><i class="bi bi-facebook"></i> Facebook</a>
                <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" class="text-light me-3 text-decoration-none"><i class="bi bi-instagram"></i> Instagram</a>
                <a href="https://www.tiktok.com/" target="_blank" rel="noopener noreferrer" class="text-light me-3 text-decoration-none"><i class="bi bi-tiktok"></i> TikTok</a>
                <a href="https://www.whatsapp.com/" target="_blank" rel="noopener noreferrer" class="text-light text-decoration-none"><i class="bi bi-whatsapp"></i> WhatsApp</a>
            </div>
            <hr class="border-secondary">
            <div class="row g-4">
                <div class="col-md-6">
                    <h4 class="fw-bold"><i class="bi bi-tools text-warning"></i> Don Pancho</h4>
                    <p class="text-white-50">Autopartes y Repuestos. Calidad y confianza para el cuidado y mantenimiento de tu vehículo </p>
                </div>
                <div class="col-md-6">
                    <h5 class="fw-bold">Nuestras sucursales</h5>
                    <p class="text-white-50 mb-1"><i class="bi bi-geo-alt"></i> Tegucigalpa</p>
                    <p class="text-white-50 mb-1"><i class="bi bi-geo-alt"></i> San Pedro Sula</p>
                    <p class="text-white-50 mb-0"><i class="bi bi-geo-alt"></i> La Ceiba</p>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center">
                <p class="mb-0 text-white-50">&copy; 2026 Autopartes y Repuestos Don Pancho. Todos los derechos reservados</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>