<?php
// contenido/modulos-lista.php

// 1) Traer módulos con suficiente info para agrupar y mostrar permisos
$sql = "SELECT m1.id,
               m1.nombre,
               m1.archivo,
               m1.icono,
               m1.observaciones,
               m1.modulo_padre,
               m1.tipo_modulo,
               m1.canal,
               m1.app_id,
               m1.app_root_key,
               IFNULL(m1.soporta_crear,1)   AS soporta_crear,
               IFNULL(m1.soporta_editar,1)  AS soporta_editar,
               IFNULL(m1.soporta_eliminar,1)AS soporta_eliminar,
               m2.nombre AS padre
        FROM modulos m1
        LEFT JOIN modulos m2 ON m1.modulo_padre = m2.id
        ORDER BY m1.canal, m1.nombre";
$modulos = $clsConsulta->consultaGeneral($sql) ?: [];

// 2) Agrupar: padres e hijos
$padres = [];
$hijosPorPadre = [];
$appRoots = []; // Para identificar módulos raíz de apps

foreach ($modulos as $k => $m) {
    if (!is_numeric($k)) continue;
    if (empty($m['modulo_padre'])) {
        $padres[$m['id']] = $m;
        // Si es módulo raíz de app, registrar
        if (($m['canal'] ?? '') === 'app' && !empty($m['app_id'])) {
            $appRoots[$m['id']] = $m['app_id'];
        }
    } else {
        $hijosPorPadre[$m['modulo_padre']][] = $m;
    }
}

// 3) Ordenar por canal y nombre (manteniendo keys en padres)
uasort($padres, function ($a, $b) {
    // Primero por canal (ERP primero, luego APP)
    $canalOrder = ['erp' => 1, 'app' => 2];
    $canalA = $canalOrder[$a['canal'] ?? ''] ?? 3;
    $canalB = $canalOrder[$b['canal'] ?? ''] ?? 3;

    if ($canalA !== $canalB) {
        return $canalA <=> $canalB;
    }

    // Luego por nombre
    return strcasecmp((string)($a['nombre'] ?? ''), (string)($b['nombre'] ?? ''));
});

foreach ($hijosPorPadre as $pid => &$lst) {
    usort($lst, fn($a, $b) => strcasecmp((string)($a['nombre'] ?? ''), (string)($b['nombre'] ?? '')));
}
unset($lst);

// Helpers de UI
function tipoUiLabel($t)
{
    $t = strtolower((string)$t);
    if ($t === 'padre')     return 'Menú principal';
    if ($t === 'pagina')    return 'Acceso (botones)';
    if ($t === 'lista')     return 'Listado';
    if ($t === 'submodulo') return 'Sección interna';
    if ($t === 'accion')    return 'Acción puntual';
    return '';
}

function canalBadge($canal)
{
    if ($canal === 'app') {
        return '<span class="badge bg-success">APP</span>';
    }
    return '<span class="badge bg-primary">ERP</span>';
}
?>
<style>
    .tag-utilerias {
        display: inline-block;
        padding: .15rem .45rem;
        border-radius: 999px;
        font-size: .75rem;
        background: #e8f1ff;
        color: #0b57d0;
        border: 1px solid #cfe1ff;
        margin-left: .35rem
    }

    .row-utilerias {
        background: #fbfdff
    }

    .pill {
        display: inline-block;
        min-width: 1.6rem;
        padding: .1rem .35rem;
        border-radius: 6px;
        border: 1px solid #e2e6ea;
        background: #fff;
        font-size: .75rem
    }

    .pill.on {
        background: #e8fff3;
        border-color: #c6f5dc;
        color: #0f5132
    }

    .pill.off {
        background: #fff1f0;
        border-color: #ffd1cf;
        color: #842029
    }

    .w-120 {
        min-width: 120px
    }

    .app-module-row {
        background-color: #f0fff4;
    }

    .app-root-badge {
        background: #10b981;
        color: white;
        font-size: 0.7em;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 5px;
    }

    .app-id {
        font-family: monospace;
        font-size: 0.9em;
        color: #059669;
    }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-body">
                        <h1 class="h3 mb-3">Módulos y Menús</h1>
                        <a href="modulos-altas" class="btn btn-success mb-3">+ Nuevo módulo</a>

                        <div class="alert alert-info">
                            <strong>Nota:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Los módulos con <span class="badge bg-success">APP</span> son exclusivos para la aplicación móvil.</li>
                                <li>Los módulos con <span class="badge bg-primary">ERP</span> son para la interfaz web del sistema.</li>
                                <li>Para módulos tipo <em>Listado</em> o <em>Sección interna</em> puedes definir <strong>Acciones especiales</strong> (p. ej. "Imprimir", "Ver ODC", "Nueva ODC").</li>
                            </ul>
                        </div>

                        <table class="table table-bordered align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Canal</th>
                                    <th>Nombre</th>
                                    <th>Archivo / App ID</th>
                                    <th>Ícono</th>
                                    <th class="w-120">Módulo Padre</th>
                                    <th class="w-120">Tipo UI</th>
                                    <th class="text-center">Soporta</th>
                                    <th>Observaciones</th>
                                    <th style="width:320px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($padres as $p):
                                    $esApp = (($p['canal'] ?? '') === 'app');
                                    $esAppRoot = ($esApp && !empty($p['app_id']));
                                    $rowClass = $esApp ? 'app-module-row' : '';
                                ?>
                                    <tr class="table-secondary <?= $rowClass ?>">
                                        <td class="text-center"><?= canalBadge($p['canal'] ?? 'erp') ?></td>
                                        <td class="fw-bold">
                                            <?= htmlspecialchars($p['nombre'] ?? '') ?>
                                            <?php if ($esAppRoot): ?>
                                                <span class="app-root-badge" title="Módulo raíz de app">RAÍZ</span>
                                            <?php endif; ?>
                                            <?php if (!empty($p['app_id'])): ?>
                                                <div class="app-id">ID: <?= htmlspecialchars($p['app_id'] ?? '') ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($esApp): ?>
                                                <span class="text-muted">App: </span>
                                                <?php if (!empty($p['app_root_key'])): ?>
                                                    <code><?= htmlspecialchars($p['app_root_key'] ?? '') ?></code>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($p['archivo'] ?? '') ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($p['icono'])): ?>
                                                <i class="<?= htmlspecialchars($p['icono'] ?? '') ?>"></i>
                                                <span class="ms-1"><?= htmlspecialchars($p['icono'] ?? '') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>—</td>
                                        <td><span class="pill"><?= tipoUiLabel($p['tipo_modulo'] ?? '') ?: 'Menú principal' ?></span></td>
                                        <td class="text-center">
                                            <span class="pill <?= ((int)($p['soporta_crear'] ?? 1) === 1 ? 'on' : 'off') ?>">Crear</span>
                                            <span class="pill <?= ((int)($p['soporta_editar'] ?? 1) === 1 ? 'on' : 'off') ?>">Editar</span>
                                            <span class="pill <?= ((int)($p['soporta_eliminar'] ?? 1) === 1 ? 'on' : 'off') ?>">Eliminar</span>
                                        </td>
                                        <td><?= htmlspecialchars($p['observaciones'] ?? '') ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones">
                                                <a href="modulos-editar/<?= (int)($p['id'] ?? 0) ?>" class="btn btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-eliminar" data-id="<?= (int)($p['id'] ?? 0) ?>" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>

                                                <!-- Los padres no tienen acciones especiales -->
                                                <button type="button" class="btn btn-info btn-codigo"
                                                    data-nombre="<?= htmlspecialchars($p['nombre'] ?? '') ?>"
                                                    data-archivo="<?= htmlspecialchars($p['archivo'] ?? '') ?>"
                                                    data-icono="<?= htmlspecialchars($p['icono'] ?? '') ?>"
                                                    data-canal="<?= htmlspecialchars($p['canal'] ?? '') ?>"
                                                    data-app_id="<?= htmlspecialchars($p['app_id'] ?? '') ?>"
                                                    data-padre=""
                                                    data-observaciones="<?= htmlspecialchars($p['observaciones'] ?? '') ?>"
                                                    title="Ver código">
                                                    <i class="fas fa-code"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <?php if (!empty($hijosPorPadre[$p['id'] ?? 0])): ?>
                                        <?php foreach ($hijosPorPadre[$p['id']] as $h):
                                            $esUtil = (strtolower((string)($p['nombre'] ?? '')) === 'utilerías' || strtolower((string)($p['nombre'] ?? '')) === 'utilerias');
                                            $hijoEsApp = (($h['canal'] ?? '') === 'app');
                                            $hijoRowClass = $hijoEsApp ? 'app-module-row' : ($esUtil ? 'row-utilerias' : '');
                                        ?>
                                            <tr class="<?= $hijoRowClass ?>">
                                                <td class="text-center"><?= canalBadge($h['canal'] ?? 'erp') ?></td>
                                                <td>
                                                    <?= htmlspecialchars($h['nombre'] ?? '') ?>
                                                    <?php if ($esUtil): ?><span class="tag-utilerias">Acceso de Utilerías</span><?php endif; ?>
                                                    <?php if (!empty($h['app_id'])): ?>
                                                        <div class="app-id">ID: <?= htmlspecialchars($h['app_id'] ?? '') ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($hijoEsApp): ?>
                                                        <span class="text-muted">Hereda: </span>
                                                        <?php if (isset($appRoots[$p['id']])): ?>
                                                            <code><?= htmlspecialchars($p['app_id'] ?? '') ?></code>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($h['archivo'] ?? '') ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($h['icono'])): ?>
                                                        <i class="<?= htmlspecialchars($h['icono'] ?? '') ?>"></i>
                                                        <span class="ms-1"><?= htmlspecialchars($h['icono'] ?? '') ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($p['nombre'] ?? '') ?></td>
                                                <td><span class="pill"><?= tipoUiLabel($h['tipo_modulo'] ?? '') ?></span></td>
                                                <td class="text-center">
                                                    <span class="pill <?= ((int)($h['soporta_crear'] ?? 1) === 1 ? 'on' : 'off') ?>">Crear</span>
                                                    <span class="pill <?= ((int)($h['soporta_editar'] ?? 1) === 1 ? 'on' : 'off') ?>">Editar</span>
                                                    <span class="pill <?= ((int)($h['soporta_eliminar'] ?? 1) === 1 ? 'on' : 'off') ?>">Eliminar</span>
                                                </td>
                                                <td><?= htmlspecialchars($h['observaciones'] ?? '') ?></td>
                                                <td class="d-flex gap-2">
                                                    <a href="modulos-editar/<?= (int)($h['id'] ?? 0) ?>" class="btn btn-primary btn-sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="<?= (int)($h['id'] ?? 0) ?>" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>

                                                    <?php
                                                    $tipo = strtolower((string)($h['tipo_modulo'] ?? ''));
                                                    $puedeAcciones = ($tipo === 'lista' || $tipo === 'submodulo');
                                                    ?>
                                                    <button type="button"
                                                        class="btn btn-warning btn-sm btn-acciones-especiales"
                                                        data-id="<?= (int)($h['id'] ?? 0) ?>"
                                                        data-nombre="<?= htmlspecialchars($h['nombre'] ?? '') ?>"
                                                        title="Acciones especiales"
                                                        <?= $puedeAcciones ? '' : 'disabled' ?>>
                                                        <i class="fas fa-bolt"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-info btn-sm btn-codigo"
                                                        data-nombre="<?= htmlspecialchars($h['nombre'] ?? '') ?>"
                                                        data-archivo="<?= htmlspecialchars($h['archivo'] ?? '') ?>"
                                                        data-icono="<?= htmlspecialchars($h['icono'] ?? '') ?>"
                                                        data-canal="<?= htmlspecialchars($h['canal'] ?? '') ?>"
                                                        data-app_id="<?= htmlspecialchars($h['app_id'] ?? '') ?>"
                                                        data-padre="<?= htmlspecialchars($p['nombre'] ?? '') ?>"
                                                        data-observaciones="<?= htmlspecialchars($h['observaciones'] ?? '') ?>"
                                                        title="Ver código">
                                                        <i class="fas fa-code"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <?php
                                // Huérfanos (hijos cuyo padre no está en $padres)
                                foreach ($hijosPorPadre as $pid => $lst) {
                                    if (isset($padres[$pid])) continue;
                                    foreach ($lst as $h) {
                                        $hijoEsApp = (($h['canal'] ?? '') === 'app');
                                        $hijoRowClass = $hijoEsApp ? 'app-module-row' : '';
                                ?>
                                        <tr class="<?= $hijoRowClass ?>">
                                            <td class="text-center"><?= canalBadge($h['canal'] ?? 'erp') ?></td>
                                            <td><?= htmlspecialchars($h['nombre'] ?? '') ?></td>
                                            <td>
                                                <?php if ($hijoEsApp): ?>
                                                    <span class="text-muted">App ID: </span><?= htmlspecialchars($h['app_id'] ?? '') ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($h['archivo'] ?? '') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($h['icono'])): ?>
                                                    <i class="<?= htmlspecialchars($h['icono'] ?? '') ?>"></i>
                                                    <span class="ms-1"><?= htmlspecialchars($h['icono'] ?? '') ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($h['padre'] ?? '(Sin padre)') ?></td>
                                            <td><span class="pill"><?= tipoUiLabel($h['tipo_modulo'] ?? '') ?></span></td>
                                            <td class="text-center">
                                                <span class="pill <?= ((int)($h['soporta_crear'] ?? 1) === 1 ? 'on' : 'off') ?>">Crear</span>
                                                <span class="pill <?= ((int)($h['soporta_editar'] ?? 1) === 1 ? 'on' : 'off') ?>">Editar</span>
                                                <span class="pill <?= ((int)($h['soporta_eliminar'] ?? 1) === 1 ? 'on' : 'off') ?>">Eliminar</span>
                                            </td>
                                            <td><?= htmlspecialchars($h['observaciones'] ?? '') ?></td>
                                            <td class="d-flex gap-2">
                                                <a href="modulos-editar/<?= (int)($h['id'] ?? 0) ?>" class="btn btn-primary btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="<?= (int)($h['id'] ?? 0) ?>" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php
                                                $tipo = strtolower((string)($h['tipo_modulo'] ?? ''));
                                                $puedeAcciones = ($tipo === 'lista' || $tipo === 'submodulo');
                                                ?>
                                                <button type="button"
                                                    class="btn btn-warning btn-sm btn-acciones-especiales"
                                                    data-id="<?= (int)($h['id'] ?? 0) ?>"
                                                    data-nombre="<?= htmlspecialchars($h['nombre'] ?? '') ?>"
                                                    title="Acciones especiales"
                                                    <?= $puedeAcciones ? '' : 'disabled' ?>>
                                                    <i class="fas fa-bolt"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-info btn-sm btn-codigo"
                                                    data-nombre="<?= htmlspecialchars($h['nombre'] ?? '') ?>"
                                                    data-archivo="<?= htmlspecialchars($h['archivo'] ?? '') ?>"
                                                    data-icono="<?= htmlspecialchars($h['icono'] ?? '') ?>"
                                                    data-canal="<?= htmlspecialchars($h['canal'] ?? '') ?>"
                                                    data-app_id="<?= htmlspecialchars($h['app_id'] ?? '') ?>"
                                                    data-padre="<?= htmlspecialchars($h['padre'] ?? '') ?>"
                                                    data-observaciones="<?= htmlspecialchars($h['observaciones'] ?? '') ?>"
                                                    title="Ver código">
                                                    <i class="fas fa-code"></i>
                                                </button>
                                            </td>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>

                        <!-- Modal simple para código (actualizado con nuevos campos) -->
                        <div id="codigoModal" class="modal" tabindex="-1" style="display:none; position:fixed; top:10%; left:10%; width:80%; max-height:80vh; overflow:auto; background:#fff; padding:20px; border:1px solid #ccc; box-shadow:0 0 10px rgba(0,0,0,.35); z-index:1050;">
                            <h5>Ejemplo de código SQL para insertar este módulo</h5>
                            <pre id="codigoSQL" style="background:#eee; padding:10px; overflow:auto;"></pre>
                            <h5>Ejemplo de manejo de permisos en PHP</h5>
                            <pre id="codigoPHP" style="background:#eee; padding:10px; overflow:auto;"></pre>
                            <div class="text-end">
                                <button class="btn btn-secondary btn-cerrar-modal">Cerrar</button>
                            </div>
                        </div>

                        <!-- Modal Acciones Especiales (sin cambios) -->
                        <div id="accionesModal" class="modal" tabindex="-1" style="display:none; position:fixed; top:5%; left:8%; width:84%; max-height:86vh; overflow:auto; background:#fff; padding:20px; border:1px solid #ccc; box-shadow:0 0 10px rgba(0,0,0,.35); z-index:1050;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="mb-0">Acciones especiales de: <span id="accionesTitulo"></span></h5>
                                <button class="btn btn-secondary btn-cerrar-acciones">Cerrar</button>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-lg-5">
                                    <div class="card">
                                        <div class="card-header">Agregar / Editar acción</div>
                                        <div class="card-body">
                                            <form id="formAccion">
                                                <input type="hidden" id="acc_id" value="">
                                                <input type="hidden" id="acc_modulo_id" value="">
                                                <div class="mb-2">
                                                    <label class="form-label">Etiqueta (texto visible)</label>
                                                    <input type="text" id="acc_etiqueta" class="form-control" placeholder="Imprimir ODC, Ver Órdenes de cambio, Nueva ODC...">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Clave (interna)</label>
                                                    <input type="text" id="acc_clave" class="form-control" placeholder="imprimir_odc, ver_odc, crear_odc...">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Descripción</label>
                                                    <textarea id="acc_descripcion" class="form-control" rows="2"></textarea>
                                                </div>
                                                <div class="mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="acc_es_puente">
                                                        <label class="form-check-label" for="acc_es_puente">Vincula permiso en otro módulo</label>
                                                    </div>
                                                </div>
                                                <div id="acc_puente_box" class="mb-2 d-none">
                                                    <div class="row g-2">
                                                        <div class="col-8">
                                                            <label class="form-label">Módulo destino</label>
                                                            <select id="acc_mod_destino" class="form-select"></select>
                                                        </div>
                                                        <div class="col-4">
                                                            <label class="form-label">Permiso</label>
                                                            <select id="acc_perm_destino" class="form-select">
                                                                <option value="">—</option>
                                                                <option value="ver">Ver</option>
                                                                <option value="crear">Crear</option>
                                                                <option value="editar">Editar</option>
                                                                <option value="eliminar">Eliminar</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-7">
                                    <div class="card">
                                        <div class="card-header">Listado</div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Etiqueta</th>
                                                            <th>Clave</th>
                                                            <th>Puente</th>
                                                            <th style="width:120px;">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="accionesTbody"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- /Modal Acciones Especiales -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(function() {

        // --- ELIMINAR MÓDULO (siempre 2 pasos: validar -> confirmar -> eliminar) ---
        $('.table').off('click', '.btn-eliminar');

        const deleting = {};

        $('.table').on('click', '.btn-eliminar', function(e) {
            e.preventDefault();

            const $btn = $(this);
            const id = parseInt($btn.data('id'), 10);
            const $row = $btn.closest('tr');
            if (!id) return;

            if (deleting[id]) return;
            deleting[id] = true;

            const showSpinner = (on) => {
                const sp = document.getElementById('spinner');
                if (sp) sp.style.display = on ? 'block' : 'none';
            };

            $btn.prop('disabled', true);

            // PASO 1: validar (no borra)
            showSpinner(true);
            $.ajax({
                url: 'ajax/modulos/eliminar.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    confirmar: 0,
                    from_ui: 'modulos'
                }
            }).done(function(resp) {
                showSpinner(false);

                if (!resp || resp.success !== true) {
                    alertify.error((resp && resp.message) ? resp.message : 'No se pudo validar');
                    deleting[id] = false;
                    $btn.prop('disabled', false);
                    return;
                }

                const d = resp.detalle || {};
                const hijos = parseInt(d.hijos || 0, 10);
                const acciones = parseInt(d.acciones || 0, 10);
                const permisos = parseInt(d.permisos || 0, 10);
                const permAcc = parseInt(d.perm_acc || 0, 10);

                const lines = [];
                lines.push('¿Confirmas eliminar este módulo?');
                if (hijos || acciones || permisos || permAcc) {
                    lines.push('<br><br><strong>También se hará:</strong>');
                    if (hijos > 0) lines.push(`• Eliminar ${hijos} submódulo(s) hijo(s)`);
                    if (acciones > 0) lines.push(`• Eliminar ${acciones} acción(es) especial(es)`);
                    if (permAcc > 0) lines.push(`• Eliminar ${permAcc} permiso(s) por acción`);
                    if (permisos > 0) lines.push(`• Desasociar ${permisos} permiso(s) de roles`);
                }

                alertify.confirm(
                    'Confirmar eliminación',
                    lines.join('<br>'),
                    function() {

                        // PASO 2: eliminar
                        showSpinner(true);
                        $.ajax({
                            url: 'ajax/modulos/eliminar.php',
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                id: id,
                                confirmar: 1,
                                from_ui: 'modulos'
                            }
                        }).done(function(resp2) {
                            showSpinner(false);

                            if (resp2 && resp2.success === true) {
                                $row.remove();
                                alertify.success(resp2.message || 'Módulo eliminado');
                            } else {
                                alertify.error((resp2 && resp2.message) ? resp2.message : 'No se pudo eliminar');
                            }
                        }).fail(function() {
                            showSpinner(false);
                            alertify.error('Error de comunicación (delete)');
                        }).always(function() {
                            deleting[id] = false;
                            $btn.prop('disabled', false);
                        });

                    },
                    function() {
                        deleting[id] = false;
                        $btn.prop('disabled', false);
                    }
                ).set('labels', {
                    ok: 'Sí, eliminar',
                    cancel: 'Cancelar'
                });

            }).fail(function() {
                showSpinner(false);
                alertify.error('Error de comunicación (validate)');
                deleting[id] = false;
                $btn.prop('disabled', false);
            });

        });



        // --- MODAL CÓDIGO (actualizado con nuevos campos) ---
        $('.btn-codigo').on('click', function(e) {
            e.preventDefault();
            const nombre = $(this).data('nombre') ?? '';
            const archivo = $(this).data('archivo') ?? '';
            const icono = $(this).data('icono') ?? '';
            const canal = $(this).data('canal') ?? 'erp';
            const app_id = $(this).data('app_id') ?? '';
            const padre = $(this).data('padre') ?? '';
            const observ = $(this).data('observaciones') ?? '';

            // Determinar si es módulo raíz de app
            const esAppRoot = (canal === 'app' && app_id && !padre);

            let sql = '';
            if (esAppRoot) {
                // Módulo raíz de app
                sql = `INSERT INTO modulos (nombre, archivo, icono, modulo_padre, tipo_modulo, canal, app_id, observaciones)
                    VALUES (
                    '${String(nombre).replace(/'/g,"''")}',
                    NULL,
                    '${String(icono).replace(/'/g,"''")}',
                    NULL,
                    'padre',
                    'app',
                    '${String(app_id).replace(/'/g,"''")}',
                    '${String(observ).replace(/'/g,"''")}'
                    );`;
            } else if (canal === 'app') {
                // Submódulo de app
                sql = `INSERT INTO modulos (nombre, archivo, icono, modulo_padre, tipo_modulo, canal, app_id, observaciones)
                    VALUES (
                    '${String(nombre).replace(/'/g,"''")}',
                    NULL,
                    '${String(icono).replace(/'/g,"''")}',
                    (SELECT id FROM modulos WHERE canal='app' AND app_id='${String(app_id).replace(/'/g,"''")}' AND modulo_padre IS NULL),
                    'submodulo',
                    'app',
                    '${String(app_id).replace(/'/g,"''")}',
                    '${String(observ).replace(/'/g,"''")}'
                    );`;
            } else {
                // Módulo ERP
                sql = `INSERT INTO modulos (nombre, archivo, icono, modulo_padre, tipo_modulo, canal, observaciones)
VALUES (
  '${String(nombre).replace(/'/g,"''")}',
  '${String(archivo).replace(/'/g,"''")}',
  '${String(icono).replace(/'/g,"''")}',
  ${padre ? "(SELECT id FROM modulos WHERE nombre = '" + String(padre).replace(/'/g,"''") + "' LIMIT 1)" : "NULL"},
  'pagina',
  'erp',
  '${String(observ).replace(/'/g,"''")}'
);`;
            }

            const phpEjemplo = canal === 'app' ?
                `// APP: validar permiso por app_id (ejemplo)
if (!tienePermisoApp('${app_id || ''}', 'ver')) {
  return res.status(403).json({ error: 'Sin permisos' });
}` :
                `// ERP: ejemplo leyendo permisos desde BD (NO usar $_SESSION['permisos'])
$usuarioId = $_SESSION['id_usuario'] ?? 0;

// ejemplo: tu consulta real dependerá de tu modelo de roles/usuarios
$sql = "SELECT pr.ver, pr.crear, pr.editar, pr.eliminar
        FROM permisos_rol_modulo pr
        INNER JOIN usuarios u ON u.rol = pr.rol
        WHERE u.id = ".(int)$usuarioId."
          AND pr.modulo = '".addslashes('${archivo}')."'
        LIMIT 1";

$perm = $clsConsulta->consultaGeneral($sql);
$p = (is_array($perm) && isset($perm[1])) ? $perm[1] : null;

if (!$p || empty($p['ver'])) { die("No tienes permiso."); }
// Botones según BD:
if (!empty($p['crear'])) { /* Nuevo */ }
if (!empty($p['editar'])) { /* Editar */ }
if (!empty($p['eliminar'])) { /* Eliminar */ }`;


            $('#codigoSQL').text(sql);
            $('#codigoPHP').text(phpEjemplo);
            $('#codigoModal').show();
        });
        $('#codigoModal .btn-cerrar-modal').on('click', () => $('#codigoModal').hide());

        // --- ACCIONES ESPECIALES (sin cambios) ---
        let moduloEnEdicion = 0;

        $('.btn-acciones-especiales').on('click', function() {
            moduloEnEdicion = parseInt($(this).data('id'), 10) || 0;
            const nombreMod = $(this).data('nombre') || '';
            if (!moduloEnEdicion) return;

            $('#accionesTitulo').text(nombreMod);
            $('#acc_modulo_id').val(moduloEnEdicion);
            $('#formAccion')[0].reset();
            $('#acc_id').val('');
            $('#acc_puente_box').addClass('d-none');
            $('#accionesTbody').empty();

            // Cargar acciones + catálogo de módulos para "puente"
            $.post('ajax/modulos/acciones-listar.php', {
                modulo_id: moduloEnEdicion
            }, function(resp) {
                // pintar listado
                (resp.acciones || []).forEach(a => {
                    $('#accionesTbody').append(`
          <tr>
            <td>${a.etiqueta || ''}</td>
            <td><code>${a.clave || ''}</code></td>
            <td>${a.puente_desc || '—'}</td>
            <td class="d-flex gap-1">
              <button class="btn btn-sm btn-primary btn-acc-edit" data-obj='${JSON.stringify(a)}'>
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-danger btn-acc-del" data-id="${a.id}">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        `);
                });
                // cargar catálogo módulos destino
                const $sel = $('#acc_mod_destino').empty().append('<option value="">—</option>');
                (resp.catalogo_modulos || []).forEach(m => {
                    $sel.append(`<option value="${m.id}">${m.nombre}</option>`);
                });

                $('#accionesModal').show();
            }, 'json').fail(function() {
                alertify.error('No se pudo cargar acciones');
            });
        });

        // Toggle puente
        $('#acc_es_puente').on('change', function() {
            $('#acc_puente_box').toggleClass('d-none', !$(this).is(':checked'));
        });

        // Editar acción
        $('#accionesTbody').on('click', '.btn-acc-edit', function() {
            const a = $(this).data('obj');
            $('#acc_id').val(a.id);
            $('#acc_etiqueta').val(a.etiqueta);
            $('#acc_clave').val(a.clave);
            $('#acc_descripcion').val(a.descripcion || '');
            if (a.modulo_destino && a.permiso_destino) {
                $('#acc_es_puente').prop('checked', true);
                $('#acc_puente_box').removeClass('d-none');
                $('#acc_mod_destino').val(a.modulo_destino);
                $('#acc_perm_destino').val(a.permiso_destino);
            } else {
                $('#acc_es_puente').prop('checked', false);
                $('#acc_puente_box').addClass('d-none');
                $('#acc_mod_destino').val('');
                $('#acc_perm_destino').val('');
            }
        });

        // Guardar acción
        $('#formAccion').on('submit', function(e) {
            e.preventDefault();
            const payload = {
                id: $('#acc_id').val() || '',
                modulo_id: $('#acc_modulo_id').val(),
                etiqueta: $('#acc_etiqueta').val(),
                clave: $('#acc_clave').val(),
                descripcion: $('#acc_descripcion').val(),
                modulo_destino: $('#acc_es_puente').is(':checked') ? $('#acc_mod_destino').val() : '',
                permiso_destino: $('#acc_es_puente').is(':checked') ? $('#acc_perm_destino').val() : ''
            };
            $.post('ajax/modulos/accion-guardar.php', payload, function(resp) {
                if (resp && resp.success) {
                    alertify.success('Acción guardada');
                    $('.btn-acciones-especiales[data-id="' + moduloEnEdicion + '"]').click(); // recargar
                } else {
                    alertify.error(resp && resp.message ? resp.message : 'No se pudo guardar');
                }
            }, 'json').fail(() => alertify.error('Error al guardar'));
        });

        // Eliminar acción
        $('#accionesTbody').on('click', '.btn-acc-del', function() {
            const id = $(this).data('id');
            alertify.confirm('Eliminar acción', '¿Eliminar esta acción especial?', function() {
                $.post('ajax/modulos/accion-eliminar.php', {
                    id: id
                }, function(resp) {
                    if (resp && resp.success) {
                        alertify.success('Acción eliminada');
                        $('.btn-acciones-especiales[data-id="' + moduloEnEdicion + '"]').click();
                    } else {
                        alertify.error(resp && resp.message ? resp.message : 'No se pudo eliminar');
                    }
                }, 'json').fail(() => alertify.error('Error'));
            }, function() {});
        });

        // Cerrar modal acciones
        $('.btn-cerrar-acciones').on('click', () => $('#accionesModal').hide());

    });
</script>