<?php require __DIR__ . '/../layout/header.php'; ?>

<!--
  RUBRICA: "As regras de exclusão foram feitas, dando ao usuário uma
  mensagem clara do que ocorreu?" — a confirmação e a mensagem de
  erro/sucesso (ex.: produto com vendas vinculadas) são tratadas aqui.
-->
<div class="alert" id="mensagem-exclusao" role="alert" style="display:none;"></div>
<p>Tem certeza que deseja excluir este produto?</p>
<button class="btn btn-danger" id="btn-confirmar-exclusao">Excluir</button>

<?php require __DIR__ . '/../layout/footer.php'; ?>
