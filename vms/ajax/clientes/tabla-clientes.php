<?php
/* Archivo: ajax/clientes/tabla-clientes.php */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../lib/clsConsultas.php';

if (!isset($_SESSION['id_user'])) {
    echo json_encode([
        'draw' => isset($_POST['draw']) ? (int)$_POST['draw'] : 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}

try {
    $clsConsulta = new Consultas();

    $draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 0;
    $start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
    $length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
    $searchValue = trim($_POST['search']['value'] ?? '');
    $filtroEstatus = isset($_POST['filtro_estatus']) && $_POST['filtro_estatus'] !== '' ? (int)$_POST['filtro_estatus'] : null;

    $columnas = [
        0 => 'id',
        1 => 'razon_social',
        2 => 'CAST(rfc AS CHAR)',
        3 => 'nombre_comercial',
        4 => 'correo',
        5 => 'estatus',
        6 => 'updated_at'
    ];

    $orderColumnIndex = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? strtolower($_POST['order'][0]['dir']) : 'desc';

    $orderColumn = $columnas[$orderColumnIndex] ?? 'id';
    $orderDir = ($orderDir === 'asc') ? 'ASC' : 'DESC';

    $where = " WHERE deleted_at IS NULL ";
    $params = [];

    if ($filtroEstatus !== null && in_array($filtroEstatus, [0, 1], true)) {
        $where .= " AND estatus = ? ";
        $params[] = $filtroEstatus;
    }

    if ($searchValue !== '') {
        $where .= " AND (
            razon_social LIKE ?
            OR CAST(rfc AS CHAR) LIKE ?
            OR nombre_comercial LIKE ?
            OR correo LIKE ?
            OR correo_factura LIKE ?
            OR compras_nombre LIKE ?
            OR cxc_nombre LIKE ?
            OR operaciones_nombre LIKE ?
        ) ";
        $busqueda = '%' . $searchValue . '%';
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
    }

    $totalQuery = $clsConsulta->consultaPreparada(
        "SELECT COUNT(*) AS total FROM cat_clientes WHERE deleted_at IS NULL"
    );
    $recordsTotal = isset($totalQuery[0]['total']) ? (int)$totalQuery[0]['total'] : 0;

    $filteredQuery = $clsConsulta->consultaPreparada(
        "SELECT COUNT(*) AS total FROM cat_clientes $where",
        $params
    );
    $recordsFiltered = isset($filteredQuery[0]['total']) ? (int)$filteredQuery[0]['total'] : 0;

    $sql = "
        SELECT
            id,
            razon_social,
            CAST(rfc AS CHAR) AS rfc_texto,
            nombre_comercial,
            correo,
            estatus,
            fecha_alta,
            updated_at
        FROM cat_clientes
        $where
        ORDER BY $orderColumn $orderDir
    ";

    if ($length !== -1) {
        $sql .= " LIMIT ? OFFSET ? ";
        $paramsConsulta = $params;
        $paramsConsulta[] = $length;
        $paramsConsulta[] = $start;
        $rows = $clsConsulta->consultaPreparada($sql, $paramsConsulta);
    } else {
        $rows = $clsConsulta->consultaPreparada($sql, $params);
    }

    $data = [];

    if (!empty($rows)) {
        foreach ($rows as $row) {
            $estatus = ((int)$row['estatus'] === 1)
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-danger">Inactivo</span>';

            $fechaMostrar = '';
            if (!empty($row['updated_at'])) {
                $fechaMostrar = date('d/m/Y H:i', strtotime($row['updated_at']));
            } elseif (!empty($row['fecha_alta'])) {
                $fechaMostrar = date('d/m/Y H:i', strtotime($row['fecha_alta']));
            }

            $acciones = '
                <a href="clientes-modificar/' . (int)$row['id'] . '" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Modificar">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>
            ';

            $data[] = [
                'id' => (int)$row['id'],
                'razon_social' => htmlspecialchars((string)$row['razon_social'], ENT_QUOTES, 'UTF-8'),
                'rfc' => htmlspecialchars((string)$row['rfc_texto'], ENT_QUOTES, 'UTF-8'),
                'nombre_comercial' => htmlspecialchars((string)$row['nombre_comercial'], ENT_QUOTES, 'UTF-8'),
                'correo' => htmlspecialchars((string)$row['correo'], ENT_QUOTES, 'UTF-8'),
                'estatus' => $estatus,
                'updated_at' => $fechaMostrar,
                'acciones' => $acciones
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
    error_log('Error en ajax/clientes/tabla-clientes.php: ' . $e->getMessage());

    echo json_encode([
        'draw' => isset($_POST['draw']) ? (int)$_POST['draw'] : 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'No fue posible cargar el listado de clientes.'
    ]);
    exit;
}
