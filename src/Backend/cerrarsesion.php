<?php
declare(strict_types=1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {//ña solicitud si o si debe ser post
    http_response_code(405);
    exit('El metodo no esta permitido');
}

$_SESSION = [];//elimina todos los inicios de sesion

if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [   'expires' => time() - 42000,
            'path' => $parametros['path'],
            'domain' => $parametros['domain'],
            'secure' => $parametros['secure'],
            'httponly' => $parametros['httponly'],
            'samesite' => $parametros['samesite'] ?? 'Lax'
        ]
    );
}

session_destroy();//elimina la sesion

header('Location: ../Frontend/login.php');//lo manda al login
exit;
?>
