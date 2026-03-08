<?php

require_once __DIR__ . '/../includes/application.php';

$sql = "SELECT * FROM productos";
$resultado = $conn->query($sql);

while ($fila = $resultado->fetch_assoc()) {
    echo $fila['nombre'] . " - " . $fila['descripcion'] . " - " . 
    $fila['categoria_id'] . " - " . $fila['precio_base'] . " - " . $fila['iva'] 
    . " - " . $fila['disponible'] . " - " . $fila['ofertado'] . "<br>";
}

?>