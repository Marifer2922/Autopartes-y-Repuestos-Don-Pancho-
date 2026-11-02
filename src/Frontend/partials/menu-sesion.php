<?php
$sesionMenuActiva =
    isset($_SESSION['autenticado'], $_SESSION['usuario_id'], $_SESSION['usuario_rol'])
    && $_SESSION['autenticado'] === true
    && in_array($_SESSION['usuario_rol'], ['admin', 'bodeguero'], true);
?>

<?php if ($sesionMenuActiva): ?>

    <?php
    $nombreMenu = (string) ($_SESSION['usuario_nombre'] ?? 'Usuario');

    if ($_SESSION['usuario_rol'] === 'admin') {
        $rolMenu = 'Admin';
        $rutaMenu = 'admin.php';
    } else {
        $rolMenu = 'Bodega';
        $rutaMenu = 'bodega.php';
    }
    ?>

    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
        <a href="<?= $rutaMenu ?>" class="nav-link text-white">
            <i class="bi bi-person-circle me-1" aria-hidden="true"></i>
            <?= htmlspecialchars($nombreMenu, ENT_QUOTES, 'UTF-8') ?>
            <span class="badge bg-dark ms-1"><?= $rolMenu ?></span>
        </a>
    </li>

    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
        <form method="post" action="../Backend/cerrarsesion.php" class="m-0">
            <button type="submit" class="btn btn-warning fw-semibold">
                <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>
                Cerrar sesión
            </button>
        </form>
    </li>

<?php else: ?>

    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
        <a href="login.php" class="btn btn-warning fw-semibold">
            <i class="bi bi-person-circle me-1" aria-hidden="true"></i>
            Iniciar sesión
        </a>
    </li>

<?php endif; ?>
