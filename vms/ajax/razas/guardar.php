<?php
/* Archivo: ajax/razas/guardar.php */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../lib/clsConsultas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);
    exit;
}

if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'La sesión ha expirado. Vuelve a iniciar sesión.'
    ]);
    exit;
}

try {
    $clsConsulta = new Consultas();

    $idEspecie = isset($_POST['id_especie']) ? (int)$_POST['id_especie'] : 0;
    $nombreRaza = trim($_POST['nombre_raza'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estatus = trim($_POST['estatus'] ?? '');

    if ($idEspecie <= 0) {
        throw new Exception('Selecciona una especie válida.');
    }

    if ($nombreRaza === '') {
        throw new Exception('El nombre de la raza es obligatorio.');
    }

    if (!in_array($estatus, ['activo', 'inactivo'], true)) {
        throw new Exception('El estatus enviado no es válido.');
    }

    $especieExiste = $clsConsulta->consultaPreparada(
        "SELECT id FROM cat_especies WHERE id = ? LIMIT 1",
        [$idEspecie]
    );

    if (empty($especieExiste)) {
        throw new Exception('La especie seleccionada no existe.');
    }

    $nombreRaza = mb_strtoupper($nombreRaza, 'UTF-8');
    $descripcion = ($descripcion !== '') ? mb_strtoupper($descripcion, 'UTF-8') : null;

    $duplicada = $clsConsulta->consultaPreparada(
        "SELECT id FROM cat_razas WHERE id_especie = ? AND nombre_raza = ? LIMIT 1",
        [$idEspecie, $nombreRaza]
    );

    if (!empty($duplicada)) {
        throw new Exception('Ya existe una raza con ese nombre para la especie seleccionada.');
    }

    $datos = [
        'id_especie' => $idEspecie,
        'nombre_raza' => $nombreRaza,
        'descripcion' => $descripcion,
        'estatus' => $estatus
    ];

    $resultado = $clsConsulta->insertarSeguro('cat_razas', $datos);

    if ($resultado <= 0) {
        throw new Exception('No fue posible guardar la raza.');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Raza guardada correctamente.',
        'id' => (int)$clsConsulta->ultimoid
    ]);
    exit;
} catch (Exception $e) {
    error_log('Error en ajax/razas/guardar.php: ' . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
