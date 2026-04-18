<?php
/* Archivo: contenido/especies-modificar.php */
require_once 'lib/clsConsultas.php';

$clsConsulta = new Consultas();
$idEspecie = isset($cat) ? (int)$cat : 0;
$especie = null;

if ($idEspecie > 0) {
    $resultado = $clsConsulta->consultaIndividualSegura('cat_especies', $idEspecie);
    if (!empty($resultado)) {
        $especie = $resultado[0];
    }
}
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
            <li class="breadcrumb-item"><a href="especies">Especies</a></li>
            <li class="active breadcrumb-item" aria-current="page">Modificar Especie</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-solid fa-pen-to-square"></i> &nbsp; Modificar Especie</h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php if (!$especie): ?>
                            <div class="alert alert-warning mb-3">
                                No se encontró la especie solicitada.
                            </div>
                            <a href="especies" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Regresar
                            </a>
                        <?php else: ?>
                            <form id="formEspecieModificar" autocomplete="off">
                                <input type="hidden" name="id" id="id" value="<?= (int)$especie['id']; ?>">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nombre_especie" class="form-label">Nombre de la especie <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="nombre_especie"
                                            name="nombre_especie"
                                            maxlength="100"
                                            value="<?= htmlspecialchars($especie['nombre_especie'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            required>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="estatus" class="form-label">Estatus <span class="text-danger">*</span></label>
                                        <select class="form-select" id="estatus" name="estatus" required>
                                            <option value="activo" <?= (($especie['estatus'] ?? '') === 'activo') ? 'selected' : ''; ?>>Activo</option>
                                            <option value="inactivo" <?= (($especie['estatus'] ?? '') === 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
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
                                            placeholder="Descripción breve de la especie"><?= htmlspecialchars($especie['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary" id="btnModificarEspecie">
                                        <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                                    </button>
                                    <a href="especies" class="btn btn-secondary">
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

<?php if ($especie): ?>
    <script>
        $(document).ready(function() {
            $('#formEspecieModificar').validate({
                rules: {
                    id: {
                        required: true,
                        digits: true
                    },
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
                        '¿Deseas guardar los cambios de la especie?',
                        function() {
                            var $btn = $('#btnModificarEspecie');
                            var textoOriginal = $btn.html();

                            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

                            $.ajax({
                                url: 'ajax/especies/modificar.php',
                                type: 'POST',
                                dataType: 'json',
                                data: $('#formEspecieModificar').serialize(),
                                success: function(respuesta) {
                                    if (respuesta.success) {
                                        alertify.success(respuesta.message || 'Especie actualizada correctamente.');
                                        setTimeout(function() {
                                            window.location.href = 'especies';
                                        }, 900);
                                    } else {
                                        alertify.error(respuesta.message || 'No fue posible actualizar la especie.');
                                    }
                                },
                                error: function(xhr) {
                                    var mensaje = 'Ocurrió un error al actualizar la especie.';
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
<?php endif; ?>