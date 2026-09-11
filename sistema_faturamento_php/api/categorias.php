<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare('SELECT id, nome FROM categorias WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) errorResponse('Categoria não encontrada.', 404);
            jsonResponse($row);
        }

        jsonResponse($pdo->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll());
    }

    if ($method === 'POST') {
        $data = input();
        $nome = trim((string)($data['nome'] ?? ''));
        if ($nome === '') errorResponse('Informe o nome da categoria.');

        $stmt = $pdo->prepare('INSERT INTO categorias (nome) VALUES (?)');
        $stmt->execute([$nome]);
        jsonResponse(['mensagem' => 'Categoria cadastrada.', 'id' => (int)$pdo->lastInsertId()], 201);
    }

    if ($method === 'PUT') {
        if (!$id) errorResponse('ID da categoria é obrigatório.');
        $data = input();
        $nome = trim((string)($data['nome'] ?? ''));
        if ($nome === '') errorResponse('Informe o nome da categoria.');

        $stmt = $pdo->prepare('UPDATE categorias SET nome = ? WHERE id = ?');
        $stmt->execute([$nome, $id]);
        jsonResponse(['mensagem' => 'Categoria atualizada.']);
    }

    if ($method === 'DELETE') {
        if (!$id) errorResponse('ID da categoria é obrigatório.');
        try {
            $stmt = $pdo->prepare('DELETE FROM categorias WHERE id = ?');
            $stmt->execute([$id]);
            jsonResponse(['mensagem' => 'Categoria excluída.']);
        } catch (PDOException $e) {
            errorResponse('Não é possível excluir uma categoria que possui produtos vinculados.', 409);
        }
    }

    errorResponse('Método não permitido.', 405);
} catch (PDOException $e) {
    errorResponse('Erro no banco de dados: ' . $e->getMessage(), 500);
}
