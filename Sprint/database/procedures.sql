-- =====================================================================
-- procedures.sql
-- RUBRICA (Banco de Dados avançado):
--  [2] "Desenvolvimento de Stored Procedures otimizadas para
--       centralizar a busca, filtro e paginação dos indicadores do
--       dashboard, permitindo que a API em PHP faça chamadas limpas
--       (CALL) e assíncronas."
--
-- Chamada esperada a partir do PHP (ver src/api/models/Registro.php):
--   CALL sp_listar_produtos(:busca, :categoria_id, :limite, :offset);
-- =====================================================================

DELIMITER $$

CREATE PROCEDURE sp_listar_produtos (
    IN p_busca        VARCHAR(150),
    IN p_categoria_id INT,
    IN p_limite       INT,
    IN p_offset       INT
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
END $$

DELIMITER ;
