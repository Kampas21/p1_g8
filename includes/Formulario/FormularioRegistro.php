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
        $username = $datos['username'] ?? '';
        $email = $datos['email'] ?? '';
        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        $erroresCampos = self::generaErroresCampos(
            ['username','email','nombre','apellidos','password','password_confirm','avatar'],
            $this->errores,
            'span',
            ['class'=>'error']
        );

return <<<EOF
$htmlErroresGlobales

<fieldset>

<legend>Crea tu cuenta de Cliente</legend>

<div>
<label for="username">Usuario (mínimo 3 caracteres):</label>
<input id="username" type="text" name="username" value="$username" required minlength="3">
{$erroresCampos['username']}
</div>

<div>
<label for="email">Email:</label>
<input id="email" type="email" name="email" value="$email" required>
{$erroresCampos['email']}
</div>

<div>
<label for="nombre">Nombre:</label>
<input id="nombre" type="text" name="nombre" value="$nombre" required>
{$erroresCampos['nombre']}
</div>

<div>
<label for="apellidos">Apellidos:</label>
<input id="apellidos" type="text" name="apellidos" value="$apellidos" required>
{$erroresCampos['apellidos']}
</div>

<div>
<label for="password">Contraseña:</label>
<input id="password" type="password" name="password" required minlength="6">
{$erroresCampos['password']}
</div>

<div>
<label for="password_confirm">Confirmar contraseña:</label>
<input id="password_confirm" type="password" name="password_confirm" required minlength="6">
{$erroresCampos['password_confirm']}
</div>


<div>

<label>Avatar:</label>

<br><br>

<label>
<input type="radio" name="avatar_mode" value="default" checked>
Avatar por defecto
</label>

<br><br>

<label>
<input type="radio" name="avatar_mode" value="preset">
Avatar predefinido
</label>

<div style="display:flex;gap:20px;margin-top:10px;">

<label>
<input
type="radio"
name="avatar_preset"
value="preset_chef"
disabled>
<img src="/P1/p1_g8/img/avatares/cocinero.png" width="70">
</label>

<label>
<input
type="radio"
name="avatar_preset"
value="preset_waiter"
disabled>
<img src="/P1/p1_g8/img/avatares/camarero.png" width="70">
</label>

<label>
<input
type="radio"
name="avatar_preset"
value="preset_manager"
disabled>
<img src="/P1/p1_g8/img/avatares/gerente.png" width="70">
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
class="input-archivo"
accept="image/jpeg,image/png,image/webp,image/gif">

{$erroresCampos['avatar']}

</div>


<div class="mt-16">
<button type="submit" name="registro_submit" class="btn primary">
Registrarme
</button>
</div>

</fieldset>


<script>
document.addEventListener('DOMContentLoaded',function(){

const modeRadios=
document.querySelectorAll('input[name="avatar_mode"]');

const presetRadios=
document.querySelectorAll('input[name="avatar_preset"]');

function actualizarAvatares(){

const checked=
document.querySelector(
'input[name="avatar_mode"]:checked'
);

if(!checked)return;

if(checked.value==='preset'){

presetRadios.forEach(function(r){
r.disabled=false;
});

}
else{

presetRadios.forEach(function(r){
r.checked=false;
r.disabled=true;
});

}

}

modeRadios.forEach(function(r){
r.addEventListener(
'change',
actualizarAvatares
);
});

actualizarAvatares();

});
</script>

EOF;

    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores=[];

        $datos['username']=filter_var(
            $datos['username'] ?? '',
            FILTER_SANITIZE_SPECIAL_CHARS
        );

        $datos['email']=filter_var(
            $datos['email'] ?? '',
            FILTER_SANITIZE_EMAIL
        );

        $datos['nombre']=filter_var(
            $datos['nombre'] ?? '',
            FILTER_SANITIZE_SPECIAL_CHARS
        );

        $datos['apellidos']=filter_var(
            $datos['apellidos'] ?? '',
            FILTER_SANITIZE_SPECIAL_CHARS
        );

        list($clean,$erroresValidacion)=
            \UsuarioDAO::user_validate_data(
                $datos,
                true,
                null,
                false
            );

        if(count($erroresValidacion)>0){
            $this->errores=$erroresValidacion;
        }

        $pwd1=$datos['password'] ?? '';
        $pwd2=$datos['password_confirm'] ?? '';

        if(mb_strlen($pwd1)<6){
            $this->errores['password']=
                'La contraseña debe tener al menos 6 caracteres.';
        }

        if($pwd1!==$pwd2){
            $this->errores['password_confirm']=
                'Las contraseñas no coinciden.';
        }

        if(count($this->errores)===0){

            $clean['rol']='cliente';
            $clean['password']=$pwd1;

            try{

                $avatarDetails=
                    \UsuarioDAO::resolve_avatar_choice_from_request(
                        null,
                        true
                    );

            }
            catch(\RuntimeException $ex){

                $this->errores['avatar']=
                    $ex->getMessage();

                return;
            }

            $newId=
                \UsuarioDAO::user_create(
                    $clean,
                    $avatarDetails
                );

            if($newId){

                $user=
                    \UsuarioDAO::user_find_by_id(
                        $newId
                    );

                login_user($user);

            }
            else{

                $this->errores['global']=
                    'Error interno al crear el usuario en la BD.';
            }

        }
    }
}