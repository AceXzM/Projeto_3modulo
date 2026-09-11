<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($method === 'GET') {
        $sql = 'SELECT v.id, v.produto_id, p.nome AS produto,
                       v.quantidade, v.data_venda, v.valor_unitario,
                       (v.quantidade * v.valor_unitario) AS total
                FROM vendas v
                JOIN produtos p ON p.id = v.produto_id
                ORDER BY v.data_venda DESC, v.id DESC';

        $params = [];
        if ($id) {
            $sql = str_replace('ORDER BY', 'WHERE v.id = ? ORDER BY', $sql);
            $params[] = $id;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll();
        if ($id && !$dados) errorResponse('Venda não encontrada.', 404);
        jsonResponse($id ? $dados[0] : $dados);
    }

    if ($method === 'POST') {
        $data = input();
        $produtoId = requirePositiveInt($data['produto_id'] ?? null, 'produto_id');
        $quantidade = requirePositiveInt($data['quantidade'] ?? null, 'quantidade');

        $produto = $pdo->prepare('SELECT id, valor_unitario, estoque FROM produtos WHERE id = ?');
        $produto->execute([$produtoId]);
        $produto = $produto->fetch();

        if (!$produto) errorResponse('Produto não encontrado.', 404);
        if ((int)$produto['estoque'] < $quantidade) errorResponse('Estoque insuficiente.');

        $pdo->beginTransaction();
        try {
            // O preço é gravado na venda para preservar o histórico.
            $stmt = $pdo->prepare(
                'INSERT INTO vendas (produto_id, quantidade, valor_unitario)
                 VALUES (?, ?, ?)'
            );
            $stmt->execute([$produtoId, $quantidade, $produto['valor_unitario']]);

            $update = $pdo->prepare('UPDATE produtos SET estoque = estoque - ? WHERE id = ?');
            $update->execute([$quantidade, $produtoId]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        jsonResponse(['mensagem' => 'Venda registrada.', 'id' => (int)$pdo->lastInsertId()], 201);
    }

    if ($method === 'PUT') {
        if (!$id) errorResponse('ID da venda é obrigatório.');

        $data = input();
        $quantidadeNova = requirePositiveInt($data['quantidade'] ?? null, 'quantidade');

        $pdo->beginTransaction();
        try {
            $find = $pdo->prepare(
                'SELECT v.produto_id, v.quantidade, p.estoque, p.valor_unitario
                 FROM vendas v
                 JOIN produtos p ON p.id = v.produto_id
                 WHERE v.id = ? FOR UPDATE'
            );
            $find->execute([$id]);
            $venda = $find->fetch();

            if (!$venda) {
                $pdo->rollBack();
                errorResponse('Venda não encontrada.', 404);
            }

            // Ao editar, devolvemos a quantidade antiga ao estoque e
            // verificamos se há estoque suficiente para a nova quantidade.
            $estoqueDisponivel = (int)$venda['estoque'] + (int)$venda['quantidade'];
            if ($estoqueDisponivel < $quantidadeNova) {
                $pdo->rollBack();
                errorResponse('Estoque insuficiente para a nova quantidade.');
            }

            $updateVenda = $pdo->prepare(
                'UPDATE vendas SET quantidade = ?, valor_unitario = ? WHERE id = ?'
            );
            $updateVenda->execute([
                $quantidadeNova,
                $venda['valor_unitario'],
                $id
            ]);

            $updateEstoque = $pdo->prepare(
                'UPDATE produtos SET estoque = ? WHERE id = ?'
            );
            $updateEstoque->execute([
                $estoqueDisponivel - $quantidadeNova,
                $venda['produto_id']
            ]);

            $pdo->commit();
            jsonResponse(['mensagem' => 'Venda atualizada.']);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    if ($method === 'DELETE') {
        if (!$id) errorResponse('ID da venda é obrigatório.');

        $pdo->beginTransaction();
        try {
            $find = $pdo->prepare('SELECT produto_id, quantidade FROM vendas WHERE id = ? FOR UPDATE');
            $find->execute([$id]);
            $venda = $find->fetch();
            if (!$venda) {
                $pdo->rollBack();
                errorResponse('Venda não encontrada.', 404);
            }

            $restore = $pdo->prepare('UPDATE produtos SET estoque = estoque + ? WHERE id = ?');
            $restore->execute([(int)$venda['quantidade'], (int)$venda['produto_id']]);

            $delete = $pdo->prepare('DELETE FROM vendas WHERE id = ?');
            $delete->execute([$id]);

            $pdo->commit();
            jsonResponse(['mensagem' => 'Venda excluída e estoque restaurado.']);
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    errorResponse('Método não permitido.', 405);
} catch (PDOException $e) {
    errorResponse('Erro no banco de dados: ' . $e->getMessage(), 500);
}
