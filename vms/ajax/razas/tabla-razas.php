<?php
/* Archivo: ajax/razas/tabla-razas.php */

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
    $filtroEstatus = trim($_POST['filtro_estatus'] ?? '');
    $filtroEspecie = isset($_POST['filtro_especie']) && $_POST['filtro_especie'] !== '' ? (int)$_POST['filtro_especie'] : 0;

    $columnas = [
        0 => 'r.id',
        1 => 'e.nombre_especie',
        2 => 'r.nombre_raza',
        3 => 'r.descripcion',
        4 => 'r.estatus',
        5 => 'r.updated_at'
    ];

    $orderColumnIndex = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? strtolower($_POST['order'][0]['dir']) : 'desc';

    $orderColumn = $columnas[$orderColumnIndex] ?? 'r.id';
    $orderDir = ($orderDir === 'asc') ? 'ASC' : 'DESC';

    $where = " WHERE 1 = 1 ";
    $params = [];

    if ($filtroEstatus !== '' && in_array($filtroEstatus, ['activo', 'inactivo'], true)) {
        $where .= " AND r.estatus = ? ";
        $params[] = $filtroEstatus;
    }

    if ($filtroEspecie > 0) {
        $where .= " AND r.id_especie = ? ";
        $params[] = $filtroEspecie;
    }

    if ($searchValue !== '') {
        $where .= " AND (
            r.id LIKE ?
            OR e.nombre_especie LIKE ?
            OR r.nombre_raza LIKE ?
            OR r.descripcion LIKE ?
            OR r.estatus LIKE ?
        ) ";
        $busqueda = '%' . $searchValue . '%';
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
    }

    $totalQuery = $clsConsulta->consultaPreparada("SELECT COUNT(*) AS total FROM cat_razas");
    $recordsTotal = isset($totalQuery[0]['total']) ? (int)$totalQuery[0]['total'] : 0;

    $filteredQuery = $clsConsulta->consultaPreparada(
        "SELECT COUNT(*) AS total
         FROM cat_razas r
         INNER JOIN cat_especies e ON e.id = r.id_especie
         $where",
        $params
    );
    $recordsFiltered = isset($filteredQuery[0]['total']) ? (int)$filteredQuery[0]['total'] : 0;

    $sql = "
        SELECT
            r.id,
            r.nombre_raza,
            r.descripcion,
            r.estatus,
            r.updated_at,
            e.nombre_especie
        FROM cat_razas r
        INNER JOIN cat_especies e ON e.id = r.id_especie
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
            $estatus = ($row['estatus'] === 'activo')
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-danger">Inactivo</span>';

            $updatedAt = '';
            if (!empty($row['updated_at'])) {
                $updatedAt = date('d/m/Y H:i', strtotime($row['updated_at']));
            }

            $acciones = '
                <a href="razas-modificar/' . (int)$row['id'] . '" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Modificar">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>
            ';

            $data[] = [
                'id' => (int)$row['id'],
                'especie' => htmlspecialchars((string)$row['nombre_especie'], ENT_QUOTES, 'UTF-8'),
                'nombre_raza' => htmlspecialchars((string)$row['nombre_raza'], ENT_QUOTES, 'UTF-8'),
                'descripcion' => htmlspecialchars((string)$row['descripcion'], ENT_QUOTES, 'UTF-8'),
                'estatus' => $estatus,
                'updated_at' => $updatedAt,
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
    error_log('Error en ajax/razas/tabla-razas.php: ' . $e->getMessage());

    echo json_encode([
        'draw' => isset($_POST['draw']) ? (int)$_POST['draw'] : 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'No fue posible cargar el listado de razas.'
    ]);
    exit;
}
