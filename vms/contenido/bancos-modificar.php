<?php
/* ========================================================================== */
/* Archivo: contenido/bancos-modificar.php                                    */
/* Ruta: contenido/bancos-modificar.php                                       */
/* ========================================================================== */

$banco = null;

if (isset($cat) && $cat != '') {
    $id_banco = (int)$cat;
    $sqlBanco = "SELECT id, nombre_banco, clave_banco, status FROM cat_bancos WHERE id = " . $id_banco . " LIMIT 1";
    $resBanco = $clsConsulta->consultaGeneral($sqlBanco);

    if ($clsConsulta->numrows > 0) {
        $banco = $resBanco[1];
    }
}

if (!$banco) {
?>
    <div class="ms-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="clientes">Inicio</a></li>
                <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
                <li class="breadcrumb-item"><a href="bancos">Bancos</a></li>
                <li class="active breadcrumb-item" aria-current="page"> Modificar banco </li>
            </ol>
        </nav>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-9 col-md-10 col-12">
                    <div class="card vm-section">
                        <div class="card-header">
                            <h3 class="card-title">Modificar banco</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="alert alert-warning mb-0">
                                El banco solicitado no existe.
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <div class="d-flex justify-content-end">
                                <a href="bancos" class="btn btn-secondary">Regresar</a>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
<?php
    return;
}
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="clientes">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item"><a href="bancos">Bancos</a></li>
            <li class="active breadcrumb-item" aria-current="page"> Modificar banco </li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9 col-md-10 col-12">
                <form id="frmBancoModificar" name="frmBancoModificar" method="post" autocomplete="off">
                    <input type="hidden" id="id" name="id" value="<?= $banco['id']; ?>">

                    <div class="card vm-section">
                        <div class="card-header">
                            <h3 class="card-title">Modificar banco</h3>
                        </div>
                        <!-- /.card-header -->

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label for="nombre_banco" class="form-label">Nombre del banco</label>
                                    <input type="text" class="form-control" id="nombre_banco" name="nombre_banco" maxlength="255" value="<?= htmlspecialchars($banco['nombre_banco']); ?>" placeholder="Ej. Banco Nacional">
                                </div>

                                <div class="col-md-3 col-12">
                                    <label for="clave_banco" class="form-label">Clave banco</label>
                                    <input type="text" class="form-control text-uppercase" id="clave_banco" name="clave_banco" maxlength="3" value="<?= htmlspecialchars($banco['clave_banco']); ?>" placeholder="Ej. BBV">
                                </div>

                                <div class="col-md-3 col-12">
                                    <label for="status" class="form-label">Estatus</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="activo" <?= $banco['status'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="inactivo" <?= $banco['status'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->

                        <div class="card-footer">
                            <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-md-end">
                                <a href="bancos" class="btn btn-secondary">
                                    Regresar
                                </a>
                                <button type="submit" id="btnModificar" name="btnModificar" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Modificar
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

        $('#frmBancoModificar').validate({
            rules: {
                nombre_banco: {
                    required: true
                },
                clave_banco: {
                    required: true,
                    minlength: 3,
                    maxlength: 3
                },
                status: {
                    required: true
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
                },
                status: {
                    required: "Selecciona el estatus"
                }
            },
            errorElement: 'div',
            errorClass: 'text-danger',
            submitHandler: function(form) {
                alertify.confirm(
                    'Confirmar',
                    '¿Deseas modificar este banco?',
                    function() {
                        $('#btnModificar').prop('disabled', true);
                        $('#spinner').show();

                        $.ajax({
                            url: 'ajax/bancos/modificar.php',
                            type: 'POST',
                            dataType: 'json',
                            data: $('#frmBancoModificar').serialize(),
                            success: function(res) {
                                $('#spinner').hide();
                                $('#btnModificar').prop('disabled', false);

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
                                $('#btnModificar').prop('disabled', false);
                                alertify.error('Ocurrió un error al modificar el banco');
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