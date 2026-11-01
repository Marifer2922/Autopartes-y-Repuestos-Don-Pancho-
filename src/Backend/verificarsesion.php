<?php
declare(strict_types=1);

session_start();
if (
    !isset($_SESSION['autenticado']) ||
    $_SESSION['autenticado'] !== true ||
    !isset($_SESSION['usuario_id']) ||
    !isset($_SESSION['usuario_rol'])
) {
    $_SESSION = [];
    header('Location: ../Frontend/login.php');
    exit;
}

if (!in_array($_SESSION['usuario_rol'], ['admin', 'bodeguero'], true)) {
    $_SESSION = [];
    header('Location: ../Frontend/login.php');
    exit;
}
?>
