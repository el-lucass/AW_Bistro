# Feedback de la P2 (Grupo 9)

## Número de miembros: 5

## Funcionalidades a implementar en el proyecto

F0- Gestión de usuarios.
F1- Gestión de productos: categorías y productos.
F2- Gestión de pedidos.
F3- Gestión de preparación de pedidos.
F4- Gestión de ofertas.
F5- Gestión de recompensas. (Para grupos de tamaño 5)

## Funcionalidades implementadas en P2 (50% del total)

1. F0. Gestión de usuarios.
2. F1. Gestión de productos: categorías y productos.
3. F2. Gestión de pedidos.
4. F3. Gestión de preparación de pedidos. (Sólo para grupos de tamaño 5)

## Calificación: 8 / 10

## Memoria (1.25 / 2)

- [X] La memoria tiene al menos las secciones solicitadas (0.5 puntos)

- [X] Los listados de scripts se limitan a las funcionalidades implementadas (0.5 puntos)
- [ ] Los listados de scripts parece que cubren todas las funcionalidades de la aplicación (1 punto)

- [X] El diagrama de base de datos cubre las funcionalidades implementadas (0.25 puntos)
- [ ] El diagrama de base de datos parece cubrir todas las funcionalidades de la aplicación (0.5 puntos)

Contenido:

- [X] Listado de scripts para las vistas
- [X] Listado de scripts adicionales
- [X] Estructura de la base de datos
- [X] Prototipo funcional del proyecto: funcionalidades implementadas y usuarios y passwords para probar la aplicación.

### Comentarios sobre la memoria

- La memoria es correcta, pero no incluye todos los scripts de las funcionalidades del proyecto.

## HTML (0.75 / 1)

- [ ] Hay errores graves en el HTML (0 puntos)
- [ ] Hay bastantes errores en el HTML (0.5 puntos)
- [X] Hay algunos errores en el HTML (0.75 puntos) (crear_producto.php)
- [ ] Se hace un uso adecuado de las etiquetas (1 punto)

### Comentarios

## Evaluación de funcionalidades y código (6 / 7)

- Calificación de las funcionalidades implementadas (0-4 puntos)
- Puntos por la calidad del código PHP (0-3 puntos)
- Regla de calificación: (F1 + F2 + F3 (si se ha implementado)) / 4*3 (o 4*2 si no se ha implementado F3)
- Calificación de funcionalidades: (12/12)*4 = 4

### F1. Gestión de productos: categorías y productos (4 / 4)

#### Puebas de la funcionalidad F1

- [ ] Al probar la funcionalidad implementada no funciona o tiene bastantes errores (0 puntos)
- [ ] Al probar la funcionalidad implementada falla en algunos casos (1 punto)
- [X] Al probar la funcionalidad implementada funciona correctamente (2 puntos)

#### Grado de madurez de la funcionalidad F1

- [X] La funcionalidad incluye la visualización, creación, actualización y borrado (2 puntos)
- [ ] La funcionalidad no incluye la actualización o el borrado (1 punto)
- [ ] La funcionalidad no incluye la actualización ni el borrado (0 puntos)

#### Comentarios de la funcionalidad F1 y F0

- El registro de usuarios es correcto.
- La actualización del perfil de usuario es correcta, pero no se puede cambiar la contraseña.
- La funcionalidad de gestión de categorías y productos es correcta.

### F2. Gestión de pedidos (4 / 4)

#### Puebas de la funcionalidad F2

- [ ] Al probar la funcionalidad implementada no funciona o tiene bastantes errores (0 puntos)
- [ ] Al probar la funcionalidad implementada falla en algunos casos (1 punto)
- [X] Al probar la funcionalidad implementada funciona correctamente (2 puntos)

#### Grado de madurez de la funcionalidad F2

- [X] La funcionalidad incluye la visualización, creación, actualización y borrado (2 puntos)
- [ ] La funcionalidad no incluye la actualización o el borrado (1 punto)
- [ ] La funcionalidad no incluye la actualización ni el borrado (0 puntos)

#### Comentarios de la funcionalidad F2

- La funcionalidad de gestión de pedidos es correcta, el carrito permite la edición de las cantidades de los productos del pedido.

### F3. Gestión de preparación de pedidos (4 / 4)

#### Puebas de la funcionalidad F3

- [ ] Al probar la funcionalidad implementada no funciona o tiene bastantes errores (0 puntos)
- [ ] Al probar la funcionalidad implementada falla en algunos casos (1 punto)
- [X] Al probar la funcionalidad implementada funciona correctamente (2 puntos)

#### Grado de madurez de la funcionalidad F3

- [X] La funcionalidad incluye la visualización, creación, actualización y borrado (2 puntos)
- [ ] La funcionalidad no incluye la actualización o el borrado (1 punto)
- [ ] La funcionalidad no incluye la actualización ni el borrado (0 puntos)

#### Comentarios de la funcionalidad F3

- La funcionalidad de gestión de preparación de pedidos es correcta. Se pueden visualizar los pedidos, marcar un pedido como preparado y seguir el flujo de preparación de los pedidos.

### Evaluación de código PHP (2 / 3)

- [ ] No existe una separación clara entre scripts de vista y scripts de lógica (0 puntos)
- [X] Existe una separación clara entre scripts de vista y scripts de lógica (0.5 puntos)
- [ ] Existe una separación clara entre scripts de vista y scripts de lógica. Además, la lógica en los scripts de vista es concentrada al comienzo del script y se utilizan funciones de apoyo para simplificar la generación y el mantenimiento del HTML de las páginas. (1 punto)

- [ ] El código contiene bastantes errores comunes o de otro tipo(0 puntos)
- [X] El código contiene algunos errores comunes o de otro tipo (0.5 puntos)
- [ ] El código no contiene errores apreciables (1 punto)

- [X] Sigue la estructura del ejercicio 2 o la estructura-proyecto. Las clases de entidad se encargan de la gestión de acceso a la base de datos (0.5 puntos)

- [X] La solución utiliza orientación a objetos al menos para las clases de entidad de la aplicación (0.5 puntos)

## Errores comunes encontrados y errores de despliegue

- [X] No se liberan recursos $rs->free() cuando se lanza una consulta SELECT. (Cocina.php)
- [ ] Las operaciones de base de datos no escapan ($conn->real_escape_string()) los parámetros del usuario.
- [ ] No se utiliza HTTP POST cuando la operación modifica el estado del servidor.
- [ ] Los datos que provienen del usuario no se validan adecuadamente.
- [ ] Las clases de entidad (e.g. Usuario, Mensaje, etc.) generan HTML. Las clases de entidad no deben de tener esa responsabilidad.
- [ ] Las operaciones de BD devuelven arrays cuyo contenido son directamente las filas que se obtienen de la base de datos y no instancias de la clase correspondiente.
- [X] Uso de style en HTML, en lugar de un fichero JavaScript.
- [ ] La aplicación no está desplegada en la raíz del servidor.
