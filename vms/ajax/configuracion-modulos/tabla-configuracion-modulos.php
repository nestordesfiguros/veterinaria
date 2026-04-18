<?php
/* Archivo: ajax/configuracion-modulos/tabla-configuracion-modulos.php */

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
    $filtroHabilitado = trim($_POST['filtro_habilitado'] ?? '');
    $filtroPaquete = trim($_POST['filtro_paquete'] ?? '');

    $columnas = [
        0 => 'm.id',
        1 => 'm.nombre',
        2 => 'm.archivo',
        3 => 'm.tipo_modulo',
        4 => 'm.canal',
        5 => 'cm.paquete_origen',
        6 => 'cm.habilitado',
        7 => 'cm.visible_menu',
        8 => 'cm.obligatorio'
    ];

    $orderColumnIndex = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? strtolower($_POST['order'][0]['dir']) : 'asc';

    $orderColumn = $columnas[$orderColumnIndex] ?? 'm.id';
    $orderDir = ($orderDir === 'desc') ? 'DESC' : 'ASC';

    $from = "
        FROM modulos m
        LEFT JOIN configuracion_modulos cm ON cm.modulo_id = m.id
    ";

    $where = " WHERE m.canal = 'erp' ";
    $params = [];

    if ($filtroHabilitado !== '') {
        if ($filtroHabilitado === 'sin_config') {
            $where .= " AND cm.id IS NULL ";
        } elseif (in_array($filtroHabilitado, ['0', '1'], true)) {
            $where .= " AND cm.habilitado = ? ";
            $params[] = (int)$filtroHabilitado;
        }
    }

    if ($filtroPaquete !== '') {
        if ($filtroPaquete === 'sin_paquete') {
            $where .= " AND (cm.paquete_origen IS NULL OR cm.paquete_origen = '') ";
        } else {
            $where .= " AND cm.paquete_origen = ? ";
            $params[] = $filtroPaquete;
        }
    }

    if ($searchValue !== '') {
        $where .= " AND (
            m.nombre LIKE ?
            OR m.archivo LIKE ?
            OR m.tipo_modulo LIKE ?
            OR m.canal LIKE ?
            OR IFNULL(cm.paquete_origen, '') LIKE ?
        ) ";
        $busqueda = '%' . $searchValue . '%';
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
    }

    $totalQuery = $clsConsulta->consultaPreparada("
        SELECT COUNT(*) AS total
        FROM modulos
        WHERE canal = 'erp'
    ");
    $recordsTotal = isset($totalQuery[0]['total']) ? (int)$totalQuery[0]['total'] : 0;

    $filteredQuery = $clsConsulta->consultaPreparada(
        "SELECT COUNT(*) AS total " . $from . $where,
        $params
    );
    $recordsFiltered = isset($filteredQuery[0]['total']) ? (int)$filteredQuery[0]['total'] : 0;

    $sql = "
        SELECT
            m.id,
            m.nombre,
            m.archivo,
            m.tipo_modulo,
            m.canal,
            cm.id AS configuracion_id,
            cm.paquete_origen,
            cm.habilitado,
            cm.visible_menu,
            cm.obligatorio
        " . $from . $where . "
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
            $paquete = ($row['paquete_origen'] !== null && $row['paquete_origen'] !== '')
                ? '<span class="badge bg-info text-dark">' . htmlspecialchars((string)$row['paquete_origen'], ENT_QUOTES, 'UTF-8') . '</span>'
                : '<span class="badge bg-secondary">Sin paquete</span>';

            if ($row['configuracion_id'] === null) {
                $habilitado = '<span class="badge bg-warning text-dark">Sin config</span>';
                $visibleMenu = '<span class="badge bg-warning text-dark">Sin config</span>';
                $obligatorio = '<span class="badge bg-warning text-dark">Sin config</span>';
                $acciones = '
                    <a href="configuracion-modulos-altas?modulo=' . (int)$row['id'] . '" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Configurar">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                ';
            } else {
                $habilitado = ((int)$row['habilitado'] === 1)
                    ? '<span class="badge bg-success">Sí</span>'
                    : '<span class="badge bg-danger">No</span>';

                $visibleMenu = ((int)$row['visible_menu'] === 1)
                    ? '<span class="badge bg-success">Sí</span>'
                    : '<span class="badge bg-danger">No</span>';

                $obligatorio = ((int)$row['obligatorio'] === 1)
                    ? '<span class="badge bg-primary">Sí</span>'
                    : '<span class="badge bg-secondary">No</span>';

                $acciones = '
                    <a href="configuracion-modulos-modificar/' . (int)$row['configuracion_id'] . '" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Modificar">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                ';
            }

            $data[] = [
                'id' => (int)$row['id'],
                'modulo' => htmlspecialchars((string)$row['nombre'], ENT_QUOTES, 'UTF-8'),
                'archivo' => htmlspecialchars((string)$row['archivo'], ENT_QUOTES, 'UTF-8'),
                'tipo_modulo' => htmlspecialchars((string)$row['tipo_modulo'], ENT_QUOTES, 'UTF-8'),
                'canal' => htmlspecialchars((string)$row['canal'], ENT_QUOTES, 'UTF-8'),
                'paquete_origen' => $paquete,
                'habilitado' => $habilitado,
                'visible_menu' => $visibleMenu,
                'obligatorio' => $obligatorio,
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
    error_log('Error en ajax/configuracion-modulos/tabla-configuracion-modulos.php: ' . $e->getMessage());

    echo json_encode([
        'draw' => isset($_POST['draw']) ? (int)$_POST['draw'] : 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'No fue posible cargar el listado de configuración de módulos.'
    ]);
    exit;
}
