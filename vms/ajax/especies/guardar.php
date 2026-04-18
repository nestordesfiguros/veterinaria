<?php
/* Archivo: ajax/especies/guardar.php */
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

    $nombreEspecie = trim($_POST['nombre_especie'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estatus = trim($_POST['estatus'] ?? 'activo');

    if ($nombreEspecie === '') {
        throw new Exception('El nombre de la especie es obligatorio.');
    }

    if (!in_array($estatus, ['activo', 'inactivo'], true)) {
        throw new Exception('El estatus enviado no es válido.');
    }

    $nombreEspecie = mb_strtoupper($nombreEspecie, 'UTF-8');
    $descripcion = ($descripcion !== '') ? mb_strtoupper($descripcion, 'UTF-8') : null;

    $existe = $clsConsulta->consultaPreparada(
        "SELECT id FROM cat_especies WHERE nombre_especie = ? LIMIT 1",
        [$nombreEspecie]
    );

    if (!empty($existe)) {
        throw new Exception('Ya existe una especie con ese nombre.');
    }

    $datos = [
        'nombre_especie' => $nombreEspecie,
        'descripcion' => $descripcion,
        'estatus' => $estatus
    ];

    $resultado = $clsConsulta->insertarSeguro('cat_especies', $datos);

    if ($resultado <= 0) {
        throw new Exception('No fue posible guardar la especie.');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Especie guardada correctamente.',
        'id' => (int)$clsConsulta->ultimoid
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
