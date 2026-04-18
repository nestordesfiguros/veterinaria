<?php
// contenido/modulos-editar.php

// Obtener ID del módulo desde la URL
$id = isset($cat) ? (int)$cat : 0;
if ($id <= 0) {
    echo '<div class="alert alert-danger">ID de módulo inválido</div>';
    echo '<a href="modulos-lista" class="btn btn-secondary">Regresar</a>';
    return;
}

// Obtener datos del módulo
$sql = "SELECT m.*, 
               padre.nombre AS padre_nombre,
               padre.canal AS padre_canal
        FROM modulos m
        LEFT JOIN modulos padre ON m.modulo_padre = padre.id
        WHERE m.id = $id LIMIT 1";
$modulo = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows <= 0) {
    echo '<div class="alert alert-danger">Módulo no encontrado</div>';
    echo '<a href="modulos-lista" class="btn btn-secondary">Regresar</a>';
    return;
}

// El método consultaGeneral devuelve array indexado desde 1
$m = $modulo[1] ?? [];

// Cargar lista de módulos padres disponibles (excluyendo el actual y sus hijos)
$sqlPadres = "SELECT id, nombre, canal FROM modulos 
              WHERE modulo_padre IS NULL 
              AND id != $id
              AND canal = '" . ($m['canal'] ?? 'erp') . "'
              ORDER BY nombre";
$padres = $clsConsulta->consultaGeneral($sqlPadres) ?: [];

// Si es módulo APP, cargar todos los padres APP (para permitir cambiar entre apps)
if (($m['canal'] ?? 'erp') === 'app') {
    $sqlPadresApp = "SELECT id, nombre FROM modulos 
                     WHERE canal = 'app' 
                     AND modulo_padre IS NULL
                     AND id != $id
                     ORDER BY nombre";
    $padres = $clsConsulta->consultaGeneral($sqlPadresApp) ?: [];
}

$nombreModulo = $m['nombre'] ?? '';
// Determinar si es módulo raíz de APP (no se puede cambiar de padre)
$esAppRoot = (($m['canal'] ?? 'erp') === 'app' && empty($m['modulo_padre']) && !empty($m['app_id']));

// Si es módulo raíz de APP, verificar si tiene submódulos
$tieneHijos = false;
if ($esAppRoot) {
    $sqlHijos = "SELECT COUNT(*) as total FROM modulos WHERE modulo_padre = $id";
    $resHijos = $clsConsulta->consultaGeneral($sqlHijos);
    $tieneHijos = ($resHijos[1]['total'] ?? 0) > 0;
}

// Helper para safe html
function safeHtml($value)
{
    if ($value === null) return '';
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card animation slide-in-down">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0">Editar Módulo | <?php echo $nombreModulo; ?></h1>
                        <div>
                            <?php if (($m['canal'] ?? 'erp') === 'app'): ?>
                                <span class="badge bg-success">APP</span>
                            <?php else: ?>
                                <span class="badge bg-primary">ERP</span>
                            <?php endif; ?>
                            <?php if ($esAppRoot): ?>
                                <span class="badge bg-warning">RAÍZ APP</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($esAppRoot && $tieneHijos): ?>
                            <div class="alert alert-warning">
                                <strong>¡Atención!</strong> Este módulo es la raíz de una APP y tiene submódulos asociados.
                                <ul class="mb-0 mt-2">
                                    <li>No puedes cambiar el <strong>App ID</strong> porque rompería la relación con los submódulos.</li>
                                    <li>No puedes cambiar el <strong>Canal</strong> de APP a ERP.</li>
                                    <li>No puedes asignarle un padre porque es módulo raíz.</li>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form id="formModulo" action="ajax/modulos/guardar.php" method="post">
                            <input type="hidden" name="id" value="<?= (int)$id ?>">

                            <div class="row g-3">
                                <!-- Canal (solo editable si no es raíz de APP con hijos) -->
                                <div class="col-12">
                                    <label class="form-label">Canal <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="canal" id="canal_erp" value="erp"
                                                <?= ($m['canal'] ?? 'erp') === 'erp' ? 'checked' : '' ?>
                                                <?= ($esAppRoot && $tieneHijos) ? 'disabled' : '' ?>>
                                            <label class="form-check-label" for="canal_erp">
                                                <span class="badge bg-primary">ERP</span> - Módulo para sistema web
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="canal" id="canal_app" value="app"
                                                <?= ($m['canal'] ?? 'erp') === 'app' ? 'checked' : '' ?>
                                                <?= ($esAppRoot && $tieneHijos) ? 'disabled' : '' ?>>
                                            <label class="form-check-label" for="canal_app">
                                                <span class="badge bg-success">APP</span> - Módulo para aplicación móvil
                                            </label>
                                        </div>
                                    </div>
                                    <?php if ($esAppRoot && $tieneHijos): ?>
                                        <div class="form-text text-warning">No se puede cambiar el canal porque es módulo raíz con submódulos</div>
                                    <?php endif; ?>
                                </div>

                                <!-- Nombre -->
                                <div class="col-12 col-md-6">
                                    <label for="nombre" class="form-label">Nombre del módulo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required
                                        value="<?= safeHtml($m['nombre'] ?? '') ?>"
                                        placeholder="Ej: Control de Presupuesto, Bitácora de Plataformas">
                                </div>

                                <!-- Archivo (solo para ERP) -->
                                <div class="col-12 col-md-6" id="archivo_container"
                                    style="<?= ($m['canal'] ?? 'erp') === 'app' ? 'display:none;' : '' ?>">
                                    <label for="archivo" class="form-label">Archivo PHP (sin extensión) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="archivo" name="archivo"
                                        value="<?= safeHtml($m['archivo'] ?? '') ?>"
                                        placeholder="Ej: control-presupuesto, bitacora-plataformas"
                                        <?= ($m['canal'] ?? 'erp') === 'erp' ? 'required' : '' ?>>
                                    <div class="form-text">Nombre del archivo en la carpeta <code>contenido/</code></div>
                                </div>

                                <!-- App ID (solo para APP) -->
                                <div class="col-12 col-md-6" id="app_id_container"
                                    style="<?= ($m['canal'] ?? 'erp') === 'erp' ? 'display:none;' : '' ?>">
                                    <label for="app_id" class="form-label">App ID (identificador único) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="app_id" name="app_id"
                                        value="<?= safeHtml($m['app_id'] ?? '') ?>"
                                        placeholder="Ej: plataformas, control_activos"
                                        <?= ($m['canal'] ?? 'erp') === 'app' ? 'required' : '' ?>
                                        <?= ($esAppRoot && $tieneHijos) ? 'readonly' : '' ?>>
                                    <div class="form-text">Debe coincidir con el <code>$APP_ID</code> en la API móvil</div>
                                    <?php if ($esAppRoot && $tieneHijos): ?>
                                        <div class="form-text text-warning">No se puede cambiar porque es módulo raíz con submódulos</div>
                                    <?php endif; ?>
                                </div>

                                <!-- Icono -->
                                <div class="col-12 col-md-6">
                                    <label for="icono" class="form-label">Icono (Font Awesome)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i id="icono_preview" class="<?= safeHtml($m['icono'] ?? 'fas fa-cube') ?>"></i>
                                        </span>
                                        <input type="text" class="form-control" id="icono" name="icono"
                                            value="<?= safeHtml($m['icono'] ?? '') ?>"
                                            placeholder="fas fa-cube, far fa-file, etc.">
                                    </div>
                                    <div class="form-text">
                                        <a href="https://fontawesome.com/search" target="_blank">Buscar iconos</a> |
                                        <small>Ej: fas fa-home, far fa-file-alt</small>
                                    </div>
                                </div>

                                <!-- Módulo Padre -->
                                <div class="col-12 col-md-6">
                                    <label for="modulo_padre" class="form-label">Módulo Padre</label>
                                    <select class="form-select" id="modulo_padre" name="modulo_padre"
                                        <?= $esAppRoot ? 'disabled' : '' ?>>
                                        <option value="">— Sin padre (módulo raíz)</option>
                                        <?php if ($clsConsulta->numrows > 0): ?>
                                            <?php foreach ($padres as $k => $p):
                                                if (!is_numeric($k)) continue;
                                                $selected = ($p['id'] == ($m['modulo_padre'] ?? 0)) ? 'selected' : '';
                                            ?>
                                                <option value="<?= (int)$p['id'] ?>" data-canal="<?= safeHtml($p['canal'] ?? 'erp') ?>" <?= $selected ?>>
                                                    <?= safeHtml($p['nombre']) ?>
                                                    <small>(<?= ($p['canal'] ?? 'erp') === 'app' ? 'APP' : 'ERP' ?>)</small>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <?php if ($esAppRoot): ?>
                                        <div class="form-text text-warning">Módulo raíz de APP no puede tener padre</div>
                                        <input type="hidden" name="modulo_padre" value="">
                                    <?php endif; ?>
                                </div>

                                <!-- Tipo de Módulo -->
                                <div class="col-12 col-md-6">
                                    <label for="tipo_modulo" class="form-label">Tipo de Módulo <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tipo_modulo" name="tipo_modulo" required>
                                        <option value="padre" <?= ($m['tipo_modulo'] ?? '') === 'padre' ? 'selected' : '' ?>>Menú principal (padre)</option>
                                        <option value="pagina" <?= ($m['tipo_modulo'] ?? 'pagina') === 'pagina' ? 'selected' : '' ?>>Página/Acceso (botones)</option>
                                        <option value="lista" <?= ($m['tipo_modulo'] ?? '') === 'lista' ? 'selected' : '' ?>>Listado</option>
                                        <option value="submodulo" <?= ($m['tipo_modulo'] ?? '') === 'submodulo' ? 'selected' : '' ?>>Sección interna</option>
                                        <option value="accion" <?= ($m['tipo_modulo'] ?? '') === 'accion' ? 'selected' : '' ?>>Acción puntual</option>
                                    </select>
                                </div>

                                <!-- Observaciones -->
                                <div class="col-12">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="2"
                                        placeholder="Descripción breve del propósito del módulo..."><?= safeHtml($m['observaciones'] ?? '') ?></textarea>
                                </div>

                                <!-- Soporta (checkboxes) -->
                                <div class="col-12">
                                    <label class="form-label">Este módulo soporta:</label>
                                    <div class="row">
                                        <div class="col-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="soporta_crear" name="soporta_crear" value="1"
                                                    <?= ((int)($m['soporta_crear'] ?? 1) === 1 ? 'checked' : '') ?>
                                                    <?= ($m['tipo_modulo'] ?? '') === 'accion' ? 'disabled' : '' ?>>
                                                <label class="form-check-label" for="soporta_crear">Crear registros</label>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="soporta_editar" name="soporta_editar" value="1"
                                                    <?= ((int)($m['soporta_editar'] ?? 1) === 1 ? 'checked' : '') ?>
                                                    <?= ($m['tipo_modulo'] ?? '') === 'accion' ? 'disabled' : '' ?>>
                                                <label class="form-check-label" for="soporta_editar">Editar registros</label>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="soporta_eliminar" name="soporta_eliminar" value="1"
                                                    <?= ((int)($m['soporta_eliminar'] ?? 1) === 1 ? 'checked' : '') ?>
                                                    <?= ($m['tipo_modulo'] ?? '') === 'accion' ? 'disabled' : '' ?>>
                                                <label class="form-check-label" for="soporta_eliminar">Eliminar registros</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Información de sistema -->
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body p-3">
                                            <h6 class="card-title">Información del sistema</h6>
                                            <div class="row small">
                                                <div class="col-md-6">
                                                    <strong>ID:</strong> <?= (int)$id ?><br>
                                                    <strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($m['fecha_creacion'] ?? 'now')) ?><br>
                                                    <?php if (!empty($m['app_root_key'])): ?>
                                                        <strong>App Root Key:</strong> <code><?= safeHtml($m['app_root_key']) ?></code>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php if (!empty($m['modulo_padre'])): ?>
                                                        <strong>Padre actual:</strong> <?= safeHtml($m['padre_nombre'] ?? '') ?>
                                                        <small>(<?= ($m['padre_canal'] ?? 'erp') === 'app' ? 'APP' : 'ERP' ?>)</small><br>
                                                    <?php endif; ?>
                                                    <?php if ($esAppRoot && $tieneHijos): ?>
                                                        <strong>Submódulos:</strong> <span class="text-warning">Tiene <?= $resHijos[1]['total'] ?? 0 ?> submódulo(s)</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones -->
                                <div class="col-12 mt-4">
                                    <div class="d-flex justify-content-between">
                                        <a href="modulos-lista" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i> Cancelar
                                        </a>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save me-1"></i> Guardar Cambios
                                            </button>
                                            <?php if (!$esAppRoot): ?>
                                                <!-- No mostrar botón eliminar para módulos raíz de APP -->
                                                <button type="button" class="btn btn-danger" id="btnEliminar">
                                                    <i class="fas fa-trash me-1"></i> Eliminar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        const moduloId = <?= (int)$id ?>;
        const esAppRoot = <?= $esAppRoot ? 'true' : 'false' ?>;
        const tieneHijos = <?= $tieneHijos ? 'true' : 'false' ?>;
        const canalActual = '<?= $m['canal'] ?? 'erp' ?>';
        const appIdActual = '<?= safeHtml($m['app_id'] ?? '') ?>';

        // Previsualización del icono
        $('#icono').on('input', function() {
            const iconClass = $(this).val().trim();
            if (iconClass) {
                $('#icono_preview').attr('class', iconClass);
            } else {
                $('#icono_preview').attr('class', 'fas fa-cube');
            }
        });

        // Toggle entre Archivo (ERP) y App ID (APP)
        $('input[name="canal"]').on('change', function() {
            if (esAppRoot && tieneHijos) {
                // Si es módulo raíz con hijos, no permitir cambiar canal
                $(this).prop('checked', canalActual === 'app' ? '#canal_app' : '#canal_erp').trigger('change');
                alertify.error('No se puede cambiar el canal en módulos raíz con submódulos');
                return;
            }

            const canal = $(this).val();

            if (canal === 'app') {
                $('#archivo_container').hide();
                $('#app_id_container').show();
                $('#app_id').prop('required', true);
                $('#archivo').prop('required', false);

                // Para módulos APP raíz, forzar sin padre
                if ($('#tipo_modulo').val() === 'padre') {
                    $('#modulo_padre').val('');
                    $('#modulo_padre').prop('disabled', true);
                }

                // Si es APP, solo permitir ciertos tipos de módulo
                $('#tipo_modulo').html(`
                <option value="padre">Menú principal (padre - solo raíz)</option>
                <option value="submodulo">Sección interna (hijo)</option>
                <option value="accion">Acción puntual</option>
            `);

                // Seleccionar el tipo apropiado
                const tipoActual = '<?= $m['tipo_modulo'] ?? '' ?>';
                if (tipoActual === 'padre' || tipoActual === 'submodulo' || tipoActual === 'accion') {
                    $('#tipo_modulo').val(tipoActual);
                } else {
                    $('#tipo_modulo').val('submodulo');
                }
            } else {
                $('#archivo_container').show();
                $('#app_id_container').hide();
                $('#archivo').prop('required', true);
                $('#app_id').prop('required', false);
                $('#modulo_padre').prop('disabled', false);

                // Restaurar opciones para ERP
                $('#tipo_modulo').html(`
                <option value="padre">Menú principal (padre)</option>
                <option value="pagina" <?= ($m['tipo_modulo'] ?? 'pagina') === 'pagina' ? 'selected' : '' ?>>Página/Acceso (botones)</option>
                <option value="lista">Listado</option>
                <option value="submodulo">Sección interna</option>
                <option value="accion">Acción puntual</option>
            `);

                // Restaurar valor original
                $('#tipo_modulo').val('<?= $m['tipo_modulo'] ?? 'pagina' ?>');
            }

            // Filtrar módulos padres por canal
            filtrarPadresPorCanal(canal);

            // Actualizar estado de checkboxes soporta
            actualizarCheckboxesSoporta();
        });

        // Filtrar módulos padres según el canal seleccionado
        function filtrarPadresPorCanal(canal) {
            $('#modulo_padre option').each(function() {
                const $option = $(this);
                const dataCanal = $option.data('canal');

                if (!dataCanal) return; // Opción "Sin padre"

                if (canal === 'app') {
                    // Para APP, solo mostrar padres que también sean APP
                    $option.toggle(dataCanal === 'app');
                } else {
                    // Para ERP, mostrar todos los padres
                    $option.show();
                }
            });
        }

        // Cuando cambia el tipo de módulo
        $('#tipo_modulo').on('change', function() {
            const tipo = $(this).val();
            const canal = $('input[name="canal"]:checked').val();

            // Si es tipo "padre" y canal es "app", forzar sin padre
            if (tipo === 'padre' && canal === 'app') {
                $('#modulo_padre').val('');
                $('#modulo_padre').prop('disabled', true);
            } else {
                $('#modulo_padre').prop('disabled', esAppRoot);
            }

            // Actualizar estado de checkboxes soporta
            actualizarCheckboxesSoporta();
        });

        // Actualizar estado de checkboxes "soporta"
        function actualizarCheckboxesSoporta() {
            const tipo = $('#tipo_modulo').val();

            if (tipo === 'accion') {
                $('#soporta_crear, #soporta_editar, #soporta_eliminar').prop('checked', false).prop('disabled', true);
            } else {
                $('#soporta_crear, #soporta_editar, #soporta_eliminar').prop('disabled', false);
                // Restaurar valores originales si no son tipo acción
                if (tipo !== 'accion') {
                    $('#soporta_crear').prop('checked', <?= ((int)($m['soporta_crear'] ?? 1) === 1 ? 'true' : 'false') ?>);
                    $('#soporta_editar').prop('checked', <?= ((int)($m['soporta_editar'] ?? 1) === 1 ? 'true' : 'false') ?>);
                    $('#soporta_eliminar').prop('checked', <?= ((int)($m['soporta_eliminar'] ?? 1) === 1 ? 'true' : 'false') ?>);
                }
            }
        }

        // Validación del formulario
        $('#formModulo').on('submit', function(e) {
            e.preventDefault();

            const canal = $('input[name="canal"]:checked').val();
            const appId = $('#app_id').val();
            const archivo = $('#archivo').val().trim();
            const moduloPadre = $('#modulo_padre').val();
            const tipoModulo = $('#tipo_modulo').val();

            // Si es módulo raíz con hijos y se intentó cambiar algo crítico
            if (esAppRoot && tieneHijos) {
                if (canal !== canalActual) {
                    alertify.error('No se puede cambiar el canal en módulos raíz con submódulos');
                    return;
                }
                if (appId !== appIdActual) {
                    alertify.error('No se puede cambiar el App ID en módulos raíz con submódulos');
                    return;
                }
            }

            // Validaciones específicas para APP
            if (canal === 'app') {
                // Validar App ID (solo minúsculas, números y guiones bajos)
                if (!/^[a-z][a-z0-9_]*$/.test(appId)) {
                    alertify.error('App ID inválido. Solo minúsculas, números y guiones bajos. Ej: plataformas, control_activos');
                    return;
                }

                // Validar que módulos raíz de APP sean tipo "padre"
                if (!moduloPadre && tipoModulo !== 'padre') {
                    alertify.error('Los módulos raíz de APP deben ser de tipo "Menú principal (padre)"');
                    return;
                }
            }

            // Validar archivo para ERP
            if (canal === 'erp') {
                if (!archivo) {
                    alertify.error('El archivo es requerido para módulos ERP');
                    return;
                }
                if (!/^[a-z0-9\-]+$/.test(archivo)) {
                    alertify.error('Nombre de archivo inválido. Solo minúsculas, números y guiones. Ej: control-presupuesto');
                    return;
                }
            }

            // Enviar formulario
            const formData = $(this).serialize();

            alertify.confirm('Guardar cambios', '¿Confirmas guardar los cambios en este módulo?', function() {
                document.getElementById('spinner').style.display = 'block';

                $.ajax({
                    url: 'ajax/modulos/guardar.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        document.getElementById('spinner').style.display = 'none';
                        if (response.success) {
                            alertify.success('Módulo actualizado correctamente');
                            setTimeout(function() {
                                window.location.href = 'modulos-lista';
                            }, 1500);
                        } else {
                            alertify.error(response.message || 'Error al actualizar el módulo');
                        }
                    },
                    error: function() {
                        document.getElementById('spinner').style.display = 'none';
                        alertify.error('Error de comunicación con el servidor');
                    }
                });
            }, function() {
                // Cancelar
            });
        });

        // Botón eliminar
        $('#btnEliminar').on('click', function() {
            alertify.confirm('Eliminar módulo',
                '¿Estás seguro de eliminar este módulo?<br><br>' +
                '<span class="text-danger">Esta acción no se puede deshacer.</span><br>' +
                'Se eliminarán también todas las acciones especiales asociadas.',
                function() {
                    document.getElementById('spinner').style.display = 'block';

                    $.ajax({
                        url: 'ajax/modulos/eliminar.php',
                        type: 'POST',
                        data: {
                            id: moduloId
                        },
                        dataType: 'json',
                        success: function(response) {
                            document.getElementById('spinner').style.display = 'none';
                            if (response.ok) {
                                alertify.success('Módulo eliminado correctamente');
                                setTimeout(function() {
                                    window.location.href = 'modulos-lista';
                                }, 1500);
                            } else {
                                alertify.error(response.msg || 'Error al eliminar el módulo');
                            }
                        },
                        error: function() {
                            document.getElementById('spinner').style.display = 'none';
                            alertify.error('Error de comunicación con el servidor');
                        }
                    });
                },
                function() {
                    // Cancelar
                }
            );
        });

        // Inicializar
        const canalInicial = '<?= $m['canal'] ?? 'erp' ?>';
        filtrarPadresPorCanal(canalInicial);
        actualizarCheckboxesSoporta();
    });
</script>