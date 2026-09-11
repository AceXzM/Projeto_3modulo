<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();

    $resumo = $pdo->query(
        'SELECT
            COUNT(*) AS total_produtos,
            COALESCE(SUM(estoque), 0) AS estoque_total,
            COALESCE(SUM(faturamento_total), 0) AS faturamento_total,
            COALESCE(SUM(quantidade_vendida), 0) AS unidades_vendidas
         FROM vw_dashboard_resumo'
    )->fetch();

    $categorias = $pdo->query(
        'SELECT c.id, c.nome,
                COUNT(p.id) AS total_produtos,
                COALESCE(SUM(p.estoque), 0) AS estoque
         FROM categorias c
         LEFT JOIN produtos p ON p.categoria_id = c.id
         GROUP BY c.id, c.nome
         ORDER BY c.nome'
    )->fetchAll();

    jsonResponse([
        'resumo' => $resumo,
        'categorias' => $categorias
    ]);
} catch (PDOException $e) {
    errorResponse('Erro no banco de dados: ' . $e->getMessage(), 500);
}
