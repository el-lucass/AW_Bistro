
-- Para la funcionalidad 0
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('cliente', 'camarero', 'cocinero', 'gerente') DEFAULT 'cliente',
    avatar VARCHAR(255) DEFAULT 'default.png',
    bistrocoins INT DEFAULT 0
);

-- Para la funcionalidad 1
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    id_categoria INT NOT NULL,
    precio_base DECIMAL(10, 2) NOT NULL, -- Precio sin IVA
    iva INT NOT NULL CHECK (iva IN (4, 10, 21)),
    disponible BOOLEAN DEFAULT TRUE,
    ofertado BOOLEAN DEFAULT TRUE, -- Borrado lógico de los productos que ya no se ofrecen
    
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE CASCADE
);

CREATE TABLE producto_imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY, -- La primera imagen sera la principal
    id_producto INT NOT NULL,
    ruta_imagen VARCHAR(255) NOT NULL,

    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
);


-- Para la funcionalidad 2 (Gestión de Pedidos)

-- Tabla principal de pedidos
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    numero_dia INT NOT NULL, -- El número que empieza en 1 cada día
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('nuevo', 'recibido', 'en preparación', 'cocinando', 'listo cocina', 'terminado', 'entregado', 'cancelado') DEFAULT 'nuevo',
    tipo ENUM('local', 'llevar') NOT NULL,
    
    -- Nuevos campos para la F4 (Gestión de ofertas):
    total_sin_descuento DECIMAL(10, 2) NOT NULL, 
    descuento_aplicado DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    
    total_iva DECIMAL(10, 2) NOT NULL, -- El total final a pagar (con IVA y con descuento ya aplicado)
    
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de qué productos tiene cada pedido
CREATE TABLE pedido_productos (
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario_historico DECIMAL(10, 2) NOT NULL, 
    
    PRIMARY KEY (id_pedido, id_producto),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id)
);

-- Para la funcionalidad 4, (Gestión de ofertas)

CREATE TABLE ofertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    porcentaje_descuento DECIMAL(5,2) NOT NULL -- Guarda el porcentaje
);

CREATE TABLE oferta_productos (
    id_oferta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad_requerida INT NOT NULL DEFAULT 1,
    
    PRIMARY KEY (id_oferta, id_producto),
    FOREIGN KEY (id_oferta) REFERENCES ofertas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
);