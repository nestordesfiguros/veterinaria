<?php
/* ========================================================================== */
/* Archivo: ajax/configuracion-estilos/tabla-configuracion-estilos.php        */
/* Ruta: ajax/configuracion-estilos/tabla-configuracion-estilos.php           */
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

    $grupo = isset($_POST['grupo']) ? trim($_POST['grupo']) : '';

    $columnas = [
        0 => 'id',
        1 => 'grupo',
        2 => 'subgrupo',
        3 => 'clave',
        4 => 'nombre',
        5 => 'valor',
        6 => 'tipo_control',
        7 => 'estatus'
    ];

    $orderColumnIndex = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? strtolower(trim($_POST['order'][0]['dir'])) : 'asc';

    if (!isset($columnas[$orderColumnIndex])) {
        $orderColumnIndex = 0;
    }

    if ($orderDir !== 'asc' && $orderDir !== 'desc') {
        $orderDir = 'asc';
    }

    $orderColumn = $columnas[$orderColumnIndex];

    $where = " WHERE 1 = 1 ";

    if ($grupo !== '') {
        $grupoSeguro = $clsConsulta->escape($grupo);
        $where .= " AND grupo = '" . $grupoSeguro . "' ";
    }

    if ($searchValue !== '') {
        $searchSeguro = $clsConsulta->escape($searchValue);
        $where .= " AND (
                        grupo LIKE '%" . $searchSeguro . "%'
                        OR subgrupo LIKE '%" . $searchSeguro . "%'
                        OR clave LIKE '%" . $searchSeguro . "%'
                        OR nombre LIKE '%" . $searchSeguro . "%'
                        OR valor LIKE '%" . $searchSeguro . "%'
                    ) ";
    }

    $sqlTotal = "SELECT COUNT(*) AS total FROM configuracion_estilos";
    $resTotal = $clsConsulta->consultaGeneral($sqlTotal);
    $recordsTotal = 0;

    if ($clsConsulta->numrows > 0) {
        $recordsTotal = (int)$resTotal[1]['total'];
    }

    $sqlFiltered = "SELECT COUNT(*) AS total FROM configuracion_estilos " . $where;
    $resFiltered = $clsConsulta->consultaGeneral($sqlFiltered);
    $recordsFiltered = 0;

    if ($clsConsulta->numrows > 0) {
        $recordsFiltered = (int)$resFiltered[1]['total'];
    }

    $sqlDatos = "SELECT 
                    id,
                    grupo,
                    subgrupo,
                    clave,
                    nombre,
                    valor,
                    valor_default,
                    tipo_control,
                    unidad,
                    estatus
                 FROM configuracion_estilos
                 " . $where . "
                 ORDER BY " . $orderColumn . " " . $orderDir . "
                 LIMIT " . $start . ", " . $length;

    $resDatos = $clsConsulta->consultaGeneral($sqlDatos);
    $data = [];

    $gruposAmigables = [
        'general' => 'General',
        'titulos' => 'Títulos',
        'formularios' => 'Formularios',
        'botones' => 'Botones',
        'tablas' => 'Tablas',
        'breadcrumb' => 'Breadcrumb',
        'navegacion' => 'Navegación',
        'submenu' => 'Submenú'
    ];

    $subgruposAmigables = [
        'colores' => 'Colores',
        'bordes' => 'Bordes',
        'sombras' => 'Sombras',
        'texto' => 'Texto',
        'estructura' => 'Estructura',
        'espacios' => 'Espacios',
        'filas' => 'Filas',
        'encabezado' => 'Encabezado',
        'botones' => 'Botones',
        'bienvenida' => 'Bienvenida',
        'general' => 'General'
    ];

    $tiposAmigables = [
        'color' => 'Color',
        'text' => 'Texto',
        'number' => 'Número',
        'select' => 'Selección',
        'range' => 'Rango',
        'textarea' => 'Área de texto'
    ];

    if ($clsConsulta->numrows > 0) {
        foreach ($resDatos as $row) {
            $id = (int)$row['id'];

            $grupoTecnico = (string)$row['grupo'];
            $subgrupoTecnico = (string)($row['subgrupo'] ?? '');
            $tipoTecnico = (string)$row['tipo_control'];

            $grupoTxt = isset($gruposAmigables[$grupoTecnico]) ? $gruposAmigables[$grupoTecnico] : ucfirst($grupoTecnico);
            $subgrupoTxt = $subgrupoTecnico !== ''
                ? (isset($subgruposAmigables[$subgrupoTecnico]) ? $subgruposAmigables[$subgrupoTecnico] : ucfirst($subgrupoTecnico))
                : '';
            $tipoTxt = isset($tiposAmigables[$tipoTecnico]) ? $tiposAmigables[$tipoTecnico] : ucfirst($tipoTecnico);

            $claveTxt  = htmlspecialchars($row['clave'], ENT_QUOTES, 'UTF-8');
            $nombreTxt = htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8');
            $valorTxt  = htmlspecialchars($row['valor'], ENT_QUOTES, 'UTF-8');

            if ($row['estatus'] === 'activo') {
                $estatusHtml = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Activo</span>';
            } else {
                $estatusHtml = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Inactivo</span>';
            }

            $valorHtml = $valorTxt;

            if ($tipoTecnico === 'color') {
                $valorHtml = ''
                    . '<div class="d-flex align-items-center gap-2">'
                    . '<span style="display:inline-block;width:18px;height:18px;border-radius:4px;border:1px solid #d1d5db;background:' . $valorTxt . ';"></span>'
                    . '<span>' . $valorTxt . '</span>'
                    . '</div>';
            } elseif (($row['unidad'] ?? '') !== '') {
                $valorHtml = $valorTxt . ' ' . htmlspecialchars((string)$row['unidad'], ENT_QUOTES, 'UTF-8');
            }

            $acciones = ''
                . '<div class="d-flex justify-content-start">'
                . '<a href="configuracion-estilos-modificar/' . $id . '" class="btn btn-primary btn-sm">'
                . '<i class="fa fa-edit"></i> Editar'
                . '</a>'
                . '</div>';

            $data[] = [
                $id,
                htmlspecialchars($grupoTxt, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($subgrupoTxt, ENT_QUOTES, 'UTF-8'),
                $claveTxt,
                $nombreTxt,
                $valorHtml,
                htmlspecialchars($tipoTxt, ENT_QUOTES, 'UTF-8'),
                $estatusHtml,
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
    error_log('Error en ajax/configuracion-estilos/tabla-configuracion-estilos.php: ' . $e->getMessage());

    echo json_encode([
        'draw' => isset($draw) ? $draw : 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}
