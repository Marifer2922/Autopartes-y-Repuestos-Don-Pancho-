<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../include/conexion.php';

$conexion = new ConexionBD();
$pdo = $conexion->conectar();

$categorias = [];
if ($pdo !== null) {
    $categorias = $pdo->query("SELECT * FROM categorias ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Don Pancho | Categorías</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-secondary shadow-sm sticky-top">
        <div class="container">
            <a href="index.php" class="navbar-brand fw-bold"><i class="bi bi-car-front-fill"></i> Don Pancho </a>
            <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu-principal" aria-controls="menu-principal" aria-expanded="false" aria-label="Abrir menu"><span class="navbar-toggler-icon"></span></button>

            <div id="menu-principal" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a href="index.php" class="nav-link px-3"><i class="bi bi-house-door"></i> Inicio</a></li>
                    <li class="nav-item"><a href="repuestos.php" class="nav-link px-3"><i class="bi bi-gear"></i> Repuestos</a></li>
                    <li class="nav-item"><a href="categorias.php" class="nav-link px-3 active"><i class="bi bi-grid"></i> Categorías</a></li>
                    <li class="nav-item"><a href="kits.php" class="nav-link px-3"><i class="bi bi-box-seam"></i> Kits</a></li>
                    <li class="nav-item"><a href="sucursales.php" class="nav-link px-3"><i class="bi bi-geo-alt"></i> Sucursales</a></li>
                    <?php require __DIR__ . '/partials/menu-sesion.php'; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">Categorías de Autopartes</h2>
            <p class="text-secondary">Selecciona el sistema de tu vehículo para ver repuestos disponibles y cotizaciones</p>
        </div>

        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php if (empty($categorias)): ?>
                <div class="col-12 text-center text-muted py-5">No se pudieron cargar las categorías desde la base de datos.</div>
            <?php else: ?>
                <?php foreach ($categorias as $cat): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-warning text-dark p-3 rounded-circle me-3 fs-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <?php if ((int)$cat['id'] === 1): ?>
                                            <i class="bi bi-disc"></i>
                                        <?php elseif ((int)$cat['id'] === 2): ?>
                                            <i class="bi bi-cpu"></i>
                                        <?php elseif ((int)$cat['id'] === 3): ?>
                                            <i class="bi bi-lightning-charge"></i>
                                        <?php else: ?>
                                            <i class="bi bi-droplet-half"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h4 class="fw-bold mb-0"><?= htmlspecialchars($cat['nombre']) ?></h4>
                                        <span class="badge bg-secondary small">Línea Original y Reemplazo</span>
                                    </div>
                                </div>
                                <p class="text-muted mb-4"><?= htmlspecialchars($cat['descripcion'] ?? 'Repuestos de calidad comprobada para marcas japonesas, coreanas y americanas.') ?></p>
                            </div>
                            <div>
                                <a href="repuestos.php?cat=<?= $cat['id'] ?>" class="btn btn-warning w-100 fw-semibold py-2">
                                    Ver repuestos de <?= htmlspecialchars($cat['nombre']) ?> <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="text-center mb-4">
                <h5 class="fw-bold mb-3">Síguenos en nuestras redes sociales</h5>
                <a href="https://www.facebook.com/" target="_blank" class="text-light me-3 text-decoration-none"><i class="bi bi-facebook"></i> Facebook</a>
                <a href="https://www.instagram.com/" target="_blank" class="text-light me-3 text-decoration-none"><i class="bi bi-instagram"></i> Instagram</a>
                <a href="https://www.tiktok.com/" target="_blank" class="text-light me-3 text-decoration-none"><i class="bi bi-tiktok"></i> TikTok</a>
                <a href="https://www.whatsapp.com/" target="_blank" class="text-light text-decoration-none"><i class="bi bi-whatsapp"></i> WhatsApp</a>
            </div>
            <hr class="border-secondary">
            <div class="row g-4">
                <div class="col-md-5">
                    <h4 class="fw-bold"><i class="bi bi-tools text-warning"></i> Don Pancho</h4>
                    <p class="text-white-50">Autopartes y Repuestos. Calidad y confianza para el cuidado y mantenimiento de tu vehículo.</p>
                </div>
                <div class="col-md-7">
                    <h5 class="fw-bold mb-3">Nuestras sucursales físicas</h5>
                    <div class="row g-3 small text-white-50">
                        <div class="col-sm-6">
                            <p class="mb-1 text-white fw-semibold"><i class="bi bi-geo-alt text-warning"></i> Tegucigalpa - Toncontín</p>
                            <p class="mb-0">Boulevard Comunidad Económica Europea, sector del Aeropuerto Toncontín.</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1 text-white fw-semibold"><i class="bi bi-geo-alt text-warning"></i> Tegucigalpa - Suyapa</p>
                            <p class="mb-0">Boulevard Suyapa, sector cercano a la Universidad Nacional Autónoma de Honduras.</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1 text-white fw-semibold"><i class="bi bi-geo-alt text-warning"></i> San Pedro Sula - Medina</p>
                            <p class="mb-0">Barrio Medina, sector de la 7.ª Avenida.</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1 text-white fw-semibold"><i class="bi bi-geo-alt text-warning"></i> La Ceiba - CA-13</p>
                            <p class="mb-0">Carretera CA-13, sector de acceso a la ciudad de La Ceiba.</p>
                        </div>
                    </div>
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