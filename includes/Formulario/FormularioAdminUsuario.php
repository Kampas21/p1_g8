<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/UsuarioDAO.php';
require_once __DIR__ . '/../../includes/util.php';

class FormularioAdminUsuario extends Formulario
{
    private $isCreate;
    private $userToEdit;

    public function __construct(bool $isCreate, ?\Usuario $userToEdit = null) { 
        $this->isCreate = $isCreate;
        $this->userToEdit = $userToEdit;
        
        $opciones = ['enctype' => 'multipart/form-data'];
        
        if (!$isCreate && $userToEdit) {
            $opciones['urlRedireccion'] = RUTA_APP.'/vistas/usuarios/usuario_ver.php?id='.$userToEdit->getId();
        } else {
            $opciones['urlRedireccion'] = RUTA_APP.'/vistas/usuarios/usuarios.php';
        }

        parent::__construct('formAdminUsuario', $opciones);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        $username = $datos['username'] ?? ($this->userToEdit ? $this->userToEdit->getUsername() : '');
        $email = $datos['email'] ?? ($this->userToEdit ? $this->userToEdit->getEmail() : '');
        $nombre = $datos['nombre'] ?? ($this->userToEdit ? $this->userToEdit->getNombre() : '');
        $apellidos = $datos['apellidos'] ?? ($this->userToEdit ? $this->userToEdit->getApellidos() : '');
        $rol = $datos['rol'] ?? ($this->userToEdit ? $this->userToEdit->getRol() : 'cliente');

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(
            ['username', 'email', 'nombre', 'apellidos', 'rol', 'password', 'avatar'], 
            $this->errores, 'span', array('class' => 'error')
        );

        $htmlPass = "";
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
        
        // Generador de opciones de rol dinámicas
        $htmlRoles = "";
        foreach (\UsuarioDAO::valid_roles() as $r) {
            $sel = ($r === $rol) ? 'selected' : '';
            $lbl = \UsuarioDAO::role_label($r);
            $htmlRoles .= "<option value=\"$r\" $sel>$lbl</option>";
        }

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
                <label>Foto / Avatar:</label>
                <input type="file" name="avatar_pers" class="input-archivo">
                <small>(Opcional. Si seleccionas uno, reemplazará al que tenga)</small>
                {$erroresCampos['avatar']}
            </div>
            <div class="mt-20">
                <button type="submit" class="boton-primario">Guardar Usuario</button>
            </div>
        </fieldset>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        // Forzar avatar upload mode si seleccionan fichero
                if (isset($_FILES['avatar_pers']) && $_FILES['avatar_pers']['error'] !== UPLOAD_ERR_NO_FILE) {
            $_POST['avatar_mode'] = 'upload';
            $_FILES['avatar_upload'] = $_FILES['avatar_pers'];
        } else {
            // Si el gerente cambia el rol y no sube foto, forzamos un Avatar Predefinido automático
            $rolSeleccionado = $_POST['rol'] ?? 'cliente';
            $rolActual = $this->userToEdit ? $this->userToEdit->getRol() : 'cliente';
            
            if ($rolSeleccionado !== $rolActual || $this->isCreate) {
                $_POST['avatar_mode'] = 'preset';
                
                
                if ($rolSeleccionado === 'gerente') $_POST['avatar_preset'] = 'preset_manager';
                elseif ($rolSeleccionado === 'cocinero') $_POST['avatar_preset'] = 'preset_chef';
                elseif ($rolSeleccionado === 'camarero') $_POST['avatar_preset'] = 'preset_waiter';
                else {
                    $_POST['avatar_mode'] = 'default';
                }
            } else {
                // Si el gerente cambia el rol y no sube foto, forzamos un Avatar Predefinido automático
            $rolSeleccionado = $_POST['rol'] ?? 'cliente';
            $rolActual = $this->userToEdit ? $this->userToEdit->getRol() : 'cliente';
            
            if ($rolSeleccionado !== $rolActual || $this->isCreate) {
                $_POST['avatar_mode'] = 'preset';
                
                
                if ($rolSeleccionado === 'gerente') $_POST['avatar_preset'] = 'preset_manager';
                elseif ($rolSeleccionado === 'cocinero') $_POST['avatar_preset'] = 'preset_chef';
                elseif ($rolSeleccionado === 'camarero') $_POST['avatar_preset'] = 'preset_waiter';
                else {
                    $_POST['avatar_mode'] = 'default';
                }
            } else {
                $_POST['avatar_mode'] = 'keep'; // Si no hay cambio de rol y no sube foto, se mantiene igual
            }
            }
        }

        $ignoreId = $this->isCreate ? null : (int)$this->userToEdit->getId()    ;
        
        list($clean, $erroresValidacion) = \UsuarioDAO::user_validate_data($_POST, $this->isCreate, $ignoreId, true);
        
        if (count($erroresValidacion) > 0) {
            $this->errores = $erroresValidacion;
        }

        if (count($this->errores) === 0) {
            try {
                $avatarChoice = \UsuarioDAO::resolve_avatar_choice_from_request($this->userToEdit, $this->isCreate);
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
