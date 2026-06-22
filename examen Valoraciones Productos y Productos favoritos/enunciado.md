El examen consiste en modificar la aplicación BistroFDI para introducir un Sistema de Valoraciones de Productos y una Lista de Favoritos.

Actualmente, los clientes compran productos a ciegas. La modificación consiste en permitir que los clientes puedan dejar una reseña (puntuación y comentario) sobre los productos que ya han consumido. Estas puntuaciones medias se mostrarán en el catálogo. Además, se proporcionará una funcionalidad (respetuosa con la privacidad del usuario) para que cada cliente pueda marcar productos como "Favoritos" y filtrar el catálogo para ver solo los que más le gustan.

---------------------------

Entrega del examen y criterios de calificación:

Se valorará muy positivamente el uso de orientación a objetos, utilizando clases como las utilizadas en la estructura de proyecto propuesta en la asignatura.

Se considerará un error muy grave si no se usa el método HTTP adecuado a la petición a gestionar.

Se penalizará la solución: 1) no es Orientada a Objetos; 2) no existe una separación clara entre scripts de vista y scripts de lógica; 3) el acceso a la base de datos no está desacoplada de la lógica y (recomendablemente) encapsulada en clases de entidad; 4) no se validan los parámetros (tanto vistas como formularios) de manera adecuada.

Durante el examen podrás usar los materiales proporcionados por el profesor.

Está explícitamente prohibido el uso de cualquier tipo de herramienta de comunicación o dispositivo electrónico (e.g., smartphone, smartwatch, etc.), IA generativa, que permita acceder a algún recurso diferente de los proporcionados por el profesor o ayuden a resolver el examen. Se tomarán medidas disciplinarias en caso de detectar cualquier acción fraudulenta.

------------------------

INSTRUCCIONES DE ENTREGA DEL EXAMEN

Crea un fichero ejercicios.txt en la raíz del proyecto donde indicas qué ejercicios has completado, e.g., ejercicio 1, ejercicio 2, ejercicio 3.

Genera un único fichero ZIP cuyo nombre sea Apellidos_Nombre.zip

Entrégalo por el sistema de entrega de exámenes en laboratorio (FTP).

No cierres la sesión de tu equipo hasta verificar la entrega en el equipo del profesor.

----------------------------

Ejercicio 1: Lógica y Visualización de Valoraciones (4 puntos)
Modifica el sistema para almacenar y mostrar valoraciones de los productos:

Crea una tabla en la base de datos para almacenar las valoraciones. Cada valoración debe registrar qué usuario la hizo, a qué producto, la puntuación (entero del 1 al 5) y un comentario opcional de texto. Un usuario solo puede tener una valoración activa por producto (si vuelve a valorar, se actualiza la anterior).

Modifica el modelo (Producto.php) para que, al extraer la lista de productos, se calcule la puntuación media y el número total de valoraciones de cada producto.

En catalogo.php, modifica la tarjeta de cada producto (.producto-card) para mostrar visualmente esta información justo debajo de la descripción (por ejemplo: "⭐ 4.5/5 (12 opiniones)"). Si un producto no tiene valoraciones, debe mostrar "Sin valoraciones".

---------------------------------

Ejercicio 2: Formulario de Valoración (3 puntos)
Permite a los clientes valorar los productos que han pedido:

En historial_pedidos.php, añade un botón "Valorar" al lado de cada producto, exclusivamente en aquellos pedidos que tengan el estado terminado o entregado.

Este botón debe llevar a una nueva vista (valorar_producto.php) que implemente un formulario orientado a objetos (creando una clase que herede de Formulario).

El formulario debe pedir la puntuación (selector o input numérico del 1 al 5) y un área de texto para el comentario.

Seguridad: Debes procesar la petición mediante el método HTTP correcto, validar que la puntuación esté en el rango permitido (1-5) y asegurar que el usuario logueado es realmente quien hace la valoración. Si la validación es correcta, se guarda en BD y se redirige al catálogo o historial con un mensaje de éxito.

-------------------------

Ejercicio 3: Lista de Favoritos Privada (3 puntos)
El objetivo es que el cliente pueda marcar productos como favoritos y filtrar el catálogo para encontrarlos rápidamente, pero maximizando su privacidad (sin guardar estos datos en el servidor).

En catalogo.php, añade un icono o botón (ej. un corazón 🤍) en la tarjeta de cada producto. Al hacer clic, el producto se debe añadir a la lista de favoritos del usuario y el icono debe cambiar de estado (ej. corazón relleno ❤️). Al volver a hacer clic, se elimina de favoritos.

Añade un interruptor o botón en la cabecera del catálogo (.catalogo-header) con el texto "Mostrar solo mis favoritos".

Al activar este botón, la aplicación debe ocultar dinámicamente del DOM (ej. mediante CSS display: none) todas las .producto-card de aquellos productos que no estén en la lista de favoritos. Al desactivarlo, se vuelven a mostrar todos.

Si durante el proceso de compra se cambia de página, recarga el catálogo o se va al carrito y se vuelve, los productos marcados como favoritos no se pierden y sus corazones deben aparecer correctamente rellenados al cargar la página.