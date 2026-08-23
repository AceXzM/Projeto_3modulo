-- =====================================================================
-- schema.sql
-- Estrutura base das tabelas (dados brutos) que serão limpos e
-- consolidados pelas CTEs/Views/Procedures/Triggers/Functions do
-- critério "Banco de Dados avançado".
-- =====================================================================

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
    valor_unitario DECIMAL(10,2) NOT NULL, -- preenchido pela TRIGGER (triggers.sql)
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);
