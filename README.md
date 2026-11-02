# Autopartes y Repuestos Don Pancho

Sistema web para la venta y administración de autopartes de **Autopartes y Repuestos Don Pancho**, desarrollado en PHP con MySQL. Incluye un catálogo público de repuestos, un armador de kits de mantenimiento (Tune-Up), facturación con ISV y fletes, directorio de sucursales y un área privada con inicio de sesión para el administrador y el encargado de bodega.

---

## Tabla de contenido

1. [Funcionalidades](#funcionalidades)
2. [Tecnologías utilizadas](#tecnologías-utilizadas)
3. [Requisitos](#requisitos)
4. [Estructura del proyecto](#estructura-del-proyecto)
5. [Instalación](#instalación)
6. [Importar la base de datos](#importar-la-base-de-datos)
7. [Credenciales de acceso](#credenciales-de-acceso)
8. [Uso del sistema](#uso-del-sistema)
9. [Seguridad implementada](#seguridad-implementada)
10. [Control de versiones](#control-de-versiones)
11. [Integrantes](#integrantes)

---

## Funcionalidades

### Área pública
| Página | Descripción |
|---|---|
| **Inicio** (`index.php`) | Bienvenida y productos destacados (insignia) de la tienda. |
| **Repuestos** (`repuestos.php`) | Catálogo dinámico cargado desde la base de datos, con filtro por categoría. |
| **Categorías** (`categorias.php`) | Frenos y Suspensión, Motor y Transmisión, Sistema Eléctrico, Fluidos y Lubricantes. |
| **Arma tu Kit** (`kits.php`) | Armado de un kit Tune-Up según el tipo de vehículo (Sedán, SUV, Pick-Up 4x4), aceite, filtros y adicionales. |
| **Sucursales** (`sucursales.php`) | Directorio físico de sucursales en Tegucigalpa, San Pedro Sula y La Ceiba. |
| **Facturación** (`facturacion.php`) | Generación de factura con RTN del cliente, ISV del 15 %, descuento del 10 % por volumen (10 unidades o más) y tipo de entrega (mostrador, moto o camioncito). |

### Área privada (requiere inicio de sesión)
| Rol | Página | Descripción |
|---|---|---|
| Administrador | `admin.php` | Panel administrativo para gestionar el inventario de repuestos. |
| Bodeguero | `bodega.php` | Tabla de inventario e ingreso de nuevos productos con fotografía. |

---

## Tecnologías utilizadas

- **PHP 8** (programación orientada a objetos, `declare(strict_types=1)`)
- **MySQL / MariaDB** con **PDO** y consultas preparadas
- **HTML5, CSS3 y JavaScript**
- **Bootstrap 5.3** y **Bootstrap Icons** (cargados por CDN)
- **Git y GitHub** para el control de versiones

---

## Requisitos

| Requisito | Versión recomendada |
|---|---|
| [XAMPP](https://www.apachefriends.org/es/index.html) (Apache + MySQL/MariaDB + PHP) | 8.0 o superior |
| PHP | 8.0 o superior, con las extensiones `pdo_mysql` y `fileinfo` activas (vienen activas en XAMPP) |
| Navegador web | Chrome, Firefox o Edge actualizados |
| Conexión a internet | Necesaria para cargar Bootstrap desde el CDN |

---

## Estructura del proyecto

```
Autopartes-y-Repuestos-Don-Pancho-/
├── database/
│   └── don_pancho.sql          # Esquema relacional y datos de prueba
├── include/
│   └── conexion.php            # Clase ConexionBD (PDO)
├── src/
│   ├── Backend/
│   │   ├── iniciarsesion.php   # Autenticación de usuarios
│   │   ├── verificarsesion.php # Protección de páginas privadas
│   │   └── cerrarsesion.php    # Cierre de sesión
│   ├── Frontend/
│   │   ├── css/                # Hojas de estilo
│   │   ├── js/                 # Scripts del cliente
│   │   ├── partials/           # Fragmentos reutilizables (menú de sesión)
│   │   ├── index.php
│   │   ├── repuestos.php
│   │   ├── categorias.php
│   │   ├── kits.php
│   │   ├── sucursales.php
│   │   ├── facturacion.php
│   │   ├── login.php
│   │   ├── admin.php
│   │   └── bodega.php
│   ├── imagenes/               # Logo y portada
│   ├── Categoria.php           # Clases del modelo
│   ├── Cliente.php
│   ├── Configuracion.php
│   ├── Factura.php
│   ├── Kit.php
│   ├── Repuesto.php
│   ├── Sucursal.php
│   ├── Usuario.php
│   └── Validador.php
├── uploads/                    # Fotografías de productos (se crea automáticamente)
├── prueba.php                  # Prueba rápida de conexión a la BD
├── .gitignore
└── README.md
```

---

## Instalación

1. **Instalar XAMPP** e iniciar los módulos **Apache** y **MySQL** desde el panel de control.

2. **Copiar el proyecto** dentro de la carpeta `htdocs` de XAMPP:

   - Clonando el repositorio:
     ```bash
     cd C:\xampp\htdocs
     git clone https://github.com/Marifer2922/Autopartes-y-Repuestos-Don-Pancho-.git
     ```
   - O descomprimiendo el archivo `.zip` de la entrega en `C:\xampp\htdocs\`.

   La ruta final debe quedar así: `C:\xampp\htdocs\Autopartes-y-Repuestos-Don-Pancho-\`

3. **Importar la base de datos** siguiendo la sección siguiente.

4. **Verificar la conexión** abriendo en el navegador:
   ```
   http://localhost/Autopartes-y-Repuestos-Don-Pancho-/prueba.php
   ```
   Debe mostrar el mensaje **"Conexion Establecida"**.

5. **Abrir el sistema**:
   ```
   http://localhost/Autopartes-y-Repuestos-Don-Pancho-/src/Frontend/index.php
   ```

---

## Importar la base de datos

El archivo `database/don_pancho.sql` crea la base de datos **`don_pancho_db`** con todas sus tablas y datos de prueba (usuarios, categorías, marcas, repuestos, sucursales, opciones de kits y configuración de facturación).

> **Importante:** el script ejecuta `CREATE DATABASE don_pancho_db;`. Si ya existe una base con ese nombre, elimínela primero o el script dará error.

### Opción A: phpMyAdmin (recomendada)

1. Abrir `http://localhost/phpmyadmin`.
2. Ir a la pestaña **Importar** (sin seleccionar ninguna base de datos).
3. En **Archivo a importar**, seleccionar `database/don_pancho.sql`.
4. Presionar **Importar** (o **Continuar**).
5. Verificar que en el panel izquierdo aparezca la base **`don_pancho_db`** con sus 13 tablas.

### Opción B: línea de comandos

```bash
cd C:\xampp\mysql\bin
mysql -u root < C:\xampp\htdocs\Autopartes-y-Repuestos-Don-Pancho-\database\don_pancho.sql
```

### Configuración de la conexión

Los datos de conexión están en `include/conexion.php` y usan la configuración por defecto de XAMPP:


| Host | `localhost` |
| Base de datos | `don_pancho_db` |
| Usuario | `root` |
| Contraseña | *(vacía)* |

Si su MySQL tiene otra contraseña para `root`, modifique la propiedad `$pass` en ese archivo.

### Tablas principales

| Tabla | Contenido |
|---|---|
| `usuarios`, `roles` | Usuarios del sistema y sus roles (admin, bodeguero). Las contraseñas se guardan cifradas con bcrypt. |
| `repuestos`, `categorias`, `marcas_vehiculo` | Inventario de productos. |
| `tipos_vehiculo`, `kit_opciones` | Datos para el armador de kits Tune-Up. |
| `sucursales` | Directorio de tiendas. |
| `clientes`, `facturas`, `factura_detalle`, `correlativo_factura` | Facturación. |
| `configuracion` | Parámetros del negocio: ISV, descuento por volumen y costos de flete. |

---

## Credenciales de acceso

Para evaluar el área privada, ingrese en:
```
http://localhost/Autopartes-y-Repuestos-Don-Pancho-/src/Frontend/login.php
```

| Rol | Correo | Contraseña | Página a la que redirige |
|---|---|---|---|
| Administrador | `admin@donpancho.hn` | `Admin123!` | `admin.php` |
| Encargado de Bodega | `bodega@donpancho.hn` | `Bodega123!` | `bodega.php` |

> Estas credenciales son solo para fines académicos y de evaluación.

---

## Uso del sistema

1. **Navegar el catálogo** desde el menú superior: Inicio, Repuestos, Categorías, Arma tu Kit, Sucursales y Facturación.
2. **Armar un kit:** elegir el tipo de vehículo, el aceite, los filtros y los adicionales. El sistema calcula los litros de aceite y el total.
3. **Facturar:** ingresar los datos del cliente (nombre, RTN y teléfono), la sucursal, la cantidad y el tipo de entrega. El sistema aplica ISV, descuento por volumen y flete.
4. **Iniciar sesión** con una de las cuentas de la tabla anterior para entrar al panel correspondiente.
5. **Gestionar inventario** (bodega/admin): ingresar productos nuevos con su fotografía y revisar la tabla de existencias.
6. **Cerrar sesión** con el botón amarillo del menú.

---

## Seguridad implementada

- **Contraseñas cifradas** con `password_hash()` / `password_verify()` (bcrypt) y rehash automático.
- **Consultas preparadas con PDO** para prevenir inyección SQL.
- **Control de sesiones:** `session_regenerate_id()` al iniciar sesión, validación de rol en cada página privada y destrucción completa de la sesión y su cookie al salir.
- **Cierre de sesión solo por POST**, para evitar cierres forzados mediante enlaces.
- **Carga segura de imágenes:** solo se aceptan JPG, JPEG, PNG y WEBP; se verifica el tipo MIME real con `finfo`, y cada archivo se guarda con un nombre aleatorio (`random_bytes`).
- **Escape de salida** con `htmlspecialchars()` para prevenir XSS.
- **Errores de conexión registrados** con `error_log()` sin exponer detalles técnicos al usuario.

---

## Control de versiones

El desarrollo se registró mediante **commits progresivos y atómicos** en GitHub:

**Repositorio:** https://github.com/Marifer2922/Autopartes-y-Repuestos-Don-Pancho-

El archivo `.gitignore` excluye del repositorio las fotografías subidas por los usuarios (`uploads/`), archivos de credenciales y entorno (`.env`), carpetas de editores (`.vscode/`, `.idea/`), archivos del sistema operativo, dependencias (`vendor/`, `node_modules/`), archivos temporales y los comprimidos de entrega.

