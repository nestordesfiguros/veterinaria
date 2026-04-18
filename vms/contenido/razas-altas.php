<?php
/* Archivo: contenido/razas-altas.php */

$listaEspecies = $clsConsulta->consultaGeneral("SELECT id, nombre_especie FROM cat_especies WHERE estatus = 'activo' ORDER BY nombre_especie ASC");
$totalEspecies = $clsConsulta->numrows;
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
            <li class="breadcrumb-item"><a href="razas">Razas</a></li>
            <li class="active breadcrumb-item" aria-current="page">Nueva Raza</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-solid fa-plus"></i> &nbsp; Alta de Raza</h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php if ($totalEspecies <= 0): ?>
                            <div class="alert alert-warning">
                                No existen especies activas para registrar razas.
                            </div>

                            <a href="razas" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Regresar
                            </a>
                        <?php else: ?>
                            <form id="formRazaAlta" autocomplete="off">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="id_especie" class="form-label">Especie <span class="text-danger">*</span></label>
                                        <select class="form-select" id="id_especie" name="id_especie" required>
                                            <option value="">Selecciona una especie</option>
                                            <?php
                                            for ($i = 1; $i <= $totalEspecies; $i++) {
                                            ?>
                                                <option value="<?= (int)$listaEspecies[$i]['id']; ?>">
                                                    <?= htmlspecialchars($listaEspecies[$i]['nombre_especie'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-5">
                                        <label for="nombre_raza" class="form-label">Nombre de la raza <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="nombre_raza"
                                            name="nombre_raza"
                                            maxlength="100"
                                            placeholder="Ejemplo: HOLSTEIN"
                                            required>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="estatus" class="form-label">Estatus <span class="text-danger">*</span></label>
                                        <select class="form-select" id="estatus" name="estatus" required>
                                            <option value="activo" selected>Activo</option>
                                            <option value="inactivo">Inactivo</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea
                                            class="form-control"
                                            id="descripcion"
                                            name="descripcion"
                                            rows="4"
                                            maxlength="255"
                                            placeholder="Descripción breve de la raza"></textarea>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary" id="btnGuardarRaza">
                                        <i class="fa-solid fa-floppy-disk"></i> Guardar
                                    </button>
                                    <a href="razas" class="btn btn-secondary">
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

<?php if ($totalEspecies > 0): ?>
    <script>
        function bloquearPantallaRazaAlta() {
            if ($('#overlayProcesoRazaAlta').length === 0) {
                $('body').append(
                    '<div id="overlayProcesoRazaAlta" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.75);z-index:99999;display:flex;align-items:center;justify-content:center;">' +
                    '<div class="text-center">' +
                    '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                    '<div class="fw-semibold">Procesando...</div>' +
                    '</div>' +
                    '</div>'
                );
            }
        }

        function desbloquearPantallaRazaAlta() {
            $('#overlayProcesoRazaAlta').remove();
        }

        $(document).ready(function() {
            $('#formRazaAlta').validate({
                rules: {
                    id_especie: {
                        required: true,
                        digits: true
                    },
                    nombre_raza: {
                        required: true,
                        minlength: 2,
                        maxlength: 100
                    },
                    estatus: {
                        required: true
                    },
                    descripcion: {
                        maxlength: 255
                    }
                },
                messages: {
                    id_especie: {
                        required: 'Selecciona una especie.',
                        digits: 'La especie seleccionada no es válida.'
                    },
                    nombre_raza: {
                        required: 'Ingresa el nombre de la raza.',
                        minlength: 'El nombre debe tener al menos 2 caracteres.',
                        maxlength: 'El nombre no puede exceder 100 caracteres.'
                    },
                    estatus: {
                        required: 'Selecciona un estatus.'
                    },
                    descripcion: {
                        maxlength: 'La descripción no puede exceder 255 caracteres.'
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
                    error.insertAfter(element);
                },
                submitHandler: function() {
                    alertify.confirm(
                        'Confirmar',
                        '¿Deseas guardar la raza?',
                        function() {
                            var $btn = $('#btnGuardarRaza');
                            var textoOriginal = $btn.html();

                            bloquearPantallaRazaAlta();
                            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

                            $.ajax({
                                url: 'ajax/razas/guardar.php',
                                type: 'POST',
                                dataType: 'json',
                                data: $('#formRazaAlta').serialize(),
                                success: function(respuesta) {
                                    if (respuesta.success) {
                                        alertify.success(respuesta.message);
                                        setTimeout(function() {
                                            window.location.href = 'razas';
                                        }, 900);
                                    } else {
                                        alertify.error(respuesta.message);
                                    }
                                },
                                error: function(xhr) {
                                    var mensaje = 'Ocurrió un error al guardar la raza.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        mensaje = xhr.responseJSON.message;
                                    }
                                    alertify.error(mensaje);
                                },
                                complete: function() {
                                    desbloquearPantallaRazaAlta();
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