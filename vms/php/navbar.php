<?php
// admin/php/navbar.php

// Este archivo debe ser incluido desde index.php, que ya contiene session_start()
// Asegúrate de que $nav esté definido en index.php como: $nav = $cat ?? '';

?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow mb-3">
  <div class="container">
    <a class="navbar-brand" href="inicio">
      <img src="img/logo-inicio.png" alt="Volta" class="img-fluid" style="max-width: 150px">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-toggle="collapse" data-bs-target="#navbarNavAltMarkup" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">

      <div class="navbar-nav me-auto gap-2 mt-2 mt-lg-0">
        <?php
        $archivoActual = $nav ?? '';
        $idModuloActivo = null;
        $idModuloPadreActivo = null;

        // Detectar módulo activo (por archivo)
        foreach ($_SESSION['modulos'] as $id => $mod) {
          if ($mod['archivo'] === $archivoActual) {
            $idModuloActivo = $id;
            break;
          }
        }

        // Si el módulo tiene padre, resaltamos ese
        if ($idModuloActivo !== null) {
          $idModuloPadreActivo = $_SESSION['modulos'][$idModuloActivo]['modulo_padre'] ?? $idModuloActivo;
        }

        foreach ($_SESSION['modulos'] as $idModulo => $datos) {
          if (!isset($_SESSION['permisos'][$idModulo]['ver']) || $_SESSION['permisos'][$idModulo]['ver'] != 1) continue;

          // Solo mostrar módulos principales
          if (!empty($datos['modulo_padre'])) continue;

          // Asignación de la clase active para el diseño corporativo
          $activo = ($idModulo == $idModuloPadreActivo) ? 'active' : '';

          $iconoHTML = '';
          if (!empty($datos['icono'])) {
            $iconoHTML = '<i class="' . $datos['icono'] . '"></i>';
          }

          echo '
            <div class="text-center">
              <a class="btn nav-btn-corporate btn-square-sm ' . $activo . '" aria-current="page" href="' . $datos['archivo'] . '">
                  ' . $iconoHTML . '
                  <p>' . $datos['nombre'] . '</p>
              </a>
            </div>';
        }
        ?>

        <div class="text-center">
          <a class="btn nav-btn-corporate btn-square-sm" aria-current="page" href="salir">
            <i class="fa-solid fa-right-from-bracket"></i>
            <p>SALIR</p>
          </a>
        </div>
      </div>

      <div class="d-none d-lg-flex align-items-center mt-3 mt-lg-0">
        <div class="nav-welcome-text text-end">
          <span>Bienvenido: <b class="nav-username"><?php echo $_SESSION['nombre']; ?></b></span>
        </div>
      </div>

    </div>
  </div>
</nav>

<div class="d-block d-lg-none text-center mb-3">
  <div class="nav-welcome-text">
    <span>Bienvenido: <b class="nav-username"><?php echo $_SESSION['nombre']; ?></b></span>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var togglerBtn = document.querySelector('.navbar-toggler');
    var collapseMenu = document.querySelector('#navbarNavAltMarkup');

    if (togglerBtn && collapseMenu) {
      togglerBtn.addEventListener('click', function(e) {
        // Se da un margen de 50ms para ver si Bootstrap resolvió el evento de forma nativa.
        // Si no lo hizo (por conflictos de versión), forzamos la apertura añadiendo la clase 'show'.
        setTimeout(function() {
          if (!collapseMenu.classList.contains('show') && !collapseMenu.classList.contains('collapsing')) {
            collapseMenu.classList.toggle('show');
          } else if (collapseMenu.classList.contains('show') && !collapseMenu.classList.contains('collapsing') && togglerBtn.getAttribute('aria-expanded') === 'true') {
            // Ya está abierto, no hacemos nada extra para no interferir.
          }
        }, 50);
      });
    }
  });
</script>