<?php

require_once __DIR__ . '/config.php';

$conn = crearConexion();



function crearConexion() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        return $conn;
    } catch (Exception $e) {
        // En local usaríamos throw $e o die, 
        // pero imprimimos en pantalla para poder depurar en el VPS:
        die("Error crítico conectando a la base de datos: " . $e->getMessage());
    }
}

?>

