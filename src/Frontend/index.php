<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../include/conexion.php';

$conexion = new ConexionBD();
$pdo = $conexion->conectar();

$insignias = [];
if ($pdo !== null) {
    $stmt = $pdo->query("SELECT r.*, c.nombre AS categoria, m.nombre AS marca 
                         FROM repuestos r 
                         LEFT JOIN categorias c ON r.categoria_id = c.id 
                         LEFT JOIN marcas_vehiculo m ON r.marca_id = m.id 
                         WHERE r.destacado = 1 AND r.activo = 1 
                         ORDER BY r.id ASC LIMIT 5");
    $insignias = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Don Pancho | Autopartes y Repuestos</title>

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
                    <li class="nav-item"><a href="categorias.php" class="nav-link px-3"><i class="bi bi-grid"></i> Categorías</a></li>
                    <li class="nav-item"><a href="kits.php" class="nav-link px-3"><i class="bi bi-box-seam"></i> Kits</a></li>
                    <li class="nav-item"><a href="sucursales.php" class="nav-link px-3"><i class="bi bi-geo-alt"></i> Sucursales</a></li>
                    <?php require __DIR__ . '/partials/menu-sesion.php'; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <section class="container py-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold">Todo lo que tu vehículo necesita</h2>
                    <p class="lead text-secondary">En Autopartes y Repuestos Don Pancho encontrarás productos para el mantenimiento y cuidado de tu vehículo.</p>
                    <p>Contamos con diferentes categorías de repuestos, kits y sucursales para brindarte una mejor atención</p>
                    <a href="repuestos.php" class="btn btn-warning btn-lg"><i class="bi bi-search"></i> Explorar repuestos</a>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="../imagenes/portada.jpg" alt="Autopartes y repuestos Don Pancho" class="img-fluid rounded-4 shadow">
                </div>

            </div>

        </section>

        <section class="bg-white py-5 border-top border-bottom">
            <div class="container">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Nuestros 5 Repuestos Insignia</h3>
                    <p class="text-muted">Piezas destacadas de alta rotación consultadas en tiempo real desde la base de datos</p>
                </div>
                <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-3">
                    <?php if (empty($insignias)): ?>
                        <div class="col-12 text-center text-muted py-4">No hay repuestos insignia disponibles en este momento.</div>
                    <?php else: ?>
                        <?php foreach ($insignias as $item): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0">
                                <img src="../../uploads/<?= htmlspecialchars($item['fotografia'] ?? 'default.jpg') ?>" 
                                     class="card-img-top object-fit-cover" style="height: 150px;" 
                                     alt="<?= htmlspecialchars($item['nombre']) ?>"
                                     onerror="this.src='../imagenes/logo.jpg'">
                                <div class="card-body p-3 d-flex flex-column">
                                    <span class="badge bg-secondary mb-1 small"><?= htmlspecialchars($item['categoria'] ?? 'Repuesto') ?></span>
                                    <h6 class="card-title fw-bold text-truncate" title="<?= htmlspecialchars($item['nombre']) ?>"><?= htmlspecialchars($item['nombre']) ?></h6>
                                    <p class="card-text small text-muted flex-grow-1"><?= htmlspecialchars($item['especificaciones'] ?? $item['descripcion'] ?? '') ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                        <span class="fw-bold text-danger">L. <?= number_format((float)$item['precio'], 2) ?></span>
                                        <a href="facturacion.php?repuesto_id=<?= $item['id'] ?>" class="btn btn-warning btn-sm fw-semibold"><i class="bi bi-cart-plus"></i> Comprar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="container py-5 text-center">
            <h4 class="fw-bold mb-4 text-secondary text-uppercase">Marcas Vehiculares Aliadas</h4>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
                <div class="col"><div class="p-3 border rounded shadow-sm bg-white fw-bold fs-5 text-dark">Toyota</div></div>
                <div class="col"><div class="p-3 border rounded shadow-sm bg-white fw-bold fs-5 text-dark">Nissan</div></div>
                <div class="col"><div class="p-3 border rounded shadow-sm bg-white fw-bold fs-5 text-dark">Honda</div></div>
                <div class="col"><div class="p-3 border rounded shadow-sm bg-white fw-bold fs-5 text-dark">Hyundai</div></div>
                <div class="col"><div class="p-3 border rounded shadow-sm bg-white fw-bold fs-5 text-dark">Isuzu</div></div>
                <div class="col"><div class="p-3 border rounded shadow-sm bg-white fw-bold fs-5 text-dark">Ford</div></div>
            </div>
        </section>

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