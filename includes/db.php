<?php
$conn = new mysqli("localhost", "root", "", "bistrofdi_g8");

if ($conn->connect_error) {
    die("Error conexión: " . $conn->connect_error);
}