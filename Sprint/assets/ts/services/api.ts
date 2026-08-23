// =========================================================================
// services/api.ts
// RUBRICA (Tech Forge): "Consumo de API e Resolução de Fluxo Assíncrono"
// — fetch + async/await, com try/catch para não quebrar a aplicação
// durante a apresentação.
// =========================================================================

import type { Produto, RespostaApi, FiltroDashboard } from '../types/interfaces.js';

const BASE_URL = '/api';

export async function buscarIndicadores(filtro: FiltroDashboard = {}): Promise<Produto[]> {
    const params = new URLSearchParams();
    if (filtro.busca) params.set('busca', filtro.busca);
    if (filtro.categoriaId !== undefined) params.set('categoria_id', String(filtro.categoriaId));
    params.set('limite', String(filtro.limite ?? 20));
    params.set('offset', String(filtro.offset ?? 0));

    try {
        const resposta = await fetch(`${BASE_URL}/index.php?rota=dashboard&${params.toString()}`);

        if (!resposta.ok) {
            throw new Error(`Falha na requisição: ${resposta.status}`);
        }

        const corpo: RespostaApi<Produto[]> = await resposta.json();

        if (!corpo.sucesso || !corpo.dados) {
            // Edge case: backend respondeu mas sem dados úteis.
            return [];
        }

        return corpo.dados;
    } catch (erro) {
        console.error('Não foi possível carregar os indicadores:', erro);
        return []; // evita que a tela quebre durante a apresentação
    }
}

export async function excluirProduto(id: number): Promise<RespostaApi<null>> {
    try {
        const resposta = await fetch(`${BASE_URL}/index.php?rota=crud&id=${id}`, {
            method: 'DELETE',
        });
        return (await resposta.json()) as RespostaApi<null>;
    } catch {
        return { sucesso: false, mensagem: 'Erro de conexão ao excluir o produto.' };
    }
}
