<?php
/* Archivo: contenido/clientes-modificar.php */

$idCliente = isset($cat) ? (int)$cat : 0;

$registroCliente = null;
$consultaCliente = null;
$totalCliente = 0;

if ($idCliente > 0) {
    $consultaCliente = $clsConsulta->consultaGeneral("
        SELECT
            id,
            razon_social,
            CAST(rfc AS CHAR) AS rfc_texto,
            nombre_comercial,
            calle,
            num_ext,
            num_int,
            colonia,
            cp,
            id_estado,
            id_municipio,
            localidad,
            correo,
            correo_factura,
            compras_nombre,
            compras_tel,
            fecha_alta,
            updated_at,
            estatus,
            mapa,
            cxc_nombre,
            cxc_tel,
            operaciones_nombre,
            operaciones_tel,
            id_residente,
            id_gerente,
            id_empresa
        FROM cat_clientes
        WHERE id = " . $idCliente . "
        LIMIT 1
    ");
    $totalCliente = $clsConsulta->numrows;

    if ($totalCliente > 0) {
        $registroCliente = $consultaCliente[1];
    }
}

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
            <li class="active breadcrumb-item" aria-current="page">Modificar Cliente</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-solid fa-user-pen"></i> &nbsp; Modificar Cliente</h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php if (!$registroCliente): ?>
                            <div class="alert alert-warning mb-3">
                                No se encontró el cliente solicitado.
                            </div>

                            <a href="clientes" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Regresar
                            </a>
                        <?php else: ?>
                            <form id="formClienteModificar" autocomplete="off">
                                <input type="hidden" name="id" id="id" value="<?= (int)$registroCliente['id']; ?>">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="razon_social" class="form-label">Razón social <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="razon_social"
                                            name="razon_social"
                                            maxlength="255"
                                            value="<?= htmlspecialchars((string)$registroCliente['razon_social'], ENT_QUOTES, 'UTF-8'); ?>"
                                            required>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="rfc" class="form-label">RFC</label>
                                        <input
                                            type="text"
                                            class="form-control text-uppercase"
                                            id="rfc"
                                            name="rfc"
                                            maxlength="14"
                                            value="<?= htmlspecialchars((string)$registroCliente['rfc_texto'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-3">
                                        <label for="estatus" class="form-label">Estatus <span class="text-danger">*</span></label>
                                        <select class="form-select" id="estatus" name="estatus" required>
                                            <option value="1" <?= ((int)$registroCliente['estatus'] === 1) ? 'selected' : ''; ?>>Activo</option>
                                            <option value="0" <?= ((int)$registroCliente['estatus'] === 0) ? 'selected' : ''; ?>>Inactivo</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nombre_comercial" class="form-label">Nombre comercial</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="nombre_comercial"
                                            name="nombre_comercial"
                                            maxlength="200"
                                            value="<?= htmlspecialchars((string)$registroCliente['nombre_comercial'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-3">
                                        <label for="correo" class="form-label">Correo</label>
                                        <input
                                            type="email"
                                            class="form-control"
                                            id="correo"
                                            name="correo"
                                            maxlength="50"
                                            value="<?= htmlspecialchars((string)$registroCliente['correo'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-3">
                                        <label for="correo_factura" class="form-label">Correo factura</label>
                                        <input
                                            type="email"
                                            class="form-control"
                                            id="correo_factura"
                                            name="correo_factura"
                                            maxlength="50"
                                            value="<?= htmlspecialchars((string)$registroCliente['correo_factura'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="calle" class="form-label">Calle</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="calle"
                                            name="calle"
                                            maxlength="50"
                                            value="<?= htmlspecialchars((string)$registroCliente['calle'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="num_ext" class="form-label">No. exterior</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="num_ext"
                                            name="num_ext"
                                            maxlength="20"
                                            value="<?= htmlspecialchars((string)$registroCliente['num_ext'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="num_int" class="form-label">No. interior</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="num_int"
                                            name="num_int"
                                            maxlength="20"
                                            value="<?= htmlspecialchars((string)$registroCliente['num_int'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="cp" class="form-label">C.P.</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="cp"
                                            name="cp"
                                            maxlength="5"
                                            value="<?= htmlspecialchars((string)$registroCliente['cp'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="localidad" class="form-label">Localidad</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="localidad"
                                            name="localidad"
                                            maxlength="200"
                                            value="<?= htmlspecialchars((string)$registroCliente['localidad'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="id_estado" class="form-label">Estado</label>
                                        <select class="form-select" id="id_estado" name="id_estado">
                                            <option value="">Selecciona un estado</option>
                                            <?php
                                            if ($totalEstados > 0) {
                                                for ($i = 1; $i <= $totalEstados; $i++) {
                                                    $selectedEstado = ((int)$registroCliente['id_estado'] === (int)$listaEstados[$i]['id']) ? 'selected' : '';
                                            ?>
                                                    <option value="<?= (int)$listaEstados[$i]['id']; ?>" <?= $selectedEstado; ?>>
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
                                        <select class="form-select" id="id_municipio" name="id_municipio" data-selected="<?= (int)$registroCliente['id_municipio']; ?>">
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
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="colonia"
                                            name="colonia"
                                            maxlength="200"
                                            value="<?= htmlspecialchars((string)$registroCliente['colonia'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="compras_nombre" class="form-label">Contacto compras</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="compras_nombre"
                                            name="compras_nombre"
                                            maxlength="150"
                                            value="<?= htmlspecialchars((string)$registroCliente['compras_nombre'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="compras_tel" class="form-label">Tel. compras</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="compras_tel"
                                            name="compras_tel"
                                            maxlength="20"
                                            value="<?= htmlspecialchars((string)$registroCliente['compras_tel'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="cxc_nombre" class="form-label">Contacto CxC</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="cxc_nombre"
                                            name="cxc_nombre"
                                            maxlength="200"
                                            value="<?= htmlspecialchars((string)$registroCliente['cxc_nombre'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="cxc_tel" class="form-label">Tel. CxC</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="cxc_tel"
                                            name="cxc_tel"
                                            maxlength="20"
                                            value="<?= htmlspecialchars((string)$registroCliente['cxc_tel'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="operaciones_nombre" class="form-label">Contacto operaciones</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="operaciones_nombre"
                                            name="operaciones_nombre"
                                            maxlength="200"
                                            value="<?= htmlspecialchars((string)$registroCliente['operaciones_nombre'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="operaciones_tel" class="form-label">Tel. operaciones</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="operaciones_tel"
                                            name="operaciones_tel"
                                            maxlength="20"
                                            value="<?= htmlspecialchars((string)$registroCliente['operaciones_tel'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="id_residente" class="form-label">ID residente</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            id="id_residente"
                                            name="id_residente"
                                            min="1"
                                            step="1"
                                            value="<?= htmlspecialchars((string)$registroCliente['id_residente'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="id_gerente" class="form-label">ID gerente</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            id="id_gerente"
                                            name="id_gerente"
                                            min="1"
                                            step="1"
                                            value="<?= htmlspecialchars((string)$registroCliente['id_gerente'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="id_empresa" class="form-label">ID empresa</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            id="id_empresa"
                                            name="id_empresa"
                                            min="1"
                                            step="1"
                                            value="<?= htmlspecialchars((string)$registroCliente['id_empresa'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="mapa" class="form-label">Mapa / URL ubicación</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="mapa"
                                            name="mapa"
                                            maxlength="200"
                                            value="<?= htmlspecialchars((string)$registroCliente['mapa'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>

                                <div class="mt-4 d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary" id="btnModificarCliente">
                                        <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                                    </button>
                                    <a href="clientes" class="btn btn-secondary">
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

<?php if ($registroCliente): ?>
    <script>
        function bloquearPantallaClienteModificar() {
            if ($('#overlayProcesoClienteModificar').length === 0) {
                $('body').append(
                    '<div id="overlayProcesoClienteModificar" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.75);z-index:99999;display:flex;align-items:center;justify-content:center;">' +
                    '<div class="text-center">' +
                    '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                    '<div class="fw-semibold">Procesando...</div>' +
                    '</div>' +
                    '</div>'
                );
            }
        }

        function desbloquearPantallaClienteModificar() {
            $('#overlayProcesoClienteModificar').remove();
        }

        function filtrarMunicipiosModificar(valorEstado, valorMunicipio) {
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
            var municipioSeleccionado = $('#id_municipio').attr('data-selected') || '';
            filtrarMunicipiosModificar($('#id_estado').val(), municipioSeleccionado);

            $('#id_estado').on('change', function() {
                filtrarMunicipiosModificar($(this).val(), '');
            });

            $('#formClienteModificar').validate({
                rules: {
                    id: {
                        required: true,
                        digits: true
                    },
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
                        '¿Deseas guardar los cambios del cliente?',
                        function() {
                            var $btn = $('#btnModificarCliente');
                            var textoOriginal = $btn.html();

                            bloquearPantallaClienteModificar();
                            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

                            $.ajax({
                                url: 'ajax/clientes/modificar.php',
                                type: 'POST',
                                dataType: 'json',
                                data: $('#formClienteModificar').serialize(),
                                success: function(respuesta) {
                                    if (respuesta.success) {
                                        alertify.success(respuesta.message || 'Cliente actualizado correctamente.');
                                        setTimeout(function() {
                                            window.location.href = 'clientes';
                                        }, 900);
                                    } else {
                                        alertify.error(respuesta.message || 'No fue posible actualizar el cliente.');
                                    }
                                },
                                error: function(xhr) {
                                    var mensaje = 'Ocurrió un error al actualizar el cliente.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        mensaje = xhr.responseJSON.message;
                                    }
                                    alertify.error(mensaje);
                                },
                                complete: function() {
                                    desbloquearPantallaClienteModificar();
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