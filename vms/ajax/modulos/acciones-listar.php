<?php
// ajax/modulos/acciones-listar.php

require '../../lib/clsConsultas.php';
header('Content-Type: application/json');
$clsConsulta = new Consultas();

$modulo_id = intval($_POST['modulo_id'] ?? 0);
if ($modulo_id <= 0) {
    echo json_encode(['acciones' => [], 'catalogo_modulos' => []]);
    exit;
}

// acciones existentes
$q = "SELECT id, clave, etiqueta, descripcion, modulo_destino, permiso_destino
      FROM acciones_modulo
      WHERE modulo_id = $modulo_id AND activo = 1
      ORDER BY etiqueta";
$rows = $clsConsulta->consultaGeneral($q) ?: [];
$acciones = [];
foreach ($rows as $k => $r) {
    if (!is_numeric($k)) continue;
    $puente = '';
    if (!empty($r['modulo_destino']) && !empty($r['permiso_destino'])) {
        $r2 = $clsConsulta->consultaGeneral("SELECT nombre FROM modulos WHERE id = {$r['modulo_destino']} LIMIT 1");
        $dest = (is_array($r2) && isset($r2[1]['nombre'])) ? $r2[1]['nombre'] : 'Módulo destino';
        $puente = $dest . ' → ' . $r['permiso_destino'];
    }
    $acciones[] = [
        'id' => (int)$r['id'],
        'clave' => $r['clave'],
        'etiqueta' => $r['etiqueta'],
        'descripcion' => $r['descripcion'],
        'modulo_destino' => isset($r['modulo_destino']) ? (int)$r['modulo_destino'] : null,
        'permiso_destino' => $r['permiso_destino'] ?? null,
        'puente_desc' => $puente ?: '—'
    ];
}

// catálogo de módulos para “puente”
$mods = $clsConsulta->consultaGeneral("SELECT id, nombre FROM modulos ORDER BY nombre") ?: [];
$catalogo = [];
foreach ($mods as $k => $m) {
    if (!is_numeric($k)) continue;
    $catalogo[] = ['id' => (int)$m['id'], 'nombre' => $m['nombre']];
}

echo json_encode(['acciones' => $acciones, 'catalogo_modulos' => $catalogo]);
