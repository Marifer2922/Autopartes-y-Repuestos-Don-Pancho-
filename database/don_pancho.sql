CREATE DATABASE don_pancho_db;
USE don_pancho_db;

CREATE TABLE configuracion (
  clave  VARCHAR(50)  NOT NULL PRIMARY KEY,
  valor  VARCHAR(100) NOT NULL,
  descripcion VARCHAR(200)
);

CREATE TABLE roles (
  id  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE usuarios (
  id  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre   VARCHAR(100) NOT NULL,
  correo VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  rol_id INT NOT NULL,
  activo  INT NOT NULL DEFAULT 1,
  FOREIGN KEY (rol_id) REFERENCES roles(id)
);

CREATE TABLE categorias (
  id  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre  VARCHAR(60) NOT NULL UNIQUE,
  descripcion VARCHAR(200)
);

CREATE TABLE marcas_vehiculo (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(40) NOT NULL UNIQUE
);

CREATE TABLE repuestos (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30)  NOT NULL UNIQUE,
  nombre  VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255),
  especificaciones VARCHAR(255),
  categoria_id     INT NOT NULL,
  marca_id INT,
  precio DECIMAL(10,2) NOT NULL,
  stock  INT NOT NULL DEFAULT 0,
  fotografia VARCHAR(255),
  destacado  INT NOT NULL DEFAULT 0,
  activo INT NOT NULL DEFAULT 1,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id),
  FOREIGN KEY (marca_id) REFERENCES marcas_vehiculo(id) ON DELETE SET NULL
);

CREATE TABLE tipos_vehiculo (
  id   INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(30) NOT NULL UNIQUE,
  litros_aceite DECIMAL(4,1) NOT NULL
);

CREATE TABLE kit_opciones (
  id  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(20) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  marca VARCHAR(40),
  viscosidad VARCHAR(15),
  precio DECIMAL(10,2) NOT NULL,
  activo INT NOT NULL DEFAULT 1
);

CREATE TABLE sucursales (
  id   INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL,
  ciudad  VARCHAR(40) NOT NULL,
  direccion VARCHAR(200) NOT NULL,
  telefono  VARCHAR(20),
  horario VARCHAR(100)
);

CREATE TABLE clientes (
  id  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre  VARCHAR(120) NOT NULL,
  rtn  VARCHAR(14) NOT NULL UNIQUE,
  telefono  VARCHAR(8) NOT NULL,
  correo VARCHAR(120),
  direccion VARCHAR(200)
);

CREATE TABLE correlativo_factura (
  id   INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  prefijo VARCHAR(10) NOT NULL,
  siguiente INT NOT NULL DEFAULT 1,
  rango_final  INT NOT NULL,
  cai     VARCHAR(50),
  fecha_limite DATE
);

CREATE TABLE facturas (
  id   INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  numero_factura VARCHAR(19) NOT NULL UNIQUE,
  cliente_id  INT NOT NULL,
  sucursal_id INT NOT NULL,
  fecha  DATETIME NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  descuento  DECIMAL(12,2) NOT NULL DEFAULT 0,
  isv  DECIMAL(12,2) NOT NULL,
  flete_tipo  VARCHAR(20) NOT NULL DEFAULT 'mostrador',
  flete_monto  DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
);

CREATE TABLE factura_detalle (
  id    INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  factura_id INT NOT NULL,
  repuesto_id INT,
  descripcion VARCHAR(150) NOT NULL,
  cantidad  INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal  DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE CASCADE,
  FOREIGN KEY (repuesto_id) REFERENCES repuestos(id) ON DELETE SET NULL
);

INSERT INTO configuracion (clave, valor, descripcion) VALUES
  ('isv_porcentaje',  '0.15',  'Impuesto sobre ventas (ISV) 15 %'),
  ('descuento_porcentaje',  '0.10',  'Descuento automatico por volumen'),
  ('descuento_min_unidades','10','Unidades minimas para aplicar el descuento'),
  ('flete_mostrador', '0.00',  'Retiro en mostrador (L.)'),
  ('flete_moto', '50.00', 'Moto express (L.)'),
  ('flete_camioncito','180.00','Flete en camioncito (L.)');

INSERT INTO roles (id, nombre) VALUES
  (1, 'admin'),
  (2, 'bodeguero');

INSERT INTO usuarios (nombre, correo, password_hash, rol_id) VALUES
  ('Administrador Don Pancho', 'admin@donpancho.hn','$2y$10$b.GTRmF82R.t9N4jTS7bSudHg0NhmVt7CsG/qrk8SBwu/hdVLJzCC', 1),
  ('Encargado de Bodega',   'bodega@donpancho.hn','$2y$10$CbaA.iQ9hvooZk5RX3GIvuQVG/x3icv.4YBIzqobBzTqQE2ZngeV2', 2);

INSERT INTO categorias (id, nombre, descripcion) VALUES
  (1, 'Frenos y Suspensión','Pastillas, discos, amortiguadores y rótulas'),
  (2, 'Motor y Transmisión','Bujías, correas, filtros y embragues'),
  (3, 'Sistema Eléctrico','Baterías, alternadores, bobinas y sensores'),
  (4, 'Fluidos y Lubricantes','Aceites, refrigerantes y líquidos');

INSERT INTO marcas_vehiculo (id, nombre) VALUES
  (1, 'Toyota'), (2, 'Nissan'), (3, 'Honda'),
  (4, 'Hyundai'), (5, 'Isuzu'), (6, 'Ford');

INSERT INTO repuestos
  (codigo, nombre, descripcion, especificaciones, categoria_id, marca_id, precio, stock, destacado) VALUES
  ('FRE-001', 'Pastillas de freno cerámicas delanteras', 'Juego de 4 pastillas, baja emisión de polvo', 'Compatible Toyota Corolla 2009-2019', 1, 1, 685.00, 40, 1),
  ('MOT-001', 'Bujías de iridio (juego de 4)','Mayor vida útil y mejor ahorro de combustible', 'Rosca 14 mm, separación 1.1 mm', 2, NULL, 920.00, 55, 1),
  ('ELE-001', 'Batería 12V 65Ah', 'Libre de mantenimiento, 18 meses de garantía', '12V / 650 CCA', 3, NULL, 2850.00, 18, 1),
  ('FLU-001', 'Aceite sintético 5W-30 (galón)','Aceite 100 % sintético para motores a gasolina', 'API SN, ILSAC GF-5', 4, NULL, 780.00, 90, 1),
  ('SUS-001', 'Amortiguador delantero','Amortiguador a gas, unidad', 'Compatible Nissan Sentra B15/B16', 1, 2, 1450.00, 22, 1),

  ('FRE-002', 'Discos de freno ventilados (par)','Acero de alta resistencia al calor', 'Diámetro 280 mm', 1, 3, 1980.00, 14, 0),
  ('FRE-003', 'Pastillas de freno traseras','Juego de 4 pastillas semimetálicas', 'Compatible Honda Civic 2006-2015', 1, 3, 540.00, 33, 0),
  ('SUS-002', 'Rótula inferior','Rótula de suspensión, unidad', 'Compatible Hyundai Accent 2012-2018', 1, 4, 390.00, 25, 0),
  ('SUS-003', 'Terminal de dirección','Terminal exterior, unidad', 'Compatible Isuzu D-Max', 1, 5, 450.00, 20, 0),
  ('SUS-004', 'Barra estabilizadora (goma)','Par de gomas de barra estabilizadora', 'Compatible Ford Ranger 2012-2022', 1, 6, 260.00, 30, 0),

  ('MOT-002', 'Kit de correa de distribución',  'Correa, tensor y polea', 'Compatible Toyota Hilux 2.7L', 2, 1, 2350.00, 9, 0),
  ('MOT-003', 'Filtro de aceite', 'Filtro de flujo completo', 'Rosca 3/4-16 UNF', 2, NULL, 145.00, 120, 0),
  ('MOT-004', 'Filtro de aire de motor','Elemento de papel de alta eficiencia', 'Compatible Nissan Frontier', 2, 2, 310.00, 45, 0),
  ('MOT-005', 'Embrague completo (kit)','Disco, plato y collarín', 'Compatible Isuzu D-Max 3.0L', 2, 5, 4200.00, 6, 0),
  ('MOT-006', 'Bomba de agua', 'Bomba con empaque incluido', 'Compatible Hyundai Elantra 1.8L', 2, 4, 980.00, 12, 0),

  ('ELE-002', 'Alternador reconstruido 90A','Alternador probado en banco, 6 meses de garantía', '12V / 90A', 3, 1, 3600.00, 7, 0),
  ('ELE-003', 'Bobina de encendido', 'Bobina individual, unidad', 'Compatible Honda Accord 2.4L', 3, 3, 760.00, 28, 0),
  ('ELE-004', 'Sensor de oxígeno','Sensor aguas arriba, 4 cables', 'Compatible Ford Escape 2.5L', 3, 6, 1250.00, 15, 0),

  ('FLU-002', 'Líquido de frenos DOT 4 (500 ml)','Alto punto de ebullición', 'DOT 4', 4, NULL, 165.00, 70, 0),
  ('FLU-003', 'Refrigerante concentrado (galón)', 'Larga duración, color verde', 'Mezclar 50/50 con agua', 4, NULL, 340.00, 60, 0),
  ('FLU-004', 'Aceite de transmisión ATF (cuarto)','Fluido para transmisión automática', 'Dexron VI', 4, NULL, 195.00, 48, 0);

INSERT INTO tipos_vehiculo (id, nombre, litros_aceite) VALUES
  (1, 'Sedán', 4.0),
  (2, 'SUV',5.5),
  (3, 'Pick-Up 4x4',7.0);

INSERT INTO kit_opciones (tipo, nombre, marca, viscosidad, precio) VALUES
  ('aceite','Aceite sintético','Mobil 1','5W-30',210.00),
  ('aceite','Aceite sintético','Castrol', '0W-20', 225.00),
  ('aceite', 'Aceite semisintético','Valvoline', '10W-40', 165.00),
  ('aceite','Aceite mineral', 'Quaker State', '20W-50', 120.00),

  ('filtro', 'Filtro de aceite', NULL, NULL, 145.00),
  ('filtro','Filtro de aire', NULL, NULL, 310.00),
  ('filtro','Filtro de combustible', NULL, NULL, 420.00),
  ('filtro','Filtro de cabina', NULL, NULL, 260.00),

  ('adicional','Bujías de iridio (juego de 4)',NULL, NULL, 920.00),
  ('adicional','Pastillas cerámicas delanteras',  NULL, NULL, 685.00),
  ('adicional','Limpiador de inyectores', NULL, NULL, 180.00),
  ('adicional','Líquido de frenos DOT 4',NULL, NULL, 165.00),
  ('adicional','Refrigerante (galón)',  NULL, NULL, 340.00);

INSERT INTO sucursales (nombre, ciudad, direccion, telefono, horario) VALUES
  ('Sucursal Toncontín', 'Tegucigalpa',   'Zona Toncontín, Tegucigalpa, F.M.', '2200-0001', 'Lun-Sáb 8:00 am - 5:30 pm'),
  ('Sucursal Suyapa', 'Tegucigalpa','Colonia Suyapa, Tegucigalpa, F.M.', '2200-0002', 'Lun-Sáb 8:00 am - 5:30 pm'),
  ('Sucursal Medina','San Pedro Sula','Zona Medina, San Pedro Sula, Cortés','2500-0003', 'Lun-Sáb 8:00 am - 5:30 pm'),
  ('Sucursal CA-13','La Ceiba', 'Carretera CA-13, La Ceiba, Atlántida','2400-0004', 'Lun-Sáb 8:00 am - 5:00 pm');

INSERT INTO correlativo_factura (prefijo, siguiente, rango_final, cai, fecha_limite) VALUES
  ('000-001-01', 1, 99999999, NULL, NULL);

INSERT INTO clientes (nombre, rtn, telefono, correo) VALUES
  ('Taller Mecánico de Prueba', '08019999123456', '99990000', 'taller@ejemplo.hn');