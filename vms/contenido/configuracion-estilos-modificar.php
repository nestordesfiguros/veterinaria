<?php
/* ========================================================================== */
/* Archivo: contenido/configuracion-estilos-modificar.php                     */
/* Ruta: contenido/configuracion-estilos-modificar.php                        */
/* ========================================================================== */

$configuracion = null;

if (isset($cat) && $cat != '') {
    $id_configuracion = (int)$cat;

    $sqlConfiguracion = "SELECT 
                            id,
                            grupo,
                            subgrupo,
                            clave,
                            nombre,
                            valor,
                            valor_default,
                            tipo_control,
                            unidad,
                            opciones,
                            descripcion,
                            estatus
                         FROM configuracion_estilos
                         WHERE id = " . $id_configuracion . "
                         LIMIT 1";

    $resConfiguracion = $clsConsulta->consultaGeneral($sqlConfiguracion);

    if ($clsConsulta->numrows > 0) {
        $configuracion = $resConfiguracion[1];
    }
}

if (!$configuracion) {
?>
    <div class="ms-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="clientes">Inicio</a></li>
                <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
                <li class="breadcrumb-item"><a href="configuracion-estilos">Configuración de estilos</a></li>
                <li class="active breadcrumb-item" aria-current="page"> Modificar configuración </li>
            </ol>
        </nav>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-9 col-md-10 col-12">
                    <div class="card vm-section">
                        <div class="card-header">
                            <h3 class="card-title">Modificar configuración</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="alert alert-warning mb-0">
                                La configuración solicitada no existe.
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <div class="d-flex justify-content-end">
                                <a href="configuracion-estilos" class="btn btn-secondary">Regresar</a>
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

$gruposAmigables = [
    'general' => 'General',
    'titulos' => 'Títulos',
    'formularios' => 'Formularios',
    'botones' => 'Botones',
    'tablas' => 'Tablas',
    'breadcrumb' => 'Breadcrumb',
    'navegacion' => 'Navegación',
    'submenu' => 'Submenú'
];

$subgruposAmigables = [
    'colores' => 'Colores',
    'bordes' => 'Bordes',
    'sombras' => 'Sombras',
    'texto' => 'Texto',
    'estructura' => 'Estructura',
    'espacios' => 'Espacios',
    'filas' => 'Filas',
    'encabezado' => 'Encabezado',
    'botones' => 'Botones',
    'bienvenida' => 'Bienvenida',
    'general' => 'General'
];

$tiposAmigables = [
    'color' => 'Color',
    'text' => 'Texto',
    'number' => 'Número',
    'select' => 'Selección',
    'range' => 'Rango',
    'textarea' => 'Área de texto'
];

$grupoMostrar = isset($gruposAmigables[(string)$configuracion['grupo']])
    ? $gruposAmigables[(string)$configuracion['grupo']]
    : ucfirst((string)$configuracion['grupo']);

$subgrupoMostrar = ((string)($configuracion['subgrupo'] ?? '') !== '')
    ? (isset($subgruposAmigables[(string)$configuracion['subgrupo']])
        ? $subgruposAmigables[(string)$configuracion['subgrupo']]
        : ucfirst((string)$configuracion['subgrupo']))
    : '';

$tipoMostrar = isset($tiposAmigables[(string)$configuracion['tipo_control']])
    ? $tiposAmigables[(string)$configuracion['tipo_control']]
    : ucfirst((string)$configuracion['tipo_control']);
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="clientes">Inicio</a></li>
            <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
            <li class="breadcrumb-item"><a href="configuracion-estilos">Configuración de estilos</a></li>
            <li class="active breadcrumb-item" aria-current="page"> Modificar configuración </li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10 col-md-11 col-12">
                <form id="frmConfiguracionEstilosModificar" name="frmConfiguracionEstilosModificar" method="post" autocomplete="off">
                    <input type="hidden" id="id" name="id" value="<?= $configuracion['id']; ?>">
                    <input type="hidden" id="tipo_control" name="tipo_control" value="<?= htmlspecialchars((string)$configuracion['tipo_control']); ?>">

                    <div class="card vm-section">
                        <div class="card-header">
                            <h3 class="card-title">Modificar configuración de estilos</h3>
                        </div>
                        <!-- /.card-header -->

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <label for="grupo" class="form-label">Grupo</label>
                                    <input type="text" class="form-control" id="grupo" name="grupo" value="<?= htmlspecialchars((string)$grupoMostrar); ?>" readonly>
                                </div>

                                <div class="col-md-4 col-12">
                                    <label for="subgrupo" class="form-label">Subgrupo</label>
                                    <input type="text" class="form-control" id="subgrupo" name="subgrupo" value="<?= htmlspecialchars((string)$subgrupoMostrar); ?>" readonly>
                                </div>

                                <div class="col-md-4 col-12">
                                    <label for="clave" class="form-label">Clave</label>
                                    <input type="text" class="form-control" id="clave" name="clave" value="<?= htmlspecialchars((string)$configuracion['clave']); ?>" readonly>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars((string)$configuracion['nombre']); ?>" readonly>
                                </div>

                                <div class="col-md-3 col-12">
                                    <label for="tipo_control_mostrar" class="form-label">Tipo de control</label>
                                    <input type="text" class="form-control" id="tipo_control_mostrar" name="tipo_control_mostrar" value="<?= htmlspecialchars((string)$tipoMostrar); ?>" readonly>
                                </div>

                                <div class="col-md-3 col-12">
                                    <label for="unidad" class="form-label">Unidad</label>
                                    <input type="text" class="form-control" id="unidad" name="unidad" value="<?= htmlspecialchars((string)($configuracion['unidad'] ?? '')); ?>" readonly>
                                </div>

                                <div class="col-md-6 col-12" id="contenedor_valor_texto" style="display:none;">
                                    <label for="valor" class="form-label">Valor</label>
                                    <input type="text" class="form-control" id="valor" name="valor" value="<?= htmlspecialchars((string)$configuracion['valor']); ?>">
                                </div>

                                <div class="col-md-6 col-12" id="contenedor_valor_numero" style="display:none;">
                                    <label for="valor_numero" class="form-label">Valor</label>
                                    <input type="number" step="any" class="form-control" id="valor_numero" name="valor_numero" value="<?= htmlspecialchars((string)$configuracion['valor']); ?>">
                                </div>

                                <div class="col-md-6 col-12" id="contenedor_valor_color" style="display:none;">
                                    <label for="valor_color" class="form-label">Valor</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" class="form-control form-control-color p-1" id="valor_color" name="valor_color" value="<?= htmlspecialchars((string)$configuracion['valor']); ?>" style="max-width: 60px; min-width: 60px;">
                                        <input type="text" class="form-control" id="valor_color_texto" name="valor_color_texto" value="<?= htmlspecialchars((string)$configuracion['valor']); ?>">
                                    </div>
                                </div>

                                <div class="col-md-6 col-12" id="contenedor_valor_textarea" style="display:none;">
                                    <label for="valor_textarea" class="form-label">Valor</label>
                                    <textarea class="form-control" id="valor_textarea" name="valor_textarea"><?= htmlspecialchars((string)$configuracion['valor']); ?></textarea>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="valor_default" class="form-label">Valor default</label>
                                    <input type="text" class="form-control" id="valor_default" name="valor_default" value="<?= htmlspecialchars((string)($configuracion['valor_default'] ?? '')); ?>" readonly>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="estatus" class="form-label">Estatus</label>
                                    <select class="form-select" id="estatus" name="estatus">
                                        <option value="activo" <?= $configuracion['estatus'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="inactivo" <?= $configuracion['estatus'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" readonly><?= htmlspecialchars((string)($configuracion['descripcion'] ?? '')); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->

                        <div class="card-footer">
                            <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-md-end">
                                <a href="configuracion-estilos" class="btn btn-secondary">
                                    Regresar
                                </a>
                                <button type="button" id="btnRestaurarDefault" name="btnRestaurarDefault" class="btn btn-secondary">
                                    <i class="fa fa-rotate-left"></i> Restaurar default
                                </button>
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
        function mostrarControlSegunTipo() {
            var tipo = $('#tipo_control').val();

            $('#contenedor_valor_texto').hide();
            $('#contenedor_valor_numero').hide();
            $('#contenedor_valor_color').hide();
            $('#contenedor_valor_textarea').hide();

            if (tipo === 'color') {
                $('#contenedor_valor_color').show();
            } else if (tipo === 'number' || tipo === 'range') {
                $('#contenedor_valor_numero').show();
            } else if (tipo === 'textarea') {
                $('#contenedor_valor_textarea').show();
            } else {
                $('#contenedor_valor_texto').show();
            }
        }

        function sincronizarColorTexto() {
            $('#valor_color_texto').val($('#valor_color').val());
        }

        function sincronizarTextoColor() {
            var valor = $('#valor_color_texto').val();
            var expresionHex = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;

            if (expresionHex.test(valor)) {
                $('#valor_color').val(valor);
            }
        }

        function obtenerValorFinal() {
            var tipo = $('#tipo_control').val();

            if (tipo === 'color') {
                return $('#valor_color_texto').val();
            }

            if (tipo === 'number' || tipo === 'range') {
                return $('#valor_numero').val();
            }

            if (tipo === 'textarea') {
                return $('#valor_textarea').val();
            }

            return $('#valor').val();
        }

        mostrarControlSegunTipo();

        $('#valor_color').on('input change', function() {
            sincronizarColorTexto();
        });

        $('#valor_color_texto').on('input blur', function() {
            sincronizarTextoColor();
        });

        $('#btnRestaurarDefault').on('click', function() {
            var valorDefault = $('#valor_default').val();
            var tipo = $('#tipo_control').val();

            if (tipo === 'color') {
                $('#valor_color').val(valorDefault);
                $('#valor_color_texto').val(valorDefault);
            } else if (tipo === 'number' || tipo === 'range') {
                $('#valor_numero').val(valorDefault);
            } else if (tipo === 'textarea') {
                $('#valor_textarea').val(valorDefault);
            } else {
                $('#valor').val(valorDefault);
            }
        });

        $('#frmConfiguracionEstilosModificar').validate({
            ignore: [],
            rules: {
                estatus: {
                    required: true
                }
            },
            messages: {
                estatus: {
                    required: "Selecciona el estatus"
                }
            },
            errorElement: 'div',
            errorClass: 'text-danger',
            submitHandler: function(form) {
                var valorFinal = $.trim(obtenerValorFinal());

                if (valorFinal === '') {
                    alertify.error('Ingresa un valor para la configuración');
                    return false;
                }

                alertify.confirm(
                    'Confirmar',
                    '¿Deseas modificar esta configuración?',
                    function() {
                        $('#btnModificar').prop('disabled', true);
                        $('#spinner').show();

                        $.ajax({
                            url: 'ajax/configuracion-estilos/modificar.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                id: $('#id').val(),
                                valor: valorFinal,
                                estatus: $('#estatus').val()
                            },
                            success: function(res) {
                                $('#spinner').hide();
                                $('#btnModificar').prop('disabled', false);

                                if (res.success) {
                                    alertify.success(res.message);
                                    setTimeout(function() {
                                        window.location.href = 'configuracion-estilos';
                                    }, 1000);
                                } else {
                                    alertify.error(res.message);
                                }
                            },
                            error: function() {
                                $('#spinner').hide();
                                $('#btnModificar').prop('disabled', false);
                                alertify.error('Ocurrió un error al modificar la configuración');
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