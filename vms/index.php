<?php
session_start();
//include 'lib/clsSeguridad.php';
//error_reporting(0);

require_once 'vendor/autoload.php';
date_default_timezone_set('Etc/GMT+6');

include 'lib/clsConsultas.php';
$clsConsulta = new Consultas();

$time = date("H:i:s");

if (isset($_SESSION['id_user'])) {
    require('php/menu.php');

    if (!function_exists('vmsTablaExiste')) {
        function vmsTablaExiste($clsConsulta, string $tabla): bool
        {
            $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
            if ($tabla === '') {
                return false;
            }

            $clsConsulta->consultaGeneral("SHOW TABLES LIKE '{$tabla}'");
            return ((int)$clsConsulta->numrows > 0);
        }
    }

    if (!function_exists('vmsCargarConfiguracionModulos')) {
        function vmsCargarConfiguracionModulos($clsConsulta): array
        {
            $mapaPorId = [];
            $mapaPorArchivo = [];

            if (!vmsTablaExiste($clsConsulta, 'configuracion_modulos')) {
                return [
                    'por_id' => $mapaPorId,
                    'por_archivo' => $mapaPorArchivo
                ];
            }

            $sql = "
                SELECT
                    cm.modulo_id,
                    cm.habilitado,
                    cm.visible_menu,
                    cm.visible_busqueda,
                    cm.obligatorio,
                    cm.forzar_oculto_si_padre_off,
                    cm.orden_override,
                    cm.paquete_origen,
                    m.archivo,
                    m.modulo_padre
                FROM configuracion_modulos cm
                INNER JOIN modulos m ON m.id = cm.modulo_id
            ";
            $res = $clsConsulta->consultaGeneral($sql);

            if ((int)$clsConsulta->numrows > 0) {
                foreach ($res as $fila) {
                    if (!is_array($fila) || !isset($fila['modulo_id'])) {
                        continue;
                    }

                    $moduloId = (int)$fila['modulo_id'];
                    $filaNormalizada = [
                        'modulo_id' => $moduloId,
                        'habilitado' => isset($fila['habilitado']) ? (int)$fila['habilitado'] : 1,
                        'visible_menu' => isset($fila['visible_menu']) ? (int)$fila['visible_menu'] : 1,
                        'visible_busqueda' => isset($fila['visible_busqueda']) ? (int)$fila['visible_busqueda'] : 1,
                        'obligatorio' => isset($fila['obligatorio']) ? (int)$fila['obligatorio'] : 0,
                        'forzar_oculto_si_padre_off' => isset($fila['forzar_oculto_si_padre_off']) ? (int)$fila['forzar_oculto_si_padre_off'] : 1,
                        'orden_override' => $fila['orden_override'] ?? null,
                        'paquete_origen' => $fila['paquete_origen'] ?? null,
                        'archivo' => $fila['archivo'] ?? '',
                        'modulo_padre' => !empty($fila['modulo_padre']) ? (int)$fila['modulo_padre'] : null
                    ];

                    $mapaPorId[$moduloId] = $filaNormalizada;

                    if (!empty($filaNormalizada['archivo'])) {
                        $mapaPorArchivo[$filaNormalizada['archivo']] = $filaNormalizada;
                    }
                }
            }

            return [
                'por_id' => $mapaPorId,
                'por_archivo' => $mapaPorArchivo
            ];
        }
    }

    if (!function_exists('vmsResolverModuloIdPorArchivo')) {
        function vmsResolverModuloIdPorArchivo(string $archivo, array $modulosSesion = [], array $configuracionPorArchivo = []): ?int
        {
            if ($archivo === '') {
                return null;
            }

            if (isset($configuracionPorArchivo[$archivo]['modulo_id'])) {
                return (int)$configuracionPorArchivo[$archivo]['modulo_id'];
            }

            foreach ($modulosSesion as $idModulo => $modulo) {
                if (($modulo['archivo'] ?? '') === $archivo) {
                    return (int)$idModulo;
                }
            }

            return null;
        }
    }

    if (!function_exists('vmsObtenerPadreModulo')) {
        function vmsObtenerPadreModulo(int $idModulo, array $configuracionPorId = [], array $modulosSesion = []): ?int
        {
            if (isset($configuracionPorId[$idModulo]['modulo_padre']) && !empty($configuracionPorId[$idModulo]['modulo_padre'])) {
                return (int)$configuracionPorId[$idModulo]['modulo_padre'];
            }

            if (isset($modulosSesion[$idModulo]['modulo_padre']) && !empty($modulosSesion[$idModulo]['modulo_padre'])) {
                return (int)$modulosSesion[$idModulo]['modulo_padre'];
            }

            return null;
        }
    }

    if (!function_exists('vmsModuloHabilitadoPorInstalacion')) {
        function vmsModuloHabilitadoPorInstalacion(int $idModulo, array $configuracionPorId = [], array $modulosSesion = [], bool $validarJerarquia = true): bool
        {
            if ($idModulo <= 0) {
                return true;
            }

            if (isset($configuracionPorId[$idModulo]) && (int)$configuracionPorId[$idModulo]['habilitado'] !== 1) {
                return false;
            }

            if ($validarJerarquia && isset($configuracionPorId[$idModulo])) {
                $forzar = (int)($configuracionPorId[$idModulo]['forzar_oculto_si_padre_off'] ?? 1);

                if ($forzar === 1) {
                    $idPadre = vmsObtenerPadreModulo($idModulo, $configuracionPorId, $modulosSesion);

                    if ($idPadre !== null) {
                        if (isset($configuracionPorId[$idPadre]) && (int)$configuracionPorId[$idPadre]['habilitado'] !== 1) {
                            return false;
                        }

                        if ($idPadre !== $idModulo) {
                            return vmsModuloHabilitadoPorInstalacion($idPadre, $configuracionPorId, $modulosSesion, true);
                        }
                    }
                }
            }

            return true;
        }
    }

    if (!function_exists('vmsModuloVisibleEnMenuPorInstalacion')) {
        function vmsModuloVisibleEnMenuPorInstalacion(int $idModulo, array $configuracionPorId = [], array $modulosSesion = []): bool
        {
            if (!vmsModuloHabilitadoPorInstalacion($idModulo, $configuracionPorId, $modulosSesion, true)) {
                return false;
            }

            if (isset($configuracionPorId[$idModulo]) && (int)$configuracionPorId[$idModulo]['visible_menu'] !== 1) {
                return false;
            }

            $idPadre = vmsObtenerPadreModulo($idModulo, $configuracionPorId, $modulosSesion);
            if ($idPadre !== null && isset($configuracionPorId[$idModulo])) {
                $forzar = (int)($configuracionPorId[$idModulo]['forzar_oculto_si_padre_off'] ?? 1);

                if ($forzar === 1 && isset($configuracionPorId[$idPadre])) {
                    if ((int)$configuracionPorId[$idPadre]['habilitado'] !== 1) {
                        return false;
                    }
                    if ((int)$configuracionPorId[$idPadre]['visible_menu'] !== 1) {
                        return false;
                    }
                }
            }

            return true;
        }
    }

    $configuracionModulosContexto = vmsCargarConfiguracionModulos($clsConsulta);
    $configuracionModulosMapa = $configuracionModulosContexto['por_id'];
    $configuracionModulosPorArchivo = $configuracionModulosContexto['por_archivo'];

    $mensajeModuloDeshabilitado = '';
    $idModuloNavActual = null;

    if (isset($nav) && trim((string)$nav) !== '') {
        $idModuloNavActual = vmsResolverModuloIdPorArchivo((string)$nav, $_SESSION['modulos'] ?? [], $configuracionModulosPorArchivo);

        if (
            $idModuloNavActual !== null &&
            !vmsModuloHabilitadoPorInstalacion($idModuloNavActual, $configuracionModulosMapa, $_SESSION['modulos'] ?? [], true)
        ) {
            $mensajeModuloDeshabilitado = 'El módulo solicitado no está habilitado en esta instalación.';
            $navegar = '404.php';
        }
    }

    include 'lib/clsFechas.php';
    $clsFecha = new Fechas();

    $fecha_hoy  = $clsFecha->fecha_Hoy();
    $fecha_bd   = $clsFecha->dame_fecha_bd();
    $fechahora  = $clsFecha->fechaHora();

    $hora       = date("H:i:s");
    $anio_hoy   = substr($fecha_bd, 0, 4);
    $mes_hoy    = substr($fecha_bd, 5, 2);
    $dia_hoy    = substr($fecha_bd, 8, 2);

    $logo       = 'no_img.png?update=rand()';
    $favicon    = 'no_favicon.png?update=rand()';
    $titulo     = 'Sin titulo';
    $clickAviso = ' onclick="avisoRol();"';

    $mostrar_modal = (!isset($_SESSION['razon_social']) || empty($_SESSION['razon_social']))
        && (count($_SESSION['empresas_disponibles'] ?? []) > 1);
?>
    <!DOCTYPE html>
    <html>

    <head>
        <base href="<?php echo $base; ?>">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Administrador | VMS</title>

        <link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="img/favicon.svg" />
        <link rel="shortcut icon" href="img/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png" />
        <link rel="manifest" href="img/site.webmanifest" />

        <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
        <link href="assets/bootstrap-5-02/css/bootstrap.min.css" rel="stylesheet">

        <script src="assets/js/jquery-3.6.3.min.js"></script>

        <script src="assets/js/validate/jquery.validate.min.js"></script>
        <script src="assets/js/validate/additional-methods.min.js"></script>
        <script src="assets/js/validate/messages_es.min.js"></script>

        <link rel="stylesheet" href="assets/datatables/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="assets/datatables/css/fixedHeader.dataTables.min.css">

        <link rel="stylesheet" href="assets/alertify/css/alertify.min.css" />
        <link rel="stylesheet" href="assets/alertify/css/default.min.css" />
        <link rel="stylesheet" href="assets/alertify/css/semantic.min.css" />
        <link rel="stylesheet" href="assets/alertify/css/bootstrap.min.css" />
        <script src="assets/alertify/js/alertify.min.js"></script>

        <link rel="stylesheet" href="dist/tagsinput/bootstrap-tagsinput.css">

        <link href="assets/css/navbar-styles.css" rel="stylesheet">
        <link href="assets/css/style-modulos.css" rel="stylesheet">

        <?php
        $cssVariablesDinamicas = '';

        $sqlEstilosDinamicos = "SELECT clave, valor, unidad
                        FROM configuracion_estilos
                        WHERE estatus = 'activo'
                        ORDER BY orden ASC, id ASC";
        $resEstilosDinamicos = $clsConsulta->consultaGeneral($sqlEstilosDinamicos);

        if ($clsConsulta->numrows > 0) {
            foreach ($resEstilosDinamicos as $estilo) {
                $clave  = trim((string)($estilo['clave'] ?? ''));
                $valor  = trim((string)($estilo['valor'] ?? ''));
                $unidad = trim((string)($estilo['unidad'] ?? ''));

                if ($clave !== '' && $valor !== '') {
                    if ($unidad !== '' && is_numeric($valor)) {
                        $cssVariablesDinamicas .= '--' . $clave . ': ' . $valor . $unidad . ';' . PHP_EOL;
                    } else {
                        $cssVariablesDinamicas .= '--' . $clave . ': ' . $valor . ';' . PHP_EOL;
                    }
                }
            }
        }

        if ($cssVariablesDinamicas !== '') {
        ?>
            <style>
                :root {
                    <?= $cssVariablesDinamicas; ?>
                }
            </style>
        <?php
        }
        ?>

        <style>
            #modalSpiner .modal-content {
                background-color: rgba(0, 0, 0, 0.3);
            }

            #preloader {
                position: fixed;
                z-index: 9999;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: white;
            }

            .bootstrap-tagsinput .tag {
                margin-right: 2px;
                color: #666666 !important;
                background-color: #E3EBF7 !important;
                padding: 0.2rem 0.5rem 0.2rem 0.5rem;
                border-radius: 5px;
                margin-top: 15px !important;
                margin-bottom: 15px !important;
                width: 100% !important;
            }

            .bootstrap-tagsinput {
                width: 100%;
            }

            table.dataTable {
                width: 100% !important;
            }

            #search {
                border-color: #032759ff !important;
            }

            [data-title]:hover:after {
                opacity: 1;
                transition: all 0.1s ease 0.5s;
                visibility: visible;
            }

            [data-title]:after {
                content: attr(data-title);
                background-color: #E3EBF7;
                color: #000;
                font-size: 11px;
                font-family: Arial, Helvetica, sans-serif;
                position: absolute;
                padding: 3px 20px;
                bottom: -.6em;
                right: 100%;
                white-space: nowrap;
                box-shadow: 1px 1px 3px #222222;
                opacity: 0;
                border: 1px solid #111111;
                z-index: 99999;
                visibility: hidden;
                border-radius: 6px;
                border-color: #E3EBF7;
            }

            [data-title] {
                position: relative;
            }

            datalist {
                background-color: #E3EBF7 !important;
                color: #000 !important;
            }

            #datalistOptions {
                background-color: #E3EBF7 !important;
                color: #000 !important;
            }

            .alertify-notifier .ajs-message.ajs-custom {
                background: transparent;
                box-shadow: none;
                padding: 0;
            }
        </style>
        <script>
            window.onload = function() {
                $('#preloader').fadeOut();
            }
        </script>
    </head>

    <body class="hold-transition sidebar-mini layout-fixed">
        <div id="spinner" style="display: none;">
            <div class="spinner-border text-warning" style="width: 10rem; height: 10rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="wrapper">
            <?php include "php/navbar.php"; ?>

            <div class="content-wrapper">
                <?php
                require $navegar;
                include 'php/footer.php';
                ?>
            </div>
        </div>

        <script src="assets/bootstrap-5-02/js/bootstrap.bundle.min.js"></script>

        <script src="assets/datatables/js/jquery.dataTables.min.js"></script>
        <script src="assets/datatables/js/dataTables.bootstrap5.min.js"></script>
        <script src="assets/datatables/js/dataTables.responsive.min.js"></script>
        <script src="assets/datatables/js/dataTables.buttons.min.js"></script>
        <script src="assets/datatables/js/jszip.min.js"></script>
        <script src="assets/datatables/js/buttons.html5.min.js"></script>
        <script src="assets/datatables/js/buttons.print.min.js"></script>

        <script src="assets/alertify/js/alertify.min.js"></script>

        <script src="assets/js/validate/jquery.validate.min.js"></script>
        <script src="assets/js/validate/additional-methods.min.js"></script>
        <script src="assets/js/validate/messages_es.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function(el) {
                    new bootstrap.Tooltip(el);
                });
            });

            <?php if ($mensajeModuloDeshabilitado !== ''): ?>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof alertify !== 'undefined') {
                        alertify.error('<?php echo addslashes($mensajeModuloDeshabilitado); ?>');
                    }
                });
            <?php endif; ?>

            document.body.classList.add('loading');
            window.onload = function() {
                document.getElementById('spinner').style.display = 'none';
                document.body.classList.remove('loading');
            };
        </script>
    </body>

    </html>
<?php
} else {
    session_unset();
    setcookie(session_name(), '', time() - 42000, '/');
    session_write_close();
    include 'login.php';
    exit;
}
?>