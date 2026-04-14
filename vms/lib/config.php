<?php

// Definición de variables reutilizables de conexión
/* #################  */
// catsa producción
/* #################  */

/*
$dbHost = 'localhost';
$dbUser = = 'hostin13_admin';
$dbPass = 'D3sf1gur0s';
$dbName = 'vetarinaria';
$dbPort = 3306;
*/

/* #################  */
// Desarrollo
/* #################  */

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'veterinaria';
$dbPort = 3306;

// Crear conexión con MySQL
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

// Verificar errores
if ($mysqli->connect_errno) {
    echo "Fallo al conectar a MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit;
}

$mysqli->set_charset("utf8mb4");
