<?php
/* ========================================================================== */
/* Archivo: ajax/bancos/modificar.php                                         */
/* Ruta: ajax/bancos/modificar.php                                            */
/* ========================================================================== */
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    include '../../lib/clsConsultas.php';
    $clsConsulta = new Consultas();

    $id           = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre_banco = isset($_POST['nombre_banco']) ? trim($_POST['nombre_banco']) : '';
    $clave_banco  = isset($_POST['clave_banco']) ? strtoupper(trim($_POST['clave_banco'])) : '';
    $status       = isset($_POST['status']) ? trim($_POST['status']) : '';

    if ($id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Registro no válido'
        ]);
        exit;
    }

    if ($nombre_banco == '') {
        echo json_encode([
            'success' => false,
            'message' => 'Ingresa el nombre del banco'
        ]);
        exit;
    }

    if ($clave_banco == '') {
        echo json_encode([
            'success' => false,
            'message' => 'Ingresa la clave del banco'
        ]);
        exit;
    }

    if (strlen($clave_banco) != 3) {
        echo json_encode([
            'success' => false,
            'message' => 'La clave del banco debe tener 3 caracteres'
        ]);
        exit;
    }

    if ($status != 'activo' && $status != 'inactivo') {
        echo json_encode([
            'success' => false,
            'message' => 'Selecciona un estatus válido'
        ]);
        exit;
    }

    $nombre_banco_seguro = $clsConsulta->escape($nombre_banco);
    $clave_banco_seguro  = $clsConsulta->escape($clave_banco);
    $status_seguro       = $clsConsulta->escape($status);

    $sqlExiste = "SELECT id FROM cat_bancos WHERE clave_banco = '" . $clave_banco_seguro . "' AND id <> " . $id . " LIMIT 1";
    $resExiste = $clsConsulta->consultaGeneral($sqlExiste);

    if ($clsConsulta->numrows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe otro banco con esa clave'
        ]);
        exit;
    }

    $sqlBanco = "SELECT id FROM cat_bancos WHERE id = " . $id . " LIMIT 1";
    $resBanco = $clsConsulta->consultaGeneral($sqlBanco);

    if ($clsConsulta->numrows <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El banco no existe'
        ]);
        exit;
    }

    $sqlModificar = "UPDATE cat_bancos
                     SET nombre_banco = '" . $nombre_banco_seguro . "',
                         clave_banco = '" . $clave_banco_seguro . "',
                         status = '" . $status_seguro . "',
                         updated_at = NOW()
                     WHERE id = " . $id;
    $clsConsulta->aplicaQuery($sqlModificar);

    echo json_encode([
        'success' => true,
        'message' => 'Banco modificado correctamente'
    ]);
    exit;
} catch (Exception $e) {
    error_log('Error en ajax/bancos/modificar.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'No fue posible modificar el banco'
    ]);
    exit;
}
