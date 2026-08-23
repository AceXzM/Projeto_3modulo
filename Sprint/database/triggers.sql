-- =====================================================================
-- triggers.sql
-- RUBRICA (Banco de Dados avançado):
--  [3] "Implementação de Triggers (BEFORE UPDATE) para padronizar a
--       inserção de valores positivos [...]"
-- =====================================================================

DELIMITER $$

-- Garante que nenhum valor_unitario ou quantidade negativa seja
-- persistido, e "trava" o valor unitário da venda no momento da
-- inserção com base no preço atual do produto.
CREATE TRIGGER trg_vendas_before_insert
BEFORE INSERT ON vendas
FOR EACH ROW
BEGIN
    IF NEW.quantidade <= 0 THEN
        SET NEW.quantidade = 1;
    END IF;

    IF NEW.valor_unitario IS NULL OR NEW.valor_unitario < 0 THEN
        SELECT valor_unitario INTO @preco_atual
        FROM produtos WHERE id = NEW.produto_id;
        SET NEW.valor_unitario = @preco_atual;
    END IF;
END $$

CREATE TRIGGER trg_produtos_before_update
BEFORE UPDATE ON produtos
FOR EACH ROW
BEGIN
    IF NEW.valor_unitario < 0 THEN
        SET NEW.valor_unitario = OLD.valor_unitario;
    END IF;
    IF NEW.estoque < 0 THEN
        SET NEW.estoque = 0;
    END IF;
END $$

DELIMITER ;
