<?php
namespace es\ucm\fdi\aw\usuarios;

use es\ucm\fdi\aw\usuarios\FormularioUsuarioBase;
use es\ucm\fdi\aw\usuarios\Usuario;

class FormularioAdminCrear extends FormularioUsuarioBase
{
    public function __construct() {
        // Redirección a la lista de usuarios tras éxito
        parent::__construct('formAdminCrear', ['urlRedireccion' => '../admin/usuarios.php?msg=creado']);
    }

    protected function generaCamposFormulario(&$datos)
    {
        // 1. Traemos los campos comunes del padre
        $html = parent::generaCamposBasicos($datos);

        // 2. Añadimos el SELECT de Rol y el botón
        $rol = $datos['rol'] ?? 'cliente';
        
        // Preparamos los atributos 'selected' ANTES del heredoc para no romper el HTML
        $selCliente  = ($rol == 'cliente')  ? 'selected' : '';
        $selCamarero = ($rol == 'camarero') ? 'selected' : '';
        $selCocinero = ($rol == 'cocinero') ? 'selected' : '';
        $selGerente  = ($rol == 'gerente')  ? 'selected' : '';
        
        $html .= <<<EOF
        <fieldset>
            <legend>Permisos de empleado</legend>
            <label>Rol:</label>
            <select name="rol">
                <option value="cliente" $selCliente>Cliente</option>
                <option value="camarero" $selCamarero>Camarero</option>
                <option value="cocinero" $selCocinero>Cocinero</option>
                <option value="gerente" $selGerente>Gerente</option>
            </select>
        </fieldset>

        <div class="mt-20">
            <button type="submit">Crear Usuario</button>
        </div>
EOF;
        return $html;
    }

    protected function insertaUsuario($datos)
    {
        // LLAMADA ACTUALIZADA: Usamos el método estático de la clase Usuario
        if (Usuario::creaUsuario($datos['nombre_usuario'], $datos['password'], $datos['nombre'], $datos['apellidos'], $datos['email'], $datos['rol'])) {
            return $this->urlRedireccion;
        }
        
        $this->errores[] = "Error al crear el usuario en base de datos.";
    }
}