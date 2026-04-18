<?php
/* Archivo: ajax/razas/modificar.php */

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

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $idEspecie = isset($_POST['id_especie']) ? (int)$_POST['id_especie'] : 0;
    $nombreRaza = trim($_POST['nombre_raza'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estatus = trim($_POST['estatus'] ?? '');

    if ($id <= 0) {
        throw new Exception('El identificador de la raza no es válido.');
    }

    if ($idEspecie <= 0) {
        throw new Exception('Selecciona una especie válida.');
    }

    if ($nombreRaza === '') {
        throw new Exception('El nombre de la raza es obligatorio.');
    }

    if (!in_array($estatus, ['activo', 'inactivo'], true)) {
        throw new Exception('El estatus enviado no es válido.');
    }

    $registroActual = $clsConsulta->consultaPreparada(
        "SELECT id FROM cat_razas WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($registroActual)) {
        throw new Exception('La raza que intentas modificar no existe.');
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
        "SELECT id FROM cat_razas WHERE id_especie = ? AND nombre_raza = ? AND id <> ? LIMIT 1",
        [$idEspecie, $nombreRaza, $id]
    );

    if (!empty($duplicada)) {
        throw new Exception('Ya existe otra raza con ese nombre para la especie seleccionada.');
    }

    $datos = [
        'id_especie' => $idEspecie,
        'nombre_raza' => $nombreRaza,
        'descripcion' => $descripcion,
        'estatus' => $estatus
    ];

    $clsConsulta->actualizarSeguro('cat_razas', $id, $datos);

    echo json_encode([
        'success' => true,
        'message' => 'Raza actualizada correctamente.'
    ]);
    exit;
} catch (Exception $e) {
    error_log('Error en ajax/razas/modificar.php: ' . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
