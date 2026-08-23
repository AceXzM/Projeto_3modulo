<?php
declare(strict_types=1);

require_once __DIR__ . '/../controllers/DashboardController.php';

// GET /api/dashboard/indicadores?busca=&categoria_id=&limite=&offset=
DashboardController::indicadores();
