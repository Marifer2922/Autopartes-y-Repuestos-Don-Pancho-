<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../include/conexion.php';

$conexion = new ConexionBD();
$pdo = $conexion->conectar();

$sucursales = [];
$historial = [];
if ($pdo !== null) {
    $sucursales = $pdo->query("SELECT * FROM sucursales ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Consulta de compras / facturas emitidas por sucursal
    $sqlHistorial = "SELECT f.numero_factura, f.fecha, f.total, s.nombre AS sucursal, c.nombre AS cliente 
                     FROM facturas f 
                     INNER JOIN sucursales s ON f.sucursal_id = s.id 
                     INNER JOIN clientes c ON f.cliente_id = c.id 
                     ORDER BY f.id DESC LIMIT 10";
    $historial = $pdo->query($sqlHistorial)->fetchAll(PDO::FETCH_ASSOC);
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
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0"><a href="login.php" class="btn btn-warning fw-semibold"><i class="bi bi-person-circle"></i> Iniciar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">Directorio de Sucursales Físicas</h2>
            <p class="text-secondary">Visita nuestras salas de ventas y bodegas autorizadas a nivel nacional</p>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-5">
            <?php foreach ($sucursales as $s): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <span class="badge bg-warning text-dark mb-2"><?= htmlspecialchars($s['ciudad']) ?></span>
                        <h5 class="fw-bold"><?= htmlspecialchars($s['nombre']) ?></h5>
                        <p class="small text-muted mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($s['direccion']) ?></p>
                        <p class="small text-secondary mb-1"><i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars($s['telefono']) ?></p>
                        <p class="small text-secondary mb-0"><i class="bi bi-clock-fill me-1"></i><?= htmlspecialchars($s['horario']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-clock-history me-2"></i> Historial Reciente de Compras por Sucursal
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-secondary small">
                            <tr>
                                <th>Factura SAR</th>
                                <th>Fecha</th>
                                <th>Sucursal</th>
                                <th>Cliente</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($historial)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No se registran transacciones aún.</td></tr>
                            <?php else: ?>
                                <?php foreach ($historial as $h): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($h['numero_factura']) ?></td>
                                    <td><?= htmlspecialchars($h['fecha']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($h['sucursal']) ?></span></td>
                                    <td><?= htmlspecialchars($h['cliente']) ?></td>
                                    <td class="fw-bold text-danger">L. <?= number_format((float)$h['total'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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