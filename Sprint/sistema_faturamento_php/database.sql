-- Execute este arquivo depois de criar/selecionar seu banco.
-- Exemplo:
-- CREATE DATABASE faturamento CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE faturamento;

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    categoria_id INT NOT NULL,
    valor_unitario DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE IF NOT EXISTS vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    data_venda DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    valor_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

CREATE OR REPLACE VIEW vw_faturamento_por_produto AS
SELECT
    p.id AS produto_id,
    p.nome AS produto,
    c.nome AS categoria,
    SUM(v.quantidade) AS quantidade_vendida,
    SUM(v.quantidade * v.valor_unitario) AS faturamento_total
FROM vendas v
JOIN produtos p ON p.id = v.produto_id
JOIN categorias c ON c.id = p.categoria_id
WHERE v.quantidade > 0
GROUP BY p.id, p.nome, c.nome;

CREATE OR REPLACE VIEW vw_dashboard_resumo AS
SELECT
    p.id AS produto_id,
    p.nome AS produto,
    p.categoria_id AS categoria_id,
    c.nome AS categoria,
    p.estoque,
    COALESCE(f.quantidade_vendida, 0) AS quantidade_vendida,
    COALESCE(f.faturamento_total, 0) AS faturamento_total
FROM produtos p
JOIN categorias c ON c.id = p.categoria_id
LEFT JOIN vw_faturamento_por_produto f ON f.produto_id = p.id;

DELIMITER $$

DROP PROCEDURE IF EXISTS sp_listar_produtos$$
CREATE PROCEDURE sp_listar_produtos (
    IN p_busca VARCHAR(150),
    IN p_categoria_id INT,
    IN p_limite INT,
    IN p_offset INT
)
BEGIN
    SELECT
        produto_id, produto, categoria, estoque,
        quantidade_vendida, faturamento_total
    FROM vw_dashboard_resumo
    WHERE (p_busca IS NULL OR produto LIKE CONCAT('%', p_busca, '%'))
      AND (p_categoria_id IS NULL OR categoria_id = p_categoria_id)
    ORDER BY faturamento_total DESC
    LIMIT p_limite OFFSET p_offset;
END$$

DROP TRIGGER IF EXISTS trg_vendas_before_insert$$
CREATE TRIGGER trg_vendas_before_insert
BEFORE INSERT ON vendas
FOR EACH ROW
BEGIN
    DECLARE v_preco_atual DECIMAL(10,2);

    IF NEW.quantidade <= 0 THEN
        SET NEW.quantidade = 1;
    END IF;

    IF NEW.valor_unitario IS NULL OR NEW.valor_unitario < 0 THEN
        SELECT valor_unitario INTO v_preco_atual
        FROM produtos WHERE id = NEW.produto_id;

        IF v_preco_atual IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Produto informado não existe.';
        END IF;

        SET NEW.valor_unitario = v_preco_atual;
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_produtos_before_update$$
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
END$$

DROP FUNCTION IF EXISTS fn_faturamento_produto$$
CREATE FUNCTION fn_faturamento_produto (p_produto_id INT)
RETURNS DECIMAL(12,2)
NOT DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_total DECIMAL(12,2);

    SELECT COALESCE(SUM(quantidade * valor_unitario), 0)
    INTO v_total
    FROM vendas
    WHERE produto_id = p_produto_id;

    RETURN v_total;
END$$

DELIMITER ;

-- Teste:
-- SELECT fn_faturamento_produto(3);
