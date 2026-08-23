// =========================================================================
// utils/ranking.ts
// RUBRICA (Lógica avançada) [4]: "Algoritmos de Ranking e Frequência
// (Destaques)" — descobrir valores máximos/recorrências de forma
// dinâmica, sem valores fixos no código.
// =========================================================================

import type { Produto } from '../types/interfaces.js';

export function produtoMaisVendido(produtos: Produto[]): Produto | null {
    if (produtos.length === 0) return null;

    return produtos.reduce((maisVendido, atual) =>
        atual.quantidade_vendida > maisVendido.quantidade_vendida ? atual : maisVendido
    );
}

export function topNPorFaturamento(produtos: Produto[], n: number): Produto[] {
    return [...produtos]
        .sort((a, b) => b.faturamento_total - a.faturamento_total)
        .slice(0, n);
}
