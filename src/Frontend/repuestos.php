<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../include/conexion.php';

$conexion = new ConexionBD();
$pdo = $conexion->conectar();

$categoriaFiltro = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT r.*, c.nombre AS categoria, m.nombre AS marca 
        FROM repuestos r 
        INNER JOIN categorias c ON r.categoria_id = c.id 
        LEFT JOIN marcas_vehiculo m ON r.marca_id = m.id 
        WHERE r.activo = 1";

if ($categoriaFiltro > 0) {
    $sql .= " AND r.categoria_id = " . $categoriaFiltro;
}
$sql .= " ORDER BY c.id, r.nombre ASC";
$repuestos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0"><a href="login.php" class="btn btn-warning fw-semibold"><i class="bi bi-person-circle"></i> Iniciar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Catálogo General de Repuestos</h2>
                <p class="text-secondary mb-0">Piezas consultadas directamente desde nuestra base de datos</p>
            </div>
            <div class="btn-group my-2" role="group">
                <a href="repuestos.php" class="btn btn-outline-dark <?= $categoriaFiltro === 0 ? 'active' : '' ?>">Todas</a>
                <?php foreach ($categorias as $c): ?>
                    <a href="repuestos.php?cat=<?= $c['id'] ?>" class="btn btn-outline-dark <?= $categoriaFiltro === (int)$c['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($c['nombre']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php if (empty($repuestos)): ?>
                <div class="col-12 text-center text-muted py-5">No se encontraron repuestos para la categoría seleccionada.</div>
            <?php else: ?>
                <?php foreach ($repuestos as $r): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="../../uploads/<?= htmlspecialchars($r['fotografia'] ?? 'default.jpg') ?>" 
                             class="card-img-top object-fit-cover" style="height: 180px;" 
                             alt="<?= htmlspecialchars($r['nombre']) ?>"
                             onerror="this.src='../imagenes/logo.jpg'">
                        <div class="card-body d-flex flex-column p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-secondary small"><?= htmlspecialchars($r['categoria']) ?></span>
                                <span class="badge bg-light text-dark border small"><?= htmlspecialchars($r['codigo']) ?></span>
                            </div>
                            <h5 class="card-title fw-bold mt-2 fs-6"><?= htmlspecialchars($r['nombre']) ?></h5>
                            <p class="card-text small text-muted flex-grow-1 mb-2">
                                <strong>Especificaciones:</strong> <?= htmlspecialchars($r['especificaciones'] ?? $r['descripcion'] ?? 'N/D') ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-auto">
                                <span class="fw-bold fs-5 text-danger">L. <?= number_format((float)$r['precio'], 2) ?></span>
                                <a href="facturacion.php?repuesto_id=<?= $r['id'] ?>" class="btn btn-warning btn-sm fw-semibold"><i class="bi bi-cart-plus"></i> Comprar</a>
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