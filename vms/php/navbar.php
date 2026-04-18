<?php
/* ============================
 * Archivo: ajax/nomina-historial/generar-periodo.php
 * Ajuste: excluye contratistas de la prenomina del personal
 * Nota: el pago a contratistas debe vivir en un flujo separado
 * ============================ */

session_start();
require_once "../../lib/clsConsultas.php";

error_reporting(0);
if (ob_get_level()) {
  ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

function tablaExiste($db, $tabla)
{
  $tablaEsc = $db->escape($tabla);
  $res = $db->consultaGeneral("SHOW TABLES LIKE '$tablaEsc'");
  return ($db->numrows > 0);
}

function obtenerUltimoInsertId($db)
{
  $res = $db->consultaGeneral("SELECT LAST_INSERT_ID() AS id");
  return (int)($res[1]['id'] ?? 0);
}

try {
  $db = new Consultas();

  $idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
  $idUsuario = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;

  if ($idEmpresa <= 0 || $idUsuario <= 0) {
    echo json_encode(["status" => "error", "msg" => "Sesión inválida."]);
    exit;
  }

  $idProyecto = isset($_POST['id_proyecto']) ? (int)$_POST['id_proyecto'] : 0;
  $tipo = isset($_POST['tipo']) ? trim(strtolower($_POST['tipo'])) : '';
  $fechaInicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
  $fechaFin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : '';
  $anio = isset($_POST['anio']) ? (int)$_POST['anio'] : 0;
  $numero = isset($_POST['numero']) ? (int)$_POST['numero'] : 0;
  $categoria = isset($_POST['categoria']) ? trim(strtolower($_POST['categoria'])) : 'personal';

  if ($idProyecto <= 0 || $tipo === '' || $fechaInicio === '' || $fechaFin === '' || $anio <= 0 || $numero <= 0) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos obligatorios."]);
    exit;
  }

  if (!in_array($tipo, ['semanal', 'quincenal'], true)) {
    echo json_encode(["status" => "error", "msg" => "Tipo de nómina inválido."]);
    exit;
  }

  if ($categoria !== 'personal') {
    echo json_encode(["status" => "error", "msg" => "Este endpoint por ahora solo genera la prenómina del personal. El pago a contratistas debe procesarse por separado."]);
    exit;
  }

  $tipoFormateado = ucfirst($tipo);
  $tipoEsc = $db->escape($tipoFormateado);
  $tipoPuro = $tipo;
  $fechaIniEsc = $db->escape($fechaInicio);
  $fechaFinEsc = $db->escape($fechaFin);

  // Validar acceso del usuario al proyecto BioTime por medio del cruce ERP <-> BioTime
  $sqlAcceso = "
        SELECT 1
        FROM rh_biotime_proyecto_relacion rel
        INNER JOIN proyectos_accesos pa
            ON pa.id_proyecto = rel.id_proyecto_erp
           AND pa.id_usuario = $idUsuario
           AND pa.estatus = 1
        WHERE rel.id_biotime_proyecto = $idProyecto
          AND rel.id_empresa = $idEmpresa
          AND rel.estatus = 1
        LIMIT 1
    ";
  $db->consultaGeneral($sqlAcceso);
  if ($db->numrows === 0) {
    echo json_encode(["status" => "error", "msg" => "No tienes permisos para generar nómina en este proyecto."]);
    exit;
  }

  // Verificar duplicados del periodo de personal
  $sqlCheck = "
        SELECT id
        FROM nomina_periodos
        WHERE id_proyecto = $idProyecto
          AND id_empresa = $idEmpresa
          AND tipo = '$tipoEsc'
          AND anio = $anio
          AND numero_periodo = $numero
        LIMIT 1
    ";
  $db->consultaGeneral($sqlCheck);
  if ($db->numrows > 0) {
    echo json_encode(["status" => "error", "msg" => "Este periodo ya fue generado anteriormente."]);
    exit;
  }

  // Calcular rango diario
  $inicioObj = new DateTime($fechaInicio);
  $finObj = new DateTime($fechaFin);
  $finObj->modify('+1 day');
  $periodo = new DatePeriod($inicioObj, new DateInterval('P1D'), $finObj);
  $rangoFechas = [];
  foreach ($periodo as $dt) {
    $rangoFechas[] = $dt->format('Y-m-d');
  }

  // Crear periodo maestro
  $sqlInsertPeriodo = "
        INSERT INTO nomina_periodos
            (id_proyecto, id_empresa, tipo, anio, numero_periodo, fecha_inicio, fecha_fin, estatus, creado_por)
        VALUES
            ($idProyecto, $idEmpresa, '$tipoEsc', $anio, $numero, '$fechaIniEsc', '$fechaFinEsc', 'Borrador', $idUsuario)
    ";
  $db->aplicaQuery($sqlInsertPeriodo);

  $idNuevoPeriodo = obtenerUltimoInsertId($db);
  if ($idNuevoPeriodo <= 0) {
    $resMax = $db->consultaGeneral("SELECT MAX(id) AS max_id FROM nomina_periodos WHERE id_proyecto = $idProyecto AND id_empresa = $idEmpresa AND anio = $anio AND numero_periodo = $numero");
    $idNuevoPeriodo = (int)($resMax[1]['max_id'] ?? 0);
  }

  if ($idNuevoPeriodo <= 0) {
    echo json_encode(["status" => "error", "msg" => "Ocurrió un error al guardar el registro del periodo."]);
    exit;
  }

  $descripcionBitacora = $db->escape("Se generó el periodo de nómina $tipoFormateado No. $numero");
  $db->aplicaQuery("INSERT INTO nomina_bitacora (id_nomina_periodo, id_usuario, accion, descripcion) VALUES ($idNuevoPeriodo, $idUsuario, 'CREACIÓN', '$descripcionBitacora')");

  $empleadosInsertados = 0;
  $contratistasExcluidos = 0;
  $msgAdicional = '';

  try {
    // Precargar asistencias base del rango
    $sqlAsistencias = "
            SELECT emp_code, fecha, entrada, salida
            FROM biotime_asistencias
            WHERE fecha BETWEEN '$fechaIniEsc' AND '$fechaFinEsc'
        ";
    $resAsistencias = $db->consultaGeneral($sqlAsistencias);
    $mapAsistencias = [];

    if ($db->numrows > 0) {
      for ($a = 1; $a <= $db->numrows; $a++) {
        $eCode = $resAsistencias[$a]['emp_code'];
        $f = $resAsistencias[$a]['fecha'];
        $mapAsistencias[$eCode][$f] = [
          'entrada' => $resAsistencias[$a]['entrada'],
          'salida' => $resAsistencias[$a]['salida']
        ];
      }
    }

    $existeRelacionContratista = tablaExiste($db, 'rh_contratista_departamento_biotime');
    $joinContratista = '';
    $campoEsContratista = '0 AS es_contratista';

    if ($existeRelacionContratista) {
      $joinContratista = "
                LEFT JOIN rh_contratista_departamento_biotime rcdb
                    ON rcdb.id_empresa = cp.id_empresa
                   AND rcdb.id_personal_contratista = cp.id
                   AND rcdb.estatus = 1
                   AND (rcdb.fecha_inicio IS NULL OR rcdb.fecha_inicio <= '$fechaFinEsc')
                   AND (rcdb.fecha_fin IS NULL OR rcdb.fecha_fin >= '$fechaIniEsc')
            ";
      $campoEsContratista = 'CASE WHEN rcdb.id IS NULL THEN 0 ELSE 1 END AS es_contratista';
      $msgAdicional = ' (Los contratistas se excluyeron automáticamente de la prenómina del personal).';
    } else {
      $msgAdicional = ' (Aviso: no existe la tabla rh_contratista_departamento_biotime; no fue posible excluir contratistas automáticamente).';
    }

    // Cargar personal operativo del proyecto, excluyendo contratistas cuando ya exista el catálogo maestro
    $sqlPersonal = "
            SELECT
                cp.id,
                bpe.emp_code,
                IFNULL(cp.salario, 0) AS salario,
                IFNULL(cp.viaticos_cantidad, 0) AS viaticos_cantidad,
                IFNULL(cp.viaticos, 'NO') AS aplica_viaticos,
                $campoEsContratista
            FROM biotime_proyecto_empleados bpe
            INNER JOIN biotime_personal_map bpm
                ON BINARY bpe.emp_code = BINARY bpm.emp_code
            INNER JOIN cat_personal cp
                ON bpm.id_personal = cp.id
            $joinContratista
            WHERE bpe.id_proyecto = $idProyecto
              AND bpe.activo = 1
              AND cp.id_empresa = $idEmpresa
              AND cp.estatus = 1
              AND LOWER(cp.tipo_pago) = '$tipoPuro'
            GROUP BY cp.id, bpe.emp_code, cp.salario, cp.viaticos_cantidad, cp.viaticos, es_contratista
            ORDER BY cp.id ASC
        ";

    $resPersonal = $db->consultaGeneral($sqlPersonal);
    $totalRegistros = $db->numrows;

    if ($totalRegistros > 0) {
      for ($i = 1; $i <= $totalRegistros; $i++) {
        if ((int)$resPersonal[$i]['es_contratista'] === 1) {
          $contratistasExcluidos++;
          continue;
        }

        $idPersonal = (int)$resPersonal[$i]['id'];
        $empCode = $resPersonal[$i]['emp_code'];
        $salarioBase = (float)$resPersonal[$i]['salario'];

        $aplicaViaticos = (strtoupper(trim($resPersonal[$i]['aplica_viaticos'])) === 'SI');
        $viaticos = ($tipoPuro === 'semanal' && $aplicaViaticos) ? (float)$resPersonal[$i]['viaticos_cantidad'] : 0.00;

        $db->aplicaQuery("INSERT INTO nomina_detalle (id_nomina_periodo, id_personal, salario_base, viaticos_cantidad) VALUES ($idNuevoPeriodo, $idPersonal, $salarioBase, $viaticos)");

        $idNuevoDetalle = obtenerUltimoInsertId($db);
        if ($idNuevoDetalle <= 0) {
          $resMaxDet = $db->consultaGeneral("SELECT MAX(id) AS max_id FROM nomina_detalle WHERE id_nomina_periodo = $idNuevoPeriodo AND id_personal = $idPersonal");
          $idNuevoDetalle = (int)($resMaxDet[1]['max_id'] ?? 0);
        }

        if ($idNuevoDetalle <= 0) {
          continue;
        }

        $empleadosInsertados++;

        foreach ($rangoFechas as $f) {
          $entradaSql = 'NULL';
          $salidaSql = 'NULL';
          $asistencia = 0;

          if (isset($mapAsistencias[$empCode][$f])) {
            $horaIn = $mapAsistencias[$empCode][$f]['entrada'];
            $horaOut = $mapAsistencias[$empCode][$f]['salida'];

            if (!empty($horaIn) && $horaIn !== '0000-00-00 00:00:00') {
              $entradaSql = "'" . date('H:i:s', strtotime($horaIn)) . "'";
              $asistencia = 1;
            }
            if (!empty($horaOut) && $horaOut !== '0000-00-00 00:00:00') {
              $salidaSql = "'" . date('H:i:s', strtotime($horaOut)) . "'";
              $asistencia = 1;
            }
          }

          $db->aplicaQuery(" 
                        INSERT INTO nomina_asistencia_diaria
                            (id_nomina_detalle, id_personal, fecha, entrada_real, salida_real, asistencia, tiempo_extra)
                        VALUES
                            ($idNuevoDetalle, $idPersonal, '$f', $entradaSql, $salidaSql, $asistencia, 0)
                    ");
        }
      }
    } else {
      $msgAdicional = " (Aviso: No hay empleados activos en el Proyecto BioTime #$idProyecto con pago '$tipoFormateado').";
    }

    if ($contratistasExcluidos > 0) {
      $msgAdicional .= " Se excluyeron $contratistasExcluidos contratista(s) de la prenómina del personal.";
    }
  } catch (Throwable $e2) {
    $msgAdicional = ' (Error técnico cargando empleados: ' . $e2->getMessage() . ')';
  }

  echo json_encode([
    'status' => 'ok',
    'msg' => 'Prenómina de personal generada con ' . $empleadosInsertados . ' empleados cargados.' . $msgAdicional,
    'id_periodo' => $idNuevoPeriodo,
    'categoria' => 'personal'
  ]);
} catch (Throwable $e) {
  echo json_encode(['status' => 'error', 'msg' => 'Error crítico: ' . $e->getMessage()]);
}
