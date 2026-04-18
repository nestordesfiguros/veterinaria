<?php
// ajax/modulos/accion-guardar.php

require '../../lib/clsConsultas.php';
header('Content-Type: application/json');

$cls = new Consultas();

$id          = intval($_POST['id'] ?? 0);
$modulo_id   = intval($_POST['modulo_id'] ?? 0);
$nombre      = trim($_POST['nombre'] ?? '');
$clave       = trim($_POST['clave'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$mod_dest    = intval($_POST['modulo_destino'] ?? 0);

if ($modulo_id <= 0 || $nombre === '' || $clave === '') {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    if ($id > 0) {
        $datos = [
            'nombre'          => $nombre,
            'clave'           => $clave,
            'descripcion'     => $descripcion,
            'modulo_destino'  => $mod_dest > 0 ? $mod_dest : null,
            'estatus'         => 1
        ];

        $cls->actualizarSeguro('acciones_modulo', $id, $datos);
        echo json_encode(['success' => true]);
    } else {
        // validar duplicado
        $ex = $cls->consultaPreparada(
            "SELECT id FROM acciones_modulo WHERE modulo_id = ? AND clave = ? LIMIT 1",
            [$modulo_id, $clave],
            "is"
        );

        if (!empty($ex)) {
            echo json_encode(['success' => false, 'message' => 'La clave ya existe']);
            exit;
        }

        $datos = [
            'modulo_id'       => $modulo_id,
            'nombre'          => $nombre,
            'clave'           => $clave,
            'descripcion'     => $descripcion,
            'modulo_destino'  => $mod_dest > 0 ? $mod_dest : null,
            'estatus'         => 1
        ];

        $cls->insertarSeguro('acciones_modulo', $datos);
        echo json_encode(['success' => true, 'id' => $cls->ultimoid]);
    }
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar acción',
        'error'   => $cls->obtenerError()
    ]);
}
