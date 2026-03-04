<?php
require_once 'FormularioUsuarioBase.php';

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
        
        $html .= <<<EOF
        <fieldset>
            <legend>Permisos de empleado</legend>
            <label>Rol:</label>
            <select name="rol">
                <option value="cliente" . ($rol == 'cliente' ? 'selected' : '') . >Cliente</option>
                <option value="camarero" . ($rol == 'camarero' ? 'selected' : '') . >Camarero</option>
                <option value="cocinero" . ($rol == 'cocinero' ? 'selected' : '') . >Cocinero</option>
                <option value="gerente" . ($rol == 'gerente' ? 'selected' : '') . >Gerente</option>
            </select>
        </fieldset>

        <div style="margin-top: 20px;">
            <button type="submit">Crear Usuario</button>
        </div>
EOF;
        return $html;
    }

    protected function insertaUsuario($datos)
    {
        // Aquí pasamos el ROL que viene del formulario
        if (creaUsuario($datos['nombre_usuario'], $datos['password'], $datos['nombre'], $datos['apellidos'], $datos['email'], $datos['rol'])) {
            return $this->urlRedireccion;
        }
        
        $this->errores[] = "Error al crear el usuario en base de datos.";
    }
}