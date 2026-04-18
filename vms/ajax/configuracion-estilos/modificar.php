<?php
/* ========================================================================== */
/* Archivo: ajax/configuracion-estilos/modificar.php                          */
/* Ruta: ajax/configuracion-estilos/modificar.php                             */
/* ========================================================================== */
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    include '../../lib/clsConsultas.php';
    $clsConsulta = new Consultas();

    $id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $valor   = isset($_POST['valor']) ? trim($_POST['valor']) : '';
    $estatus = isset($_POST['estatus']) ? trim($_POST['estatus']) : '';

    if ($id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Registro no válido'
        ]);
        exit;
    }

    if ($valor === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Ingresa un valor para la configuración'
        ]);
        exit;
    }

    if ($estatus !== 'activo' && $estatus !== 'inactivo') {
        echo json_encode([
            'success' => false,
            'message' => 'Selecciona un estatus válido'
        ]);
        exit;
    }

    $sqlExiste = "SELECT id, clave, tipo_control 
                  FROM configuracion_estilos 
                  WHERE id = " . $id . " 
                  LIMIT 1";
    $resExiste = $clsConsulta->consultaGeneral($sqlExiste);

    if ($clsConsulta->numrows <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'La configuración no existe'
        ]);
        exit;
    }

    $tipoControl = $resExiste[1]['tipo_control'];

    if ($tipoControl === 'color') {
        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $valor)) {
            echo json_encode([
                'success' => false,
                'message' => 'El valor del color no es válido'
            ]);
            exit;
        }
    }

    if ($tipoControl === 'number' || $tipoControl === 'range') {
        if (!is_numeric($valor)) {
            echo json_encode([
                'success' => false,
                'message' => 'El valor numérico no es válido'
            ]);
            exit;
        }
    }

    $valorSeguro = $clsConsulta->escape($valor);
    $estatusSeguro = $clsConsulta->escape($estatus);

    $sqlModificar = "UPDATE configuracion_estilos
                     SET valor = '" . $valorSeguro . "',
                         estatus = '" . $estatusSeguro . "',
                         updated_at = NOW()
                     WHERE id = " . $id;

    $clsConsulta->aplicaQuery($sqlModificar);

    echo json_encode([
        'success' => true,
        'message' => 'Configuración modificada correctamente'
    ]);
    exit;
} catch (Exception $e) {
    error_log('Error en ajax/configuracion-estilos/modificar.php: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'No fue posible modificar la configuración'
    ]);
    exit;
}
