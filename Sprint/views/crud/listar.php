<?php require __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Produtos</h1>
<a href="/produtos/novo" class="btn btn-primary mb-3">Novo produto</a>

<table class="table" id="tabela-crud">
    <thead>
        <tr><th>Nome</th><th>Categoria</th><th>Valor</th><th>Estoque</th><th></th></tr>
    </thead>
    <tbody id="tabela-crud-corpo">
        <!-- preenchido via fetch() em assets/ts/main.ts -->
    </tbody>
</table>

<?php require __DIR__ . '/../layout/footer.php'; ?>
