-- =====================================================================
-- functions.sql
-- RUBRICA (Banco de Dados avançado):
--  [4] "Criação de uma função no banco de dados para recálculo de
--       scripts massivos ou complexos [...]"
-- =====================================================================

DELIMITER $$

-- Recalcula o faturamento total de um produto específico "sob demanda",
-- sem precisar reprocessar toda a tabela de vendas na aplicação.
CREATE FUNCTION fn_faturamento_produto (p_produto_id INT)
RETURNS DECIMAL(12,2)
DETERMINISTIC
READS SQL DATA      
BEGIN
    DECLARE v_total DECIMAL(12,2);

    SELECT COALESCE(SUM(quantidade * valor_unitario), 0)
    INTO v_total
    FROM vendas
    WHERE produto_id = p_produto_id;

    RETURN v_total;
END $$

DELIMITER ;

-- Uso: SELECT fn_faturamento_produto(3);
