<?php require __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Novo produto</h1>
<form id="form-produto" class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nome</label>
        <input type="text" class="form-control" name="nome" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Valor unitário</label>
        <input type="number" step="0.01" class="form-control" name="valor_unitario" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Estoque</label>
        <input type="number" class="form-control" name="estoque" required>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-success">Salvar</button>
    </div>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
