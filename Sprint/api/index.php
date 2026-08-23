<?php
// =========================================================================
// api/index.php
// Front controller simples: direciona /api/{recurso} para o arquivo de
// rota correspondente. Mantém a estrutura do projeto separada por
// responsabilidade (Desenvolvimento Web avançado: "estrutura do projeto
// bem definida, com arquivos separados para melhorar a manutenção").
// =========================================================================

declare(strict_types=1);

$rota = $_GET['rota'] ?? '';

$mapa = [
    'dashboard' => __DIR__ . '/routes/dashboard.php',
    'crud'      => __DIR__ . '/routes/crud.php',
];

if (isset($mapa[$rota])) {
    require $mapa[$rota];
    return;
}

http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['sucesso' => false, 'mensagem' => 'Rota não encontrada.']);
