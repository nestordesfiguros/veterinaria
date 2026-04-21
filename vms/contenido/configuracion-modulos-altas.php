<?php
/* Archivo: contenido/configuracion-modulos-altas.php */

$moduloPreseleccionado = isset($_GET['modulo']) ? (int)$_GET['modulo'] : 0;

$listaModulos = $clsConsulta->consultaGeneral("
    SELECT
        m.id,
        m.nombre,
        m.archivo,
        m.tipo_modulo
    FROM modulos m
    LEFT JOIN configuracion_modulos cm ON cm.modulo_id = m.id
    WHERE cm.id IS NULL
    ORDER BY m.nombre ASC
");
$totalModulos = $clsConsulta->numrows;

$listaPaquetes = $clsConsulta->consultaGeneral("
    SELECT id, clave, nombre
    FROM configuracion_paquetes
    WHERE estatus = 'activo'
    ORDER BY
        CASE WHEN clave = 'base' THEN 0 ELSE 1 END,
        nombre ASC
");
$totalPaquetes = $clsConsulta->numrows;
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
            <li class="breadcrumb-item"><a href="configuracion-modulos">Configuración de Módulos</a></li>
            <li class="active breadcrumb-item" aria-current="page">Nueva Configuración</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-solid fa-plus"></i> &nbsp; Nueva Configuración de Módulo</h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php if ($totalModulos <= 0): ?>
                            <div class="alert alert-warning mb-3">
                                Todos los módulos existentes ya tienen configuración registrada.
                            </div>

                            <a href="configuracion-modulos" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Regresar
                            </a>
                        <?php else: ?>
                            <form id="formConfiguracionModuloAlta" autocomplete="off">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="modulo_id" class="form-label">Módulo <span class="text-danger">*</span></label>
                                        <select class="form-select" id="modulo_id" name="modulo_id" required>
                                            <option value="">Selecciona un módulo</option>
                                            <?php
                                            for ($i = 1; $i <= $totalModulos; $i++) {
                                                $selected = ($moduloPreseleccionado === (int)$listaModulos[$i]['id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= (int)$listaModulos[$i]['id']; ?>" <?= $selected; ?>>
                                                    <?= htmlspecialchars($listaModulos[$i]['nombre'] . ' [' . $listaModulos[$i]['archivo'] . ']', ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="paquete_origen" class="form-label">Paquete origen</label>
                                        <select class="form-select" id="paquete_origen" name="paquete_origen">
                                            <option value="">Sin paquete</option>
                                            <?php
                                            if ($totalPaquetes > 0) {
                                                for ($i = 1; $i <= $totalPaquetes; $i++) {
                                            ?>
                                                    <option value="<?= htmlspecialchars($listaPaquetes[$i]['clave'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?= htmlspecialchars($listaPaquetes[$i]['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="orden_override" class="form-label">Orden override</label>
                                        <input type="number" class="form-control" id="orden_override" name="orden_override" min="1" step="1">
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4 pt-2">
                                            <input type="hidden" name="habilitado" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="habilitado" name="habilitado" value="1" checked>
                                            <label class="form-check-label" for="habilitado">Módulo habilitado</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4 pt-2">
                                            <input type="hidden" name="visible_menu" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="visible_menu" name="visible_menu" value="1" checked>
                                            <label class="form-check-label" for="visible_menu">Visible en menú</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="visible_busqueda" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="visible_busqueda" name="visible_busqueda" value="1" checked>
                                            <label class="form-check-label" for="visible_busqueda">Visible en búsqueda</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="obligatorio" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="obligatorio" name="obligatorio" value="1">
                                            <label class="form-check-label" for="obligatorio">Módulo obligatorio</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="forzar_oculto_si_padre_off" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="forzar_oculto_si_padre_off" name="forzar_oculto_si_padre_off" value="1" checked>
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
                                            placeholder="Observaciones internas de configuración"></textarea>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary" id="btnGuardarConfiguracionModulo">
                                        <i class="fa-solid fa-floppy-disk"></i> Guardar
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

<?php if ($totalModulos > 0): ?>
    <script>
        function bloquearPantallaConfigModuloAlta() {
            if ($('#overlayProcesoConfigModuloAlta').length === 0) {
                $('body').append(
                    '<div id="overlayProcesoConfigModuloAlta" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.75);z-index:99999;display:flex;align-items:center;justify-content:center;">' +
                    '<div class="text-center">' +
                    '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                    '<div class="fw-semibold">Procesando...</div>' +
                    '</div>' +
                    '</div>'
                );
            }
        }

        function desbloquearPantallaConfigModuloAlta() {
            $('#overlayProcesoConfigModuloAlta').remove();
        }

        $(document).ready(function() {
            $('#formConfiguracionModuloAlta').validate({
                rules: {
                    modulo_id: {
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
                    modulo_id: {
                        required: 'Selecciona un módulo.',
                        digits: 'El módulo seleccionado no es válido.'
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
                        '¿Deseas guardar la configuración del módulo?',
                        function() {
                            var $btn = $('#btnGuardarConfiguracionModulo');
                            var textoOriginal = $btn.html();

                            bloquearPantallaConfigModuloAlta();
                            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

                            $.ajax({
                                url: 'ajax/configuracion-modulos/guardar.php',
                                type: 'POST',
                                dataType: 'json',
                                data: $('#formConfiguracionModuloAlta').serialize(),
                                success: function(respuesta) {
                                    if (respuesta.success) {
                                        alertify.success(respuesta.message || 'Configuración guardada correctamente.');
                                        setTimeout(function() {
                                            window.location.href = 'configuracion-modulos';
                                        }, 900);
                                    } else {
                                        alertify.error(respuesta.message || 'No fue posible guardar la configuración.');
                                    }
                                },
                                error: function(xhr) {
                                    var mensaje = 'Ocurrió un error al guardar la configuración.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        mensaje = xhr.responseJSON.message;
                                    }
                                    alertify.error(mensaje);
                                },
                                complete: function() {
                                    desbloquearPantallaConfigModuloAlta();
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