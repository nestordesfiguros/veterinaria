<?php
/* Archivo: ajax/especies/tabla-especies.php */
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../lib/clsConsultas.php';

if (!isset($_SESSION['id_user'])) {
    echo json_encode([
        'draw' => (int)($_POST['draw'] ?? 0),
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

    $columnas = [
        0 => 'id',
        1 => 'nombre_especie',
        2 => 'descripcion',
        3 => 'estatus',
        4 => 'updated_at'
    ];

    $orderColumnIndex = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? strtolower($_POST['order'][0]['dir']) : 'desc';

    $orderColumn = $columnas[$orderColumnIndex] ?? 'id';
    $orderDir = ($orderDir === 'asc') ? 'ASC' : 'DESC';

    $where = " WHERE 1=1 ";
    $params = [];

    if ($filtroEstatus !== '' && in_array($filtroEstatus, ['activo', 'inactivo'], true)) {
        $where .= " AND estatus = ? ";
        $params[] = $filtroEstatus;
    }

    if ($searchValue !== '') {
        $where .= " AND (
            nombre_especie LIKE ?
            OR descripcion LIKE ?
            OR estatus LIKE ?
        ) ";
        $busqueda = '%' . $searchValue . '%';
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
    }

    $totalQuery = $clsConsulta->consultaPreparada("SELECT COUNT(*) AS total FROM cat_especies");
    $recordsTotal = (int)($totalQuery[0]['total'] ?? 0);

    $filteredQuery = $clsConsulta->consultaPreparada(
        "SELECT COUNT(*) AS total FROM cat_especies " . $where,
        $params
    );
    $recordsFiltered = (int)($filteredQuery[0]['total'] ?? 0);

    $sql = "
        SELECT
            id,
            nombre_especie,
            descripcion,
            estatus,
            updated_at
        FROM cat_especies
        $where
        ORDER BY $orderColumn $orderDir
    ";

    $rows = [];

    if ($length === -1) {
        $rows = $clsConsulta->consultaPreparada($sql, $params);
    } else {
        $sql .= " LIMIT ? OFFSET ? ";
        $paramsPaginados = $params;
        $paramsPaginados[] = $length;
        $paramsPaginados[] = $start;

        $rows = $clsConsulta->consultaPreparada($sql, $paramsPaginados);
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
                <a href="especies-modificar/' . (int)$row['id'] . '" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Modificar">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>
            ';

            $data[] = [
                'id' => (int)$row['id'],
                'nombre_especie' => htmlspecialchars($row['nombre_especie'] ?? '', ENT_QUOTES, 'UTF-8'),
                'descripcion' => htmlspecialchars($row['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'),
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
} catch (Exception $e) {
    echo json_encode([
        'draw' => (int)($_POST['draw'] ?? 0),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage()
    ]);
}
