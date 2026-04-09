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
            <legend>Introduce tus datos</legend>
            <div class="mt-10">
                <label for="login">Usuario o Email:</label>
                <input id="login" type="text" name="login" value="$loginUsuario" required class="w-100" />
                {$erroresCampos['login']}
            </div>
            <div class="mt-10">
                <label for="password">Contraseña (Mínimo 6 caracteres):</label>
                <input id="password" type="password" name="password" required minlength="6" class="w-100" />
                {$erroresCampos['password']}
            </div>
            <div class="mt-20">
                <button type="submit" name="login_submit" class="btn primary">Entrar</button>
            </div>
        </fieldset>
        EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        
        // Saneamiento de datos
        $loginRaw = $datos['login'] ?? '';
        $login = filter_var(trim($loginRaw), FILTER_SANITIZE_SPECIAL_CHARS);
        
        if ( ! $login || empty($login) ) {
            $this->errores['login'] = 'El nombre de usuario o email no puede estar vacío.';
        }
        
        $password = trim($datos['password'] ?? '');
        if ( mb_strlen($password) < 6 ) {
            $this->errores['password'] = 'La contraseña no puede tener menos de 6 caracteres.';
        }
        
        if (count($this->errores) === 0) {
            $user = user_find_by_username_or_email($login);
            
            
            if (!$user || !password_verify($password, $user->getPasswordHash())) {
                $this->errores['global'] = "El usuario o el password introducido no son correctos.";
            } else {
                login_user($user); 
            }
        }
    }
}