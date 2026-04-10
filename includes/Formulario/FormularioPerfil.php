<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php'; 

class FormularioPerfil extends Formulario
{
    private $user;

    public function __construct() {
        
        $this->user = \current_user();
        parent::__construct('formPerfil', [
            'urlRedireccion' => RUTA_APP.'/vistas/usuarios/perfil.php',
            'enctype' => 'multipart/form-data'
        ]);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        $username = $datos['username'] ?? $this->user->getUsername() ?? '';
        $email = $datos['email'] ?? $this->user->getEmail() ?? '';
        $nombre = $datos['nombre'] ?? $this->user->getNombre() ?? '';
        $apellidos = $datos['apellidos'] ?? $this->user->getApellidos() ?? '';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(
            ['username', 'email', 'nombre', 'apellidos', 'avatar'], 
            $this->errores, 'span', array('class' => 'error')
        );


        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Datos personales</legend>
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
                <label>Cambiar mi foto / avatar personal:</label><br>
                <small>(Si seleccionas una imagen, sobreescribirá la actual)</small><br>
                <input type="file" name="avatar_pers" class="input-archivo">
                {$erroresCampos['avatar']}
            </div>
            <div>
                <button type="submit" name="accion" value="guardar_perfil">Guardar Cambios</button>
            </div>
        </fieldset>
        EOF;
    }

     protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $_POST['username'] = $datos['username'] ?? '';
        $_POST['email'] = $datos['email'] ?? '';
        $_POST['nombre'] = $datos['nombre'] ?? '';
        $_POST['apellidos'] = $datos['apellidos'] ?? '';
        
        
        if (isset($_FILES['avatar_pers']) && $_FILES['avatar_pers']['error'] !== UPLOAD_ERR_NO_FILE) {
            $_POST['avatar_mode'] = 'upload';
            $_FILES['avatar_upload'] = $_FILES['avatar_pers'];
        } else {
            $_POST['avatar_mode'] = 'keep';
        }
        
        list($clean, $erroresValidacion) = \user_validate_data($_POST, false, $this->user->getId(), false);
        
        if (count($erroresValidacion) > 0) {
            $this->errores = $erroresValidacion;
        }

        if (count($this->errores) === 0) {
            try {
                
         $avatarChoice = \resolve_avatar_choice_from_request([
         'avatar_tipo' => $this->user->getAvatarTipo(),
         'avatar_valor' => $this->user->getAvatarValor()
], false);           
         } catch (\RuntimeException $ex) {
                $this->errores['avatar'] = $ex->getMessage();
                return;
            }

            
            \user_update($this->user->getId(), $clean, [
                'avatar_choice' => $avatarChoice,
                'allow_role' => false
            ]);
            
            \login_user(\user_find_by_id((int)$this->user->getId()));
        }
    }
}