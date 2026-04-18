<?php
/* ========================================================================== */
/* Archivo: contenido/bancos-altas.php                                        */
/* Ruta: contenido/bancos-altas.php                                           */
/* ========================================================================== */
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="clientes">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item"><a href="bancos">Bancos</a></li>
            <li class="active breadcrumb-item" aria-current="page"> Nuevo banco </li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9 col-md-10 col-12">
                <form id="frmBancoAlta" name="frmBancoAlta" method="post" autocomplete="off">
                    <div class="card vm-section">
                        <div class="card-header">
                            <h3 class="card-title">Alta de banco</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-8 col-12">
                                    <label for="nombre_banco" class="form-label">Nombre del banco</label>
                                    <input type="text" class="form-control" id="nombre_banco" name="nombre_banco" maxlength="255" placeholder="Ej. Banco Nacional">
                                </div>

                                <div class="col-md-4 col-12">
                                    <label for="clave_banco" class="form-label">Clave banco</label>
                                    <input type="text" class="form-control text-uppercase" id="clave_banco" name="clave_banco" maxlength="3" placeholder="Ej. BBV">
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->

                        <div class="card-footer">
                            <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-md-end">
                                <a href="bancos" class="btn btn-secondary">
                                    Regresar
                                </a>
                                <button type="submit" id="btnGuardar" name="btnGuardar" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Guardar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
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
        $('#clave_banco').on('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 3);
        });

        $('#frmBancoAlta').validate({
            rules: {
                nombre_banco: {
                    required: true
                },
                clave_banco: {
                    required: true,
                    minlength: 3,
                    maxlength: 3
                }
            },
            messages: {
                nombre_banco: {
                    required: "Ingresa el nombre del banco"
                },
                clave_banco: {
                    required: "Ingresa la clave del banco",
                    minlength: "La clave debe tener 3 caracteres",
                    maxlength: "La clave debe tener 3 caracteres"
                }
            },
            errorElement: 'div',
            errorClass: 'text-danger',
            submitHandler: function(form) {
                alertify.confirm(
                    'Confirmar',
                    '¿Deseas guardar este banco?',
                    function() {
                        $('#btnGuardar').prop('disabled', true);
                        $('#spinner').show();

                        $.ajax({
                            url: 'ajax/bancos/guardar.php',
                            type: 'POST',
                            dataType: 'json',
                            data: $('#frmBancoAlta').serialize(),
                            success: function(res) {
                                $('#spinner').hide();
                                $('#btnGuardar').prop('disabled', false);

                                if (res.success) {
                                    alertify.success(res.message);
                                    setTimeout(function() {
                                        window.location.href = 'bancos';
                                    }, 1000);
                                } else {
                                    alertify.error(res.message);
                                }
                            },
                            error: function() {
                                $('#spinner').hide();
                                $('#btnGuardar').prop('disabled', false);
                                alertify.error('Ocurrió un error al guardar el banco');
                            }
                        });
                    },
                    function() {}
                ).set('labels', {
                    ok: 'Aceptar',
                    cancel: 'Cancelar'
                });

                return false;
            }
        });
    });
</script>