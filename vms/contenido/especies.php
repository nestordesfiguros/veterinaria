<?php
/* Archivo: contenido/especies.php */
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
            <li class="active breadcrumb-item" aria-current="page">Especies</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-solid fa-paw"></i> &nbsp; Catálogo de Especies</h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <style>
                            .especies-toolbar .form-control,
                            .especies-toolbar .form-select,
                            .especies-toolbar .btn {
                                min-height: 42px;
                            }

                            .especies-toolbar .input-group-text {
                                min-height: 42px;
                            }

                            #tablaEspecies thead th {
                                white-space: nowrap;
                                vertical-align: middle;
                            }

                            #tablaEspecies tbody td {
                                vertical-align: middle;
                            }

                            .especies-buscador {
                                min-width: 260px;
                            }

                            @media (max-width: 991.98px) {
                                .especies-buscador {
                                    min-width: 100%;
                                }
                            }
                        </style>

                        <div class="row especies-toolbar g-3 align-items-end mb-3">
                            <div class="col-12 col-lg-4 text-start">
                                <button type="button" class="btn btn-primary" id="btnNuevaEspecie">
                                    <i class="fa-solid fa-plus"></i> Nueva Especie
                                </button>
                            </div>

                            <div class="col-12 col-lg-4">
                                <label for="filtro_estatus" class="form-label mb-1">Filtrar por estatus</label>
                                <select class="form-select" id="filtro_estatus" name="filtro_estatus">
                                    <option value="">Todos</option>
                                    <option value="activo" selected>Activos</option>
                                    <option value="inactivo">Inactivos</option>
                                </select>
                            </div>

                            <div class="col-12 col-lg-4">
                                <label for="search" class="form-label mb-1">Buscar</label>
                                <div class="input-group especies-buscador">
                                    <span class="input-group-text">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                    </span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="search"
                                        name="search"
                                        placeholder="Buscar especie..."
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaEspecies" class="table table-bordered table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th style="width: 90px;">ID</th>
                                        <th>Especie</th>
                                        <th>Descripción</th>
                                        <th style="width: 120px;">Estatus</th>
                                        <th style="width: 180px;">Actualizado</th>
                                        <th style="width: 130px;" class="text-center">Acciones</th>
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
        var tabla = $('#tablaEspecies').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'ajax/especies/tabla-especies.php',
                type: 'POST',
                data: function(d) {
                    d.filtro_estatus = $('#filtro_estatus').val();
                }
            },
            paging: true,
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "Todo"]
            ],
            dom: "<'row'<'col-sm-3'l><'col-sm-2'><'col-sm-7 d-flex justify-content-end'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-3'l><'col-sm-2'><'col-sm-7 d-flex justify-content-end'p>>",
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar',
                processing: "Procesando..."
            },
            responsive: true,
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'nombre_especie',
                    name: 'nombre_especie'
                },
                {
                    data: 'descripcion',
                    name: 'descripcion',
                    defaultContent: ''
                },
                {
                    data: 'estatus',
                    name: 'estatus'
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    defaultContent: ''
                },
                {
                    data: 'acciones',
                    name: 'acciones',
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

        $('#search').on('keyup', function() {
            tabla.search($(this).val()).draw();
        });

        $('#filtro_estatus').on('change', function() {
            tabla.ajax.reload();
        });

        $('#btnNuevaEspecie').on('click', function() {
            window.location.href = 'especies-altas';
        });
    });
</script>