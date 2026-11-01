<?php
    declare(strict_types=1);

    require_once __DIR__ . '/../Backend/verificarsesion.php';
    require_once __DIR__ . '/../../include/conexion.php';
    require_once __DIR__ . '/../Repuesto.php';

    $conexion = new ConexionBD();
    $pdo = $conexion->conectar();

    if ($_SESSION['usuario_rol'] !== 'bodeguero') {
    header('Location: admin.php');
    exit;
    }

    //Carga segura de archivos
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

        <!--Mensaje de estado de ingreso de producto-->
            <?php if($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <?php if($estadoSubida): ?>
                    <div class="container py-4">
                        <div class="badge rounded-pill bg-success">
                            <h5 class="m-2"><i class="bi bi-check2-circle"></i> Excito al ingresar el producto.</h5>
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
            <?php endif; ?>

        <!--Boton para ingresar nuevos productos-->
            <section class="py-4">
                <div class="container">
                    <button type="button" class="btn btn-warning fw-bold" data-bs-toggle="modal" data-bs-target="#modal-nuevo-repuesto">
                        Ingrese nuevo producto
                    </button>
                </div>
            </section>

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
                                    <input type="text" class="form-control" name="precioProducto" id="input-precio-producto" required>
                                </div>
                                <div class="p-2">
                                    <label class="form-label" for="input-stock-producto">Cantidad en bodega: </label>
                                    <input type="number" class="form-control" name="stockProducto" id="input-stock-producto" min=0 required>
                                </div>
                                <div class="p-2">
                                    <label class="form-label" for="input-imagen-producto">Imagen del repuesto: </label>
                                    <input type="file" class="form-control" name="imagenProducto" id="input-imagen-producto">
                                </div>
                                <div class="p-2">
                                    <label class="form-label" for="input-destacado-producto">Destacado: </label>
                                    <select class="form-select" name="destacadoProducto" id="input-destacado-producto" required>
                                        <option value="1">Si</option>
                                        <option value="2">No</option>
                                    </select>
                                </div>
                                <div class="p-2 d-flex justify-content-center">
                                    <button type="submit" class="btn btn-warning">Ingresar nuevo producto</button>
                                </div>
                            </form>
                        </div>
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