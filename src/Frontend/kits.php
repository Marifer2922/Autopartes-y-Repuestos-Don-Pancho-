<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../include/conexion.php';
require_once __DIR__ . '/../Kit.php';

$conexion = new ConexionBD();
$pdo = $conexion->conectar();
$kitModel = new Kit($pdo);

$tiposVehiculo = $kitModel->tiposVehiculo();
$aceites = $kitModel->opciones('aceite');
$filtros = $kitModel->opciones('filtro');
$adicionales = $kitModel->opciones('adicional');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Don Pancho | Arma tu Kit Tune-Up</title>

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
                    <li class="nav-item"><a href="kits.php" class="nav-link px-3 active"><i class="bi bi-box-seam"></i> Kits</a></li>
                    <li class="nav-item"><a href="sucursales.php" class="nav-link px-3"><i class="bi bi-geo-alt"></i> Sucursales</a></li>
                    <?php require __DIR__ . '/partials/menu-sesion.php'; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">Arma tu Kit de Mantenimiento Preventivo / Tune-Up</h2>
            <p class="text-secondary">Selecciona las especificaciones de tu vehículo y calcula tu presupuesto en tiempo real</p>
        </div>

        <form action="facturacion.php" method="POST">
            <input type="hidden" name="origen" value="tuneup">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-dark text-white fw-bold"><i class="bi bi-truck me-2"></i>1. Tipo de Vehículo</div>
                        <div class="card-body">
                            <?php foreach ($tiposVehiculo as $tv): ?>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="vehiculo_id" id="veh_<?= $tv['id'] ?>" value="<?= $tv['id'] ?>" required>
                                <label class="form-check-label fw-semibold" for="veh_<?= $tv['id'] ?>">
                                    <?= htmlspecialchars($tv['nombre']) ?> 
                                    <span class="badge bg-secondary ms-2"><?= $tv['litros_aceite'] ?> Litros</span>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-dark text-white fw-bold"><i class="bi bi-droplet-half me-2"></i>2. Marca y Viscosidad de Aceite</div>
                        <div class="card-body">
                            <label class="form-label small text-muted">Selecciona el aceite adecuado para tu motor:</label>
                            <select name="aceite_id" id="aceiteSelect" class="form-select" required>
                                <option value="" data-precio="0">-- Selecciona Aceite --</option>
                                <?php foreach ($aceites as $ac): ?>
                                    <option value="<?= $ac['id'] ?>" data-precio="<?= $ac['precio'] ?>" data-nombre="<?= htmlspecialchars($ac['nombre']) ?>">
                                        <?= htmlspecialchars($ac['nombre']) ?> (<?= $ac['marca'] ?> - <?= $ac['viscosidad'] ?>) - L. <?= number_format((float)$ac['precio'], 2) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-dark text-white fw-bold"><i class="bi bi-funnel me-2"></i>3. Filtros</div>
                        <div class="card-body">
                            <?php foreach ($filtros as $fl): ?>
                            <div class="form-check d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <input class="form-check-input check-adicional" type="checkbox" name="adicionales[]" value="<?= $fl['id'] ?>" id="fl_<?= $fl['id'] ?>" data-precio="<?= $fl['precio'] ?>" data-nombre="<?= htmlspecialchars($fl['nombre']) ?>">
                                    <label class="form-check-label" for="fl_<?= $fl['id'] ?>"><?= htmlspecialchars($fl['nombre']) ?></label>
                                </div>
                                <span class="text-success fw-bold">+ L. <?= number_format((float)$fl['precio'], 2) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-dark text-white fw-bold"><i class="bi bi-plus-circle me-2"></i>4. Repuestos Adicionales Tune-Up</div>
                        <div class="card-body">
                            <?php foreach ($adicionales as $ad): ?>
                            <div class="form-check d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <input class="form-check-input check-adicional" type="checkbox" name="adicionales[]" value="<?= $ad['id'] ?>" id="ad_<?= $ad['id'] ?>" data-precio="<?= $ad['precio'] ?>" data-nombre="<?= htmlspecialchars($ad['nombre']) ?>">
                                    <label class="form-check-label" for="ad_<?= $ad['id'] ?>"><?= htmlspecialchars($ad['nombre']) ?></label>
                                </div>
                                <span class="text-success fw-bold">+ L. <?= number_format((float)$ad['precio'], 2) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="descripcion" id="inputDescripcion" value="Kit de Mantenimiento Preventivo / Tune-Up">
            <input type="hidden" name="precio_unitario" id="inputPrecioUnitario" value="0">
            <input type="hidden" name="cantidad" value="1">

            <div class="card bg-white shadow-sm mt-4 p-4 border-0 rounded-3 text-end">
                <h4 class="mb-2">Total Cotizado del Kit: <span id="labelTotal" class="text-danger fw-bold">L. 0.00</span></h4>
                <div>
                    <button type="submit" class="btn btn-warning btn-lg fw-semibold px-4"><i class="bi bi-receipt"></i> Proceder a Facturación Fiscal</button>
                </div>
            </div>
        </form>
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
    <script>
    function calcularKit() {
        let total = 0;
        let nombres = ["Kit Tune-Up"];
        const aceite = document.getElementById('aceiteSelect');
        const precioAceite = parseFloat(aceite.options[aceite.selectedIndex]?.dataset.precio || 0);
        const nombreAceite = aceite.options[aceite.selectedIndex]?.dataset.nombre;
        if (precioAceite > 0) {
            total += precioAceite;
            if (nombreAceite) nombres.push(nombreAceite);
        }

        document.querySelectorAll('.check-adicional:checked').forEach(chk => {
            total += parseFloat(chk.dataset.precio || 0);
            nombres.push(chk.dataset.nombre);
        });

        document.getElementById('labelTotal').innerText = 'L. ' + total.toFixed(2);
        document.getElementById('inputPrecioUnitario').value = total.toFixed(2);
        document.getElementById('inputDescripcion').value = nombres.join(', ');
    }

    document.getElementById('aceiteSelect').addEventListener('change', calcularKit);
    document.querySelectorAll('.check-adicional').forEach(chk => chk.addEventListener('change', calcularKit));
    </script>
</body>
</html>