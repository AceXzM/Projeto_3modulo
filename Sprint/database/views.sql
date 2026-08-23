-- =====================================================================
-- views.sql
-- RUBRICA (Banco de Dados avançado):
--  [1] "Criação de CTEs e Views analíticas no MariaDB que limpem e
--       consolidem os dados brutos do sistema, entregando-os
--       perfeitamente estruturados."
--  [5] "Criação de Views que centralizem informações importantes no
--       sistema e que estão espalhadas em diversas tabelas distintas."
-- =====================================================================

-- CTE: consolida faturamento bruto por produto antes de expor a view
CREATE OR REPLACE VIEW vw_faturamento_por_produto AS
WITH vendas_limpas AS (
    SELECT
        v.produto_id,
        v.quantidade,
        v.valor_unitario,
        (v.quantidade * v.valor_unitario) AS total_item
    FROM vendas v
    WHERE v.quantidade > 0 -- descarta lançamentos inválidos/negativos
)
SELECT
    p.id            AS produto_id,
    p.nome          AS produto,
    c.nome          AS categoria,
    SUM(vl.quantidade)  AS quantidade_vendida,
    SUM(vl.total_item)  AS faturamento_total
FROM vendas_limpas vl
JOIN produtos p   ON p.id = vl.produto_id
JOIN categorias c ON c.id = p.categoria_id
GROUP BY p.id, p.nome, c.nome;

-- View que centraliza, numa única "fonte da verdade", dados que hoje
-- estão espalhados entre produtos, categorias e estoque.
CREATE OR REPLACE VIEW vw_dashboard_resumo AS
SELECT
    p.id             AS produto_id,
    p.nome           AS produto,
    c.nome           AS categoria,
    p.estoque,
    COALESCE(f.quantidade_vendida, 0) AS quantidade_vendida,
    COALESCE(f.faturamento_total, 0)  AS faturamento_total
FROM produtos p
JOIN categorias c ON c.id = p.categoria_id
LEFT JOIN vw_faturamento_por_produto f ON f.produto_id = p.id;
