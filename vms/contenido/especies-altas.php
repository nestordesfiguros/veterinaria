<?php
/* Archivo: contenido/especies-altas.php */
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
            <li class="breadcrumb-item"><a href="especies">Especies</a></li>
            <li class="active breadcrumb-item" aria-current="page">Nueva Especie</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-solid fa-plus"></i> &nbsp; Alta de Especie</h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form id="formEspecieAlta" autocomplete="off">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nombre_especie" class="form-label">Nombre de la especie <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="nombre_especie"
                                        name="nombre_especie"
                                        maxlength="100"
                                        placeholder="Ejemplo: Bovino"
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
                                        placeholder="Descripción breve de la especie"></textarea>
                                </div>
                            </div>

                            <div class="mt-4 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary" id="btnGuardarEspecie">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>
                                <a href="especies" class="btn btn-secondary">
                                    <i class="fa-solid fa-arrow-left"></i> Regresar
                                </a>
                            </div>
                        </form>
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

<script>
    $(document).ready(function() {
        $('#formEspecieAlta').validate({
            rules: {
                nombre_especie: {
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
                nombre_especie: {
                    required: 'Ingresa el nombre de la especie.',
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
                    '¿Deseas guardar la especie?',
                    function() {
                        var $btn = $('#btnGuardarEspecie');
                        var textoOriginal = $btn.html();

                        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

                        $.ajax({
                            url: 'ajax/especies/guardar.php',
                            type: 'POST',
                            dataType: 'json',
                            data: $('#formEspecieAlta').serialize(),
                            success: function(respuesta) {
                                if (respuesta.success) {
                                    alertify.success(respuesta.message || 'Especie guardada correctamente.');
                                    setTimeout(function() {
                                        window.location.href = 'especies';
                                    }, 900);
                                } else {
                                    alertify.error(respuesta.message || 'No fue posible guardar la especie.');
                                }
                            },
                            error: function(xhr) {
                                var mensaje = 'Ocurrió un error al guardar la especie.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    mensaje = xhr.responseJSON.message;
                                }
                                alertify.error(mensaje);
                            },
                            complete: function() {
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