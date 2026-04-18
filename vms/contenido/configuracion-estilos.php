<?php
/* ========================================================================== */
/* Archivo: contenido/configuracion-estilos.php                               */
/* Ruta: contenido/configuracion-estilos.php                                  */
/* ========================================================================== */
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="clientes">Inicio</a></li>
            <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
            <li class="active breadcrumb-item" aria-current="page"> Configuración de estilos </li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card vm-section">
                    <div class="card-header">
                        <h3 class="card-title">Configuración de estilos</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="vm-toolbar">
                            <div class="vm-toolbar__left">
                                <a href="configuracion-estilos" class="btn btn-secondary">
                                    <i class="fa fa-rotate"></i> Actualizar
                                </a>
                            </div>

                            <div class="vm-toolbar__center">
                                <label for="filtro_grupo" class="form-label">Grupo</label>
                                <select id="filtro_grupo" name="filtro_grupo" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="general">General</option>
                                    <option value="titulos">Títulos</option>
                                    <option value="formularios">Formularios</option>
                                    <option value="botones">Botones</option>
                                    <option value="tablas">Tablas</option>
                                    <option value="breadcrumb">Breadcrumb</option>
                                    <option value="navegacion">Navegación</option>
                                    <option value="submenu">Submenú</option>
                                </select>
                            </div>

                            <div class="vm-toolbar__right">
                                <label for="search" class="form-label">Buscar</label>
                                <input type="text" id="search" class="form-control" placeholder="Buscar clave, nombre o grupo">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaConfiguracionEstilos" class="table table-bordered table-hover align-middle w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">ID</th>
                                        <th style="width: 140px;">Grupo</th>
                                        <th style="width: 160px;">Subgrupo</th>
                                        <th style="width: 220px;">Clave</th>
                                        <th>Nombre</th>
                                        <th style="width: 160px;">Valor</th>
                                        <th style="width: 120px;">Tipo</th>
                                        <th style="width: 110px;">Estatus</th>
                                        <th style="width: 130px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <div class="row align-items-center">
                                <div class="col-md-4 col-12 mb-2 mb-md-0">
                                    <div id="tablaConfiguracionEstilos_length_container"></div>
                                </div>
                                <div class="col-md-4 col-12 text-center mb-2 mb-md-0">
                                    <div id="tablaConfiguracionEstilos_info_container"></div>
                                </div>
                                <div class="col-md-4 col-12 text-md-end">
                                    <div id="tablaConfiguracionEstilos_paginate_container"></div>
                                </div>
                            </div>
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
        $('#tablaConfiguracionEstilos').dataTable({
            ajax: {
                url: 'ajax/configuracion-estilos/tabla-configuracion-estilos.php',
                type: 'POST',
                data: function(d) {
                    d.grupo = $('#filtro_grupo').val();
                }
            },
            paging: true,
            pageLength: 10,
            ordering: true,
            processing: true,
            serverSide: true,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "Todo"]
            ],
            dom: "<'row'<'col-sm-12'tr>>" +
                "<'d-none'l>" +
                "<'d-none'i>" +
                "<'d-none'p>",
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar',
                processing: "Procesando...",
                emptyTable: "No hay configuraciones registradas",
                zeroRecords: "No se encontraron resultados"
            },
            responsive: false,
            drawCallback: function() {
                $('#tablaConfiguracionEstilos_length').appendTo('#tablaConfiguracionEstilos_length_container');
                $('#tablaConfiguracionEstilos_info').appendTo('#tablaConfiguracionEstilos_info_container');
                $('#tablaConfiguracionEstilos_paginate').appendTo('#tablaConfiguracionEstilos_paginate_container');
            }
        });

        var oTable = $('#tablaConfiguracionEstilos').DataTable();

        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        $('#filtro_grupo').on('change', function() {
            oTable.ajax.reload();
        });
    });
</script>