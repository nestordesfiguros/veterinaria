<?php
/* Archivo: contenido/clientes.php */
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="active breadcrumb-item" aria-current="page">Clientes</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-solid fa-users"></i> &nbsp; Catálogo de Clientes</h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <style>
                            .clientes-toolbar .form-control,
                            .clientes-toolbar .form-select,
                            .clientes-toolbar .btn {
                                min-height: 42px;
                            }

                            .clientes-toolbar .input-group-text {
                                min-height: 42px;
                            }

                            .clientes-buscador {
                                min-width: 260px;
                            }

                            #tablaClientes thead th {
                                white-space: nowrap;
                                vertical-align: middle;
                            }

                            #tablaClientes tbody td {
                                vertical-align: middle;
                            }

                            @media (max-width: 991.98px) {
                                .clientes-buscador {
                                    min-width: 100%;
                                }
                            }
                        </style>

                        <div class="row clientes-toolbar g-3 align-items-end mb-3">
                            <div class="col-12 col-lg-4 text-start">
                                <button type="button" class="btn btn-primary" id="btnNuevoCliente">
                                    <i class="fa-solid fa-plus"></i> Nuevo Cliente
                                </button>
                            </div>

                            <div class="col-12 col-lg-4">
                                <label for="filtro_estatus" class="form-label mb-1">Filtrar por estatus</label>
                                <select class="form-select" id="filtro_estatus" name="filtro_estatus">
                                    <option value="">Todos</option>
                                    <option value="1" selected>Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>

                            <div class="col-12 col-lg-4">
                                <label for="search" class="form-label mb-1">Buscar</label>
                                <div class="input-group clientes-buscador">
                                    <span class="input-group-text">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                    </span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="search"
                                        name="search"
                                        placeholder="Buscar cliente..."
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaClientes" class="table table-bordered table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th style="width: 90px;">ID</th>
                                        <th>Razón Social</th>
                                        <th style="width: 150px;">RFC</th>
                                        <th>Nombre Comercial</th>
                                        <th>Correo</th>
                                        <th style="width: 120px;">Estatus</th>
                                        <th style="width: 170px;">Actualizado</th>
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
        $('#tablaClientes').dataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'ajax/clientes/tabla-clientes.php',
                type: 'POST',
                data: function(d) {
                    d.filtro_estatus = $('#filtro_estatus').val();
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
                [0, 'desc']
            ],
            columns: [{
                    data: 'id'
                },
                {
                    data: 'razon_social'
                },
                {
                    data: 'rfc'
                },
                {
                    data: 'nombre_comercial'
                },
                {
                    data: 'correo'
                },
                {
                    data: 'estatus'
                },
                {
                    data: 'updated_at'
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

        var oTable = $('#tablaClientes').DataTable();

        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        $('#filtro_estatus').on('change', function() {
            oTable.ajax.reload();
        });

        $('#btnNuevoCliente').on('click', function() {
            window.location.href = 'clientes-altas';
        });
    });
</script>