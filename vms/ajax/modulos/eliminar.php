<?php
// ajax/modulos/eliminar.php

require '../../lib/clsConsultas.php';
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cls = new Consultas();

// =========================
// INPUTS
// =========================
$id        = intval($_POST['id'] ?? 0);
$confirmar = intval($_POST['confirmar'] ?? -1); // 0 validar, 1 eliminar
$fromUI    = (string)($_POST['from_ui'] ?? '');

// Blindaje mínimo para evitar llamadas externas directas
if ($fromUI !== 'modulos') {
    echo json_encode(['success' => false, 'message' => 'Solicitud no autorizada']);
    exit;
}

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

if ($confirmar !== 0 && $confirmar !== 1) {
    echo json_encode(['success' => false, 'message' => 'Parámetro confirmar inválido']);
    exit;
}

// =========================
// HELPERS (clsConsulta devuelve [1]..[n])
// =========================
function oneRow($cls, $sql)
{
    $r = $cls->consultaGeneral($sql);
    return (is_array($r) && isset($r[1])) ? $r[1] : null;
}

function rowsOnly($rows)
{
    $out = [];
    if (!is_array($rows)) return $out;
    foreach ($rows as $k => $r) if (is_numeric($k)) $out[] = $r;
    return $out;
}

function q($v)
{ // quote simple para IN('a','b')
    return "'" . addslashes((string)$v) . "'";
}

// =========================
// CONEXIÓN + TX HELPERS (mysqli o PDO)
// =========================
$conn = null;
if (method_exists($cls, 'getConexion')) {
    $conn = $cls->getConexion();
}

$isMysqli = (is_object($conn) && ($conn instanceof mysqli));
$isPdo    = (is_object($conn) && ($conn instanceof PDO));

$txBegin = function () use ($conn, $isMysqli, $isPdo) {
    if ($isMysqli) {
        @$conn->begin_transaction();
        return true;
    }
    if ($isPdo) {
        if (!$conn->inTransaction()) $conn->beginTransaction();
        return true;
    }
    return false;
};

$txCommit = function () use ($conn, $isMysqli, $isPdo) {
    if ($isMysqli) {
        @$conn->commit();
        return true;
    }
    if ($isPdo) {
        if ($conn->inTransaction()) $conn->commit();
        return true;
    }
    return false;
};

$txRollback = function () use ($conn, $isMysqli, $isPdo) {
    if ($isMysqli) {
        @$conn->rollback();
        return true;
    }
    if ($isPdo) {
        if ($conn->inTransaction()) $conn->rollBack();
        return true;
    }
    return false;
};

// =========================
// CARGA MÓDULO PRINCIPAL
// =========================
$mod = oneRow($cls, "SELECT id, nombre, archivo, canal, app_id, modulo_padre FROM modulos WHERE id=$id LIMIT 1");
if (!$mod) {
    echo json_encode(['success' => false, 'message' => 'Módulo no encontrado']);
    exit;
}

// =========================
// OBTENER HIJOS RECURSIVOS (BFS)
// =========================
$childIds = [];
$queue = [$id];

while (!empty($queue)) {
    $pid = (int)array_shift($queue);
    $rows = rowsOnly($cls->consultaGeneral("SELECT id FROM modulos WHERE modulo_padre = $pid"));

    foreach ($rows as $r) {
        $cid = intval($r['id'] ?? 0);
        if ($cid > 0 && !in_array($cid, $childIds, true)) {
            $childIds[] = $cid;
            $queue[] = $cid;
        }
    }
}

$allIds = array_merge([$id], $childIds);
$idsCsv = implode(',', array_map('intval', $allIds));

// =========================
// CONTADORES (DEPENDENCIAS)
// =========================
$cantHijos = count($childIds);

$cls->consultaGeneral("SELECT id FROM acciones_modulo WHERE modulo_id IN ($idsCsv)");
$cantAcciones = (int)$cls->numrows;

$cls->consultaGeneral("
    SELECT pra.id
    FROM permisos_rol_accion pra
    INNER JOIN acciones_modulo am ON am.id = pra.accion_id
    WHERE am.modulo_id IN ($idsCsv)
");
$cantPermAcc = (int)$cls->numrows;

// permisos_rol_modulo.modulo es VARCHAR (ERP=archivo / APP=app_id)
$permKeys = [];
foreach ($allIds as $mid) {
    $m2 = oneRow($cls, "SELECT id, canal, archivo, app_id FROM modulos WHERE id=" . (int)$mid . " LIMIT 1");
    if (!$m2) continue;
    $key = (($m2['canal'] ?? '') === 'app') ? (string)($m2['app_id'] ?? '') : (string)($m2['archivo'] ?? '');
    if ($key !== '') $permKeys[$key] = true;
}
$permKeys = array_keys($permKeys);

$cantPermisos = 0;
if (!empty($permKeys)) {
    $in = implode(',', array_map('q', $permKeys));
    $cls->consultaGeneral("SELECT id FROM permisos_rol_modulo WHERE modulo IN ($in)");
    $cantPermisos = (int)$cls->numrows;
}

// =========================
// VALIDACIÓN (confirmar=0) -> NUNCA BORRA
// =========================
if ($confirmar === 0) {
    echo json_encode([
        'success' => true,
        'modo' => 'validacion',
        'requiere_confirmacion' => true,
        'detalle' => [
            'hijos'    => $cantHijos,
            'acciones' => $cantAcciones,
            'permisos' => $cantPermisos,
            'perm_acc' => $cantPermAcc
        ],
        'preview' => [
            'modulo' => [
                'id'    => (int)$mod['id'],
                'nombre' => (string)($mod['nombre'] ?? ''),
                'canal' => (string)($mod['canal'] ?? ''),
                'app_id' => (string)($mod['app_id'] ?? ''),
            ]
        ]
    ]);
    exit;
}

// =========================
// ELIMINACIÓN (confirmar=1) con TRANSACCIÓN
// =========================
$startedTx = false;

try {
    $startedTx = $txBegin();

    // 1) permisos por acción
    $cls->aplicaQuery("
        DELETE pra
        FROM permisos_rol_accion pra
        INNER JOIN acciones_modulo am ON am.id = pra.accion_id
        WHERE am.modulo_id IN ($idsCsv)
    ");

    // 2) acciones
    $cls->aplicaQuery("DELETE FROM acciones_modulo WHERE modulo_id IN ($idsCsv)");

    // 3) permisos por rol (keys varchar)
    if (!empty($permKeys)) {
        $in = implode(',', array_map('q', $permKeys));
        $cls->aplicaQuery("DELETE FROM permisos_rol_modulo WHERE modulo IN ($in)");
    }

    // 4) borrar hijos primero
    if (!empty($childIds)) {
        $childCsv = implode(',', array_map('intval', $childIds));
        $cls->aplicaQuery("DELETE FROM modulos WHERE id IN ($childCsv)");
    }

    // 5) borrar principal
    if (method_exists($cls, 'eliminarSeguro')) {
        $cls->eliminarSeguro('modulos', $id);
    } else {
        $cls->aplicaQuery("DELETE FROM modulos WHERE id=$id LIMIT 1");
    }

    // 6) bitácora (NO debe romper el éxito del borrado)
    if (method_exists($cls, 'bitacora') && method_exists($cls, 'generaFolio')) {
        try {
            $folio     = $cls->generaFolio('MODULO');
            $idUsuario = $_SESSION['id_usuario'] ?? 0;
            $cls->bitacora(
                $folio,
                $idUsuario,
                'ELIMINAR_MODULO',
                'Se eliminó el módulo ID ' . $id . ' (y ' . count($childIds) . ' hijo(s))',
                'MODULOS'
            );
        } catch (Throwable $eBit) {
            error_log('[MODULOS][ELIMINAR][BITACORA] ' . $eBit->getMessage());
            // NO lanzar; es opcional
        }
    }

    if ($startedTx) $txCommit();

    echo json_encode([
        'success' => true,
        'modo' => 'eliminacion',
        'message' => 'Módulo eliminado correctamente'
    ]);
    exit;
} catch (Throwable $e) {

    if ($startedTx) $txRollback();
    error_log('[MODULOS][ELIMINAR] ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el módulo'
    ]);
    exit;
}
