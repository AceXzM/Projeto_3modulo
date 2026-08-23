<?php
declare(strict_types=1);

require_once __DIR__ . '/../controllers/CrudController.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$corpo  = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($metodo) {
    case 'GET':
        CrudController::listar();
        break;

    case 'POST':
        CrudController::criar($corpo);
        break;

    case 'PUT':
        CrudController::editar((int) ($_GET['id'] ?? 0), $corpo);
        break;

    case 'DELETE':
        CrudController::excluir((int) ($_GET['id'] ?? 0));
        break;

    default:
        http_response_code(405);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Método não suportado.']);
}
