<?php
/* Archivo: contenido/clientes-altas.php */

$listaEstados = $clsConsulta->consultaGeneral("SELECT id, nombre, abrev FROM estados ORDER BY nombre ASC");
$totalEstados = $clsConsulta->numrows;

$listaMunicipios = $clsConsulta->consultaGeneral("SELECT id, nombre, estado_id FROM municipios ORDER BY nombre ASC");
$totalMunicipios = $clsConsulta->numrows;
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="clientes">Clientes</a></li>
            <li class="active breadcrumb-item" aria-current="page">Nuevo Cliente</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-solid fa-user-plus"></i> &nbsp; Alta de Cliente</h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form id="formClienteAlta" autocomplete="off">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="razon_social" class="form-label">Razón social <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="razon_social" name="razon_social" maxlength="255" required>
                                </div>

                                <div class="col-md-3">
                                    <label for="rfc" class="form-label">RFC</label>
                                    <input type="text" class="form-control text-uppercase" id="rfc" name="rfc" maxlength="14">
                                </div>

                                <div class="col-md-3">
                                    <label for="estatus" class="form-label">Estatus <span class="text-danger">*</span></label>
                                    <select class="form-select" id="estatus" name="estatus" required>
                                        <option value="1" selected>Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="nombre_comercial" class="form-label">Nombre comercial</label>
                                    <input type="text" class="form-control" id="nombre_comercial" name="nombre_comercial" maxlength="200">
                                </div>

                                <div class="col-md-3">
                                    <label for="correo" class="form-label">Correo</label>
                                    <input type="email" class="form-control" id="correo" name="correo" maxlength="50">
                                </div>

                                <div class="col-md-3">
                                    <label for="correo_factura" class="form-label">Correo factura</label>
                                    <input type="email" class="form-control" id="correo_factura" name="correo_factura" maxlength="50">
                                </div>

                                <div class="col-md-4">
                                    <label for="calle" class="form-label">Calle</label>
                                    <input type="text" class="form-control" id="calle" name="calle" maxlength="50">
                                </div>

                                <div class="col-md-2">
                                    <label for="num_ext" class="form-label">No. exterior</label>
                                    <input type="text" class="form-control" id="num_ext" name="num_ext" maxlength="20">
                                </div>

                                <div class="col-md-2">
                                    <label for="num_int" class="form-label">No. interior</label>
                                    <input type="text" class="form-control" id="num_int" name="num_int" maxlength="20">
                                </div>

                                <div class="col-md-2">
                                    <label for="cp" class="form-label">C.P.</label>
                                    <input type="text" class="form-control" id="cp" name="cp" maxlength="5">
                                </div>

                                <div class="col-md-2">
                                    <label for="localidad" class="form-label">Localidad</label>
                                    <input type="text" class="form-control" id="localidad" name="localidad" maxlength="200">
                                </div>

                                <div class="col-md-4">
                                    <label for="id_estado" class="form-label">Estado</label>
                                    <select class="form-select" id="id_estado" name="id_estado">
                                        <option value="">Selecciona un estado</option>
                                        <?php
                                        if ($totalEstados > 0) {
                                            for ($i = 1; $i <= $totalEstados; $i++) {
                                        ?>
                                                <option value="<?= (int)$listaEstados[$i]['id']; ?>">
                                                    <?= htmlspecialchars($listaEstados[$i]['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="id_municipio" class="form-label">Municipio</label>
                                    <select class="form-select" id="id_municipio" name="id_municipio">
                                        <option value="">Selecciona un estado primero</option>
                                        <?php
                                        if ($totalMunicipios > 0) {
                                            for ($i = 1; $i <= $totalMunicipios; $i++) {
                                        ?>
                                                <option value="<?= (int)$listaMunicipios[$i]['id']; ?>" data-estado="<?= (int)$listaMunicipios[$i]['estado_id']; ?>" style="display:none;">
                                                    <?= htmlspecialchars($listaMunicipios[$i]['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="colonia" class="form-label">Colonia</label>
                                    <input type="text" class="form-control" id="colonia" name="colonia" maxlength="200">
                                </div>

                                <div class="col-md-4">
                                    <label for="compras_nombre" class="form-label">Contacto compras</label>
                                    <input type="text" class="form-control" id="compras_nombre" name="compras_nombre" maxlength="150">
                                </div>

                                <div class="col-md-2">
                                    <label for="compras_tel" class="form-label">Tel. compras</label>
                                    <input type="text" class="form-control" id="compras_tel" name="compras_tel" maxlength="20">
                                </div>

                                <div class="col-md-4">
                                    <label for="cxc_nombre" class="form-label">Contacto CxC</label>
                                    <input type="text" class="form-control" id="cxc_nombre" name="cxc_nombre" maxlength="200">
                                </div>

                                <div class="col-md-2">
                                    <label for="cxc_tel" class="form-label">Tel. CxC</label>
                                    <input type="text" class="form-control" id="cxc_tel" name="cxc_tel" maxlength="20">
                                </div>

                                <div class="col-md-4">
                                    <label for="operaciones_nombre" class="form-label">Contacto operaciones</label>
                                    <input type="text" class="form-control" id="operaciones_nombre" name="operaciones_nombre" maxlength="200">
                                </div>

                                <div class="col-md-2">
                                    <label for="operaciones_tel" class="form-label">Tel. operaciones</label>
                                    <input type="text" class="form-control" id="operaciones_tel" name="operaciones_tel" maxlength="20">
                                </div>

                                <div class="col-md-2">
                                    <label for="id_residente" class="form-label">ID residente</label>
                                    <input type="number" class="form-control" id="id_residente" name="id_residente" min="1" step="1">
                                </div>

                                <div class="col-md-2">
                                    <label for="id_gerente" class="form-label">ID gerente</label>
                                    <input type="number" class="form-control" id="id_gerente" name="id_gerente" min="1" step="1">
                                </div>

                                <div class="col-md-2">
                                    <label for="id_empresa" class="form-label">ID empresa</label>
                                    <input type="number" class="form-control" id="id_empresa" name="id_empresa" min="1" step="1">
                                </div>

                                <div class="col-md-6">
                                    <label for="mapa" class="form-label">Mapa / URL ubicación</label>
                                    <input type="text" class="form-control" id="mapa" name="mapa" maxlength="200">
                                </div>
                            </div>

                            <div class="mt-4 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary" id="btnGuardarCliente">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>
                                <a href="clientes" class="btn btn-secondary">
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
    function bloquearPantallaClienteAlta() {
        if ($('#overlayProcesoClienteAlta').length === 0) {
            $('body').append(
                '<div id="overlayProcesoClienteAlta" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.75);z-index:99999;display:flex;align-items:center;justify-content:center;">' +
                '<div class="text-center">' +
                '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                '<div class="fw-semibold">Procesando...</div>' +
                '</div>' +
                '</div>'
            );
        }
    }

    function desbloquearPantallaClienteAlta() {
        $('#overlayProcesoClienteAlta').remove();
    }

    function filtrarMunicipiosAlta(valorEstado, valorMunicipio) {
        var $municipio = $('#id_municipio');
        var opciones = $municipio.data('opciones-originales');

        if (!opciones) {
            opciones = $municipio.find('option').clone();
            $municipio.data('opciones-originales', opciones);
        }

        $municipio.empty();

        if (valorEstado === '') {
            $municipio.append('<option value="">Selecciona un estado primero</option>');
            return;
        }

        $municipio.append('<option value="">Selecciona un municipio</option>');

        opciones.each(function() {
            var estado = $(this).attr('data-estado');
            var valor = $(this).attr('value');

            if (valor && estado === valorEstado) {
                var selected = (valorMunicipio !== '' && valorMunicipio === valor) ? ' selected' : '';
                $municipio.append('<option value="' + valor + '"' + selected + '>' + $(this).text() + '</option>');
            }
        });
    }

    $(document).ready(function() {
        filtrarMunicipiosAlta($('#id_estado').val(), '');

        $('#id_estado').on('change', function() {
            filtrarMunicipiosAlta($(this).val(), '');
        });

        $('#formClienteAlta').validate({
            rules: {
                razon_social: {
                    required: true,
                    maxlength: 255
                },
                rfc: {
                    maxlength: 14
                },
                nombre_comercial: {
                    maxlength: 200
                },
                correo: {
                    email: true,
                    maxlength: 50
                },
                correo_factura: {
                    email: true,
                    maxlength: 50
                },
                calle: {
                    maxlength: 50
                },
                num_ext: {
                    maxlength: 20
                },
                num_int: {
                    maxlength: 20
                },
                colonia: {
                    maxlength: 200
                },
                cp: {
                    digits: true,
                    minlength: 5,
                    maxlength: 5
                },
                id_estado: {
                    digits: true
                },
                id_municipio: {
                    digits: true
                },
                localidad: {
                    maxlength: 200
                },
                compras_nombre: {
                    maxlength: 150
                },
                compras_tel: {
                    maxlength: 20
                },
                cxc_nombre: {
                    maxlength: 200
                },
                cxc_tel: {
                    maxlength: 20
                },
                operaciones_nombre: {
                    maxlength: 200
                },
                operaciones_tel: {
                    maxlength: 20
                },
                id_residente: {
                    digits: true
                },
                id_gerente: {
                    digits: true
                },
                id_empresa: {
                    digits: true
                },
                mapa: {
                    maxlength: 200
                },
                estatus: {
                    required: true
                }
            },
            messages: {
                razon_social: {
                    required: 'Ingresa la razón social.',
                    maxlength: 'La razón social no puede exceder 255 caracteres.'
                },
                rfc: {
                    maxlength: 'El RFC no puede exceder 14 caracteres.'
                },
                nombre_comercial: {
                    maxlength: 'El nombre comercial no puede exceder 200 caracteres.'
                },
                correo: {
                    email: 'Ingresa un correo válido.',
                    maxlength: 'El correo no puede exceder 50 caracteres.'
                },
                correo_factura: {
                    email: 'Ingresa un correo de factura válido.',
                    maxlength: 'El correo de factura no puede exceder 50 caracteres.'
                },
                cp: {
                    digits: 'El código postal debe ser numérico.',
                    minlength: 'El código postal debe tener 5 dígitos.',
                    maxlength: 'El código postal debe tener 5 dígitos.'
                },
                estatus: {
                    required: 'Selecciona un estatus.'
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
                    '¿Deseas guardar el cliente?',
                    function() {
                        var $btn = $('#btnGuardarCliente');
                        var textoOriginal = $btn.html();

                        bloquearPantallaClienteAlta();
                        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

                        $.ajax({
                            url: 'ajax/clientes/guardar.php',
                            type: 'POST',
                            dataType: 'json',
                            data: $('#formClienteAlta').serialize(),
                            success: function(respuesta) {
                                if (respuesta.success) {
                                    alertify.success(respuesta.message || 'Cliente guardado correctamente.');
                                    setTimeout(function() {
                                        window.location.href = 'clientes';
                                    }, 900);
                                } else {
                                    alertify.error(respuesta.message || 'No fue posible guardar el cliente.');
                                }
                            },
                            error: function(xhr) {
                                var mensaje = 'Ocurrió un error al guardar el cliente.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    mensaje = xhr.responseJSON.message;
                                }
                                alertify.error(mensaje);
                            },
                            complete: function() {
                                desbloquearPantallaClienteAlta();
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