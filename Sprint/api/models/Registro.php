<?php
// =========================================================================
// models/Registro.php
// RUBRICA (Banco de Dados avançado) [2]: a API em PHP deve fazer
// chamadas "limpas" (CALL) às Stored Procedures — nunca montar SQL
// solto dentro do controller.
// =========================================================================

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class Registro
{
    public static function listarProdutos(
        ?string $busca,
        ?int $categoriaId,
        int $limite = 20,
        int $offset = 0
    ): array {
        $pdo = getConnection();

        $stmt = $pdo->prepare(
            'CALL sp_listar_produtos(:busca, :categoria_id, :limite, :offset)'
        );

        $stmt->bindValue(':busca', $busca, PDO::PARAM_STR);
        $stmt->bindValue(':categoria_id', $categoriaId, $categoriaId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function faturamentoProduto(int $produtoId): float
    {
        $pdo = getConnection();
        $stmt = $pdo->prepare('SELECT fn_faturamento_produto(:id) AS total');
        $stmt->execute(['id' => $produtoId]);
        return (float) $stmt->fetchColumn();
    }
}
