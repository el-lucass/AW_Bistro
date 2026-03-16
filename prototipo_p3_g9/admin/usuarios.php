<?php
require_once '../includes/config.php';

// Importamos las clases modernas
use es\ucm\fdi\aw\Usuario;
use es\ucm\fdi\aw\Aplicacion;

// Verificación de seguridad usando el método de clase
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Gestión de Usuarios - Bistro FDI';

// Obtenemos la conexión con el Singleton de la Aplicación (adiós conectarBD)
$conn = Aplicacion::getInstance()->getConexionBd();

// Consulta con el orden jerárquico
$sql = "SELECT * FROM usuarios 
        ORDER BY FIELD(rol, 'cliente', 'camarero', 'cocinero', 'gerente') ASC";
$result = $conn->query($sql);

$tabla = '<table border="1" style="width:100%; text-align:left; border-collapse: collapse;">
    <tr style="background-color: #f2f2f2;">
        <th style="padding: 10px;">ID</th>
        <th style="padding: 10px;">Usuario</th>
        <th style="padding: 10px;">Nombre</th>
        <th style="padding: 10px;">Email</th>
        <th style="padding: 10px;">Rol (Prioridad)</th>
        <th style="padding: 10px;">Acciones</th>
    </tr>';

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $esMismoUsuario = ($id == $_SESSION['id']);
        
        $colorRol = '#16a085';
        if ($row['rol'] === 'gerente') $colorRol = '#d35400';
        elseif ($row['rol'] === 'cocinero') $colorRol = '#8e44ad';
        elseif ($row['rol'] === 'camarero') $colorRol = '#2980b9';

        $tabla .= "<tr>
            <td style='padding: 10px;'>{$id}</td>
            <td style='padding: 10px;'>" . htmlspecialchars($row['nombre_usuario']) . "</td>
            <td style='padding: 10px;'>" . htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']) . "</td>
            <td style='padding: 10px;'>" . htmlspecialchars($row['email']) . "</td>
            <td style='padding: 10px;'><span style='color: $colorRol; font-weight: bold;'> " . strtoupper($row['rol']) . "</span></td>
            <td style='padding: 10px;'>
                <a href='editar_usuario.php?id=$id'><button style='background-color:#f39c12; color:white; border:none; padding:5px 10px; cursor:pointer; border-radius:3px;'>Editar</button></a>";
        
        if (!$esMismoUsuario) {
            $tabla .= " <form action='../usuarios/admin_borrar.php' method='POST' style='display:inline;' onsubmit='return confirm(\"¿Estás seguro de borrar a este usuario?\")'>
                            <input type='hidden' name='id' value='$id'>
                            <button type='submit' style='background-color:#c0392b; color:white; border:none; padding:5px 10px; cursor:pointer; border-radius:3px;'>Borrar</button>
                        </form>";
        }
        $tabla .= "</td></tr>";
    }
} else {
    $tabla .= "<tr><td colspan='6' style='padding: 10px; text-align:center;'>No hay usuarios registrados.</td></tr>";
}
$tabla .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>Panel de Control: Gestión de Usuarios</h1>
    <p>Lista de usuarios ordenada por jerarquía (Menos a más prioridad).</p>
    
    <div style="margin-bottom: 20px;">
        <a href='crear_usuario.php'>
            <button style='background-color:#2ecc71; color:white; padding:10px; cursor:pointer; border:none; border-radius:5px;'>
                + Añadir Nuevo Usuario / Empleado
            </button>
        </a>
    </div>

    $tabla
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>