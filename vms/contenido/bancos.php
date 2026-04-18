<?php
/* ========================================================================== */
/* Archivo: contenido/bancos.php                                              */
/* Ruta: contenido/bancos.php                                                 */
/* ========================================================================== */
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="clientes">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="active breadcrumb-item" aria-current="page"> Bancos </li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card vm-section">
                    <div class="card-header">
                        <h3 class="card-title">Catálogo de Bancos</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="vm-toolbar">
                            <div class="vm-toolbar__left">
                                <a href="bancos-altas" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Nuevo banco
                                </a>
                            </div>

                            <div class="vm-toolbar__center">
                                <label for="filtro_estatus" class="form-label">Estatus</label>
                                <select id="filtro_estatus" name="filtro_estatus" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="activo">Activos</option>
                                    <option value="inactivo">Inactivos</option>
                                </select>
                            </div>

                            <div class="vm-toolbar__right">
                                <label for="search" class="form-label">Buscar</label>
                                <input type="text" id="search" class="form-control" placeholder="Buscar banco o clave">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaBancos" class="table table-bordered table-hover align-middle w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">ID</th>
                                        <th>Banco</th>
                                        <th style="width: 120px;">Clave</th>
                                        <th style="width: 130px;">Estatus</th>
                                        <th style="width: 180px;">Fecha alta</th>
                                        <th style="width: 140px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <div class="row align-items-center">
                                <div class="col-md-4 col-12 mb-2 mb-md-0">
                                    <div id="tablaBancos_length_container"></div>
                                </div>
                                <div class="col-md-4 col-12 text-center mb-2 mb-md-0">
                                    <div id="tablaBancos_info_container"></div>
                                </div>
                                <div class="col-md-4 col-12 text-md-end">
                                    <div id="tablaBancos_paginate_container"></div>
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
        $('#tablaBancos').dataTable({
            ajax: {
                url: 'ajax/bancos/tabla-bancos.php',
                type: 'POST',
                data: function(d) {
                    d.estatus = $('#filtro_estatus').val();
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
                emptyTable: "No hay bancos registrados",
                zeroRecords: "No se encontraron resultados"
            },
            responsive: false,
            drawCallback: function() {
                $('#tablaBancos_length').appendTo('#tablaBancos_length_container');
                $('#tablaBancos_info').appendTo('#tablaBancos_info_container');
                $('#tablaBancos_paginate').appendTo('#tablaBancos_paginate_container');
            }
        });

        var oTable = $('#tablaBancos').DataTable();

        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        $('#filtro_estatus').on('change', function() {
            oTable.ajax.reload();
        });
    });
</script>