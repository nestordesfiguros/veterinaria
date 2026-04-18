<?php
/* Archivo: contenido/razas.php */

$listaEspecies = $clsConsulta->consultaGeneral("SELECT id, nombre_especie FROM cat_especies WHERE estatus = 'activo' ORDER BY nombre_especie ASC");
$totalEspecies = $clsConsulta->numrows;
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
            <li class="active breadcrumb-item" aria-current="page">Razas</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-solid fa-dna"></i> &nbsp; Catálogo de Razas</h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <style>
                            .razas-toolbar .form-control,
                            .razas-toolbar .form-select,
                            .razas-toolbar .btn,
                            .razas-filtros-secundarios .form-select {
                                min-height: 42px;
                            }

                            .razas-toolbar .input-group-text {
                                min-height: 42px;
                            }

                            .razas-buscador {
                                min-width: 260px;
                            }

                            #tablaRazas thead th {
                                white-space: nowrap;
                                vertical-align: middle;
                            }

                            #tablaRazas tbody td {
                                vertical-align: middle;
                            }

                            @media (max-width: 991.98px) {
                                .razas-buscador {
                                    min-width: 100%;
                                }
                            }
                        </style>

                        <div class="row razas-toolbar g-3 align-items-end mb-3">
                            <div class="col-12 col-lg-4 text-start">
                                <button type="button" class="btn btn-primary" id="btnNuevaRaza">
                                    <i class="fa-solid fa-plus"></i> Nueva Raza
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
                                <div class="input-group razas-buscador">
                                    <span class="input-group-text">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                    </span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="search"
                                        name="search"
                                        placeholder="Buscar raza..."
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="row razas-filtros-secundarios g-3 mb-3">
                            <div class="col-12 col-lg-4">
                                <label for="filtro_especie" class="form-label mb-1">Filtrar por especie</label>
                                <select class="form-select" id="filtro_especie" name="filtro_especie">
                                    <option value="">Todas las especies</option>
                                    <?php
                                    if ($totalEspecies > 0) {
                                        for ($i = 1; $i <= $totalEspecies; $i++) {
                                    ?>
                                            <option value="<?= (int)$listaEspecies[$i]['id']; ?>">
                                                <?= htmlspecialchars($listaEspecies[$i]['nombre_especie'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaRazas" class="table table-bordered table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Especie</th>
                                        <th>Raza</th>
                                        <th>Descripción</th>
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
        $('#tablaRazas').dataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'ajax/razas/tabla-razas.php',
                type: 'POST',
                data: function(d) {
                    d.filtro_estatus = $('#filtro_estatus').val();
                    d.filtro_especie = $('#filtro_especie').val();
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
                    data: 'especie'
                },
                {
                    data: 'nombre_raza'
                },
                {
                    data: 'descripcion'
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

        var tabla = $('#tablaRazas').DataTable();

        $('#search').keyup(function() {
            tabla.search($(this).val()).draw();
        });

        $('#filtro_estatus, #filtro_especie').on('change', function() {
            tabla.ajax.reload();
        });

        $('#btnNuevaRaza').on('click', function() {
            window.location.href = 'razas-altas';
        });
    });
</script>