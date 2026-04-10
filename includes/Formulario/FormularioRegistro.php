<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';

class FormularioRegistro extends Formulario
{
    public function __construct() {
        parent::__construct('formRegistro', [
        'urlRedireccion' => RUTA_APP.'/vistas/usuarios/perfil.php','enctype' => 'multipart/form-data']);
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
            ['username', 'email', 'nombre', 'apellidos', 'password', 'password_confirm', 'avatar'], 
            $this->errores, 'span', array('class' => 'error')
        );

        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Crea tu cuenta de Cliente</legend>
            <div>
                <label for="username">Usuario (Mínimo 3 caracteres):</label>
                <input id="username" type="text" name="username" value="$username" required minlength="3" />
                {$erroresCampos['username']}
            </div>
            <div>
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" value="$email" required />
                {$erroresCampos['email']}
            </div>
            <div>
                <label for="nombre">Nombre:</label>
                <input id="nombre" type="text" name="nombre" value="$nombre" required />
                {$erroresCampos['nombre']}
            </div>
            <div>
                <label for="apellidos">Apellidos:</label>
                <input id="apellidos" type="text" name="apellidos" value="$apellidos" required />
                {$erroresCampos['apellidos']}
            </div>
            <div>
                <label for="password">Contraseña (Mínimo 6 caracteres):</label>
                <input id="password" type="password" name="password" required minlength="6" />
                {$erroresCampos['password']}
            </div>
            <div>
                <label for="password_confirm">Confirmar Contraseña:</label>
                <input id="password_confirm" type="password" name="password_confirm" required minlength="6" />
                {$erroresCampos['password_confirm']}
            </div>
            <div>
                <label>Foto de Perfil (Opcional):</label>
                <input type="file" name="avatar_pers" class="input-archivo">
                {$erroresCampos['avatar']}
            </div>
            <div class="mt-16">
                <button type="submit" name="registro_submit" class="btn primary">Registrarme</button>
            </div>
        </fieldset>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        // Saneamiento de los datos de entrada (Requerimiento estricto P2)
        $datos['username'] = filter_var($datos['username'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $datos['email'] = filter_var($datos['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $datos['nombre'] = filter_var($datos['nombre'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $datos['apellidos'] = filter_var($datos['apellidos'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

        // Validación y delegación en la función core
        list($clean, $erroresValidacion) = user_validate_data($datos, true, null, false);
        
        if (count($erroresValidacion) > 0) {
            $this->errores = $erroresValidacion;
        }

        // Comprobación reforzada de contraseñas
        $pwd1 = $datos['password'] ?? '';
        $pwd2 = $datos['password_confirm'] ?? '';
        
        if (mb_strlen($pwd1) < 6) {
            $this->errores['password'] = 'La contraseña debe tener al menos 6 caracteres.';
        }
        if ($pwd1 !== $pwd2) {
            $this->errores['password_confirm'] = 'Las contraseñas no coinciden.';
        }

        if (count($this->errores) === 0) {
            $clean['rol'] = 'cliente';
            $clean['password'] = $pwd1;
            
            // Replicamos la misma lógica brillante que usasteis en el Perfil
            $avatarDetails = ['type' => 'default']; 
            
            if (isset($_FILES['avatar_pers']) && $_FILES['avatar_pers']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Truco para que vuestra función core funcione
                $_POST['avatar_mode'] = 'upload';
                $_FILES['avatar_upload'] = $_FILES['avatar_pers'];
                
                try {
                    $avatarDetails = \resolve_avatar_choice_from_request([
                        'avatar_tipo' => 'default',
                        'avatar_valor' => ''
                    ], false);           
                } catch (\RuntimeException $ex) {
                    $this->errores['avatar'] = $ex->getMessage();
                    return; // Detenemos el registro si la imagen falla (pesa mucho, etc)
                }
            }
            
            $newId = user_create($clean, $avatarDetails);
            
            if ($newId) {
                $user = user_find_by_id($newId);
                login_user($user);
            } else {
                $this->errores['global'] = "Error interno al crear el usuario en la BD.";
            }
        }
    }
}
