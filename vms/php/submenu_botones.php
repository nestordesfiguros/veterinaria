<?php
/* ========================================================================== */
/* Archivo: php/submenu_botones.php                                           */
/* Ruta: php/submenu_botones.php                                              */
/* ========================================================================== */

// Requiere: lib/permisosHijos.php y $_SESSION['modulos'], $_SESSION['permisos']
// Parámetros opcionales (defínelos ANTES de incluir este archivo):
//   $__archivoActual   (string)  Archivo base actual (p.ej. $nav)
//   $__idPadreForzado  (int)     Si quieres forzar un padre específico
//   $__layout          (string)  'franja' (debajo del navbar) | 'grid' (dentro de la vista)
//   $__btnSize         (string)  'btn-square-sm' | 'btn-square-xl'
//   $__bgWrapperClass  (string)  Clases para wrapper (ej. "bg-light border-bottom")
//   $__btnColor        (string)  Clase de color base para botones inactivos (ej. 'btn-light')

if (!class_exists('permisosHijos')) require_once __DIR__ . '/../lib/clsPermisosHijos.php';

$__archivoActual  = $__archivoActual  ?? ($nav ?? '');
$__idPadreForzado = $__idPadreForzado ?? null;
$__layout         = $__layout         ?? 'franja';
$__btnSize        = $__btnSize        ?? 'btn-square-sm';
$__bgWrapperClass = $__bgWrapperClass ?? '';
$__btnColor       = $__btnColor       ?? 'btn-light';

// 1) Determinar padre activo
$idActivo = permisosHijos::detectarModuloActivoPorArchivo((string)$__archivoActual);
$idPadre  = $__idPadreForzado ?: permisosHijos::obtenerPadre($idActivo);
if (!$idPadre) return;

// 2) Obtener hijos visibles
$hijos = permisosHijos::obtenerHijosVisibles($idPadre);
if (empty($hijos)) return;

// 3) Render según layout
if ($__layout === 'franja'): ?>
    <div class="<?php echo htmlspecialchars($__bgWrapperClass); ?> submenu-franja-wrapper">
        <div class="container py-2">
            <div class="submenu-franja-container">
                <?php foreach ($hijos as $idHijo):
                    $h = $_SESSION['modulos'][$idHijo];
                    $esActivo = (!empty($h['archivo']) && $h['archivo'] === $__archivoActual);
                    $claseActivo = $esActivo ? ' submenu-active' : '';
                    $icono = !empty($h['icono'])
                        ? '<i class="submenu-franja-icon ' . htmlspecialchars($h['icono']) . '"></i>'
                        : '';
                ?>
                    <div class="text-center">
                        <a class="btn submenu-franja-btn<?php echo $claseActivo; ?>" href="<?php echo htmlspecialchars($h['archivo'] ?: '#'); ?>">
                            <?php echo $icono; ?>
                            <p class="submenu-franja-text"><?php echo strtoupper(htmlspecialchars($h['nombre'])); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="row justify-content-center">
        <?php foreach ($hijos as $idHijo):
            $h = $_SESSION['modulos'][$idHijo];
            $esActivo = (!empty($h['archivo']) && $h['archivo'] === $__archivoActual);
            $claseActivo = $esActivo ? ' submenu-active' : '';
            $icono = !empty($h['icono'])
                ? '<i class="submenu-grid-icon ' . htmlspecialchars($h['icono']) . '"></i>'
                : '<i class="submenu-grid-icon fa fa-circle"></i>';
        ?>
            <div class="col-lg-2 col-sm-3 col-6 mt-2 pe-1 submenu-grid-item">
                <a class="nav-link" href="<?php echo htmlspecialchars($h['archivo'] ?: '#'); ?>">
                    <button type="button" class="btn submenu-grid-btn<?php echo $claseActivo; ?>">
                        <?php echo $icono; ?>
                        <p class="submenu-grid-text"><?php echo htmlspecialchars($h['nombre']); ?></p>
                    </button>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>