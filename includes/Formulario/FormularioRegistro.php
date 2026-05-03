<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/UsuarioDAO.php';
require_once __DIR__ . '/../../includes/auth.php';

class FormularioRegistro extends Formulario
{
    public function __construct() {
        parent::__construct(
            'formRegistro',
            [
                'urlRedireccion' => RUTA_APP.'/vistas/usuarios/perfil.php',
                'enctype' => 'multipart/form-data'
            ]
        );
    }

    protected function generaCamposFormulario(&$datos)
    {
        // 🔒 Escapar SIEMPRE
        $username = htmlspecialchars($datos['username'] ?? '', ENT_QUOTES);
        $email = htmlspecialchars($datos['email'] ?? '', ENT_QUOTES);
        $nombre = htmlspecialchars($datos['nombre'] ?? '', ENT_QUOTES);
        $apellidos = htmlspecialchars($datos['apellidos'] ?? '', ENT_QUOTES);

        // ✅ rutas correctas (IMPORTANTE para VPS)
        $chef = RUTA_APP . '/img/avatares/cocinero.png';
        $waiter = RUTA_APP . '/img/avatares/camarero.png';
        $manager = RUTA_APP . '/img/avatares/gerente.png';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        $erroresCampos = self::generaErroresCampos(
            ['username','email','nombre','apellidos','password','password_confirm','avatar'],
            $this->errores,
            'span',
            ['class'=>'error']
        );

return <<<HTML
$htmlErroresGlobales

<fieldset>
<legend>Crea tu cuenta de Cliente</legend>

<div>
<label>Usuario (mínimo 3 caracteres):</label>
<input type="text" name="username" value="$username" required minlength="3">
{$erroresCampos['username']}
</div>

<div>
<label>Email:</label>
<input type="email" name="email" value="$email" required>
{$erroresCampos['email']}
</div>

<div>
<label>Nombre:</label>
<input type="text" name="nombre" value="$nombre" required>
{$erroresCampos['nombre']}
</div>

<div>
<label>Apellidos:</label>
<input type="text" name="apellidos" value="$apellidos" required>
{$erroresCampos['apellidos']}
</div>

<div>
<label>Contraseña:</label>
<input type="password" name="password" required minlength="6">
{$erroresCampos['password']}
</div>

<div>
<label>Confirmar contraseña:</label>
<input type="password" name="password_confirm" required minlength="6">
{$erroresCampos['password_confirm']}
</div>

<div class="mt-16">
<label>Avatar:</label><br><br>

<label>
<input type="radio" name="avatar_mode" value="default" checked>
Avatar por defecto
</label>

<br><br>

<label>
<input type="radio" name="avatar_mode" value="preset">
Avatar predefinido
</label>

<div class="avatar-preset-grid">
<label>
<input type="radio" name="avatar_preset" value="preset_chef" disabled>
<img src="$chef" width="70">
</label>

<label>
<input type="radio" name="avatar_preset" value="preset_waiter" disabled>
<img src="$waiter" width="70">
</label>

<label>
<input type="radio" name="avatar_preset" value="preset_manager" disabled>
<img src="$manager" width="70">
</label>
</div>

<br>

<label>
<input type="radio" name="avatar_mode" value="upload">
Subir imagen propia
</label>

<input
type="file"
name="avatar_upload"
accept="image/jpeg,image/png,image/webp,image/gif">

{$erroresCampos['avatar']}
</div>

<div class="mt-16">
<button type="submit" class="btn primary">Registrarme</button>
</div>

</fieldset>

<script>
document.addEventListener('DOMContentLoaded',function(){

const modeRadios = document.querySelectorAll('input[name="avatar_mode"]');
const presetRadios = document.querySelectorAll('input[name="avatar_preset"]');

function actualizarAvatares(){
    const checked = document.querySelector('input[name="avatar_mode"]:checked');
    if(!checked) return;

    if(checked.value === 'preset'){
        presetRadios.forEach(r => r.disabled = false);
    } else {
        presetRadios.forEach(r => {
            r.checked = false;
            r.disabled = true;
        });
    }
}

modeRadios.forEach(r => r.addEventListener('change', actualizarAvatares));
actualizarAvatares();

});
</script>
HTML;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        // Sanitizar
        $datos['username'] = trim($datos['username'] ?? '');
        $datos['email'] = trim($datos['email'] ?? '');
        $datos['nombre'] = trim($datos['nombre'] ?? '');
        $datos['apellidos'] = trim($datos['apellidos'] ?? '');

        list($clean,$erroresValidacion) =
            \UsuarioDAO::user_validate_data($datos, true, null, false);

        if(count($erroresValidacion) > 0){
            $this->errores = $erroresValidacion;
        }

        $pwd1 = $datos['password'] ?? '';
        $pwd2 = $datos['password_confirm'] ?? '';

        if(strlen($pwd1) < 6){
            $this->errores['password'] = 'La contraseña debe tener al menos 6 caracteres.';
        }

        if($pwd1 !== $pwd2){
            $this->errores['password_confirm'] = 'Las contraseñas no coinciden.';
        }

        if(count($this->errores) === 0){

            $clean['rol'] = 'cliente';
            $clean['password'] = $pwd1;

            try {
                $avatarDetails =
                    \UsuarioDAO::resolve_avatar_choice_from_request(null, true);
            }
            catch(\RuntimeException $ex){
                $this->errores['avatar'] = $ex->getMessage();
                return;
            }

            $newId = \UsuarioDAO::user_create($clean, $avatarDetails);

            if($newId){
                $user = \UsuarioDAO::user_find_by_id($newId);
                login_user($user);
            } else {
                $this->errores['global'] = 'Error interno al crear el usuario.';
            }
        }
    }
}