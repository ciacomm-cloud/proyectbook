<?php
// Parámetros de conexión al servidor local de Contabo
$host = "127.0.0.1"; 
$usuario = "dbproyectbook";
$password = "QNPlczONHJ4sustr2EW5";
$base_datos = "proyectbookdb";

// Crear la conexión
$conexion = new mysqli($host, $usuario, $password, $base_datos);

// Forzar el uso de UTF-8 para que no se rompan los acentos ni las ñ
$conexion->set_charset("utf8mb4");

// Sincronizar PHP a la hora del centro de México
date_default_timezone_set('America/Mexico_City');

// Sincronizar la Base de Datos a UTC-6 (Hora estándar de México)
$conexion->query("SET time_zone = '-06:00'");

// Diagnóstico estricto: Si hay un error, el sistema se detiene y nos avisa
if ($conexion->connect_error) {
    die("Error crítico de conexión: " . $conexion->connect_error);
}
?>