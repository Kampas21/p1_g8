<?php
namespace es\ucm\fdi\aw\Formulario; 

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/user_repo.php'; 
require_once __DIR__ . '/../../includes/auth.php'; 

class FormularioLogin extends Formulario
{
    public function __construct() {
        // ID del form
        parent::__construct('formLogin', ['urlRedireccion' => RUTA_APP.'/vistas/usuarios/perfil.php']);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        // Repoblación de campos si ha habido error
        $loginUsuario = $datos['login'] ?? '';

        // Generamos los errores por campos
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['login', 'password'], $this->errores, 'span', array('class' => 'error'));

        // HTML del formulario
        $html = <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Usuario y contraseña</legend>
            <div>
                <label for="login">Usuario o Email:</label>
                <input id="login" type="text" name="login" value="$loginUsuario" />
                {$erroresCampos['login']}
            </div>
            <div>
                <label for="password">Contraseña:</label>
                <input id="password" type="password" name="password" />
                {$erroresCampos['password']}
            </div>
            <div>
                <button type="submit" name="login_submit">Entrar</button>
            </div>
        </fieldset>
        EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        
        $login = trim($datos['login'] ?? '');
        $login = filter_var($login, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ( ! $login || empty($login) ) {
            $this->errores['login'] = 'El nombre de usuario no puede estar vacío';
        }
        
        $password = trim($datos['password'] ?? '');
        if ( ! $password || empty($password) ) {
            $this->errores['password'] = 'El password no puede estar vacío.';
        }
        
        if (count($this->errores) === 0) {
            $user = user_find_by_username_or_email($login);
            
            if (!$user || !password_verify($password, (string)$user->getPasswordHash())) {
                $this->errores[] = "El usuario o el password no coinciden";
            } else {
                login_user($user); 
            }
        }
    }
}