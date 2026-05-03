<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/RecompensaDAO.php';
require_role('gerente');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    RecompensaDAO::delete($id);
}
header('Location: listarRecompensas.php');
exit;
