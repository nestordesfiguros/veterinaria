<?php
/* Archivo: ajax/configuracion-modulos/guardar.php */

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

    $moduloId = isset($_POST['modulo_id']) ? (int)$_POST['modulo_id'] : 0;
    $habilitado = isset($_POST['habilitado']) ? (int)$_POST['habilitado'] : 0;
    $visibleMenu = isset($_POST['visible_menu']) ? (int)$_POST['visible_menu'] : 0;
    $visibleBusqueda = isset($_POST['visible_busqueda']) ? (int)$_POST['visible_busqueda'] : 0;
    $obligatorio = isset($_POST['obligatorio']) ? (int)$_POST['obligatorio'] : 0;
    $forzarOcultoPadreOff = isset($_POST['forzar_oculto_si_padre_off']) ? (int)$_POST['forzar_oculto_si_padre_off'] : 0;
    $ordenOverride = isset($_POST['orden_override']) && $_POST['orden_override'] !== '' ? (int)$_POST['orden_override'] : null;
    $paqueteOrigen = trim($_POST['paquete_origen'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    if ($moduloId <= 0) {
        throw new Exception('Selecciona un módulo válido.');
    }

    foreach ([$habilitado, $visibleMenu, $visibleBusqueda, $obligatorio, $forzarOcultoPadreOff] as $valorBinario) {
        if (!in_array($valorBinario, [0, 1], true)) {
            throw new Exception('Uno de los valores enviados no es válido.');
        }
    }

    if ($ordenOverride !== null && $ordenOverride <= 0) {
        throw new Exception('El orden override debe ser mayor a cero.');
    }

    if (strlen($observaciones) > 255) {
        throw new Exception('Las observaciones no pueden exceder 255 caracteres.');
    }

    $modulo = $clsConsulta->consultaPreparada("
        SELECT id, nombre
        FROM modulos
        WHERE id = ?
        LIMIT 1
    ", [$moduloId]);

    if (empty($modulo)) {
        throw new Exception('El módulo seleccionado no existe.');
    }

    $configExiste = $clsConsulta->consultaPreparada("
        SELECT id
        FROM configuracion_modulos
        WHERE modulo_id = ?
        LIMIT 1
    ", [$moduloId]);

    if (!empty($configExiste)) {
        throw new Exception('Ese módulo ya tiene una configuración registrada.');
    }

    if ($paqueteOrigen !== '') {
        $paquete = $clsConsulta->consultaPreparada("
            SELECT id
            FROM configuracion_paquetes
            WHERE clave = ? AND estatus = 'activo'
            LIMIT 1
        ", [$paqueteOrigen]);

        if (empty($paquete)) {
            throw new Exception('El paquete seleccionado no existe o está inactivo.');
        }
    } else {
        $paqueteOrigen = null;
    }

    if ($habilitado === 1) {
        $dependencias = $clsConsulta->consultaPreparada("
            SELECT
                md.nombre,
                COALESCE(cmd.habilitado, 0) AS habilitado_dependencia
            FROM configuracion_modulo_dependencias d
            INNER JOIN modulos md ON md.id = d.depende_modulo_id
            LEFT JOIN configuracion_modulos cmd ON cmd.modulo_id = md.id
            WHERE d.modulo_id = ?
              AND d.accion_si_falta = 'bloquear'
        ", [$moduloId]);

        if (!empty($dependencias)) {
            foreach ($dependencias as $dep) {
                if ((int)$dep['habilitado_dependencia'] !== 1) {
                    throw new Exception('No se puede habilitar el módulo mientras la dependencia "' . $dep['nombre'] . '" esté apagada.');
                }
            }
        }
    }

    $datos = [
        'modulo_id' => $moduloId,
        'habilitado' => $habilitado,
        'visible_menu' => $visibleMenu,
        'visible_busqueda' => $visibleBusqueda,
        'obligatorio' => $obligatorio,
        'forzar_oculto_si_padre_off' => $forzarOcultoPadreOff,
        'orden_override' => $ordenOverride,
        'paquete_origen' => $paqueteOrigen,
        'observaciones' => ($observaciones !== '') ? $observaciones : null,
        'updated_by' => (int)$_SESSION['id_user']
    ];

    $resultado = $clsConsulta->insertarSeguro('configuracion_modulos', $datos);

    if ($resultado <= 0) {
        throw new Exception('No fue posible guardar la configuración del módulo.');
    }

    $clsConsulta->insertarSeguro('configuracion_modulos_bitacora', [
        'modulo_id' => $moduloId,
        'accion' => ($habilitado === 1) ? 'habilitar' : 'deshabilitar',
        'valor_anterior' => null,
        'valor_nuevo' => 'habilitado=' . $habilitado . '|visible_menu=' . $visibleMenu . '|paquete=' . (($paqueteOrigen !== null) ? $paqueteOrigen : 'NULL'),
        'observaciones' => 'Alta de configuración del módulo',
        'usuario_id' => (int)$_SESSION['id_user']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Configuración guardada correctamente.',
        'id' => (int)$clsConsulta->ultimoid
    ]);
    exit;
} catch (Exception $e) {
    error_log('Error en ajax/configuracion-modulos/guardar.php: ' . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
