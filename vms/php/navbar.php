<?php
// admin/php/navbar.php
// Este archivo debe ser incluido desde index.php, que ya contiene session_start()

$archivoActual = $nav ?? '';
$modulosSesion = $_SESSION['modulos'] ?? [];
$permisosSesion = $_SESSION['permisos'] ?? [];
$configuracionModulosMapaLocal = $configuracionModulosMapa ?? [];
$configuracionModulosPorArchivoLocal = $configuracionModulosPorArchivo ?? [];

$idModuloActivo = null;
$idModuloPadreActivo = null;

if (function_exists('vmsResolverModuloIdPorArchivo')) {
  $idModuloActivo = vmsResolverModuloIdPorArchivo((string)$archivoActual, $modulosSesion, $configuracionModulosPorArchivoLocal);
} else {
  foreach ($modulosSesion as $id => $mod) {
    if (($mod['archivo'] ?? '') === $archivoActual) {
      $idModuloActivo = (int)$id;
      break;
    }
  }
}

if ($idModuloActivo !== null && isset($modulosSesion[$idModuloActivo])) {
  $idModuloPadreActivo = !empty($modulosSesion[$idModuloActivo]['modulo_padre'])
    ? (int)$modulosSesion[$idModuloActivo]['modulo_padre']
    : (int)$idModuloActivo;
}

if (
  $idModuloPadreActivo !== null &&
  function_exists('vmsModuloVisibleEnMenuPorInstalacion') &&
  !vmsModuloVisibleEnMenuPorInstalacion($idModuloPadreActivo, $configuracionModulosMapaLocal, $modulosSesion)
) {
  $idModuloPadreActivo = null;
}
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
        foreach ($modulosSesion as $idModulo => $datos) {
          $idModulo = (int)$idModulo;

          if (!isset($permisosSesion[$idModulo]['ver']) || (int)$permisosSesion[$idModulo]['ver'] !== 1) {
            continue;
          }

          // Solo mostrar módulos principales
          if (!empty($datos['modulo_padre'])) {
            continue;
          }

          if (
            function_exists('vmsModuloVisibleEnMenuPorInstalacion') &&
            !vmsModuloVisibleEnMenuPorInstalacion($idModulo, $configuracionModulosMapaLocal, $modulosSesion)
          ) {
            continue;
          }

          $activo = ($idModuloPadreActivo !== null && $idModulo === (int)$idModuloPadreActivo) ? 'active' : '';

          $iconoHTML = '';
          if (!empty($datos['icono'])) {
            $iconoHTML = '<i class="' . htmlspecialchars((string)$datos['icono'], ENT_QUOTES, 'UTF-8') . '"></i>';
          }

          echo '
            <div class="text-center">
              <a class="btn nav-btn-corporate btn-square-sm ' . $activo . '" aria-current="page" href="' . htmlspecialchars((string)($datos['archivo'] ?? '#'), ENT_QUOTES, 'UTF-8') . '">
                  ' . $iconoHTML . '
                  <p>' . htmlspecialchars((string)($datos['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') . '</p>
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
          <span>Bienvenido: <b class="nav-username"><?php echo htmlspecialchars((string)($_SESSION['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></b></span>
        </div>
      </div>

    </div>
  </div>
</nav>

<div class="d-block d-lg-none text-center mb-3">
  <div class="nav-welcome-text">
    <span>Bienvenido: <b class="nav-username"><?php echo htmlspecialchars((string)($_SESSION['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></b></span>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var togglerBtn = document.querySelector('.navbar-toggler');
    var collapseMenu = document.querySelector('#navbarNavAltMarkup');

    if (togglerBtn && collapseMenu) {
      togglerBtn.addEventListener('click', function() {
        setTimeout(function() {
          if (!collapseMenu.classList.contains('show') && !collapseMenu.classList.contains('collapsing')) {
            collapseMenu.classList.toggle('show');
          }
        }, 50);
      });
    }
  });
</script>