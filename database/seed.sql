-- ================================================
-- DATOS DE PRUEBA - JOYERÍA TORRE FUERTE
-- ================================================
-- Este archivo carga datos realistas para desarrollo
-- IMPORTANTE: Ejecutar DESPUÉS de schema.sql
-- ================================================

USE joyeria_torre_fuerte;

-- Deshabilitar verificaciones temporalmente para insertar datos
SET FOREIGN_KEY_CHECKS = 0;

-- ================================================
-- LIMPIAR TABLAS EXISTENTES
-- ================================================
DELETE FROM abonos_creditos;
DELETE FROM creditos_clientes;
DELETE FROM facturas;
DELETE FROM formas_pago_venta;
DELETE FROM detalle_ventas;
DELETE FROM ventas;
DELETE FROM materiales_trabajo;
DELETE FROM transferencias_trabajo;
DELETE FROM trabajos_taller;
DELETE FROM movimientos_caja;
DELETE FROM cajas;
DELETE FROM detalle_transferencias_inventario;
DELETE FROM transferencias_inventario;
DELETE FROM movimientos_inventario;
DELETE FROM inventario;
DELETE FROM precios_producto;
DELETE FROM productos;
DELETE FROM materias_primas;
DELETE FROM clientes;
DELETE FROM categorias;
DELETE FROM proveedores;
DELETE FROM audit_log;
DELETE FROM configuracion_sistema;
DELETE FROM usuarios;
DELETE FROM sucursales;

-- Resetear auto_increment
ALTER TABLE sucursales AUTO_INCREMENT = 1;
ALTER TABLE usuarios AUTO_INCREMENT = 1;
ALTER TABLE categorias AUTO_INCREMENT = 1;
ALTER TABLE proveedores AUTO_INCREMENT = 1;
ALTER TABLE productos AUTO_INCREMENT = 1;
ALTER TABLE clientes AUTO_INCREMENT = 1;
ALTER TABLE materias_primas AUTO_INCREMENT = 1;
ALTER TABLE configuracion_sistema AUTO_INCREMENT = 1;

-- ================================================
-- 1. SUCURSALES (2 sucursales)
-- ================================================
INSERT INTO sucursales (id, nombre, direccion, telefono, email, activo) VALUES
(1, 'Joyería Torre Fuerte - Los Arcos', 'Centro Comercial Los Arcos, Local 25, Zona 4, Huehuetenango', '7765-1234', 'losarcos@torrefuerte.com', 1),
(2, 'Joyería Torre Fuerte - Chinaca Central', 'Calzada La Paz 12-45, Zona 1, Huehuetenango', '7765-5678', 'chinaca@torrefuerte.com', 1);

-- ================================================
-- 2. USUARIOS (6 personas con diferentes roles)
-- ================================================
-- Password para todos: "123456" (hash bcrypt)
INSERT INTO usuarios (id, nombre, email, password, rol, sucursal_id, activo) VALUES
(1, 'Carlos Méndez', 'admin@torrefuerte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', NULL, 1),
(2, 'María García', 'duena@torrefuerte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dueño', NULL, 1),
(3, 'Juan Pérez', 'vendedor1@torrefuerte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor', 1, 1),
(4, 'Ana López', 'cajera1@torrefuerte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cajero', 1, 1),
(5, 'Roberto Martínez', 'orfebre1@torrefuerte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'orfebre', 1, 1),
(6, 'Laura Hernández', 'publicidad@torrefuerte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'publicidad', NULL, 1);

-- Actualizar responsables de sucursales
UPDATE sucursales SET responsable_id = 3 WHERE id = 1;
UPDATE sucursales SET responsable_id = 3 WHERE id = 2;

-- ================================================
-- 3. CATEGORÍAS (organizadas por tipo)
-- ================================================
INSERT INTO categorias (id, nombre, descripcion, tipo_clasificacion, categoria_padre_id, activo) VALUES
-- Categorías por TIPO
(1, 'Anillos', 'Anillos de diversos estilos', 'tipo', NULL, 1),
(2, 'Aretes', 'Aretes y pendientes', 'tipo', NULL, 1),
(3, 'Collares', 'Collares y cadenas', 'tipo', NULL, 1),
(4, 'Pulseras', 'Pulseras y brazaletes', 'tipo', NULL, 1),
(5, 'Relojes', 'Relojes de pulsera', 'tipo', NULL, 1),
(6, 'Exhibidores', 'Material de exhibición', 'tipo', NULL, 1),
(7, 'Estuches', 'Cajas y estuches para joyería', 'tipo', NULL, 1),

-- Categorías por MATERIAL
(8, 'Oro 18K', 'Productos en oro de 18 kilates', 'material', NULL, 1),
(9, 'Oro 14K', 'Productos en oro de 14 kilates', 'material', NULL, 1),
(10, 'Plata 925', 'Productos en plata ley 925', 'material', NULL, 1),
(11, 'Acero Inoxidable', 'Productos en acero quirúrgico', 'material', NULL, 1),
(12, 'Fantasía', 'Bisutería fina', 'material', NULL, 1);

-- ================================================
-- 4. PROVEEDORES
-- ================================================
INSERT INTO proveedores (id, nombre, empresa, telefono, email, direccion, productos_suministra, activo) VALUES
(1, 'José Ramírez', 'Distribuidora El Dorado', '7765-9876', 'eldorado@gmail.com', 'Zona 3, Guatemala', 'Oro 18K, Oro 14K', 1),
(2, 'Patricia Morales', 'Platería San Miguel', '7766-5432', 'plateria@hotmail.com', 'Quetzaltenango', 'Plata 925, Acero', 1),
(3, 'Ricardo Flores', 'Exhibidores Modernos', '7767-1111', 'exhibidores@gmail.com', 'Ciudad de Guatemala', 'Exhibidores, Estuches', 1),
(4, 'Carmen Soto', 'Joyería al Por Mayor', '7768-2222', 'mayorista@yahoo.com', 'Zona 1, Guatemala', 'Relojes, Fantasía', 1);

-- ================================================
-- 5. PRODUCTOS (25 productos variados)
-- ================================================
INSERT INTO productos (codigo, codigo_barras, nombre, descripcion, categoria_id, proveedor_id, es_por_peso, peso_gramos, estilo, imagen, activo) VALUES
-- ANILLOS
('AN001', '7501234560001', 'Anillo Compromiso Solitario', 'Anillo de compromiso con diamante', 1, 1, 0, NULL, 'Clásico', NULL, 1),
('AN002', '7501234560002', 'Anillo Oro 18K Liso', 'Anillo matrimonial liso', 1, 1, 1, 4.5, 'Tradicional', NULL, 1),
('AN003', '7501234560003', 'Anillo Plata con Circón', 'Anillo de plata con piedra', 1, 2, 0, NULL, 'Moderno', NULL, 1),
('AN004', '7501234560004', 'Anillo Acero Inoxidable', 'Anillo casual acero', 1, 2, 0, NULL, 'Casual', NULL, 1),

-- ARETES
('AR001', '7501234560011', 'Aretes Oro 18K Perla', 'Aretes con perla cultivada', 2, 1, 1, 3.2, 'Elegante', NULL, 1),
('AR002', '7501234560012', 'Aretes Plata 925 Largos', 'Aretes colgantes de plata', 2, 2, 0, NULL, 'Moderno', NULL, 1),
('AR003', '7501234560013', 'Aretes Oro 14K Pequeños', 'Aretes diarios oro', 2, 1, 1, 2.1, 'Clásico', NULL, 1),
('AR004', '7501234560014', 'Aretes Fantasía Colores', 'Aretes bisutería fina', 2, 4, 0, NULL, 'Casual', NULL, 1),

-- COLLARES
('CO001', '7501234560021', 'Collar Oro 18K 45cm', 'Cadena oro maciza', 3, 1, 1, 15.5, '45cm', NULL, 1),
('CO002', '7501234560022', 'Collar Plata con Dije', 'Collar plata con corazón', 3, 2, 0, NULL, '40cm', NULL, 1),
('CO003', '7501234560023', 'Collar Acero con Cruz', 'Collar religioso acero', 3, 2, 0, NULL, '50cm', NULL, 1),
('CO004', '7501234560024', 'Collar Fantasía Moda', 'Collar tendencia actual', 3, 4, 0, NULL, '35cm', NULL, 1),

-- PULSERAS
('PU001', '7501234560031', 'Pulsera Oro 18K Sólida', 'Pulsera oro maciza 18K', 4, 1, 1, 12.8, '18cm', NULL, 1),
('PU002', '7501234560032', 'Pulsera Plata con Dijes', 'Pulsera tipo pandora', 4, 2, 0, NULL, '19cm', NULL, 1),
('PU003', '7501234560033', 'Pulsera Acero Magnética', 'Pulsera terapéutica', 4, 2, 0, NULL, '20cm', NULL, 1),
('PU004', '7501234560034', 'Pulsera Fantasía Perlas', 'Pulsera con perlas sintéticas', 4, 4, 0, NULL, '18cm', NULL, 1),

-- RELOJES
('RE001', '7501234560041', 'Reloj Casio Digital', 'Reloj digital resistente agua', 5, 4, 0, NULL, 'Deportivo', NULL, 1),
('RE002', '7501234560042', 'Reloj Seiko Automático', 'Reloj mecánico japonés', 5, 4, 0, NULL, 'Elegante', NULL, 1),
('RE003', '7501234560043', 'Reloj Citizen Eco-Drive', 'Reloj solar Citizen', 5, 4, 0, NULL, 'Clásico', NULL, 1),
('RE004', '7501234560044', 'Reloj Timex Análogo', 'Reloj clásico análogo', 5, 4, 0, NULL, 'Tradicional', NULL, 1),

-- EXHIBIDORES Y ESTUCHES
('EX001', '7501234560051', 'Exhibidor Anillos Terciopelo', 'Base para 20 anillos', 6, 3, 0, NULL, 'Grande', NULL, 1),
('EX002', '7501234560052', 'Exhibidor Collares Vertical', 'Torre para 6 collares', 6, 3, 0, NULL, 'Mediano', NULL, 1),
('EX003', '7501234560053', 'Estuche Individual Lujo', 'Caja regalo individual', 7, 3, 0, NULL, 'Premium', NULL, 1),
('EX004', '7501234560054', 'Estuche Anillo Terciopelo', 'Cajita para anillo', 7, 3, 0, NULL, 'Estándar', NULL, 1),
('EX005', '7501234560055', 'Exhibidor Pulseras Acrílico', 'Soporte para 10 pulseras', 6, 3, 0, NULL, 'Pequeño', NULL, 1);

-- ================================================
-- 6. PRECIOS DE PRODUCTOS (4 precios por producto)
-- ================================================
-- Insertando precios para los 25 productos
INSERT INTO precios_producto (producto_id, tipo_precio, precio) VALUES
-- Producto 1-4 (Anillos)
(1, 'publico', 4500.00), (1, 'mayorista', 4200.00), (1, 'descuento', 4000.00), (1, 'especial', 3800.00),
(2, 'publico', 350.00), (2, 'mayorista', 340.00), (2, 'descuento', 330.00), (2, 'especial', 320.00),
(3, 'publico', 850.00), (3, 'mayorista', 800.00), (3, 'descuento', 750.00), (3, 'especial', 700.00),
(4, 'publico', 250.00), (4, 'mayorista', 220.00), (4, 'descuento', 200.00), (4, 'especial', 180.00),

-- Producto 5-8 (Aretes)
(5, 'publico', 350.00), (5, 'mayorista', 340.00), (5, 'descuento', 330.00), (5, 'especial', 320.00),
(6, 'publico', 650.00), (6, 'mayorista', 600.00), (6, 'descuento', 550.00), (6, 'especial', 500.00),
(7, 'publico', 320.00), (7, 'mayorista', 310.00), (7, 'descuento', 300.00), (7, 'especial', 290.00),
(8, 'publico', 180.00), (8, 'mayorista', 160.00), (8, 'descuento', 140.00), (8, 'especial', 120.00),

-- Producto 9-12 (Collares)
(9, 'publico', 350.00), (9, 'mayorista', 340.00), (9, 'descuento', 330.00), (9, 'especial', 320.00),
(10, 'publico', 950.00), (10, 'mayorista', 900.00), (10, 'descuento', 850.00), (10, 'especial', 800.00),
(11, 'publico', 320.00), (11, 'mayorista', 280.00), (11, 'descuento', 250.00), (11, 'especial', 220.00),
(12, 'publico', 220.00), (12, 'mayorista', 200.00), (12, 'descuento', 180.00), (12, 'especial', 160.00),

-- Producto 13-16 (Pulseras)
(13, 'publico', 350.00), (13, 'mayorista', 340.00), (13, 'descuento', 330.00), (13, 'especial', 320.00),
(14, 'publico', 1200.00), (14, 'mayorista', 1100.00), (14, 'descuento', 1000.00), (14, 'especial', 900.00),
(15, 'publico', 450.00), (15, 'mayorista', 400.00), (15, 'descuento', 350.00), (15, 'especial', 300.00),
(16, 'publico', 280.00), (16, 'mayorista', 250.00), (16, 'descuento', 220.00), (16, 'especial', 200.00),

-- Producto 17-20 (Relojes)
(17, 'publico', 650.00), (17, 'mayorista', 600.00), (17, 'descuento', 550.00), (17, 'especial', 500.00),
(18, 'publico', 3500.00), (18, 'mayorista', 3200.00), (18, 'descuento', 3000.00), (18, 'especial', 2800.00),
(19, 'publico', 2800.00), (19, 'mayorista', 2600.00), (19, 'descuento', 2400.00), (19, 'especial', 2200.00),
(20, 'publico', 850.00), (20, 'mayorista', 800.00), (20, 'descuento', 750.00), (20, 'especial', 700.00),

-- Producto 21-25 (Exhibidores/Estuches)
(21, 'publico', 350.00), (21, 'mayorista', 300.00), (21, 'descuento', 280.00), (21, 'especial', 250.00),
(22, 'publico', 450.00), (22, 'mayorista', 400.00), (22, 'descuento', 380.00), (22, 'especial', 350.00),
(23, 'publico', 45.00), (23, 'mayorista', 40.00), (23, 'descuento', 35.00), (23, 'especial', 30.00),
(24, 'publico', 25.00), (24, 'mayorista', 22.00), (24, 'descuento', 20.00), (24, 'especial', 18.00),
(25, 'publico', 280.00), (25, 'mayorista', 250.00), (25, 'descuento', 230.00), (25, 'especial', 200.00);

-- ================================================
-- 7. INVENTARIO (productos en ambas sucursales)
-- ================================================
-- Sucursal 1 - Los Arcos
INSERT INTO inventario (producto_id, sucursal_id, cantidad, stock_minimo, es_compartido) VALUES
(1, 1, 15, 5, 0), (2, 1, 8, 3, 0), (3, 1, 25, 5, 0), (4, 1, 30, 10, 0), (5, 1, 12, 5, 0),
(6, 1, 20, 5, 0), (7, 1, 10, 3, 0), (8, 1, 35, 10, 0), (9, 1, 6, 2, 0), (10, 1, 18, 5, 0),
(11, 1, 22, 5, 0), (12, 1, 40, 10, 0), (13, 1, 5, 2, 0), (14, 1, 15, 5, 0), (15, 1, 25, 5, 0),
(16, 1, 30, 10, 0), (17, 1, 12, 5, 0), (18, 1, 4, 2, 0), (19, 1, 6, 2, 0), (20, 1, 10, 3, 0),
(21, 1, 50, 10, 1), (22, 1, 30, 10, 1), (23, 1, 100, 20, 1), (24, 1, 150, 30, 1), (25, 1, 40, 10, 1);

-- Sucursal 2 - Chinaca Central
INSERT INTO inventario (producto_id, sucursal_id, cantidad, stock_minimo, es_compartido) VALUES
(1, 2, 12, 5, 0), (2, 2, 6, 3, 0), (3, 2, 20, 5, 0), (4, 2, 28, 10, 0), (5, 2, 10, 5, 0),
(6, 2, 18, 5, 0), (7, 2, 8, 3, 0), (8, 2, 32, 10, 0), (9, 2, 5, 2, 0), (10, 2, 16, 5, 0),
(11, 2, 20, 5, 0), (12, 2, 38, 10, 0), (13, 2, 4, 2, 0), (14, 2, 13, 5, 0), (15, 2, 22, 5, 0),
(16, 2, 28, 10, 0), (17, 2, 10, 5, 0), (18, 2, 3, 2, 0), (19, 2, 5, 2, 0), (20, 2, 8, 3, 0),
(21, 2, 50, 10, 1), (22, 2, 30, 10, 1), (23, 2, 100, 20, 1), (24, 2, 150, 30, 1), (25, 2, 40, 10, 1);

-- ================================================
-- 8. CLIENTES (20 clientes variados)
-- ================================================
INSERT INTO clientes (nombre, nit, telefono, email, direccion, tipo_cliente, tipo_mercaderias, limite_credito, activo) VALUES
('María González', '12345678-9', '5512-3456', 'maria.g@gmail.com', 'Zona 2, Huehuetenango', 'publico', 'ambas', NULL, 1),
('Pedro Ramírez', '23456789-0', '5523-4567', 'pedro.r@hotmail.com', 'Zona 3, Huehuetenango', 'mayorista', 'oro', 15000.00, 1),
('Ana Martínez', '34567890-1', '5534-5678', NULL, 'Zona 1, Huehuetenango', 'publico', 'plata', NULL, 1),
('Carlos López', '45678901-2', '5545-6789', 'carlos.l@yahoo.com', 'Malacatancito', 'mayorista', 'ambas', 20000.00, 1),
('Laura Hernández', '56789012-3', '5556-7890', 'laura.h@gmail.com', 'Zona 5, Huehuetenango', 'publico', 'ambas', NULL, 1),
('Roberto García', '67890123-4', '5567-8901', NULL, 'Chiantla', 'publico', 'oro', NULL, 1),
('Carmen Pérez', '78901234-5', '5578-9012', 'carmen.p@hotmail.com', 'Zona 4, Huehuetenango', 'mayorista', 'plata', 12000.00, 1),
('Diego Morales', '89012345-6', '5589-0123', 'diego.m@gmail.com', 'Cuilco', 'publico', 'ambas', NULL, 1),
('Elena Soto', '90123456-7', '5590-1234', NULL, 'Zona 6, Huehuetenango', 'publico', 'plata', NULL, 1),
('Fernando Ruiz', '01234567-8', '5501-2345', 'fernando.r@yahoo.com', 'La Libertad', 'mayorista', 'oro', 18000.00, 1),
('Gloria Torres', '11234567-9', '5511-2346', 'gloria.t@gmail.com', 'Zona 7, Huehuetenango', 'publico', 'ambas', NULL, 1),
('Héctor Flores', '21234567-0', '5521-2347', NULL, 'Malacatán', 'publico', 'oro', NULL, 1),
('Isabel Vargas', '31234567-1', '5531-2348', 'isabel.v@hotmail.com', 'Zona 8, Huehuetenango', 'mayorista', 'ambas', 10000.00, 1),
('Jorge Castro', '41234567-2', '5541-2349', 'jorge.c@gmail.com', 'San Pedro Necta', 'publico', 'plata', NULL, 1),
('Karla Mendoza', '51234567-3', '5551-2350', NULL, 'Zona 9, Huehuetenango', 'publico', 'ambas', NULL, 1),
('Luis Ortiz', '61234567-4', '5561-2351', 'luis.o@yahoo.com', 'Todos Santos', 'mayorista', 'oro', 16000.00, 1),
('Mónica Silva', '71234567-5', '5571-2352', 'monica.s@gmail.com', 'Zona 10, Huehuetenango', 'publico', 'plata', NULL, 1),
('Nicolás Romero', '81234567-6', '5581-2353', NULL, 'San Juan Atitán', 'publico', 'ambas', NULL, 1),
('Olivia Campos', '91234567-7', '5591-2354', 'olivia.c@hotmail.com', 'Zona 11, Huehuetenango', 'mayorista', 'ambas', 14000.00, 1),
('Pablo Navarro', '10234567-8', '5510-2355', 'pablo.n@gmail.com', 'Colotenango', 'publico', 'oro', NULL, 1);

-- ================================================
-- 9. MATERIAS PRIMAS (para el taller) - CAMPOS CORREGIDOS
-- ================================================
INSERT INTO materias_primas (nombre, tipo, unidad_medida, cantidad_disponible, stock_minimo, precio_por_unidad, activo) VALUES
('Oro 18K', 'oro', 'gramos', 500.00, 100.00, 350.00, 1),
('Oro 14K', 'oro', 'gramos', 300.00, 80.00, 320.00, 1),
('Plata 925', 'plata', 'gramos', 1000.00, 200.00, 8.50, 1),
('Soldadura Oro', 'oro', 'gramos', 50.00, 10.00, 380.00, 1),
('Soldadura Plata', 'plata', 'gramos', 100.00, 20.00, 12.00, 1),
('Piedras Circonio', 'piedra', 'piezas', 200.00, 50.00, 15.00, 1),
('Piedras Rubí', 'piedra', 'quilates', 50.00, 10.00, 250.00, 1),
('Piedras Esmeralda', 'piedra', 'quilates', 30.00, 8.00, 300.00, 1),
('Perlas Cultivadas', 'otro', 'piezas', 150.00, 30.00, 25.00, 1),
('Broches Oro', 'oro', 'piezas', 50.00, 10.00, 80.00, 1);

-- ================================================
-- 10. CONFIGURACIÓN DEL SISTEMA
-- ================================================
INSERT INTO configuracion_sistema (clave, valor, tipo, descripcion) VALUES
('nombre_negocio', 'Joyería Torre Fuerte', 'string', 'Nombre del negocio'),
('logo_ruta', 'assets/img/logo-torre-fuerte.png', 'string', 'Ruta del logo'),
('mensaje_ticket', 'Gracias por su compra. Vuelva pronto!', 'string', 'Mensaje en tickets'),
('stock_minimo_default', '5', 'number', 'Stock mínimo por defecto'),
('dias_alerta_entrega', '3', 'number', 'Días antes de alertar entrega taller'),
('iva_porcentaje', '12', 'number', 'Porcentaje de IVA'),
('moneda', 'Q', 'string', 'Símbolo de moneda'),
('formato_fecha', 'd/m/Y', 'string', 'Formato de fecha'),
('credito_interes_mensual', '0', 'number', 'Interés mensual en créditos (0 = sin interés)'),
('mantenimiento', 'false', 'boolean', 'Modo mantenimiento');

-- ================================================
-- Restaurar verificaciones
-- ================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ================================================
-- RESUMEN DE DATOS CARGADOS
-- ================================================
SELECT '✅ DATOS DE PRUEBA CARGADOS EXITOSAMENTE!' AS estado;
SELECT '================================================' AS separador;
SELECT 'Sucursales' AS tabla, COUNT(*) AS registros FROM sucursales
UNION ALL SELECT 'Usuarios', COUNT(*) FROM usuarios
UNION ALL SELECT 'Categorías', COUNT(*) FROM categorias
UNION ALL SELECT 'Proveedores', COUNT(*) FROM proveedores
UNION ALL SELECT 'Productos', COUNT(*) FROM productos
UNION ALL SELECT 'Precios', COUNT(*) FROM precios_producto
UNION ALL SELECT 'Inventario', COUNT(*) FROM inventario
UNION ALL SELECT 'Clientes', COUNT(*) FROM clientes
UNION ALL SELECT 'Materias Primas', COUNT(*) FROM materias_primas
UNION ALL SELECT 'Configuración', COUNT(*) FROM configuracion_sistema;