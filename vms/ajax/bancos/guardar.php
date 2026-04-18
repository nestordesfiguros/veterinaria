
<?php
/* ========================================================================== */
/* Archivo: ajax/bancos/guardar.php                                           */
/* Ruta: ajax/bancos/guardar.php                                              */
/* ========================================================================== */
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    include '../../lib/clsConsultas.php';
    $clsConsulta = new Consultas();

    $nombre_banco = isset($_POST['nombre_banco']) ? trim($_POST['nombre_banco']) : '';
    $clave_banco  = isset($_POST['clave_banco']) ? strtoupper(trim($_POST['clave_banco'])) : '';

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

    $nombre_banco_seguro = $clsConsulta->escape($nombre_banco);
    $clave_banco_seguro  = $clsConsulta->escape($clave_banco);

    $sqlExiste = "SELECT id FROM cat_bancos WHERE clave_banco = '" . $clave_banco_seguro . "' LIMIT 1";
    $resExiste = $clsConsulta->consultaGeneral($sqlExiste);

    if ($clsConsulta->numrows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe un banco con esa clave'
        ]);
        exit;
    }

    $sqlGuardar = "INSERT INTO cat_bancos (nombre_banco, clave_banco, status, created_at, updated_at)
                   VALUES ('" . $nombre_banco_seguro . "', '" . $clave_banco_seguro . "', 'activo', NOW(), NOW())";
    $clsConsulta->guardarGeneral($sqlGuardar);

    echo json_encode([
        'success' => true,
        'message' => 'Banco guardado correctamente'
    ]);
    exit;
} catch (Exception $e) {
    error_log('Error en ajax/bancos/guardar.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'No fue posible guardar el banco'
    ]);
    exit;
}
