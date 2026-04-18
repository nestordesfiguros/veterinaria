<?php
/* Archivo: contenido/configuracion-modulos.php */

$listaPaquetes = $clsConsulta->consultaGeneral("SELECT id, clave, nombre FROM configuracion_paquetes WHERE estatus = 'activo' ORDER BY nombre ASC");
$totalPaquetes = $clsConsulta->numrows;
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
            <li class="active breadcrumb-item" aria-current="page">Configuración de Módulos</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-solid fa-sliders"></i> &nbsp; Configuración de Módulos</h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <style>
                            .config-modulos-toolbar .form-control,
                            .config-modulos-toolbar .form-select,
                            .config-modulos-toolbar .btn,
                            .config-modulos-toolbar .input-group-text,
                            .config-modulos-filtros .form-select {
                                min-height: 42px;
                            }

                            .config-modulos-buscador {
                                min-width: 260px;
                            }

                            #tablaConfiguracionModulos thead th {
                                white-space: nowrap;
                                vertical-align: middle;
                            }

                            #tablaConfiguracionModulos tbody td {
                                vertical-align: middle;
                            }

                            @media (max-width: 991.98px) {
                                .config-modulos-buscador {
                                    min-width: 100%;
                                }
                            }
                        </style>

                        <div class="row config-modulos-toolbar g-3 align-items-end mb-3">
                            <div class="col-12 col-lg-4 text-start">
                                <button type="button" class="btn btn-primary" id="btnNuevaConfiguracionModulo">
                                    <i class="fa-solid fa-plus"></i> Nueva Configuración
                                </button>
                            </div>

                            <div class="col-12 col-lg-4">
                                <label for="filtro_habilitado" class="form-label mb-1">Filtrar por estado</label>
                                <select class="form-select" id="filtro_habilitado" name="filtro_habilitado">
                                    <option value="">Todos</option>
                                    <option value="1" selected>Habilitados</option>
                                    <option value="0">Deshabilitados</option>
                                    <option value="sin_config">Sin configuración</option>
                                </select>
                            </div>

                            <div class="col-12 col-lg-4">
                                <label for="search" class="form-label mb-1">Buscar</label>
                                <div class="input-group config-modulos-buscador">
                                    <span class="input-group-text">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                    </span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="search"
                                        name="search"
                                        placeholder="Buscar módulo..."
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="row config-modulos-filtros g-3 mb-3">
                            <div class="col-12 col-lg-4">
                                <label for="filtro_paquete" class="form-label mb-1">Filtrar por paquete</label>
                                <select class="form-select" id="filtro_paquete" name="filtro_paquete">
                                    <option value="">Todos los paquetes</option>
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
                                    <option value="sin_paquete">Sin paquete</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaConfiguracionModulos" class="table table-bordered table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">ID</th>
                                        <th>Módulo</th>
                                        <th>Archivo</th>
                                        <th style="width: 110px;">Tipo</th>
                                        <th style="width: 120px;">Canal</th>
                                        <th style="width: 130px;">Paquete</th>
                                        <th style="width: 120px;">Habilitado</th>
                                        <th style="width: 120px;">Menú</th>
                                        <th style="width: 120px;">Obligatorio</th>
                                        <th style="width: 120px;" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
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
        $('#tablaConfiguracionModulos').dataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'ajax/configuracion-modulos/tabla-configuracion-modulos.php',
                type: 'POST',
                data: function(d) {
                    d.filtro_habilitado = $('#filtro_habilitado').val();
                    d.filtro_paquete = $('#filtro_paquete').val();
                }
            },
            paging: true,
            pageLength: 10,
            ordering: true,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "Todo"]
            ],
            dom: "<'row'<'col-sm-3'l><'col-sm-2'><'col-sm-7 d-flex justify-content-end'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-3'l><'col-sm-2'><'col-sm-7 d-flex justify-content-end'p>>",
            initComplete: function() {
                $('#custom_length').appendTo('body');
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true,
            order: [
                [0, 'asc']
            ],
            columns: [{
                    data: 'id'
                },
                {
                    data: 'modulo'
                },
                {
                    data: 'archivo'
                },
                {
                    data: 'tipo_modulo'
                },
                {
                    data: 'canal'
                },
                {
                    data: 'paquete_origen'
                },
                {
                    data: 'habilitado'
                },
                {
                    data: 'visible_menu'
                },
                {
                    data: 'obligatorio'
                },
                {
                    data: 'acciones',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            drawCallback: function() {
                if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip !== 'undefined') {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            }
        });

        var tabla = $('#tablaConfiguracionModulos').DataTable();

        $('#search').on('keyup', function() {
            tabla.search($(this).val()).draw();
        });

        $('#filtro_habilitado, #filtro_paquete').on('change', function() {
            tabla.ajax.reload();
        });

        $('#btnNuevaConfiguracionModulo').on('click', function() {
            window.location.href = 'configuracion-modulos-altas';
        });
    });
</script>