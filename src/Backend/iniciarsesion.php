<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../include/conexion.php';
require_once __DIR__ . '/../Usuario.php';
$conexion = new ConexionBD();
$pdo = $conexion->conectar();

if ($pdo === null) {
    $_SESSION['error_login'] = 'No se pudo conectar con la BD';
    header('Location: ../Frontend/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('El metodo no esta permitido');
}

$correo = $_POST['correo'] ?? '';
$contraseña = $_POST['password'] ?? '';

if (!is_string($correo) || !is_string($contraseña)) {
    $_SESSION['error_login'] = 'Los datos no son validos';
    header('Location: ../Frontend/login.php');
    exit;
}

$correo = trim($correo);

if ($correo === '' || $contraseña === '') {
    $_SESSION['error_login'] = 'Por favor llene todos los campos';
    header('Location: ../Frontend/login.php');
    exit;
}

$usuarioDAO = new Usuario($pdo);

try {

    $usuario = $usuarioDAO->autenticar($correo, $contraseña);

    if ($usuario === null || !Usuario::rolPuedeAdministrar($usuario['rol'])) {
        $_SESSION['error_login'] = 'Correo o contraseña incorrecto';
        header('Location: ../Frontend/login.php');
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_correo'] = $usuario['correo'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
    $_SESSION['autenticado'] = true;

    if ($usuario['rol'] === Usuario::ROL_ADMIN) {
        header('Location: ../Frontend/admin.php');
        exit;
    }

    if ($usuario['rol'] === Usuario::ROL_BODEGUERO) {
        header('Location: ../Frontend/bodega.php');
        exit;
    }


} catch (Throwable $excepcion) {

    error_log('Error al iniciar de sesion: ' . $excepcion->getMessage());

    $_SESSION['error_login'] = 'Error al iniciar sesion, vuelva a intentarlo';
    header('Location: ../Frontend/login.php');
    exit;
}

?>


