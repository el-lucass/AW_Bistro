# Feedback de la P3 (Grupo 9) (v007 produccion)

## Número de miembros: 5

## Funcionalidades a implementar en el proyecto

F0- Gestión de usuarios.
F1- Gestión de productos: categorías y productos.
F2- Gestión de pedidos.
F3- Gestión de preparación de pedidos.
F4- Gestión de ofertas.
F5- Gestión de recompensas. (Para grupos de tamaño 5)

## Funcionalidades implementadas en P3 (75% del total)

1. F0. Gestión de usuarios.
2. F1. Gestión de productos: categorías y productos.
3. F2. Gestión de pedidos.
4. F3. Gestión de preparación de pedidos.
5. F4- Gestión de ofertas. (Para grupos de tamaño 5)

## Calificación: 8.75 / 10

## Memoria (1.5 / 1.5)

- [ ] Los listados de los scripts NO han sido actualizados respecto a los de la P2 (0 puntos)
- [X] Los listados de los scripts han sido actualizados respecto a los de la P2 (0,5 puntos)

- [ ] El diagrama de base de datos NO ha sido actualizado respecto al de la P2 (0 puntos)
- [X] El diagrama de base de datos ha sido actualizado respecto al de la P2 (0.5 puntos)

- [X] La memoria incluye el parte de actividades detallado por cada integrante del grupo de prácticas (0.5 puntos)

Contenido:

- [X] Listado de scripts para las vistas
- [X] Listado de scripts adicionales
- [X] Estructura de la base de datos
- [X] Listado del juego de usuarios de pruebas.
- [X] Parte de actividades.

### Comentarios sobre la memoria

- La memoria es correcta.

## HTML (0.75 / 1)

- [ ] Hay errores graves en el HTML (0 puntos)
- [ ] Hay bastantes errores en el HTML (0.5 puntos)
- [X] Hay algunos errores en el HTML (0.75 puntos) (catalogo.php)
- [ ] Se hace un uso adecuado de las etiquetas (1 punto)

## CSS ( 1.5 / 1.5 )

- [ ] No se incluyen CSS o son las mismas que se proporcionan en el ejercicio 2. (0 puntos)
- [ ] Estilos mínimos o modificaciones mínimas sobre las CSS proporcionadas en el ejercicio 2 (0.25 puntos)
- [ ] Las CSS añaden nuevas reglas tanto para modificar el aspecto de elementos de las páginas como para organizar la aplicación, pero no se incluyen los comentarios necesarios (0.5 puntos)
- [ ] Las CSS añaden nuevas reglas tanto para modificar el aspecto de elementos de las páginas como para organizar la aplicación (0.75 puntos)
- [ ] Se hace un uso intensivo de CSS, en particular se usan CSS Flexbox y/o CSS Grid para organizar las páginas, pero no se incluyen los comentarios necesarios para entender el diseño de las CSS (1 punto)
- [X] Se hace un uso intensivo de CSS, en particular se usan CSS Flexbox y/o CSS Grid para organizar las páginas y se incluyen los comentarios necesarios para entender el diseño de las CSS (1.5 puntos)

## Evaluación de funcionalidades y código (5 / 6)

- Calificación de la funcionalidad implementada (F3 o F4) (0-3 puntos)
- Puntos por la calidad del código PHP (0-3 puntos)

### F4. Gestión de ofertas (3 / 3)

#### Puebas de la funcionalidad F4

- [ ] Al probar la funcionalidad implementada no funciona o tiene bastantes errores (0 puntos)
- [ ] Al probar la funcionalidad implementada falla en algunos casos (0.75 puntos)
- [X] Al probar la funcionalidad implementada funciona correctamente (1.5 puntos)

#### Grado de madurez de la funcionalidad F4

- [ ] La funcionalidad está completada por debajo del 50% (0 puntos)
- [ ] La funcionalidad está completada entre el 50% y el 75% (0.75 puntos)
- [X] La funcionalidad está completada entre el 75% y el 100% (1.5 puntos)

#### Comentarios de la funcionalidad F4

- La funcionalidad de gestión de ofertas es correcta. Se pueden visualizar las ofertas, crear nuevas ofertas, seguir el flujo de gestión de las mismas y el usuario las ve.
- Una mejora en las ofertas es que se puedan aplicar ofertas a familias de productos. Por ejemplo, una oferta posibles es un 2x1 en bebidas. Actualmente, las ofertas las estáis aplicando con AND y cada producto, pero no puedo hacer un OR para que la oferta se aplique a coca-cola o fanta, por ejemplo. De esta forma, se podrían crear ofertas más generales.
- He podido crear la oferta 2x1 en coca-cola como gerenete. Como cliente, he hecho un pedido de 4 coca-colas. La oferta se ha aplicado, pero debería haber restado 2.72€, y en su lugar ha restado 2.73€. Si hago el pedido con 6 coca-colas, debería restar 4.08€ y está restando 4.09€.Tenéis que revisar que el cálculo de las ofertas sea el correcto.
- Los clientes deben poder decidir qué ofertas aplicar a su pedido. En vuestro caso, la oferta se aplica de forma automática, pero el cliente no puede decidir si aplicarla o no. Deberíais permitir que el cliente decida qué ofertas aplicar a su pedido, ya que a veces puede interesarle no aplicarlas.

### Evaluación de código PHP (2 / 3)

- [ ] No existe una separación clara entre scripts de vista y scripts de lógica (0 puntos)
- [X] Existe una separación clara entre scripts de vista y scripts de lógica (0.5 puntos)
- [ ] Existe una separación clara entre scripts de vista y scripts de lógica. Además, la lógica en los scripts de vista es concentrada al comienzo del script y se utilizan funciones de apoyo para simplificar la generación y el mantenimiento del HTML de las páginas. (1 punto)

- [ ] El código contiene bastantes errores comunes o de otro tipo(0 puntos)
- [X] El código contiene algunos errores comunes o de otro tipo (0.5 puntos)
- [ ] El código no contiene errores apreciables (1 punto)

- [X] Sigue la estructura del ejercicio 2 o la estructura-proyecto. (0.5 puntos)

- [X] La solución utiliza orientación a objetos al menos para las clases de entidad de la aplicación (0.5 puntos)

## Errores comunes encontrados y errores de despliegue

- [X] No se liberan recursos $rs->free() cuando se lanza una consulta SELECT. (Cocina.php, Oferta.php, etc.)
- [ ] Las operaciones de base de datos no escapan ($conn->real_escape_string()) los parámetros del usuario.
- [ ] No se utiliza HTTP POST cuando la operación modifica el estado del servidor.
- [ ] Los datos que provienen del usuario no se validan adecuadamente.
- [ ] Las clases de entidad (e.g. Usuario, Mensaje, etc.) generan HTML. Las clases de entidad no deben de tener esa responsabilidad.
- [X] Las operaciones de BD devuelven arrays cuyo contenido son directamente las filas que se obtienen de la base de datos y no instancias de la clase correspondiente. (Cocina.php, Oferta.php)
- [ ] Uso de style en HTML, en lugar de un fichero CSS.
- [ ] La aplicación no está desplegada en la raíz del servidor.
