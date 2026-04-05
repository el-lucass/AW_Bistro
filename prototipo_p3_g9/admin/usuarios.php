<?php
require_once '../includes/config.php';

// Importamos las clases modernas
use es\ucm\fdi\aw\usuarios\Usuario;

// Verificación de seguridad usando el método de clase
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Gestión de Usuarios - Bistro FDI';

// Obtenemos los usuarios ordenados usando el método de la clase
$listaUsuarios = Usuario::listaUsuariosOrdenados();

$tabla = '<table>
    <thead><tr>
        <th>ID</th><th>Usuario</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th>
    </tr></thead>
    <tbody>';

if (!empty($listaUsuarios)) {
    foreach ($listaUsuarios as $row) {
        $id             = $row['id'];
        $esMismoUsuario = ($id == $_SESSION['id']);
        $claseRol       = 'rol-badge-' . $row['rol'];

        $tabla .= "<tr>
            <td>{$id}</td>
            <td>" . htmlspecialchars($row['nombre_usuario']) . "</td>
            <td>" . htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']) . "</td>
            <td>" . htmlspecialchars($row['email']) . "</td>
            <td><span class='{$claseRol}'>" . strtoupper($row['rol']) . "</span></td>
            <td>
                <a href='editar_usuario.php?id={$id}'>
                    <button class='btn-editar btn-sm'>Editar</button>
                </a>";

        if (!$esMismoUsuario) {
            $tabla .= " <form action='../usuarios/admin_borrar.php' method='POST' class='inline'
                              onsubmit='return confirm(\"¿Estás seguro de borrar a este usuario?\")'>
                            <input type='hidden' name='id' value='{$id}'>
                            <button type='submit' class='btn-peligro btn-sm'>Borrar</button>
                        </form>";
        }
        $tabla .= "</td></tr>";
    }
} else {
    $tabla .= "<tr><td colspan='6' class='texto-centro'>No hay usuarios registrados.</td></tr>";
}
$tabla .= '</tbody></table>';

$contenidoPrincipal = "
<h1>Panel de Control: Gestión de Usuarios</h1>
<p>Lista de usuarios ordenada por jerarquía.</p>
<div class='admin-toolbar'>
    <a href='crear_usuario.php'>
        <button class='btn-crear btn-lg'>+ Añadir Nuevo Usuario / Empleado</button>
    </a>
</div>
{$tabla}";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
