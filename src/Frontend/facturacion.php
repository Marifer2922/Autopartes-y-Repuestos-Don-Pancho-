<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../include/conexion.php';
$conexion = new ConexionBD();
$pdo = $conexion->conectar();
$fletes = ['mostrador' => 0.00, 'moto' => 50.00, 'camion' => 180.00];
$sucursales = $pdo ? $pdo->query("SELECT id, nombre, ciudad FROM sucursales")->fetchAll(PDO::FETCH_ASSOC) : [];
$repuestos = $pdo ? $pdo->query("SELECT id, nombre, precio, codigo FROM repuestos WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC) : [];
$factura = null;
$error = null;
$preId = (int)($_GET['repuesto_id'] ?? 0);
$preDesc = $_POST['descripcion'] ?? '';
$prePrecio = (float)($_POST['precio_unitario'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['origen'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $rtn = trim($_POST['rtn'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $sucursalId = (int)($_POST['sucursal_id'] ?? 1);
    $fleteTipo = $_POST['flete'] ?? 'mostrador';
    $cantidad = max(1, (int)($_POST['cantidad'] ?? 1));
    $repuestoId = (int)($_POST['repuesto_id'] ?? 0);

    if (!preg_match('/^\d{14}$/', $rtn)) {
        $error = "El RTN debe tener exactamente 14 dígitos numéricos.";
    } elseif (!preg_match('/^\d{8}$/', $telefono)) {
        $error = "El teléfono debe contener 8 dígitos.";
    } else {
        if ($preDesc !== '' && $prePrecio > 0) {
            $descProd = $preDesc;
            $precio = $prePrecio;
            $idRepBD = null;
        } else {
            $st = $pdo->prepare("SELECT nombre, precio, codigo FROM repuestos WHERE id = ?");
            $st->execute([$repuestoId]);
            $item = $st->fetch(PDO::FETCH_ASSOC);
            $descProd = $item['nombre'] ?? 'Repuesto';
            $precio = (float)($item['precio'] ?? 0);
            $idRepBD = $repuestoId;
        }

        $subtotal = round($precio * $cantidad, 2);
        $descuento = ($cantidad >= 10) ? round($subtotal * 0.10, 2) : (($cantidad >= 3) ? round($subtotal * 0.05, 2) : 0.00);
        $base = $subtotal - $descuento;
        $isv = round($base * 0.15, 2);
        $costoFlete = $fletes[$fleteTipo] ?? 0.00;
        $total = $base + $isv + $costoFlete;

        $stCli = $pdo->prepare("SELECT id FROM clientes WHERE rtn = ?");
        $stCli->execute([$rtn]);
        $clienteId = $stCli->fetchColumn();

        if (!$clienteId) {
            $pdo->prepare("INSERT INTO clientes (nombre, rtn, telefono) VALUES (?, ?, ?)")->execute([$nombre, $rtn, $telefono]);
            $clienteId = (int)$pdo->lastInsertId();
        }

        $numFactura = sprintf("000-001-01-%08d", random_int(1, 99999999));
        $pdo->prepare("INSERT INTO facturas (numero_factura, cliente_id, sucursal_id, fecha, subtotal, descuento, isv, flete_tipo, flete_monto, total) 
                       VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)")
            ->execute([$numFactura, $clienteId, $sucursalId, $subtotal, $descuento, $isv, $fleteTipo, $costoFlete, $total]);
        
        $facturaId = (int)$pdo->lastInsertId();
        $pdo->prepare("INSERT INTO factura_detalle (factura_id, repuesto_id, descripcion, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$facturaId, $idRepBD, $descProd, $cantidad, $precio, $subtotal]);

        $factura = compact('numFactura', 'nombre', 'rtn', 'telefono', 'descProd', 'cantidad', 'precio', 'subtotal', 'descuento', 'isv', 'costoFlete', 'total');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Don Pancho | Facturación</title>
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
                <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu-principal" aria-controls="menu-principal" aria-expanded="false" aria-label="Abrir menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
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

        <main class="container py-5">
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($factura): ?>
                <div class="card p-4 border-2 shadow-sm" style="border-style: dashed !important; border-color: #9c2b23 !important;">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <div>
                            <h4 class="fw-bold mb-0">Autopartes & Repuestos Don Pancho</h4>
                            <small class="text-muted">Factura Digital Fiscal SAR</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-dark fs-6"><?= $factura['numFactura'] ?></span>
                            <div class="small text-muted"><?= date('d/m/Y H:i') ?></div>
                        </div>
                    </div>

                    <div class="row mb-3 small">
                        <div class="col-6">
                            <strong>Cliente:</strong> <?= htmlspecialchars($factura['nombre']) ?><br>
                            <strong>RTN:</strong> <?= htmlspecialchars($factura['rtn']) ?><br>
                            <strong>Tel:</strong> <?= htmlspecialchars($factura['telefono']) ?>
                        </div>
                        <div class="col-6 text-end">
                            <strong>Producto:</strong> <?= htmlspecialchars($factura['descProd']) ?><br>
                            <strong>Cantidad:</strong> <?= $factura['cantidad'] ?> unid.
                        </div>
                    </div>

                    <table class="table table-sm text-end mb-3">
                        <tr><td>Subtotal:</td><td>L. <?= number_format($factura['subtotal'], 2) ?></td></tr>
                        <tr><td>Descuento:</td><td class="text-danger">- L. <?= number_format($factura['descuento'], 2) ?></td></tr>
                        <tr><td>ISV (15%):</td><td>L. <?= number_format($factura['isv'], 2) ?></td></tr>
                        <tr><td>Flete:</td><td>L. <?= number_format($factura['costoFlete'], 2) ?></td></tr>
                        <tr class="fw-bold fs-5 table-light"><td>Total a Pagar:</td><td class="text-danger">L. <?= number_format($factura['total'], 2) ?></td></tr>
                    </table>

                    <div class="text-center">
                        <a href="facturacion.php" class="btn btn-warning btn-sm">Nueva Compra</a>
                        <a href="sucursales.php" class="btn btn-outline-secondary btn-sm ms-2">Ver Historial</a>
                    </div>
                </div>

            <?php else: ?>
                <form method="POST" action="facturacion.php" class="card p-4 shadow-sm border-0">
                    <h4 class="fw-bold mb-3">Factura</h4>
                    <?php if ($preDesc !== '' && $prePrecio > 0): ?>
                        <input type="hidden" name="descripcion" value="<?= htmlspecialchars($preDesc) ?>">
                        <input type="hidden" name="precio_unitario" value="<?= $prePrecio ?>">
                        <div class="alert alert-info py-2 small">Kit: <strong><?= htmlspecialchars($preDesc) ?></strong> (L. <?= number_format($prePrecio, 2) ?>)</div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nombre del Cliente</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">RTN (14 dígitos)</label>
                            <input type="text" name="rtn" maxlength="14" class="form-control" placeholder="08011990123456" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Teléfono (8 dígitos)</label>
                            <input type="text" name="telefono" maxlength="8" class="form-control" placeholder="99887766" required>
                        </div>

                        <?php if ($preDesc === ''): ?>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Repuesto</label>
                            <select name="repuesto_id" class="form-select" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($repuestos as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= ($preId === (int)$r['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['nombre']) ?> (L. <?= number_format((float)$r['precio'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-<?= ($preDesc === '') ? '6' : '12' ?>">
                            <label class="form-label small fw-semibold">Cantidad</label>
                            <input type="number" name="cantidad" value="1" min="1" max="99" class="form-control" required>
                            <span class="small text-muted">A partir de 3: 5% desc. | A partir de 10: 10% desc. mayorista</span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Modalidad de Flete</label>
                            <select name="flete" class="form-select">
                                <option value="mostrador">Retiro en mostrador (Gratis)</option>
                                <option value="moto">Moto Express (+ L. 50.00)</option>
                                <option value="camion">Flete en Camioncito (+ L. 180.00)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Sucursal</label>
                            <select name="sucursal_id" class="form-select">
                                <?php foreach ($sucursales as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?> (<?= htmlspecialchars($s['ciudad']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-warning fw-semibold px-4">Generar Factura</button>
                    </div>
                </form>
            <?php endif; ?>
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