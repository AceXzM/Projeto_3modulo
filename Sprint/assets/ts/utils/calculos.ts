// =========================================================================
// utils/calculos.ts
// RUBRICA (Lógica avançada) [2]: "Agregações e Cálculos Financeiros
// (Uso de Reduce)" — acumuladores para as métricas globais do dashboard.
// =========================================================================

import type { Produto } from '../types/interfaces.js';

export function faturamentoTotal(produtos: Produto[]): number {
    return produtos.reduce((acumulado, produto) => acumulado + produto.faturamento_total, 0);
}

export function quantidadeTotalVendida(produtos: Produto[]): number {
    return produtos.reduce((acumulado, produto) => acumulado + produto.quantidade_vendida, 0);
}

export function faturamentoPorCategoria(produtos: Produto[]): Record<string, number> {
    return produtos.reduce<Record<string, number>>((mapa, produto) => {
        mapa[produto.categoria] = (mapa[produto.categoria] ?? 0) + produto.faturamento_total;
        return mapa;
    }, {});
}
