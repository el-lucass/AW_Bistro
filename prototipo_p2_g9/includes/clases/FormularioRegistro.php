<?php
require_once 'FormularioUsuarioBase.php';

class FormularioRegistro extends FormularioUsuarioBase
{
    public function __construct() {
        // ID del formulario y Redirección al login tras éxito
        parent::__construct('formRegistro', ['urlRedireccion' => 'login.php?registrado=1']);
    }

    protected function generaCamposFormulario(&$datos)
    {
        // 1. Traemos los campos del padre
        $html = parent::generaCamposBasicos($datos);

        // 2. Añadimos solo el botón
        $html .= <<<EOF
        <div style="margin-top: 20px;">
            <button type="submit">Registrarse</button>
        </div>
EOF;
        return $html;
    }

    protected function insertaUsuario($datos)
    {
        // Llamamos al modelo con rol 'cliente' fijo
        if (creaUsuario($datos['nombre_usuario'], $datos['password'], $datos['nombre'], $datos['apellidos'], $datos['email'], 'cliente')) {
            // Si devuelve true, retornamos la URL de redirección (login)
            return $this->urlRedireccion;
        }
        
        // Si falla la BD
        $this->errores[] = "Error desconocido al crear el usuario en base de datos.";
    }
}