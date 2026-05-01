<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/UsuarioDAO.php';
require_once __DIR__ . '/../../includes/util.php';

class FormularioAdminUsuario extends Formulario
{
    private $isCreate;
    private $userToEdit;

    public function __construct(bool $isCreate, ?\Usuario $userToEdit = null)
    {
        $this->isCreate = $isCreate;
        $this->userToEdit = $userToEdit;

        $opciones = ['enctype' => 'multipart/form-data'];

        if (!$isCreate && $userToEdit) {
            $opciones['urlRedireccion'] = RUTA_APP . '/vistas/usuarios/usuario_ver.php?id=' . $userToEdit->getId();
        } else {
            $opciones['urlRedireccion'] = RUTA_APP . '/vistas/usuarios/usuarios.php';
        }

        parent::__construct('formAdminUsuario', $opciones);
    }

    protected function generaCamposFormulario(&$datos)
    {
        $username = e($datos['username'] ?? ($this->userToEdit ? $this->userToEdit->getUsername() : ''));
        $email = e($datos['email'] ?? ($this->userToEdit ? $this->userToEdit->getEmail() : ''));
        $nombre = e($datos['nombre'] ?? ($this->userToEdit ? $this->userToEdit->getNombre() : ''));
        $apellidos = e($datos['apellidos'] ?? ($this->userToEdit ? $this->userToEdit->getApellidos() : ''));
        $rol = $datos['rol'] ?? ($this->userToEdit ? $this->userToEdit->getRol() : 'cliente');

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(
            ['username', 'email', 'nombre', 'apellidos', 'rol', 'password', 'avatar'],
            $this->errores,
            'span',
            ['class' => 'error']
        );

        if ($this->isCreate) {
            $htmlPass = <<<EOF
            <div>
                <label for="password">Contraseña (Obligatoria):</label>
                <input id="password" type="password" name="password" required />
                {$erroresCampos['password']}
            </div>
            EOF;
        } else {
            $htmlPass = <<<EOF
            <div>
                <label for="password">Nueva contraseña (Opcional):</label>
                <input id="password" type="password" name="password" />
                <small>Déjalo en blanco para mantener la actual.</small>
                {$erroresCampos['password']}
            </div>
            EOF;
        }

        $htmlRoles = "";
        foreach (\UsuarioDAO::valid_roles() as $r) {
            $sel = ($r === $rol) ? 'selected' : '';
            $lbl = \UsuarioDAO::role_label($r);
            $htmlRoles .= "<option value=\"$r\" $sel>$lbl</option>";
        }

        $chef = RUTA_APP . '/img/avatares/cocinero.png';
        $waiter = RUTA_APP . '/img/avatares/camarero.png';
        $manager = RUTA_APP . '/img/avatares/gerente.png';

        $modoInicial = $this->isCreate ? 'checked' : '';

return <<<EOF
$htmlErroresGlobales

<fieldset>
    <legend>Datos del usuario</legend>

    <div class="form-grid">
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
            <label for="rol">Rol en Bistro FDI:</label>
            <select id="rol" name="rol">
                $htmlRoles
            </select>
            {$erroresCampos['rol']}
        </div>

        $htmlPass
    </div>

    <div class="mt-16">
        <label>Avatar:</label><br><br>

        <label>
            <input type="radio" name="avatar_mode" value="auto" $modoInicial>
            Avatar automático según rol
        </label>

        <br><br>

        <label>
            <input type="radio" name="avatar_mode" value="keep">
            Mantener actual
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

        <div style="display:flex; gap:20px; margin-top:10px;">
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
        <button type="submit" class="boton-primario">Guardar Usuario</button>
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

            $this->errores = [];

if ($this->isCreate) {
    $_POST['password_confirm'] = $_POST['password'] ?? '';
}

if (isset($_FILES['avatar_upload']) && $_FILES['avatar_upload']['error'] !== UPLOAD_ERR_NO_FILE) {
    $_POST['avatar_mode'] = 'upload';
}

        $mode = $_POST['avatar_mode'] ?? ($this->isCreate ? 'auto' : 'keep');

        if ($mode === 'auto') {
            $rolSeleccionado = $_POST['rol'] ?? 'cliente';

            if ($rolSeleccionado === 'gerente') {
                $_POST['avatar_mode'] = 'preset';
                $_POST['avatar_preset'] = 'preset_manager';
            } elseif ($rolSeleccionado === 'cocinero') {
                $_POST['avatar_mode'] = 'preset';
                $_POST['avatar_preset'] = 'preset_chef';
            } elseif ($rolSeleccionado === 'camarero') {
                $_POST['avatar_mode'] = 'preset';
                $_POST['avatar_preset'] = 'preset_waiter';
            } else {
                $_POST['avatar_mode'] = 'default';
            }
        }

        $ignoreId = $this->isCreate ? null : (int)$this->userToEdit->getId();

        list($clean, $erroresValidacion) =
            \UsuarioDAO::user_validate_data($_POST, $this->isCreate, $ignoreId, true);

        if (count($erroresValidacion) > 0) {
            $this->errores = $erroresValidacion;
        }

        if (count($this->errores) === 0) {
            try {
                $avatarChoice =
                    \UsuarioDAO::resolve_avatar_choice_from_request($this->userToEdit, $this->isCreate);
            } catch (\RuntimeException $ex) {
                $this->errores['avatar'] = $ex->getMessage();
                return;
            }

            if ($this->isCreate) {
                \UsuarioDAO::user_create($clean, $avatarChoice);
                \flash_set('success', 'Usuario creado con éxito.');
            } else {
                \UsuarioDAO::user_update((int)$this->userToEdit->getId(), $clean, [
                    'avatar_choice' => $avatarChoice,
                    'allow_role' => true
                ]);
                \flash_set('success', 'Usuario actualizado con éxito.');
            }
        }
    }
}

