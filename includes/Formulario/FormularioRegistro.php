<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';

class FormularioRegistro extends Formulario
{
    public function __construct() {
        // ID del form y URL donde redirige al terminar (al perfil)
        parent::__construct('formRegistro', ['urlRedireccion' => RUTA_APP.'/vistas/usuarios/perfil.php']);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        // Repoblación
        $username = $datos['username'] ?? '';
        $email = $datos['email'] ?? '';
        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(
            ['username', 'email', 'nombre', 'apellidos', 'password', 'password_confirm'], 
            $this->errores, 'span', array('class' => 'error')
        );

        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Crea tu cuenta de Cliente</legend>
            <div>
                <label for="username">Usuario:</label>
                <input id="username" type="text" name="username" value="$username" />
                {$erroresCampos['username']}
            </div>
            <div>
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" value="$email" />
                {$erroresCampos['email']}
            </div>
            <div>
                <label for="nombre">Nombre:</label>
                <input id="nombre" type="text" name="nombre" value="$nombre" />
                {$erroresCampos['nombre']}
            </div>
            <div>
                <label for="apellidos">Apellidos:</label>
                <input id="apellidos" type="text" name="apellidos" value="$apellidos" />
                {$erroresCampos['apellidos']}
            </div>
            <div>
                <label for="password">Contraseña (Mínimo 6 caracteres):</label>
                <input id="password" type="password" name="password" />
                {$erroresCampos['password']}
            </div>
            <div>
                <label for="password_confirm">Confirmar Contraseña:</label>
                <input id="password_confirm" type="password" name="password_confirm" />
                {$erroresCampos['password_confirm']}
            </div>
            <div>
                <button type="submit" name="registro_submit">Registrarme</button>
            </div>
        </fieldset>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        // Validación super básica y delegación en tu función user_validate_data()
        list($clean, $erroresValidacion) = user_validate_data($datos, true, null, false);
        
        if (count($erroresValidacion) > 0) {
            $this->errores = $erroresValidacion;
        }

        // Si tu user_validate_data no valida passwords repetidos, lo hacemos aquí o en la clase
        $pwd1 = $datos['password'] ?? '';
        $pwd2 = $datos['password_confirm'] ?? '';
        if ($pwd1 !== $pwd2) {
            $this->errores['password_confirm'] = 'Las contraseñas no coinciden.';
        }

        if (count($this->errores) === 0) {
            $clean['rol'] = 'cliente'; // Por defecto, registro de cliente
            
            // Reutilzamos tu función de crear usuario
            $newId = user_create($clean, ['type' => 'default']);
            if ($newId) {
                $user = user_find_by_id($newId);
                login_user($user);
            } else {
                $this->errores[] = "Error interno al crear el usuario en la BD.";
            }
        }
    }
}