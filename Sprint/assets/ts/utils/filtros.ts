// =========================================================================
// utils/filtros.ts
// RUBRICA (Lógica avançada) [3]: "Segmentação e Filtros de Negócio
// (Uso de Filter)" — isolar subconjuntos de dados para criar
// inteligência de negócio.
// =========================================================================

import type { Produto } from '../types/interfaces.js';

export function filtrarPorCategoria(produtos: Produto[], categoria: string): Produto[] {
    return produtos.filter((produto) => produto.categoria === categoria);
}

export function produtosComEstoqueCritico(produtos: Produto[], limiteMinimo = 5): Produto[] {
    return produtos.filter((produto) => produto.estoque <= limiteMinimo);
}

export function filtrarPorPeriodoDeVendas(
    produtos: Produto[],
    quantidadeMinima: number
): Produto[] {
    return produtos.filter((produto) => produto.quantidade_vendida >= quantidadeMinima);
}
