Aplicaciones Web / Sistemas web
Grado en Ingeniería Informática
Grado en Ingeniería de Computadores
Facultad de Informática
Universidad Complutense de Madrid

Examen (Evaluación en Grupo)
DURACIÓN: 3 horas.

El examen consiste en modificar la aplicación BistroFDI para introducir un Sistema de Gestión de Incidencias en Pedidos completo, permitiendo a los clientes reportar problemas y a los administradores gestionarlos.

Actualmente, si un cliente tiene un problema con un pedido (ej. llegó frío, faltaba un producto), no tiene cómo notificarlo. La modificación consiste en permitir que los clientes abran un "ticket" o incidencia asociada a un pedido concreto. Por otro lado, los usuarios con rol de administrador dispondrán de un panel exclusivo para leer estas reclamaciones y marcarlas como resueltas una vez hayan sido atendidas.

Entrega del examen y criterios de calificación:

Se valorará muy positivamente el uso de orientación a objetos, utilizando clases como las utilizadas en la estructura de proyecto propuesta en la asignatura.

Se considerará un error muy grave si no se usa el método HTTP adecuado a la petición a gestionar.

Se penalizará la solución si: 1) no es Orientada a Objetos; 2) no existe una separación clara entre scripts de vista y scripts de lógica; 3) el acceso a la base de datos no está desacoplado de la lógica y (recomendablemente) encapsulada en clases de entidad; 4) no se validan los parámetros (tanto vistas como formularios) de manera adecuada.

Durante el examen podrás usar los materiales proporcionados por el profesor.

Está explícitamente prohibido el uso de cualquier tipo de herramienta de comunicación o dispositivo electrónico (e.g., smartphone, smartwatch, etc.), IA generativa, que permita acceder a algún recurso diferente a los proporcionados por el profesor o ayuden a resolver el examen. Se tomarán medidas disciplinarias en caso de detectar cualquier acción fraudulenta.

INSTRUCCIONES DE ENTREGA DEL EXAMEN

Crea un fichero ejercicios.txt en la raíz del proyecto donde indicas qué ejercicios has completado, e.g., ejercicio 1, ejercicio 2, ejercicio 3.

Genera un único fichero ZIP cuyo nombre sea Apellidos_Nombre.zip

Entrégalo por el sistema de entrega de exámenes en laboratorio (FTP).

No cierres la sesión de tu equipo hasta verificar la entrega en el equipo del profesor.

-----------------------------------------------------------------------------------------------

Ejercicio 1: Base de Datos y Visualización del Cliente (3 puntos)
Modifica el sistema para almacenar las reclamaciones y mostrarlas en el historial del usuario:

Crea una tabla en la base de datos para almacenar las incidencias. Cada incidencia debe registrar a qué id_pedido pertenece, qué id_usuario la abre, el tipo de problema (un texto corto, ej: "Retraso", "Falta producto"), una descripción detallada, y el estado de la incidencia (que por defecto será pendiente, pero podrá ser resuelta). Un pedido solo puede tener una incidencia asociada.

Crea o modifica el modelo correspondiente (Incidencia.php o Pedido.php) para extraer si un pedido tiene una incidencia y su estado actual.

En historial_pedidos.php, modifica la vista para que, si un pedido tiene una reclamación registrada, aparezca un mensaje indicando el estado de la misma debajo de los detalles del pedido (por ejemplo: "⚠️ Incidencia Abierta: En revisión" o "✅ Incidencia Resuelta").

-----------------------------------------------------------------------------------------------

Ejercicio 2: Formulario de Reclamación (3 puntos)
Permite a los clientes abrir una incidencia sobre pedidos conflictivos:

En historial_pedidos.php, añade un botón o enlace "Reportar Problema" en la cabecera de cada pedido, exclusivamente en aquellos que tengan el estado entregado. Si el pedido ya tiene una incidencia creada (sea pendiente o resuelta), este botón no debe aparecer.

Este botón debe llevar a una nueva vista (reportar_incidencia.php) que implemente un formulario orientado a objetos (creando una clase que herede de Formulario).

El formulario debe pedir el "Tipo de Problema" (mediante un <select> con varias opciones) y una descripción detallada en un <textarea>.

Seguridad Obligatoria: En el método de procesamiento del formulario, debes validar en el servidor que el usuario logueado es el verdadero dueño del pedido que intenta reclamar (evitando que alterando el ID en la petición se reporten pedidos ajenos) y que el pedido realmente está en estado entregado. Si es correcto, se inserta en la BD y redirige al historial.

-----------------------------------------------------------------------------------------------

Ejercicio 3: Panel de Administración de Incidencias (4 puntos)
Crea la funcionalidad para que el personal del restaurante gestione estas reclamaciones:

Crea una nueva vista llamada admin_incidencias.php. Esta vista debe estar fuertemente protegida: si un usuario sin rol de admin intenta acceder, debe ser redirigido al index o mostrar un mensaje de error ("Acceso denegado").

En esta vista, muestra un listado (tabla o tarjetas) con todas las incidencias registradas en el sistema. Debe mostrar el ID del pedido, el nombre del cliente, el tipo de queja, la descripción y el estado actual (pendiente o resuelta).

Para aquellas incidencias que estén en estado pendiente, debe aparecer un pequeño formulario con un único botón: "Marcar como Resuelta".

Al hacer clic en ese botón, se debe procesar la petición (mediante POST) para actualizar el estado de la incidencia en la base de datos a resuelta. Tras actualizarse, se debe redirigir al mismo panel de administración para que el administrador vea la lista actualizada y el botón desaparezca para esa incidencia concreta.