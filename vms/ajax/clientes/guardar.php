<?php
/* Archivo: ajax/clientes/guardar.php */

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

    $razonSocial = trim($_POST['razon_social'] ?? '');
    $rfc = strtoupper(trim($_POST['rfc'] ?? ''));
    $nombreComercial = trim($_POST['nombre_comercial'] ?? '');
    $calle = trim($_POST['calle'] ?? '');
    $numExt = trim($_POST['num_ext'] ?? '');
    $numInt = trim($_POST['num_int'] ?? '');
    $colonia = trim($_POST['colonia'] ?? '');
    $cp = trim($_POST['cp'] ?? '');
    $idEstado = isset($_POST['id_estado']) && $_POST['id_estado'] !== '' ? (int)$_POST['id_estado'] : null;
    $idMunicipio = isset($_POST['id_municipio']) && $_POST['id_municipio'] !== '' ? (int)$_POST['id_municipio'] : null;
    $localidad = trim($_POST['localidad'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $correoFactura = trim($_POST['correo_factura'] ?? '');
    $comprasNombre = trim($_POST['compras_nombre'] ?? '');
    $comprasTel = trim($_POST['compras_tel'] ?? '');
    $estatus = isset($_POST['estatus']) ? (int)$_POST['estatus'] : 1;
    $mapa = trim($_POST['mapa'] ?? '');
    $cxcNombre = trim($_POST['cxc_nombre'] ?? '');
    $cxcTel = trim($_POST['cxc_tel'] ?? '');
    $operacionesNombre = trim($_POST['operaciones_nombre'] ?? '');
    $operacionesTel = trim($_POST['operaciones_tel'] ?? '');
    $idResidente = isset($_POST['id_residente']) && $_POST['id_residente'] !== '' ? (int)$_POST['id_residente'] : null;
    $idGerente = isset($_POST['id_gerente']) && $_POST['id_gerente'] !== '' ? (int)$_POST['id_gerente'] : null;
    $idEmpresa = isset($_POST['id_empresa']) && $_POST['id_empresa'] !== '' ? (int)$_POST['id_empresa'] : null;

    if ($razonSocial === '') {
        throw new Exception('La razón social es obligatoria.');
    }

    if (!in_array($estatus, [0, 1], true)) {
        throw new Exception('El estatus enviado no es válido.');
    }

    if ($cp !== '' && !preg_match('/^[0-9]{5}$/', $cp)) {
        throw new Exception('El código postal debe contener 5 dígitos.');
    }

    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El correo principal no es válido.');
    }

    if ($correoFactura !== '' && !filter_var($correoFactura, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El correo de factura no es válido.');
    }

    if ($idEstado !== null) {
        $estadoExiste = $clsConsulta->consultaPreparada(
            "SELECT id FROM estados WHERE id = ? LIMIT 1",
            [$idEstado]
        );

        if (empty($estadoExiste)) {
            throw new Exception('El estado seleccionado no existe.');
        }
    }

    if ($idMunicipio !== null) {
        if ($idEstado === null) {
            throw new Exception('Selecciona primero un estado para el municipio.');
        }

        $municipioExiste = $clsConsulta->consultaPreparada(
            "SELECT id FROM municipios WHERE id = ? AND estado_id = ? LIMIT 1",
            [$idMunicipio, $idEstado]
        );

        if (empty($municipioExiste)) {
            throw new Exception('El municipio seleccionado no corresponde al estado indicado.');
        }
    }

    $ahora = date('Y-m-d H:i:s');

    $datos = [
        'razon_social' => mb_strtoupper($razonSocial, 'UTF-8'),
        'rfc' => ($rfc !== '') ? $rfc : null,
        'nombre_comercial' => ($nombreComercial !== '') ? mb_strtoupper($nombreComercial, 'UTF-8') : null,
        'calle' => ($calle !== '') ? mb_strtoupper($calle, 'UTF-8') : null,
        'num_ext' => ($numExt !== '') ? mb_strtoupper($numExt, 'UTF-8') : null,
        'num_int' => ($numInt !== '') ? mb_strtoupper($numInt, 'UTF-8') : null,
        'colonia' => ($colonia !== '') ? mb_strtoupper($colonia, 'UTF-8') : null,
        'cp' => ($cp !== '') ? (int)$cp : null,
        'id_estado' => $idEstado,
        'id_municipio' => $idMunicipio,
        'localidad' => ($localidad !== '') ? mb_strtoupper($localidad, 'UTF-8') : null,
        'correo' => ($correo !== '') ? mb_strtolower($correo, 'UTF-8') : null,
        'correo_factura' => ($correoFactura !== '') ? mb_strtolower($correoFactura, 'UTF-8') : null,
        'compras_nombre' => ($comprasNombre !== '') ? mb_strtoupper($comprasNombre, 'UTF-8') : null,
        'compras_tel' => ($comprasTel !== '') ? $comprasTel : null,
        'fecha_alta' => $ahora,
        'updated_at' => $ahora,
        'estatus' => $estatus,
        'mapa' => ($mapa !== '') ? $mapa : null,
        'cxc_nombre' => ($cxcNombre !== '') ? mb_strtoupper($cxcNombre, 'UTF-8') : null,
        'cxc_tel' => ($cxcTel !== '') ? $cxcTel : null,
        'operaciones_nombre' => ($operacionesNombre !== '') ? mb_strtoupper($operacionesNombre, 'UTF-8') : null,
        'operaciones_tel' => ($operacionesTel !== '') ? $operacionesTel : null,
        'id_residente' => $idResidente,
        'id_gerente' => $idGerente,
        'id_empresa' => $idEmpresa
    ];

    $resultado = $clsConsulta->insertarSeguro('cat_clientes', $datos);

    if ($resultado <= 0) {
        throw new Exception('No fue posible guardar el cliente.');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Cliente guardado correctamente.',
        'id' => (int)$clsConsulta->ultimoid
    ]);
    exit;
} catch (Exception $e) {
    error_log('Error en ajax/clientes/guardar.php: ' . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
