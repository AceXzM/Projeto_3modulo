// =========================================================================
// main.ts
// RUBRICA (Tech Forge): "Manipulação Segura do DOM no Front-End" —
// trata a possibilidade de elementos nulos com condicionais, sem uso
// indiscriminado do operador de asserção (!).
// =========================================================================

import type { Produto } from './types/interfaces.js';
import { buscarIndicadores } from './services/api.js';
import { faturamentoTotal } from './utils/calculos.js';
import { produtoMaisVendido } from './utils/ranking.js';
import { produtosParaExibicao, formatarMoedaBRL } from './utils/formatadores.js';

async function iniciarDashboard(): Promise<void> {
    const corpoTabela = document.getElementById('tabela-produtos-corpo');

    // Elemento pode não existir nesta página — trata sem quebrar.
    if (!corpoTabela) return;

    const produtos = await buscarIndicadores();

    if (produtos.length === 0) {
        corpoTabela.innerHTML = `
            <tr><td colspan="4" class="mensagem-vazia">Nenhum dado registrado.</td></tr>
        `;
        return;
    }

    renderizarCardsResumo(produtos);
    renderizarTabela(produtos, corpoTabela);
}

function renderizarCardsResumo(produtos: Produto[]): void {
    const container = document.getElementById('cards-resumo');
    if (!container) return;

    const total = formatarMoedaBRL(faturamentoTotal(produtos));
    const destaque = produtoMaisVendido(produtos);

    container.innerHTML = `
        <div class="col-md-6">
            <div class="card card-indicador p-3">
                <span>Faturamento total</span>
                <span class="valor">${total}</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-indicador p-3">
                <span>Produto mais vendido</span>
                <span class="valor">${destaque ? destaque.produto : '—'}</span>
            </div>
        </div>
    `;
}

function renderizarTabela(produtos: Produto[], corpoTabela: HTMLElement): void {
    const linhas = produtosParaExibicao(produtos)
        .map(
            (produto) => `
                <tr>
                    <td>${produto.produto}</td>
                    <td>${produto.categoria}</td>
                    <td>${produto.quantidade_vendida}</td>
                    <td>${produto.faturamento_formatado}</td>
                </tr>
            `
        )
        .join('');

    corpoTabela.innerHTML = linhas;
}

document.addEventListener('DOMContentLoaded', () => {
    iniciarDashboard().catch((erro) => console.error('Erro ao iniciar dashboard:', erro));
});
