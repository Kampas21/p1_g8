<?php
require_once __DIR__ . '/../../includes/application.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? null;

    if ($id) {
        global $conn;

        
        $stmt = $conn->prepare("UPDATE productos SET ofertado = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

header("Location: ../../vistas/productos/productosList.php");
exit;