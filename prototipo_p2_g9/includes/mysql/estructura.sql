
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