// =========================================================================
// utils/formatadores.ts
// RUBRICA (Lógica avançada) [5]: "Transformação e Formatação de
// Estruturas (Uso de Map)" — transformar a estrutura vinda da API para
// o formato exigido pela interface (moeda local, arrays para gráficos).
// =========================================================================

import type { Produto } from '../types/interfaces.js';

export function formatarMoedaBRL(valor: number): string {
    return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

export function produtosParaExibicao(produtos: Produto[]) {
    return produtos.map((produto) => ({
        ...produto,
        faturamento_formatado: formatarMoedaBRL(produto.faturamento_total),
    }));
}

// Estrutura "limpa" pronta para alimentar uma biblioteca de gráficos.
export function paraDadosDeGrafico(produtos: Produto[]) {
    return produtos.map((produto) => ({
        label: produto.produto,
        valor: produto.faturamento_total,
    }));
}
