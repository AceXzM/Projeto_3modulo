<?php
// =========================================================================
// controllers/DashboardController.php
// Monta o JSON que o front-end (TypeScript) vai consumir via fetch().
// Ver: assets/ts/services/api.ts e a rubrica "Tech Forge" (Consumo de
// API e Resolução de Fluxo Assíncrono).
// =========================================================================

declare(strict_types=1);

require_once __DIR__ . '/../models/Registro.php';

class DashboardController
{
    public static function indicadores(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $busca       = $_GET['busca'] ?? null;
            $categoriaId = isset($_GET['categoria_id']) ? (int) $_GET['categoria_id'] : null;
            $limite      = isset($_GET['limite']) ? (int) $_GET['limite'] : 20;
            $offset      = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

            $dados = Registro::listarProdutos($busca, $categoriaId, $limite, $offset);

            // Sempre retorna um array, mesmo vazio — o front-end (TS)
            // trata a ausência de dados em vez de quebrar (Edge Cases).
            echo json_encode([
                'sucesso' => true,
                'dados'   => $dados,
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'sucesso' => false,
                'erro'    => 'Não foi possível carregar os indicadores.',
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
