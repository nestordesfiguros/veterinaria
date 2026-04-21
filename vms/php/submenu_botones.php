<?php
/* ========================================================================== */
/* Archivo: php/submenu_botones.php                                           */
/* Ruta: php/submenu_botones.php                                              */
/* ========================================================================== */

if (!class_exists('permisosHijos')) {
    require_once __DIR__ . '/../lib/clsPermisosHijos.php';
}

$__archivoActual  = $__archivoActual  ?? ($nav ?? '');
$__idPadreForzado = $__idPadreForzado ?? null;
$__layout         = $__layout         ?? 'franja';
$__btnSize        = $__btnSize        ?? 'btn-square-sm';
$__bgWrapperClass = $__bgWrapperClass ?? '';
$__btnColor       = $__btnColor       ?? 'btn-light';

$modulosSesion = $_SESSION['modulos'] ?? [];
$configuracionModulosMapaLocal = $configuracionModulosMapa ?? [];
$configuracionModulosPorArchivoLocal = $configuracionModulosPorArchivo ?? [];

if (!function_exists('submenuTienePermisoModulo')) {
    function submenuTienePermisoModulo(int $idModulo, string $permiso = 'ver'): bool
    {
        if (!empty($_SESSION['permisos_recurso'])) {
            foreach ($_SESSION['permisos_recurso'] as $permisoRecurso) {
                if (
                    isset($permisoRecurso['recurso_tipo'], $permisoRecurso['recurso_id']) &&
                    $permisoRecurso['recurso_tipo'] === 'modulo' &&
                    (int)$permisoRecurso['recurso_id'] === $idModulo &&
                    !empty($permisoRecurso["puede_{$permiso}"])
                ) {
                    return true;
                }
            }
        }

        return !empty($_SESSION['permisos'][$idModulo][$permiso]);
    }
}

// 1) Determinar módulo activo
$idActivo = permisosHijos::detectarModuloActivoPorArchivo((string)$__archivoActual);

if (!$idActivo && function_exists('vmsResolverModuloIdPorArchivo')) {
    $idActivo = vmsResolverModuloIdPorArchivo((string)$__archivoActual, $modulosSesion, $configuracionModulosPorArchivoLocal);
}

// 2) Determinar padre
$idPadre = $__idPadreForzado ?: permisosHijos::obtenerPadre($idActivo);
if (!$idPadre) {
    return;
}

if (
    function_exists('vmsModuloVisibleEnMenuPorInstalacion') &&
    !vmsModuloVisibleEnMenuPorInstalacion((int)$idPadre, $configuracionModulosMapaLocal, $modulosSesion)
) {
    return;
}

// 3) Obtener hijos visibles base
$hijos = permisosHijos::obtenerHijosVisibles($idPadre);
if (empty($hijos)) {
    return;
}

// 4) Filtrar hijos por permisos y por switch backdoor
$hijosFiltrados = [];
foreach ($hijos as $idHijo) {
    $idHijo = (int)$idHijo;

    if (!isset($modulosSesion[$idHijo])) {
        continue;
    }

    if (!submenuTienePermisoModulo($idHijo, 'ver')) {
        continue;
    }

    if (
        function_exists('vmsModuloVisibleEnMenuPorInstalacion') &&
        !vmsModuloVisibleEnMenuPorInstalacion($idHijo, $configuracionModulosMapaLocal, $modulosSesion)
    ) {
        continue;
    }

    $hijosFiltrados[] = $idHijo;
}

if (empty($hijosFiltrados)) {
    return;
}

// 5) Render
if ($__layout === 'franja'): ?>
    <div class="<?php echo htmlspecialchars($__bgWrapperClass, ENT_QUOTES, 'UTF-8'); ?> submenu-franja-wrapper">
        <div class="container py-2">
            <div class="submenu-franja-container">
                <?php foreach ($hijosFiltrados as $idHijo):
                    $h = $modulosSesion[$idHijo];
                    $esActivo = (!empty($h['archivo']) && $h['archivo'] === $__archivoActual);
                    $claseActivo = $esActivo ? ' submenu-active' : '';
                    $icono = !empty($h['icono'])
                        ? '<i class="submenu-franja-icon ' . htmlspecialchars((string)$h['icono'], ENT_QUOTES, 'UTF-8') . '"></i>'
                        : '';
                ?>
                    <div class="text-center">
                        <a class="btn submenu-franja-btn<?php echo $claseActivo; ?>" href="<?php echo htmlspecialchars((string)($h['archivo'] ?: '#'), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo $icono; ?>
                            <p class="submenu-franja-text"><?php echo strtoupper(htmlspecialchars((string)$h['nombre'], ENT_QUOTES, 'UTF-8')); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="row justify-content-center">
        <?php foreach ($hijosFiltrados as $idHijo):
            $h = $modulosSesion[$idHijo];
            $esActivo = (!empty($h['archivo']) && $h['archivo'] === $__archivoActual);
            $claseActivo = $esActivo ? ' submenu-active' : '';
            $icono = !empty($h['icono'])
                ? '<i class="submenu-grid-icon ' . htmlspecialchars((string)$h['icono'], ENT_QUOTES, 'UTF-8') . '"></i>'
                : '<i class="submenu-grid-icon fa fa-circle"></i>';
        ?>
            <div class="col-lg-2 col-sm-3 col-6 mt-2 pe-1 submenu-grid-item">
                <a class="nav-link" href="<?php echo htmlspecialchars((string)($h['archivo'] ?: '#'), ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="button" class="btn submenu-grid-btn<?php echo $claseActivo; ?>">
                        <?php echo $icono; ?>
                        <p class="submenu-grid-text"><?php echo htmlspecialchars((string)$h['nombre'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </button>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>