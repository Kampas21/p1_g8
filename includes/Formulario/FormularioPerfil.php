<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/UsuarioDAO.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';

class FormularioPerfil extends Formulario
{
    private $user;

    public function __construct()
    {
        $this->user = \current_user();

        parent::__construct('formPerfil', [
            'urlRedireccion' => RUTA_APP . '/vistas/usuarios/perfil.php',
            'enctype' => 'multipart/form-data'
        ]);
    }

    protected function generaCamposFormulario(&$datos)
    {
        $username = escaparHtml($datos['username'] ?? $this->user->getUsername() ?? '');
        $email = escaparHtml($datos['email'] ?? $this->user->getEmail() ?? '');
        $nombre = escaparHtml($datos['nombre'] ?? $this->user->getNombre() ?? '');
        $apellidos = escaparHtml($datos['apellidos'] ?? $this->user->getApellidos() ?? '');

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(
            ['username', 'email', 'nombre', 'apellidos', 'avatar'],
            $this->errores,
            'span',
            ['class' => 'error']
        );

        $chef = RUTA_APP . '/img/avatares/cocinero.png';
        $waiter = RUTA_APP . '/img/avatares/camarero.png';
        $manager = RUTA_APP . '/img/avatares/gerente.png';

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

    <div class="mt-16">
        <label>Avatar:</label><br><br>

        <label>
            <input type="radio" name="avatar_mode" value="keep" checked>
            Mantener avatar actual
        </label>

        <br><br>

        <label>
            <input type="radio" name="avatar_mode" value="default">
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
            class="input-archivo"
            accept="image/jpeg,image/png,image/webp,image/gif">

        {$erroresCampos['avatar']}
    </div>

    <div class="mt-20">
        <button type="submit" name="accion" value="guardar_perfil">Guardar Cambios</button>
    </div>
</fieldset>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeRadios = document.querySelectorAll('input[name="avatar_mode"]');
    const presetRadios = document.querySelectorAll('input[name="avatar_preset"]');

    function actualizarAvatares() {
        const checked = document.querySelector('input[name="avatar_mode"]:checked');

        if (!checked) return;

        if (checked.value === 'preset') {
            presetRadios.forEach(function (r) {
                r.disabled = false;
            });
        } else {
            presetRadios.forEach(function (r) {
                r.checked = false;
                r.disabled = true;
            });
        }
    }

    modeRadios.forEach(function (r) {
        r.addEventListener('change', actualizarAvatares);
    });

    actualizarAvatares();
});
</script>
EOF;
    }

    protected function procesaFormulario(&$datos)
    {
if (isset($_FILES['avatar_upload']) && $_FILES['avatar_upload']['error'] !== UPLOAD_ERR_NO_FILE) {
    $_POST['avatar_mode'] = 'upload';
}

        $this->errores = [];

        $datos['username'] = $datos['username'] ?? '';
        $datos['email'] = $datos['email'] ?? '';
        $datos['nombre'] = $datos['nombre'] ?? '';
        $datos['apellidos'] = $datos['apellidos'] ?? '';

        if (empty($_POST['avatar_mode'])) {
            $_POST['avatar_mode'] = 'keep';
        }

        list($clean, $erroresValidacion) = \UsuarioDAO::user_validate_data(
            $datos,
            false,
            $this->user->getId(),
            false
        );

        if (count($erroresValidacion) > 0) {
            $this->errores = $erroresValidacion;
        }

        if (count($this->errores) === 0) {
            try {
                $avatarChoice = \UsuarioDAO::resolve_avatar_choice_from_request($this->user, false);
            } catch (\RuntimeException $ex) {
                $this->errores['avatar'] = $ex->getMessage();
                return;
            }

            \UsuarioDAO::user_update($this->user->getId(), $clean, [
                'avatar_choice' => $avatarChoice,
                'allow_role' => false
            ]);

            \login_user(\UsuarioDAO::user_find_by_id((int)$this->user->getId()));
        }
    }
}