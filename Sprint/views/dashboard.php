<?php require __DIR__ . '/layout/header.php'; ?>

<!--
  RUBRICA (Desenvolvimento Web avançado): "Aparência do Sistema" +
  "Comunicação Técnico Visual do Dashboard" (Tech Forge).
  Este HTML fica "vazio" de propósito: quem preenche os cards/tabela
  é o assets/ts/main.ts, via manipulação segura do DOM.
-->
<h1 class="mb-4">Indicadores</h1>

<div class="row g-3 mb-4" id="cards-resumo">
    <!-- cards de faturamento total, produto mais vendido etc. (main.ts) -->
</div>

<div class="card">
    <div class="card-body">
        <table class="table" id="tabela-produtos">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Qtd. vendida</th>
                    <th>Faturamento</th>
                </tr>
            </thead>
            <tbody id="tabela-produtos-corpo">
                <!-- preenchido dinamicamente -->
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
