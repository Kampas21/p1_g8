<?php

require_once __DIR__ . '/config.php';

$conn = crearConexion();



function crearConexion() {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        // En local usaríamos throw $e o die, 
        // pero imprimimos en pantalla para poder depurar en el VPS:
        die("Error crítico conectando a la base de datos: " . $e->getMessage());
    }
}
