<?php
/* Archivo: ajax/especies/modificar.php */
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
    $nombreEspecie = trim($_POST['nombre_especie'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estatus = trim($_POST['estatus'] ?? '');

    if ($id <= 0) {
        throw new Exception('El identificador de la especie no es válido.');
    }

    if ($nombreEspecie === '') {
        throw new Exception('El nombre de la especie es obligatorio.');
    }

    if (!in_array($estatus, ['activo', 'inactivo'], true)) {
        throw new Exception('El estatus enviado no es válido.');
    }

    $registroActual = $clsConsulta->consultaPreparada(
        "SELECT id FROM cat_especies WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($registroActual)) {
        throw new Exception('La especie que intentas modificar no existe.');
    }

    $nombreEspecie = mb_strtoupper($nombreEspecie, 'UTF-8');
    $descripcion = ($descripcion !== '') ? mb_strtoupper($descripcion, 'UTF-8') : null;

    $duplicado = $clsConsulta->consultaPreparada(
        "SELECT id FROM cat_especies WHERE nombre_especie = ? AND id <> ? LIMIT 1",
        [$nombreEspecie, $id]
    );

    if (!empty($duplicado)) {
        throw new Exception('Ya existe otra especie con ese nombre.');
    }

    $datos = [
        'nombre_especie' => $nombreEspecie,
        'descripcion' => $descripcion,
        'estatus' => $estatus
    ];

    $clsConsulta->actualizarSeguro('cat_especies', $id, $datos);

    echo json_encode([
        'success' => true,
        'message' => 'Especie actualizada correctamente.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
