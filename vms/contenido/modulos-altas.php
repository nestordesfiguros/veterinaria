<?php
// contenido/modulos-altas.php

// Cargar lista de módulos padres disponibles
$sqlPadres = "SELECT id, nombre, canal FROM modulos WHERE modulo_padre IS NULL ORDER BY nombre";
$padres = $clsConsulta->consultaGeneral($sqlPadres) ?: [];

// Cargar app_ids existentes (para evitar duplicados en módulos raíz de app)
$sqlAppIds = "SELECT DISTINCT app_id FROM modulos WHERE canal = 'app' AND app_id IS NOT NULL";
$appIdsExistentes = $clsConsulta->consultaGeneral($sqlAppIds) ?: [];
$appIdsArray = [];
foreach ($appIdsExistentes as $k => $v) {
    if (!is_numeric($k)) continue;
    if (!empty($v['app_id'])) {
        $appIdsArray[] = $v['app_id'];
    }
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h1 class="h3 mb-0">Nuevo Módulo</h1>
                    </div>
                    <div class="card-body">
                        <form id="formModulo" action="ajax/modulos/guardar.php" method="post">
                            <div class="row g-3">
                                <!-- Canal -->
                                <div class="col-12">
                                    <label class="form-label">Canal <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="canal" id="canal_erp" value="erp" checked>
                                            <label class="form-check-label" for="canal_erp">
                                                <span class="badge bg-primary">ERP</span> - Módulo para sistema web
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="canal" id="canal_app" value="app">
                                            <label class="form-check-label" for="canal_app">
                                                <span class="badge bg-success">APP</span> - Módulo para aplicación móvil
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nombre -->
                                <div class="col-12 col-md-6">
                                    <label for="nombre" class="form-label">Nombre del módulo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required
                                        placeholder="Ej: Control de Presupuesto, Bitácora de Plataformas">
                                </div>

                                <!-- Archivo (solo para ERP) -->
                                <div class="col-12 col-md-6" id="archivo_container">
                                    <label for="archivo" class="form-label">Archivo PHP (sin extensión) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="archivo" name="archivo"
                                        placeholder="Ej: control-presupuesto, bitacora-plataformas">
                                    <div class="form-text">Nombre del archivo en la carpeta <code>contenido/</code></div>
                                </div>

                                <!-- App ID (solo para APP) -->
                                <div class="col-12 col-md-6 d-none" id="app_id_container">
                                    <label for="app_id" class="form-label">App ID (identificador único) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="app_id" name="app_id"
                                        placeholder="Ej: plataformas, control_activos">
                                    <div class="form-text">Debe coincidir con el <code>$APP_ID</code> en la API móvil</div>
                                    <?php if (!empty($appIdsArray)): ?>
                                        <div class="form-text text-warning">
                                            IDs ya existentes: <?= htmlspecialchars(implode(', ', $appIdsArray)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Icono -->
                                <div class="col-12 col-md-6">
                                    <label for="icono" class="form-label">Icono (Font Awesome)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i id="icono_preview" class="fas fa-cube"></i></span>
                                        <input type="text" class="form-control" id="icono" name="icono"
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
                                    <select class="form-select" id="modulo_padre" name="modulo_padre">
                                        <option value="">— Sin padre (módulo raíz)</option>

                                        <?php foreach ($padres as $k => $p):
                                            if (!is_numeric($k)) continue;
                                        ?>
                                            <option value="<?= (int)$p['id'] ?>" data-canal="<?= htmlspecialchars($p['canal'] ?? 'erp') ?>">
                                                <?= htmlspecialchars($p['nombre']) ?>
                                                <small>(<?= $p['canal'] === 'app' ? 'APP' : 'ERP' ?>)</small>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                    <div class="form-text">Si es módulo raíz de APP, debe quedar sin padre</div>
                                </div>

                                <!-- Tipo de Módulo -->
                                <div class="col-12 col-md-6">
                                    <label for="tipo_modulo" class="form-label">Tipo de Módulo <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tipo_modulo" name="tipo_modulo" required>
                                        <option value="padre">Menú principal (padre)</option>
                                        <option value="pagina" selected>Página/Acceso (botones)</option>
                                        <option value="lista">Listado</option>
                                        <option value="submodulo">Sección interna</option>
                                        <option value="accion">Acción puntual</option>
                                    </select>
                                </div>

                                <!-- Observaciones -->
                                <div class="col-12">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="2"
                                        placeholder="Descripción breve del propósito del módulo..."></textarea>
                                </div>

                                <!-- Soporta (checkboxes) -->
                                <div class="col-12">
                                    <label class="form-label">Este módulo soporta:</label>
                                    <div class="row">
                                        <div class="col-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="soporta_crear" name="soporta_crear" value="1" checked>
                                                <label class="form-check-label" for="soporta_crear">Crear registros</label>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="soporta_editar" name="soporta_editar" value="1" checked>
                                                <label class="form-check-label" for="soporta_editar">Editar registros</label>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="soporta_eliminar" name="soporta_eliminar" value="1" checked>
                                                <label class="form-check-label" for="soporta_eliminar">Eliminar registros</label>
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
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-1"></i> Guardar Módulo
                                        </button>
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
            const canal = $(this).val();

            if (canal === 'app') {
                $('#archivo_container').addClass('d-none');
                $('#app_id_container').removeClass('d-none');
                $('#app_id').prop('required', true);
                $('#archivo').prop('required', false);

                // Para módulos APP raíz, forzar sin padre
                $('#modulo_padre').val('');
                $('#modulo_padre').prop('disabled', true);

                // Si es APP, solo permitir ciertos tipos de módulo
                $('#tipo_modulo').html(`
                <option value="padre">Menú principal (padre - solo raíz)</option>
                <option value="submodulo">Sección interna (hijo)</option>
                <option value="accion">Acción puntual</option>
            `);
            } else {
                $('#archivo_container').removeClass('d-none');
                $('#app_id_container').addClass('d-none');
                $('#archivo').prop('required', true);
                $('#app_id').prop('required', false);
                $('#modulo_padre').prop('disabled', false);

                // Restaurar opciones para ERP
                $('#tipo_modulo').html(`
                <option value="padre">Menú principal (padre)</option>
                <option value="pagina" selected>Página/Acceso (botones)</option>
                <option value="lista">Listado</option>
                <option value="submodulo">Sección interna</option>
                <option value="accion">Acción puntual</option>
            `);
            }

            // Filtrar módulos padres por canal
            filtrarPadresPorCanal(canal);
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
                $('#modulo_padre').prop('disabled', false);
            }

            // Si es tipo "accion", deshabilitar soportas
            if (tipo === 'accion') {
                $('#soporta_crear, #soporta_editar, #soporta_eliminar').prop('checked', false).prop('disabled', true);
            } else {
                $('#soporta_crear, #soporta_editar, #soporta_eliminar').prop('disabled', false);
            }
        });

        // Validación del formulario
        $('#formModulo').on('submit', function(e) {
            e.preventDefault();

            const canal = $('input[name="canal"]:checked').val();
            const appId = $('#app_id').val();
            const moduloPadre = $('#modulo_padre').val();
            const tipoModulo = $('#tipo_modulo').val();

            // Validaciones específicas para APP
            if (canal === 'app') {
                // Validar App ID (solo minúsculas, números y guiones bajos)
                if (!/^[a-z][a-z0-9_]*$/.test(appId)) {
                    alertify.error('App ID inválido. Solo minúsculas, números y guiones bajos. Ej: plataformas, control_activos');
                    return;
                }

                // Validar que no sea un App ID duplicado (para módulos raíz)
                if (!moduloPadre && appId) {
                    const appIdsExistentes = <?= json_encode($appIdsArray) ?>;
                    if (appIdsExistentes.includes(appId)) {
                        alertify.error('Este App ID ya existe. Los módulos raíz de APP deben tener un App ID único.');
                        return;
                    }
                }

                // Validar que módulos raíz de APP sean tipo "padre"
                if (!moduloPadre && tipoModulo !== 'padre') {
                    alertify.error('Los módulos raíz de APP deben ser de tipo "Menú principal (padre)"');
                    return;
                }
            }

            // Validar archivo para ERP
            if (canal === 'erp') {
                const archivo = $('#archivo').val().trim();
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

            alertify.confirm('Guardar módulo', '¿Confirmas guardar este nuevo módulo?', function() {
                document.getElementById('spinner').style.display = 'block';

                $.ajax({
                    url: 'ajax/modulos/guardar.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        document.getElementById('spinner').style.display = 'none';
                        if (response.success) {
                            alertify.success('Módulo guardado correctamente');
                            setTimeout(function() {
                                window.location.href = 'modulos-lista';
                            }, 1500);
                        } else {
                            alertify.error(response.message || 'Error al guardar el módulo');
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

        // Inicializar filtro de padres
        const canalInicial = $('input[name="canal"]:checked').val();
        filtrarPadresPorCanal(canalInicial);
    });
</script>