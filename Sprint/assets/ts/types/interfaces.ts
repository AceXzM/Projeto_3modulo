// =========================================================================
// types/interfaces.ts
// RUBRICA (Lógica avançada) [1]: "Modelagem de Dados e Contratos de
// Interface (TypeScript)". Tipagem estrita, sem uso de `any`, mapeando
// 100% do JSON enviado pelo PHP.
// =========================================================================

export interface Produto {
    produto_id: number;
    produto: string;
    categoria: string;
    estoque: number;
    quantidade_vendida: number;
    faturamento_total: number;
}

export interface RespostaApi<T> {
    sucesso: boolean;
    dados?: T;
    mensagem?: string;
    erro?: string;
}

export interface FiltroDashboard {
    busca?: string;
    categoriaId?: number;
    limite?: number;
    offset?: number;
}
