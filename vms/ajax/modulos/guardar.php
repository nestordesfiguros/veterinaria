<?php
// ajax/modulos/guardar.php
require '../../lib/clsConsultas.php';
header('Content-Type: application/json');

$cls = new Consultas();
$conn = $cls->getConexion(); // MySQLi real

$id            = intval($_POST['id'] ?? 0);
$nombre        = trim($_POST['nombre'] ?? '');
$archivo       = trim($_POST['archivo'] ?? '');
$icono         = trim($_POST['icono'] ?? '');
$modulo_padre  = intval($_POST['modulo_padre'] ?? 0);
$tipo_modulo   = trim($_POST['tipo_modulo'] ?? '');
$canal         = trim($_POST['canal'] ?? 'erp');
$app_id        = trim($_POST['app_id'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');

if ($nombre === '' || $canal === '') {
    echo json_encode(['success' => false, 'message' => 'Datos obligatorios incompletos']);
    exit;
}

try {
    $conn->begin_transaction();

    if ($id > 0) {
        // ======================
        // MODIFICAR
        // ======================
        $datos = [
            'nombre'        => $nombre,
            'archivo'       => $archivo,
            'icono'         => $icono,
            'modulo_padre'  => $modulo_padre > 0 ? $modulo_padre : null,
            'tipo_modulo'   => $tipo_modulo,
            'canal'         => $canal,
            'app_id'        => $app_id !== '' ? $app_id : null,
            'observaciones' => $observaciones
        ];

        $cls->actualizarSeguro('modulos', $id, $datos);

        $conn->commit();
        echo json_encode(['success' => true, 'id' => $id]);
        exit;
    }

    // ======================
    // ALTA
    // ======================
    $datos = [
        'nombre'        => $nombre,
        'archivo'       => $archivo,
        'icono'         => $icono,
        'modulo_padre'  => $modulo_padre > 0 ? $modulo_padre : null,
        'tipo_modulo'   => $tipo_modulo,
        'canal'         => $canal,
        'app_id'        => $app_id !== '' ? $app_id : null,
        'observaciones' => $observaciones
    ];

    $cls->insertarSeguro('modulos', $datos);
    $idModulo = $cls->ultimoid;

    if ($idModulo <= 0) {
        throw new Exception('No se generó ID de módulo');
    }

    // ======================
    // PERMISOS AUTOMÁTICOS
    // ======================
    // Administrador SIEMPRE
    $rolAdmin = 1;

    $cls->aplicaQuery("
        INSERT INTO permisos_rol_modulo (rol, modulo, puede_ver)
        VALUES ($rolAdmin, $idModulo, 1)
        ON DUPLICATE KEY UPDATE puede_ver = 1
    ");

    $conn->commit();

    echo json_encode([
        'success' => true,
        'id'      => $idModulo
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar módulo',
        'error'   => $cls->obtenerError()
    ]);
}
