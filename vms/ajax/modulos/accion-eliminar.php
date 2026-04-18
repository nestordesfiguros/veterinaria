<?php
// ajax/modulos/accion-eliminar.php

require '../../lib/clsConsultas.php';
header('Content-Type: application/json');
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Si hay FK ON DELETE CASCADE en permisos_rol_accion(accion_id) basta con borrar aquí.
// Si no, limpia manualmente:
$clsConsulta->aplicaquery("DELETE FROM permisos_rol_accion WHERE accion_id = $id");
$ok = $clsConsulta->aplicaquery("DELETE FROM acciones_modulo WHERE id = $id");

echo json_encode(['success' => (bool)$ok]);
