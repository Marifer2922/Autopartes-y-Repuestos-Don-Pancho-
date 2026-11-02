<?php
    /*declare(strict_types=1);

    require_once __DIR__ . '/../Backend/verificarsesion.php';
    require_once __DIR__ . '/../../include/conexion.php';
    require_once __DIR__ . '/../Repuesto.php';

    $conexion = new ConexionBD();
    $pdo = $conexion->conectar();

    if ($_SESSION['usuario_rol'] !== 'bodeguero') {
    header('Location: admin.php');
    exit;
    }

    $repuestos = [];
    if ($pdo !== null) {
        $stmt = $pdo->query("SELECT codigo, nombre, precio, stock FROM repuestos");
        $repuestos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }*/

    declare(strict_types=1);

    require_once __DIR__ . '/../Backend/verificarsesion.php';
    require_once __DIR__ . '/../../include/conexion.php';
    require_once __DIR__ . '/../Repuesto.php';
    require_once __DIR__ . '/../Categoria.php';

    $conexion = new ConexionBD();
    $pdo = $conexion->conectar();

    if ($pdo === null) {
        exit('No se puede mostrar el inventario sin conexión a la base de datos.');
    }

    $modeloRepuesto = new Repuesto($pdo);

    function escapar($texto)
    {
        return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

    /*//Carga segura de archivos
    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagenProducto'])){
        //Valida que no exista error al intentar subir el archivo
        $imagenProducto = $_FILES['imagenProducto'];

        if($imagenProducto['error'] !== UPLOAD_ERR_OK){
            $estadoSubida = false;
            $mensajeEstado = "Error al intentar subir la imagen, intentelo de nuevo";
        } else {
            //Validación del tamaño máximo del archivo (4MB)
            $limiteTamaño = 4*1024*1024;

            if($imagenProducto['size'] > $limiteTamaño){
                $estadoSubida = false;
                $mensajeEstado = "El tamaño de la imagen excede el límite establecido (4MB)";
            } else {
                //Validación de la extensión del archivo
                $extensionImagen = strtolower(pathinfo($imagenProducto['name'], PATHINFO_EXTENSION));
                $extensionesPermitidos = ['jpg', 'jpeg', 'png', 'webp'];

                if(!in_array($extensionImagen, $extensionesPermitidos, true)){
                    $estadoSubida = false;
                    $mensajeEstado = "El formato de la imagen no es soportado por el sitio, seleccione una imagen
                            con una de las extensiones permitidas (JPG, JPEG, PNG, WEBP)";
                } else {
                    //Validación del tipo MIME del archivo
                    $infoMime = new finfo(FILEINFO_MIME_TYPE);
                    $imagenMime = $infoMime->file($imagenProducto['tmp_name']);
                    $mimePermitidos = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];

                    if(!in_array($imagenMime, $mimePermitidos, true)){
                        $estadoSubida = false;
                        $mensajeEstado = "El archivo seleccionado no es del tipo imagen valido.";
                    } else {
                        //Renombrar imagen y asignación de identificador criptográfico
                        $nombreNuevoImagen = uniqid('repuesto_') .'.'. $extensionImagen;
                        //Se asigna el destino del archivo
                        $ruta = __DIR__ . '/uploads/'; 

                        //Se verifica que la ruta exista, de otro modo crea la carpeta
                        if (!is_dir($ruta)) {
                            mkdir($ruta, 0755, true);
                        }

                        $destinoImagen = $ruta . $nombreNuevoImagen;

                        if(move_uploaded_file($imagenProducto['tmp_name'], $destinoImagen)){
                            $estadoSubida = true;
                            
                            $codigo = htmlspecialchars(trim($_POST['codigoProducto'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $nombre = htmlspecialchars(trim($_POST['nombreProducto'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $descripcion = htmlspecialchars(trim($_POST['descripcionProducto'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $especificaciones = htmlspecialchars(trim($_POST['especificacionProducto'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $categoria_id = intval(htmlspecialchars(trim($_POST['categoriaProducto'] ?? ''), ENT_QUOTES, 'UTF-8'));
                            $marca_id = intval(htmlspecialchars(trim($_POST['marcaProducto'] ?? ''), ENT_QUOTES, 'UTF-8'));
                            $precio = floatval(htmlspecialchars(trim($_POST['precioProducto'] ?? ''), ENT_QUOTES, 'UTF-8'));
                            $stock = intval(htmlspecialchars(trim($_POST['stockProducto'] ?? ''), ENT_QUOTES, 'UTF-8'));
                            $destacado = intval(htmlspecialchars(trim($_POST['destacadoProducto'] ?? ''), ENT_QUOTES, 'UTF-8'));

                            $datos = ["codigo" => $codigo, "nombre" => $nombre, "descripcion" => $descripcion, "especificaciones" => $especificaciones,
                                        "categoria_id" => $categoria_id, "marca_id" => $marca_id, "precio" => $precio, "stock" => $stock,
                                        "fotografia" => $destinoImagen, "destacado" => $destacado];

                            $nuevoProducto = new Repuesto($pdo);
                            $nuevoProducto->crear($datos);

                        } else {
                            $estadoSubida = false;
                            $mensajeEstado = "Error al subir la imagen al directorio final";
                        }
                    }
                }
            }
        }
    }*/

    // Leer un campo sin convertir todavia su valor a numero.
    function leerCampo($nombre)
    {
        $valor = $_POST[$nombre] ?? '';

        if (!is_string($valor)) {
            throw new InvalidArgumentException('El formulario contiene un campo no válido.');
        }

        return trim($valor);
    }

    // Crear una clave de seguridad asociada a esta sesion.
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    // Recuperar el mensaje de la operacion anterior y mostrarlo una sola vez.
    $mensajeInventario = $_SESSION['mensaje_inventario'] ?? '';
    $tipoMensaje = $_SESSION['tipo_mensaje_inventario'] ?? 'success';
    unset($_SESSION['mensaje_inventario'], $_SESSION['tipo_mensaje_inventario']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $imagenGuardada = '';
        $guardado = false;
        $tipoMensaje = 'danger';

        try {
            if (empty($_POST) && empty($_FILES)) {
                throw new InvalidArgumentException(
                    'No se recibieron datos. Revisa el límite de carga del servidor.'
                );
            }

            // Comprobar que la solicitud pertenece al formulario de esta sesion.
            if (!hash_equals($_SESSION['csrf'], leerCampo('csrf'))) {
                throw new InvalidArgumentException(
                    'El formulario no es válido o la sesión cambió. Recarga la página.'
                );
            }

            $accion = leerCampo('accion');

            if ($accion !== 'crear' && $accion !== 'editar') {
                throw new InvalidArgumentException(
                    'La operación solicitada no es válida.'
                );
            }

            // Para editar necesitamos identificar el repuesto.
            $id = 0;

            if ($accion === 'editar') {
                $id = filter_var(
                    leerCampo('idRepuesto'),
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );

                if ($id === false) {
                    throw new InvalidArgumentException(
                        'El identificador del repuesto no es válido.'
                    );
                }
            }

            // Los nombres de la izquierda son los que espera Repuesto.php.
            $datos = [
                'codigo' => leerCampo('codigoProducto'),
                'nombre' => leerCampo('nombreProducto'),
                'descripcion' => leerCampo('descripcionProducto'),
                'especificaciones' => leerCampo('especificacionProducto'),
                'categoria_id' => leerCampo('categoriaProducto'),
                'marca_id' => leerCampo('marcaProducto'),
                'precio' => leerCampo('precioProducto'),
                'stock' => leerCampo('stockProducto'),
                'destacado' => leerCampo('destacadoProducto')
            ];

            if (!in_array($datos['destacado'], ['0', '1'], true)) {
                throw new InvalidArgumentException('Selecciona Sí o No en destacado.');
            }

            // Sin imagen nueva, actualizar() conserva la fotografia anterior.
            $datos['fotografia'] = null;

            $errorFoto = $_FILES['fotografia']['error'] ?? UPLOAD_ERR_NO_FILE;

            // Al crear exigimos foto.
            // Al editar la procesamos solamente cuando se intenta subir otra.
            if ($accion === 'crear' || $errorFoto !== UPLOAD_ERR_NO_FILE) {

                // Candado 1: el archivo debe llegar sin errores.
                if ($errorFoto !== UPLOAD_ERR_OK) {
                    throw new InvalidArgumentException(
                        'No se pudo recibir la fotografía. Selecciona una imagen válida.'
                    );
                }

                $foto = $_FILES['fotografia'];

                // Candado 2: maximo 4 MB y archivo no vacio.
                if ($foto['size'] <= 0 || $foto['size'] > 4 * 1024 * 1024) {
                    throw new InvalidArgumentException(
                        'La imagen debe pesar como máximo 4 MB y no estar vacía.'
                    );
                }

                // Candado 3: extension permitida y MIME coincidente.
                $extension = strtolower(
                    pathinfo($foto['name'], PATHINFO_EXTENSION)
                );

                $tiposPermitidos = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'webp' => 'image/webp'
                ];

                if (!isset($tiposPermitidos[$extension])) {
                    throw new InvalidArgumentException(
                        'Solo se permiten imágenes JPG, JPEG, PNG o WEBP.'
                    );
                }

                $infoMime = new finfo(FILEINFO_MIME_TYPE);
                $mime = $infoMime->file($foto['tmp_name']);

                if ($mime !== $tiposPermitidos[$extension]) {
                    throw new InvalidArgumentException(
                        'El contenido del archivo no coincide con su extensión.'
                    );
                }

                // Candado 4: nombre aleatorio y carpeta uploads de la raiz.
                $nombreFoto =
                    'repuesto_' . bin2hex(random_bytes(16)) . '.' . $extension;

                $carpeta = __DIR__ . '/../../uploads/';

                if (!is_dir($carpeta) && !@mkdir($carpeta, 0755, true)) {
                    throw new RuntimeException(
                        'No se pudo crear la carpeta uploads. Revisa sus permisos.'
                    );
                }

                $destino = $carpeta . $nombreFoto;

                if (!@move_uploaded_file($foto['tmp_name'], $destino)) {
                    throw new RuntimeException(
                        'No se pudo guardar la imagen. Revisa los permisos de uploads.'
                    );
                }

                $imagenGuardada = $destino;

                // En la base de datos guardamos solamente el nombre.
                $datos['fotografia'] = $nombreFoto;
            }

            // Ejecutar solamente la operacion solicitada.
            if ($accion === 'crear') {

                $modeloRepuesto->crear($datos);
                $mensajeInventario = 'Repuesto registrado correctamente.';

            } else {

                if (!$modeloRepuesto->actualizar($id, $datos)) {
                    throw new RuntimeException(
                        'No se pudieron guardar los cambios.'
                    );
                }

                $mensajeInventario = 'Repuesto actualizado correctamente.';
            }

            $guardado = true;
            $tipoMensaje = 'success';

        } catch (PDOException $error) {
            error_log('Error al guardar repuesto: ' . $error->getMessage());
            $mensajeInventario = 'No se pudo guardar el repuesto en la base de datos.';

        } catch (InvalidArgumentException $error) {
            $mensajeInventario = $error->getMessage();

        } catch (RuntimeException $error) {
            $mensajeInventario = $error->getMessage();

        } catch (Throwable $error) {
            error_log('Error inesperado en inventario: ' . $error->getMessage());
            $mensajeInventario = 'Ocurrió un error inesperado. Revisa la configuración de PHP.';
        }

        // Si fallo el registro, no dejar la nueva fotografia sin un repuesto.
        if (!$guardado && $imagenGuardada !== '') {
            if (!@unlink($imagenGuardada)) {
                error_log('No se pudo retirar la imagen: ' . $imagenGuardada);
            }
        }

        // Regresar al mismo inventario sin reenviar el POST al recargar.
        $_SESSION['mensaje_inventario'] = $mensajeInventario;
        $_SESSION['tipo_mensaje_inventario'] = $tipoMensaje;

        header('Location: bodega.php', true, 303);
        exit;
}    

    /*// Consultar el inventario después de procesar los formularios.
$repuestos = [];
$errorInventario = '';

try {
    $repuestos = $modeloRepuesto->listar(null, null, false);
} catch (PDOException $error) {
    error_log('Error al consultar el inventario: ' . $error->getMessage());
    $errorInventario = 'No se pudo consultar el inventario. Revisa la base de datos.';
}*/

// Consultar el inventario después de procesar los formularios.
$repuestos = [];
$categorias = [];
$marcas = [];
$errorInventario = '';

try {
    $repuestos = $modeloRepuesto->listar(null, null, false);

    $modeloCategoria = new Categoria($pdo);
    $categorias = $modeloCategoria->listar();
    $marcas = $modeloCategoria->listarMarcas();

} catch (PDOException $error) {
    error_log('Error al consultar el inventario: ' . $error->getMessage());

    $errorInventario =
        'No se pudo cargar el inventario o sus opciones. Revisa la base de datos.';
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

    <main>

        <!--Ventana modal para ingresar los nuevos productos-->
            <div class="modal fade" id="modal-nuevo-repuesto" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-secondary text-light shadow-lg">
                            <h3 class="modal-title">INGRESO DE NUEVO PRODUCTO</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h4>Ingrese los datos solicitados:</h4>
                            <form method="post" action="bodega.php" enctype="multipart/form-data">

                                <input type="hidden" name="accion" value="crear">

                                <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">

                                <div class="p-2">
                                    <label class="form-label" for="input-codigo-producto">Código del repuesto: </label>
                                    <input type="text" class="form-control" name="codigoProducto" id="input-codigo-producto" required>
                                </div>
                                <div class="p-2">
                                    <label class="form-label" for="input-nombre-producto">Nombre: </label>
                                    <input type="text" class="form-control" name="nombreProducto" id="input-nombre-producto" required>
                                </div>
                                <div class="p-2">
                                    <label class="form-label" for="input-especificacion-producto">Especificaciones: </label>
                                    <textarea class="form-control" name="especificacionProducto" id="input-especificacion-producto" rows="2"></textarea>
                                </div>
                                <div class="p-2">
                                    <label class="form-label" for="input-descripcion-producto">Descripción del repuesto: </label>
                                    <textarea class="form-control" name="descripcionProducto" id="input-descripcion-producto" rows="2"></textarea>
                                </div>
                                <div class="p-2">
                                    <label class="form-label" for="input-categoria-producto">Categoria: </label>
                                    <select class="form-select" name="categoriaProducto" id="input-categoria-producto" required>
                                        <option value="1">Frenos y suspension</option>
                                        <option value="2">Motor y transmisión</option>
                                        <option value="3">Sistema eléctrico</option>
                                        <option value="4">Fluidos y lubricantes</option>
                                    </select>
                                </div>
                                <div class="p-2">
                                    <label class="form-label" for="input-marca-producto">Marca: </label>
                                    <select class="form-select" name="marcaProducto" id="input-marca-producto">
                                        <option value="1">Toyota</option>
                                        <option value="2">Nissan</option>
                                        <option value="3">Honda</option>
                                        <option value="4">Hyundai</option>
                                        <option value="5">Isuzu</option>
                                        <option value="6">Ford</option>
                                    </select>
                                </div>
                                <div class="p-2">
                                    <label class="form-label" for="input-precio-producto">Precio: </label>

                                    <!-- <input type="text" class="form-control" name="precioProducto" id="input-precio-producto" required> --> 

                                    <input type="number" class="form-control" name="precioProducto" id="input-precio-producto" min="0" step="0.01" required>

                                </div>
                                <div class="p-2">
                                    <label class="form-label" for="input-stock-producto">Cantidad en bodega: </label>

                                    <!-- <input type="number" class="form-control" name="stockProducto" id="input-stock-producto" min="0" required> -->

                                    <input type="number" class="form-control" name="stockProducto" id="input-stock-producto" min="0" step="1" required>

                                </div>

                                <!-- <div class="p-2">
                                    <label class="form-label" for="input-imagen-producto">Imagen del repuesto: </label>
                                    <input type="file" class="form-control" name="imagenProducto" id="input-imagen-producto">
                                </div> -->

                                <div class="p-2">
                                    <label class="form-label" for="input-imagen-producto">Imagen del repuesto: </label>
                                    <input type="file" class="form-control" name="fotografia" id="input-imagen-producto" accept=".jpg,.jpeg,.png,.webp" required>

                                    <div class="form-text">
                                        Selecciona una imagen JPG, JPEG, PNG o WEBP.
                                        Tamaño máximo permitido: 4 MB.
                                    </div>
                                </div>

                                <div class="p-2">
                                    <label class="form-label" for="input-destacado-producto">Destacado: </label>

                                    <!-- <select class="form-select" name="destacadoProducto" id="input-destacado-producto" required>
                                        <option value="1">Si</option>
                                        <option value="2">No</option>
                                    </select> -->

                                    <select class="form-select" name="destacadoProducto" id="input-destacado-producto" required>
                                        <option value="0">No</option>
                                        <option value="1">Sí</option>
                                    </select>

                                </div>

                                <!--<div class="p-2 d-flex justify-content-center">
                                    <button type="submit" class="btn btn-warning">Ingresar nuevo producto</button>
                                </div>-->

                                <div class="p-2 d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar </button>
                                    <button type="submit" class="btn btn-warning">Guardar repuesto </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para editar un repuesto -->
            <div class="modal fade" id="modal-editar-repuesto" tabindex="-1" aria-labelledby="titulo-editar-repuesto" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">

                    <form class="modal-content" id="form-editar-repuesto" method="post" action="bodega.php" enctype="multipart/form-data">
                        <div class="modal-header bg-secondary text-light">
                            <h3 class="modal-title fs-5" id="titulo-editar-repuesto">
                                Editar repuesto
                            </h3>

                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <input type="hidden" name="accion" value="editar">

                        <input type="hidden" name="idRepuesto" id="editar-id">

                        <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>" >

                        <div class="modal-body">

                            <div class="mb-3">
                                <label for="editar-codigo" class="form-label">Código: </label>

                                <input type="text" class="form-control" id="editar-codigo" name="codigoProducto" maxlength="30" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar-nombre" class="form-label">Nombre: </label>

                                <input type="text" class="form-control" id="editar-nombre" name="nombreProducto" maxlength="120" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar-especificaciones" class="form-label">
                                    Especificaciones:
                                </label>

                                <textarea class="form-control" id="editar-especificaciones" name="especificacionProducto" rows="2" maxlength="255"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="editar-descripcion" class="form-label"> Descripción: </label>

                                <textarea class="form-control" id="editar-descripcion" name="descripcionProducto" rows="2" maxlength="255"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="editar-categoria" class="form-label"> Categoría: </label>

                                <select class="form-select" id="editar-categoria" name="categoriaProducto" required>
                                    <option value="">Selecciona una categoría</option>

                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= (int) $categoria['id'] ?>">
                                            <?= escapar($categoria['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="editar-marca" class="form-label">Marca: </label>

                                <select class="form-select" id="editar-marca" name="marcaProducto">
                                    <option value="">Sin marca específica</option>

                                    <?php foreach ($marcas as $marca): ?>
                                        <option value="<?= (int) $marca['id'] ?>">
                                            <?= escapar($marca['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="editar-precio" class="form-label">Precio: </label>

                                <input type="number" class="form-control" id="editar-precio" name="precioProducto" min="0" step="0.01" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar-stock" class="form-label">Cantidad en bodega: </label>

                                <input type="number" class="form-control" id="editar-stock" name="stockProducto" min="0" step="1" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar-fotografia" class="form-label">Nueva fotografía (opcional): </label>

                                <input type="file" class="form-control" id="editar-fotografia" name="fotografia" accept=".jpg,.jpeg,.png,.webp">

                                <div class="form-text">
                                    JPG, JPEG, PNG o WEBP. Máximo 4 MB.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="editar-destacado" class="form-label">Destacado: </label>

                                <select class="form-select" id="editar-destacado" name="destacadoProducto" required>
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>

                            <p class="small text-muted mb-0">
                                Si no seleccionas una nueva fotografía, se conservará la actual.
                            </p>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar </button>

                            <button type="submit" class="btn btn-warning">Guardar cambios </button>
                        </div>

                    </form>
                </div>
            </div>

        <!--Tabla de inventario-->
        <section class="py-4">
            <div class="container">
                    <div class="text-center mb-2">
                        <h2 class="display-6 fw-bold">Inventario de repuestos</h2>
                    </div>

                    <!--Mensaje de estado de ingreso de producto
                    <?php if($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                        <?php if($estadoSubida): ?>
                            <div class="container py-4">
                                <div class="badge rounded-pill bg-success">
                                    <h5 class="m-2"><i class="bi bi-check2-circle"></i> Éxito al ingresar el producto.</h5>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if(!$estadoSubida): ?>
                            <div class="container py-4">
                                <div class="badge rounded-pill bg-danger">
                                    <h5 class="m-2"><i class="bi bi-x-circle"></i> Error al ingresar el nuevo producto</h5>
                                    <p class="m-1"><?= $mensajeEstado ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?> -->

                    <!-- Resultado de la operacion -->
                    <?php if ($mensajeInventario !== ''): ?>

                        <div
                            class="alert alert-<?= escapar($tipoMensaje) ?>"
                            role="alert"
                        >
                            <?= escapar($mensajeInventario) ?>
                        </div>

                    <?php endif; ?>

                    <!--Boton para ingresar nuevos productos-->
                    <button type="button" class="btn btn-warning fw-bold mb-3" data-bs-toggle="modal" data-bs-target="#modal-nuevo-repuesto">
                        Ingrese nuevo producto
                    </button>

                    <!--<div class="table-responsive">
                        <table class="table table-striped-columns border">
                        <thead class="table-warning">
                            <tr>
                            <th scope="col">Código</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($repuestos as $repuesto): ?>
                            <tr>
                                <td><?= $repuesto["codigo"] ?></td>
                                <td><?= $repuesto["nombre"] ?></td>
                                <td>L.<?= number_format(floatval($repuesto["precio"]),2) ?></td>
                                <td><?= $repuesto["stock"] ?></td>
                            <tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                    </div>-->

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle border">
                            <thead class="table-warning">
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Categoría</th>
                                    <th scope="col">Marca</th>
                                    <th scope="col">Precio</th>
                                    <th scope="col">Stock</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($errorInventario !== ''): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-danger">
                                            <?= escapar($errorInventario) ?>
                                        </td>
                                    </tr>

                                <?php elseif (empty($repuestos)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            No hay repuestos registrados.
                                        </td>
                                    </tr>

                                <?php else: ?>
                                    <?php foreach ($repuestos as $repuesto): ?>
                                        <tr>
                                            <td><?= escapar($repuesto['codigo']) ?></td>

                                            <td><?= escapar($repuesto['nombre']) ?></td>

                                            <td><?= escapar($repuesto['categoria']) ?></td>

                                            <td>
                                                <?= escapar($repuesto['marca'] ?? 'Sin marca específica') ?>
                                            </td>

                                            <td class="text-nowrap">
                                                L. <?= number_format((float) $repuesto['precio'], 2) ?>
                                            </td>

                                            <td><?= (int) $repuesto['stock'] ?></td>

                                            <td class="text-nowrap">
                                                <button
                                                    type="button"
                                                    class="btn btn-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modal-editar-repuesto"

                                                    data-id="<?= (int) $repuesto['id'] ?>"
                                                    data-codigo="<?= escapar($repuesto['codigo']) ?>"
                                                    data-nombre="<?= escapar($repuesto['nombre']) ?>"
                                                    data-descripcion="<?= escapar($repuesto['descripcion'] ?? '') ?>"
                                                    data-especificaciones="<?= escapar($repuesto['especificaciones'] ?? '') ?>"
                                                    data-categoria="<?= (int) $repuesto['categoria_id'] ?>"
                                                    data-marca="<?= escapar($repuesto['marca_id'] ?? '') ?>"
                                                    data-precio="<?= escapar($repuesto['precio']) ?>"
                                                    data-stock="<?= (int) $repuesto['stock'] ?>"
                                                    data-destacado="<?= (int) $repuesto['destacado'] ?>"

                                                    onclick="cargarEdicion(this)"
                                                >
                                                    Editar
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm"
                                                    disabled
                                                >
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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
    <script src="js/inventario.js"></script>
</body>
</html>