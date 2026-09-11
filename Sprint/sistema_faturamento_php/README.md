# Sistema de Faturamento

Projeto PHP + HTML + CSS + JavaScript + Bootstrap + MariaDB.

## Estrutura

- `index.html` — frontend
- `css/style.css` — estilos próprios
- `js/app.js` — comunicação com a API e regras da interface
- `api/config.php` — conexão PDO
- `api/produtos.php` — CRUD de produtos + chamada da stored procedure
- `api/categorias.php` — CRUD de categorias
- `api/vendas.php` — CRUD de vendas e atualização de estoque
- `api/dashboard.php` — indicadores
- `database.sql` — tabelas, views, procedure, triggers e function

## Instalação no XAMPP

1. Crie o banco no MariaDB.
2. Edite `api/config.php` com banco, usuário e senha.
3. Execute `database.sql`.
4. Coloque a pasta em `C:\xampp\htdocs\sistema_faturamento`.
5. Inicie Apache e MySQL/MariaDB no XAMPP.
6. Acesse:
   `http://localhost/sistema_faturamento/`

## Requisitos da rubrica atendidos

- Interface amigável e responsiva.
- Bootstrap com navbar, cards, tabelas, formulários, botões, alertas e paginação.
- 3 CRUDs completos: categorias, produtos e vendas (inclusão, consulta, edição e exclusão).
- Arquivos separados por responsabilidade.
- Stored Procedure para listagem, filtro e paginação, usada pela API.
- Views para centralização dos indicadores.
- Triggers para regras de integridade.
- Function para cálculo de faturamento.
- Uso de PDO e prepared statements.
- Validação de dados no frontend e backend.
