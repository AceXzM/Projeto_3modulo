<?php
// =========================================================================
// controllers/CrudController.php
// RUBRICA (Desenvolvimento Web avançado):
// "3 CRUDs completos" + "Regras de exclusão feitas, com mensagem
// clara ao usuário do que ocorreu."
// =========================================================================

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class CrudController
{
    public static function listar(): void
    {
        $pdo = getConnection();
        $stmt = $pdo->query('SELECT * FROM produtos ORDER BY nome');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => true, 'dados' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
    }

    public static function criar(array $dados): void
    {
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO produtos (nome, categoria_id, valor_unitario, estoque)
             VALUES (:nome, :categoria_id, :valor_unitario, :estoque)'
        );
        $stmt->execute([
            'nome'           => $dados['nome'],
            'categoria_id'   => $dados['categoria_id'],
            'valor_unitario' => $dados['valor_unitario'],
            'estoque'        => $dados['estoque'] ?? 0,
        ]);

        self::responder(true, 'Produto cadastrado com sucesso.');
    }

    public static function editar(int $id, array $dados): void
    {
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            'UPDATE produtos SET nome = :nome, categoria_id = :categoria_id,
             valor_unitario = :valor_unitario, estoque = :estoque WHERE id = :id'
        );
        $stmt->execute([
            'nome'           => $dados['nome'],
            'categoria_id'   => $dados['categoria_id'],
            'valor_unitario' => $dados['valor_unitario'],
            'estoque'        => $dados['estoque'],
            'id'             => $id,
        ]);

        self::responder(true, 'Produto atualizado com sucesso.');
    }

    public static function excluir(int $id): void
    {
        $pdo = getConnection();

        // Regra de exclusão: impede remover produto que já tem vendas
        // vinculadas e explica claramente o motivo ao usuário.
        $check = $pdo->prepare('SELECT COUNT(*) FROM vendas WHERE produto_id = :id');
        $check->execute(['id' => $id]);

        if ((int) $check->fetchColumn() > 0) {
            self::responder(false, 'Este produto possui vendas registradas e não pode ser excluído.');
            return;
        }

        $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = :id');
        $stmt->execute(['id' => $id]);

        self::responder(true, 'Produto excluído com sucesso.');
    }

    private static function responder(bool $sucesso, string $mensagem): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => $sucesso, 'mensagem' => $mensagem], JSON_UNESCAPED_UNICODE);
    }
}
