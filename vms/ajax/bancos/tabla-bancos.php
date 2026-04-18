<?php
/* ========================================================================== */
/* Archivo: ajax/bancos/tabla-bancos.php                                      */
/* Ruta: ajax/bancos/tabla-bancos.php                                         */
/* ========================================================================== */
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    include '../../lib/clsConsultas.php';
    $clsConsulta = new Consultas();

    $draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
    $start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
    $length = isset($_POST['length']) ? (int)$_POST['length'] : 10;

    if ($start < 0) {
        $start = 0;
    }

    if ($length < 1) {
        $length = 10;
    }

    $searchValue = '';
    if (isset($_POST['search']) && isset($_POST['search']['value'])) {
        $searchValue = trim($_POST['search']['value']);
    }

    $estatus = isset($_POST['estatus']) ? trim($_POST['estatus']) : '';

    $columnas = [
        0 => 'id',
        1 => 'nombre_banco',
        2 => 'clave_banco',
        3 => 'status',
        4 => 'created_at'
    ];

    $orderColumnIndex = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? strtolower(trim($_POST['order'][0]['dir'])) : 'desc';

    if (!isset($columnas[$orderColumnIndex])) {
        $orderColumnIndex = 0;
    }

    if ($orderDir !== 'asc' && $orderDir !== 'desc') {
        $orderDir = 'desc';
    }

    $orderColumn = $columnas[$orderColumnIndex];

    $where = " WHERE 1 = 1 ";

    if ($estatus !== '') {
        $estatusSeguro = $clsConsulta->escape($estatus);
        $where .= " AND status = '" . $estatusSeguro . "' ";
    }

    if ($searchValue !== '') {
        $searchSeguro = $clsConsulta->escape($searchValue);
        $where .= " AND (nombre_banco LIKE '%" . $searchSeguro . "%' OR clave_banco LIKE '%" . $searchSeguro . "%') ";
    }

    $sqlTotal = "SELECT COUNT(*) AS total FROM cat_bancos";
    $resTotal = $clsConsulta->consultaGeneral($sqlTotal);
    $recordsTotal = 0;

    if ($clsConsulta->numrows > 0) {
        $recordsTotal = (int)$resTotal[1]['total'];
    }

    $sqlFiltered = "SELECT COUNT(*) AS total FROM cat_bancos " . $where;
    $resFiltered = $clsConsulta->consultaGeneral($sqlFiltered);
    $recordsFiltered = 0;

    if ($clsConsulta->numrows > 0) {
        $recordsFiltered = (int)$resFiltered[1]['total'];
    }

    $sqlDatos = "SELECT id, nombre_banco, clave_banco, status, created_at
                 FROM cat_bancos
                 " . $where . "
                 ORDER BY " . $orderColumn . " " . $orderDir . "
                 LIMIT " . $start . ", " . $length;

    $resDatos = $clsConsulta->consultaGeneral($sqlDatos);
    $data = [];

    if ($clsConsulta->numrows > 0) {
        foreach ($resDatos as $row) {
            $id = (int)$row['id'];
            $nombreBanco = htmlspecialchars($row['nombre_banco'], ENT_QUOTES, 'UTF-8');
            $claveBanco = htmlspecialchars($row['clave_banco'], ENT_QUOTES, 'UTF-8');
            $fechaAlta = htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8');

            if ($row['status'] === 'activo') {
                $estatusHtml = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Activo</span>';
            } else {
                $estatusHtml = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Inactivo</span>';
            }

            $acciones = ''
                . '<div class="d-flex justify-content-start">'
                . '<a href="bancos-modificar/' . $id . '" class="btn btn-primary btn-sm">'
                . '<i class="fa fa-edit"></i> Editar'
                . '</a>'
                . '</div>';

            $data[] = [
                $id,
                $nombreBanco,
                $claveBanco,
                $estatusHtml,
                $fechaAlta,
                $acciones
            ];
        }
    }

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data
    ]);
    exit;
} catch (Exception $e) {
    error_log('Error en ajax/bancos/tabla-bancos.php: ' . $e->getMessage());

    echo json_encode([
        'draw' => isset($draw) ? $draw : 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}
