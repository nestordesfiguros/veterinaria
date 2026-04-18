<?php
/* Archivo: contenido/configuracion-modulos-modificar.php */

$idConfiguracion = isset($cat) ? (int)$cat : 0;

$registro = null;
$dependencias = [];
$totalDependencias = 0;

if ($idConfiguracion > 0) {
    $resultado = $clsConsulta->consultaPreparada("
        SELECT
            cm.id,
            cm.modulo_id,
            cm.habilitado,
            cm.visible_menu,
            cm.visible_busqueda,
            cm.obligatorio,
            cm.forzar_oculto_si_padre_off,
            cm.orden_override,
            cm.paquete_origen,
            cm.observaciones,
            m.nombre,
            m.archivo,
            m.tipo_modulo,
            m.canal,
            m.modulo_padre
        FROM configuracion_modulos cm
        INNER JOIN modulos m ON m.id = cm.modulo_id
        WHERE cm.id = ?
        LIMIT 1
    ", [$idConfiguracion]);

    if (!empty($resultado)) {
        $registro = $resultado[0];
    }
}

$listaPaquetes = $clsConsulta->consultaGeneral("
    SELECT id, clave, nombre
    FROM configuracion_paquetes
    WHERE estatus = 'activo'
    ORDER BY nombre ASC
");
$totalPaquetes = $clsConsulta->numrows;

if ($registro) {
    $dependencias = $clsConsulta->consultaPreparada("
        SELECT
            d.id,
            md.nombre AS nombre_dependencia,
            md.archivo AS archivo_dependencia,
            d.tipo_dependencia,
            d.accion_si_falta,
            COALESCE(cmd.habilitado, 0) AS dependencia_habilitada
        FROM configuracion_modulo_dependencias d
        INNER JOIN modulos md ON md.id = d.depende_modulo_id
        LEFT JOIN configuracion_modulos cmd ON cmd.modulo_id = md.id
        WHERE d.modulo_id = ?
        ORDER BY md.nombre ASC
    ", [(int)$registro['modulo_id']]);
    $totalDependencias = $clsConsulta->numrows;
}
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
            <li class="breadcrumb-item"><a href="configuracion-modulos">Configuración de Módulos</a></li>
            <li class="active breadcrumb-item" aria-current="page">Modificar Configuración</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-solid fa-pen-to-square"></i> &nbsp; Modificar Configuración de Módulo</h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php if (!$registro): ?>
                            <div class="alert alert-warning mb-3">
                                No se encontró la configuración solicitada.
                            </div>

                            <a href="configuracion-modulos" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Regresar
                            </a>
                        <?php else: ?>
                            <form id="formConfiguracionModuloModificar" autocomplete="off">
                                <input type="hidden" name="id" id="id" value="<?= (int)$registro['id']; ?>">

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Módulo</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars((string)$registro['nombre'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Archivo</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars((string)$registro['archivo'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Tipo</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars((string)$registro['tipo_modulo'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Canal</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars((string)$registro['canal'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="paquete_origen" class="form-label">Paquete origen</label>
                                        <select class="form-select" id="paquete_origen" name="paquete_origen">
                                            <option value="">Sin paquete</option>
                                            <?php
                                            if ($totalPaquetes > 0) {
                                                for ($i = 1; $i <= $totalPaquetes; $i++) {
                                                    $selected = ((string)$registro['paquete_origen'] === (string)$listaPaquetes[$i]['clave']) ? 'selected' : '';
                                            ?>
                                                    <option value="<?= htmlspecialchars($listaPaquetes[$i]['clave'], ENT_QUOTES, 'UTF-8'); ?>" <?= $selected; ?>>
                                                        <?= htmlspecialchars($listaPaquetes[$i]['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label for="orden_override" class="form-label">Orden override</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            id="orden_override"
                                            name="orden_override"
                                            min="1"
                                            step="1"
                                            value="<?= htmlspecialchars((string)$registro['orden_override'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4 pt-2">
                                            <input type="hidden" name="habilitado" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="habilitado" name="habilitado" value="1" <?= ((int)$registro['habilitado'] === 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="habilitado">Módulo habilitado</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4 pt-2">
                                            <input type="hidden" name="visible_menu" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="visible_menu" name="visible_menu" value="1" <?= ((int)$registro['visible_menu'] === 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="visible_menu">Visible en menú</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="visible_busqueda" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="visible_busqueda" name="visible_busqueda" value="1" <?= ((int)$registro['visible_busqueda'] === 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="visible_busqueda">Visible en búsqueda</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="obligatorio" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="obligatorio" name="obligatorio" value="1" <?= ((int)$registro['obligatorio'] === 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="obligatorio">Módulo obligatorio</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="forzar_oculto_si_padre_off" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="forzar_oculto_si_padre_off" name="forzar_oculto_si_padre_off" value="1" <?= ((int)$registro['forzar_oculto_si_padre_off'] === 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="forzar_oculto_si_padre_off">Ocultar si el padre está apagado</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="observaciones" class="form-label">Observaciones</label>
                                        <textarea
                                            class="form-control"
                                            id="observaciones"
                                            name="observaciones"
                                            rows="4"
                                            maxlength="255"
                                            placeholder="Observaciones internas de configuración"><?= htmlspecialchars((string)$registro['observaciones'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>

                                    <?php if ($totalDependencias > 0): ?>
                                        <div class="col-12 mt-2">
                                            <h6 class="mb-2">Dependencias registradas</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Dependencia</th>
                                                            <th>Archivo</th>
                                                            <th>Tipo</th>
                                                            <th>Acción</th>
                                                            <th>Estatus</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        foreach ($dependencias as $dep) {
                                                        ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars((string)$dep['nombre_dependencia'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?= htmlspecialchars((string)$dep['archivo_dependencia'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?= htmlspecialchars((string)$dep['tipo_dependencia'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?= htmlspecialchars((string)$dep['accion_si_falta'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td>
                                                                    <?php if ((int)$dep['dependencia_habilitada'] === 1): ?>
                                                                        <span class="badge bg-success">Habilitada</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-danger">Apagada</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-4 d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary" id="btnModificarConfiguracionModulo">
                                        <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                                    </button>
                                    <a href="configuracion-modulos" class="btn btn-secondary">
                                        <i class="fa-solid fa-arrow-left"></i> Regresar
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>

<?php if ($registro): ?>
    <script>
        function bloquearPantallaConfigModuloModificar() {
            if ($('#overlayProcesoConfigModuloModificar').length === 0) {
                $('body').append(
                    '<div id="overlayProcesoConfigModuloModificar" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.75);z-index:99999;display:flex;align-items:center;justify-content:center;">' +
                    '<div class="text-center">' +
                    '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                    '<div class="fw-semibold">Procesando...</div>' +
                    '</div>' +
                    '</div>'
                );
            }
        }

        function desbloquearPantallaConfigModuloModificar() {
            $('#overlayProcesoConfigModuloModificar').remove();
        }

        $(document).ready(function() {
            $('#formConfiguracionModuloModificar').validate({
                rules: {
                    id: {
                        required: true,
                        digits: true
                    },
                    orden_override: {
                        digits: true
                    },
                    observaciones: {
                        maxlength: 255
                    }
                },
                messages: {
                    id: {
                        required: 'El identificador de la configuración es obligatorio.',
                        digits: 'El identificador no es válido.'
                    },
                    orden_override: {
                        digits: 'El orden debe ser numérico.'
                    },
                    observaciones: {
                        maxlength: 'Las observaciones no pueden exceder 255 caracteres.'
                    }
                },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function(element) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                },
                errorPlacement: function(error, element) {
                    if ($(element).hasClass('form-check-input')) {
                        error.insertAfter($(element).closest('.form-check'));
                    } else {
                        error.insertAfter(element);
                    }
                },
                submitHandler: function() {
                    alertify.confirm(
                        'Confirmar',
                        '¿Deseas guardar los cambios de la configuración del módulo?',
                        function() {
                            var $btn = $('#btnModificarConfiguracionModulo');
                            var textoOriginal = $btn.html();

                            bloquearPantallaConfigModuloModificar();
                            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

                            $.ajax({
                                url: 'ajax/configuracion-modulos/modificar.php',
                                type: 'POST',
                                dataType: 'json',
                                data: $('#formConfiguracionModuloModificar').serialize(),
                                success: function(respuesta) {
                                    if (respuesta.success) {
                                        alertify.success(respuesta.message || 'Configuración actualizada correctamente.');
                                        setTimeout(function() {
                                            window.location.href = 'configuracion-modulos';
                                        }, 900);
                                    } else {
                                        alertify.error(respuesta.message || 'No fue posible actualizar la configuración.');
                                    }
                                },
                                error: function(xhr) {
                                    var mensaje = 'Ocurrió un error al actualizar la configuración.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        mensaje = xhr.responseJSON.message;
                                    }
                                    alertify.error(mensaje);
                                },
                                complete: function() {
                                    desbloquearPantallaConfigModuloModificar();
                                    $btn.prop('disabled', false).html(textoOriginal);
                                }
                            });
                        },
                        function() {
                            alertify.message('Operación cancelada.');
                        }
                    ).set('labels', {
                        ok: 'Guardar',
                        cancel: 'Cancelar'
                    });

                    return false;
                }
            });
        });
    </script>
<?php endif; ?>