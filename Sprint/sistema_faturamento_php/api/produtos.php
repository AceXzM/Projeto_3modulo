<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare(
                'SELECT p.id, p.nome, p.categoria_id, c.nome AS categoria,
                        p.valor_unitario, p.estoque
                 FROM produtos p
                 JOIN categorias c ON c.id = p.categoria_id
                 WHERE p.id = ?'
            );
            $stmt->execute([$id]);
            $produto = $stmt->fetch();
            if (!$produto) errorResponse('Produto não encontrado.', 404);
            jsonResponse($produto);
        }

        $busca = trim((string)($_GET['busca'] ?? ''));
        $categoriaId = ($_GET['categoria_id'] ?? '') !== '' ? (int)$_GET['categoria_id'] : null;
        $limite = min(max((int)($_GET['limite'] ?? 10), 1), 100);
        $pagina = max((int)($_GET['pagina'] ?? 1), 1);
        $offset = ($pagina - 1) * $limite;

        // A busca/listagem do dashboard é centralizada na Stored Procedure.
        $stmt = $pdo->prepare('CALL sp_listar_produtos(?, ?, ?, ?)');
        $stmt->bindValue(1, $busca !== '' ? $busca : null, $busca !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(2, $categoriaId, $categoriaId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(3, $limite, PDO::PARAM_INT);
        $stmt->bindValue(4, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetchAll();
        while ($stmt->nextRowset()) {}

        // Total para a paginação.
        $count = $pdo->prepare(
            'SELECT COUNT(*)
             FROM produtos p
             WHERE (? IS NULL OR p.nome LIKE CONCAT("%", ?, "%"))
               AND (? IS NULL OR p.categoria_id = ?)'
        );
        $count->execute([$busca !== '' ? $busca : null, $busca !== '' ? $busca : null,
                         $categoriaId, $categoriaId]);
        $total = (int)$count->fetchColumn();

        jsonResponse([
            'dados' => $dados,
            'pagina' => $pagina,
            'limite' => $limite,
            'total' => $total,
            'paginas' => max((int)ceil($total / $limite), 1)
        ]);
    }

    if ($method === 'POST') {
        $data = input();
        $nome = trim((string)($data['nome'] ?? ''));
        $categoriaId = requirePositiveInt($data['categoria_id'] ?? null, 'categoria_id');
        $valor = (float)($data['valor_unitario'] ?? -1);
        $estoque = (int)($data['estoque'] ?? 0);

        if ($nome === '') errorResponse('Informe o nome do produto.');
        if ($valor < 0) errorResponse('O valor unitário não pode ser negativo.');
        if ($estoque < 0) errorResponse('O estoque não pode ser negativo.');

        $check = $pdo->prepare('SELECT id FROM categorias WHERE id = ?');
        $check->execute([$categoriaId]);
        if (!$check->fetch()) errorResponse('Categoria não encontrada.', 404);

        $stmt = $pdo->prepare(
            'INSERT INTO produtos (nome, categoria_id, valor_unitario, estoque)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nome, $categoriaId, $valor, $estoque]);

        jsonResponse(['mensagem' => 'Produto cadastrado.', 'id' => (int)$pdo->lastInsertId()], 201);
    }

    if ($method === 'PUT') {
        if (!$id) errorResponse('ID do produto é obrigatório.');
        $data = input();
        $nome = trim((string)($data['nome'] ?? ''));
        $categoriaId = requirePositiveInt($data['categoria_id'] ?? null, 'categoria_id');
        $valor = (float)($data['valor_unitario'] ?? -1);
        $estoque = (int)($data['estoque'] ?? -1);

        if ($nome === '') errorResponse('Informe o nome do produto.');
        if ($valor < 0) errorResponse('O valor unitário não pode ser negativo.');
        if ($estoque < 0) errorResponse('O estoque não pode ser negativo.');

        $stmt = $pdo->prepare(
            'UPDATE produtos
             SET nome = ?, categoria_id = ?, valor_unitario = ?, estoque = ?
             WHERE id = ?'
        );
        $stmt->execute([$nome, $categoriaId, $valor, $estoque, $id]);

        jsonResponse(['mensagem' => 'Produto atualizado.']);
    }

    if ($method === 'DELETE') {
        if (!$id) errorResponse('ID do produto é obrigatório.');
        try {
            $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = ?');
            $stmt->execute([$id]);
            jsonResponse(['mensagem' => 'Produto excluído.']);
        } catch (PDOException $e) {
            errorResponse('Não é possível excluir este produto porque ele possui vendas vinculadas.', 409);
        }
    }

    errorResponse('Método não permitido.', 405);
} catch (PDOException $e) {
    errorResponse('Erro no banco de dados: ' . $e->getMessage(), 500);
}
