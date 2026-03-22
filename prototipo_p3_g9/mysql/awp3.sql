-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-03-2026 a las 19:26:50
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `awp3`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `imagen`) VALUES
(1, 'Bebidas', 'Sabemos que un buen festín necesita el acompañamiento líquido ideal. En nuestra sección de bebidas, encontrarás la combinación perfecta para refrescar tu paladar entre bocado y bocado.', NULL),
(2, 'Kebabs', 'Nuestros Kebabs son la definición de sabor auténtico. Carne de primera calidad, asada lentamente en su punto exacto, servida en pan de pita recién horneado y acompañada de vegetales frescos y nuestras salsas secretas. Ya sea de pollo, ternera o mixto, es la elección segura para quienes buscan tradición en cada bocado.', NULL),
(3, 'Dürums', 'Si prefieres una experiencia más compacta pero igual de intensa, nuestros Dürums son para ti. Todo el sabor de nuestra carne asada enrollada en una fina y suave tortilla de trigo. Perfecto para disfrutar cómodamente sin perder ni un ápice de esa combinación explosiva de ingredientes que tanto te gusta.', NULL),
(5, 'Complementos', '¿Qué es un plato principal sin sus compañeros de aventura? En nuestra sección de complementos encontrarás desde patatas fritas crujientes en su punto de sal, hasta falafel casero, ensaladas frescas y raciones extras de nuestras salsas. El acompañamiento ideal para redondear tu pedido.', NULL),
(6, 'Postres', 'Ninguna comida está completa sin el cierre perfecto. Déjate tentar por nuestra selección de postres, desde los tradicionales dulces árabes cargados de miel y frutos secos, hasta opciones frescas y ligeras. El capricho que tu paladar se merece después de un buen banquete.', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas`
--

CREATE TABLE `ofertas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `porcentaje_descuento` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ofertas`
--

INSERT INTO `ofertas` (`id`, `nombre`, `descripcion`, `fecha_inicio`, `fecha_fin`, `porcentaje_descuento`) VALUES
(1, 'Menú Matrimonio Mixto 🌯💍🌯', 'Dicen que el amor engorda, pero con este pedazo de menú doble, ¿a quién le importa? Dos señores Durums mixtos y dos cervezas Mahou para compartir con tu persona favorita (o para comértelo todo tú, aquí no juzgamos a nadie).', '2026-03-22 15:00:00', '2026-05-20 12:00:00', 26.18);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oferta_productos`
--

CREATE TABLE `oferta_productos` (
  `id_oferta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad_requerida` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `oferta_productos`
--

INSERT INTO `oferta_productos` (`id_oferta`, `id_producto`, `cantidad_requerida`) VALUES
(1, 6, 2),
(1, 11, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_cocinero` int(11) DEFAULT NULL,
  `numero_dia` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `estado` enum('nuevo','recibido','en preparación','cocinando','listo cocina','terminado','entregado','cancelado') DEFAULT 'nuevo',
  `tipo` enum('local','llevar') NOT NULL,
  `total_sin_descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento_aplicado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_iva` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `id_usuario`, `id_cocinero`, `numero_dia`, `fecha_hora`, `estado`, `tipo`, `total_sin_descuento`, `descuento_aplicado`, `total_iva`) VALUES
(1, 3, NULL, 1, '2026-03-22 18:07:28', 'cancelado', 'llevar', 0.00, 0.00, 3.00),
(2, 3, NULL, 2, '2026-03-22 18:56:47', 'recibido', 'llevar', 0.00, 0.00, 18.29),
(3, 3, NULL, 3, '2026-03-22 19:04:36', 'cancelado', 'llevar', 0.00, 0.00, 5.50),
(4, 3, NULL, 4, '2026-03-22 19:23:06', 'cancelado', 'llevar', 11.39, 2.98, 8.41);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_productos`
--

CREATE TABLE `pedido_productos` (
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario_historico` decimal(10,2) NOT NULL,
  `preparado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_productos`
--

INSERT INTO `pedido_productos` (`id_pedido`, `id_producto`, `cantidad`, `precio_unitario_historico`, `preparado`) VALUES
(1, 7, 1, 1.50, 0),
(1, 9, 1, 1.50, 0),
(2, 6, 3, 4.50, 0),
(2, 11, 4, 1.20, 0),
(3, 4, 1, 4.00, 0),
(3, 7, 1, 1.50, 0),
(4, 6, 2, 4.50, 0),
(4, 11, 2, 1.20, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `iva` int(11) NOT NULL CHECK (`iva` in (4,10,21)),
  `disponible` tinyint(1) DEFAULT 1,
  `ofertado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `id_categoria`, `precio_base`, `iva`, `disponible`, `ofertado`) VALUES
(1, 'Kebab de pollo', 'Jugosos trozos de pollo marinados con especias orientales, servidos en pan de pita crujiente con vegetales frescos y salsa.', 2, 2.73, 10, 1, 1),
(2, 'Kebab de ternera', 'Carne de ternera tierna y sabrosa, asada a fuego lento para obtener ese sabor auténtico y tradicional que nunca falla.', 2, 2.73, 10, 1, 1),
(3, 'Kebab mixto', 'Lo mejor de dos mundos. Una combinación equilibrada de pollo y ternera para quienes no quieren renunciar a ningún sabor.', 2, 3.18, 10, 1, 1),
(4, 'Dürum de Pollo', 'Tu pollo favorito enrollado en una suave tortilla de trigo, ideal para comer donde quieras con el máximo sabor.', 3, 3.64, 10, 1, 1),
(5, 'Dürum de Ternera', 'Todo el carácter de la ternera asada en un formato práctico, compacto y repleto de frescura en cada bocado.', 3, 3.64, 10, 1, 1),
(6, 'Dürum Mixto', 'El favorito de la casa. Mezcla de carnes con vegetales y salsas, perfectamente enrollados para una experiencia explosiva.', 3, 4.09, 10, 1, 1),
(7, 'Coca-cola', 'La chispa clásica que equilibra perfectamente el sabor especiado de nuestra carne.', 1, 1.24, 21, 1, 1),
(8, 'Fanta de Naranja', 'El toque cítrico y refrescante ideal para limpiar el paladar entre bocado y bocado.', 1, 1.24, 21, 1, 1),
(9, 'Nestea', 'Té frío con un toque de limón, perfecto para quienes buscan una opción sin gas pero llena de frescura.', 1, 1.24, 21, 1, 1),
(10, 'Aquarius', 'Bebida isotónica refrescante, ligera y con ese punto ácido que tanto gusta.', 1, 1.65, 21, 1, 1),
(11, 'Cerveza Mahou', 'Una alegría para el cuerpo. No somos halal.', 1, 0.99, 21, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_imagenes`
--

CREATE TABLE `producto_imagenes` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto_imagenes`
--

INSERT INTO `producto_imagenes` (`id`, `id_producto`, `ruta_imagen`) VALUES
(1, 9, '1772125717_69a07e1591fa2_nestea.jpg'),
(3, 11, '1772127083_69a0836bb0550_cerveza.jpg'),
(4, 1, '1772206443_69a1b96bbf4b4_kebab_pollo.jpg'),
(5, 2, '1772206470_69a1b9867f554_kebab_ternera.jpg'),
(6, 3, '1772206483_69a1b993afde8_Kebab_mixto.jpg'),
(7, 4, '1772206506_69a1b9aad85fc_durum_pollo.jpg'),
(8, 5, '1772206520_69a1b9b8d07c8_durum_ternera.jpg'),
(9, 6, '1772206535_69a1b9c78140f_durum_mixto.jpg'),
(10, 7, '1772203937_69a1afa13ce82_coca-cola.jpg'),
(11, 8, '1772203975_69a1afc759b80_fanta-de-naranja.jpg'),
(12, 10, '1772203989_69a1afd517a51_aquarius.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('cliente','camarero','cocinero','gerente') DEFAULT 'cliente',
  `avatar` varchar(255) DEFAULT 'default.png',
  `bistrocoins` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `email`, `nombre`, `apellidos`, `password`, `rol`, `avatar`, `bistrocoins`) VALUES
(1, 'admin', 'pedrosanchez@gmail.com', 'Pedro', 'Sanchez', '$2y$10$jgpJdBHZRvcBui84WHc9ueNZxEE/4oeBCzvxn9Te1DmSnoi6yaZ1O', 'gerente', 'default.png', 0),
(3, 'a', 'a@a', 'a', '', '$2y$10$3ddpxCtdM.C4tPpa3qMo3u5SKe5XtYTrf5fgzZJ9KwsOmJ6dfehw6', 'cliente', 'default.png', 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `oferta_productos`
--
ALTER TABLE `oferta_productos`
  ADD PRIMARY KEY (`id_oferta`,`id_producto`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_pedidos_cocinero` (`id_cocinero`);

--
-- Indices de la tabla `pedido_productos`
--
ALTER TABLE `pedido_productos`
  ADD PRIMARY KEY (`id_pedido`,`id_producto`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `oferta_productos`
--
ALTER TABLE `oferta_productos`
  ADD CONSTRAINT `oferta_productos_ibfk_1` FOREIGN KEY (`id_oferta`) REFERENCES `ofertas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `oferta_productos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_cocinero` FOREIGN KEY (`id_cocinero`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedido_productos`
--
ALTER TABLE `pedido_productos`
  ADD CONSTRAINT `pedido_productos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_productos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD CONSTRAINT `producto_imagenes_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
