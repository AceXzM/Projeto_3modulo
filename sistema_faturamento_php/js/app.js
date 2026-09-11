const API = 'api';

let paginaAtual = 1;
const limite = 8;
let produtosCache = [];
let categoriasCache = [];

const moeda = v => Number(v || 0).toLocaleString('pt-BR', { style:'currency', currency:'BRL' });

async function api(endpoint, options = {}) {
    const response = await fetch(`${API}/${endpoint}`, {
        headers: { 'Content-Type': 'application/json' },
        ...options
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(data.erro || 'Erro na requisição.');
    return data;
}

function mostrarAlerta(msg, tipo='success') {
    document.getElementById('alerta').innerHTML =
        `<div class="alert alert-${tipo} alert-dismissible fade show">${msg}<button class="btn-close" data-bs-dismiss="alert"></button></div>`;
}

async function carregarDashboard() {
    const data = await api('dashboard.php');
    const r = data.resumo;
    document.getElementById('mProdutos').textContent = r.total_produtos;
    document.getElementById('mEstoque').textContent = r.estoque_total;
    document.getElementById('mVendas').textContent = r.unidades_vendidas;
    document.getElementById('mFaturamento').textContent = moeda(r.faturamento_total);
}

async function carregarCategorias() {
    categoriasCache = await api('categorias.php');
    const selects = [document.getElementById('produtoCategoria')];
    selects.forEach(select => {
        select.innerHTML = '<option value="">Selecione...</option>' +
            categoriasCache.map(c => `<option value="${c.id}">${escapeHtml(c.nome)}</option>`).join('');
    });

    const filtro = document.getElementById('filtroCategoria');
    filtro.innerHTML = '<option value="">Todas as categorias</option>' +
        categoriasCache.map(c => `<option value="${c.id}">${escapeHtml(c.nome)}</option>`).join('');

    document.getElementById('tabelaCategorias').innerHTML = categoriasCache.map(c => `
        <tr>
            <td>${escapeHtml(c.nome)}</td>
            <td class="text-end action-btns">
                <button class="btn btn-sm btn-outline-secondary" onclick="editarCategoria(${c.id})">Editar</button>
                <button class="btn btn-sm btn-outline-danger" onclick="excluirCategoria(${c.id})">Excluir</button>
            </td>
        </tr>`).join('');

    const vendaProduto = document.getElementById('vendaProduto');
    await carregarProdutosParaVenda(vendaProduto);
}

async function carregarProdutosParaVenda(select) {
    const data = await api('produtos.php?limite=100');
    produtosCache = data.dados;
    select.innerHTML = '<option value="">Selecione...</option>' +
        produtosCache.map(p => `<option value="${p.produto_id}">${escapeHtml(p.produto)} — estoque: ${p.estoque}</option>`).join('');
}

async function carregarProdutos() {
    const busca = document.getElementById('filtroBusca').value.trim();
    const categoria = document.getElementById('filtroCategoria').value;
    const params = new URLSearchParams({ pagina: paginaAtual, limite });
    if (busca) params.set('busca', busca);
    if (categoria) params.set('categoria_id', categoria);
    const data = await api(`produtos.php?${params}`);
    document.getElementById('tabelaProdutos').innerHTML = data.dados.map(p => `
        <tr>
            <td>${escapeHtml(p.produto)}</td>
            <td>${escapeHtml(p.categoria)}</td>
            <td>${p.estoque}</td>
            <td>${p.quantidade_vendida}</td>
            <td>${moeda(p.faturamento_total)}</td>
            <td class="text-end action-btns">
                <button class="btn btn-sm btn-outline-secondary" onclick="editarProduto(${p.produto_id})">Editar</button>
                <button class="btn btn-sm btn-outline-danger" onclick="excluirProduto(${p.produto_id})">Excluir</button>
            </td>
        </tr>`).join('');

    renderPaginacao(data.paginas, data.pagina);
}

function renderPaginacao(totalPaginas, atual) {
    let html = '';
    for (let i = 1; i <= totalPaginas; i++) {
        html += `<li class="page-item ${i === atual ? 'active' : ''}">
                    <button class="page-link" onclick="irPagina(${i})">${i}</button>
                 </li>`;
    }
    document.getElementById('paginacao').innerHTML = html;
}

function irPagina(p) { paginaAtual = p; carregarProdutos(); }

function aplicarFiltros() {
    paginaAtual = 1;
    carregarProdutos();
}

async function carregarVendas() {
    const vendas = await api('vendas.php');
    document.getElementById('tabelaVendas').innerHTML = vendas.map(v => `
        <tr>
            <td>${escapeHtml(v.produto)}</td>
            <td>${v.quantidade}</td>
            <td>${moeda(v.valor_unitario)}</td>
            <td>${moeda(v.total)}</td>
            <td>${new Date(v.data_venda.replace(' ', 'T')).toLocaleString('pt-BR')}</td>
            <td><button class="btn btn-sm btn-outline-danger" onclick="excluirVenda(${v.id})">Excluir</button></td>
        </tr>`).join('');
}

document.getElementById('produtoForm').addEventListener('submit', async e => {
    e.preventDefault();
    const id = document.getElementById('produtoId').value;
    const body = {
        nome: document.getElementById('produtoNome').value,
        categoria_id: Number(document.getElementById('produtoCategoria').value),
        valor_unitario: Number(document.getElementById('produtoValor').value),
        estoque: Number(document.getElementById('produtoEstoque').value)
    };
    try {
        await api(`produtos.php${id ? `?id=${id}` : ''}`, {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(body)
        });
        mostrarAlerta(id ? 'Produto atualizado.' : 'Produto cadastrado.');
        novoProduto();
        await carregarTudo();
    } catch(e) { mostrarAlerta(e.message, 'danger'); }
});

document.getElementById('categoriaForm').addEventListener('submit', async e => {
    e.preventDefault();
    const id = document.getElementById('categoriaId').value;
    const body = { nome: document.getElementById('categoriaNome').value };
    try {
        await api(`categorias.php${id ? `?id=${id}` : ''}`, {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(body)
        });
        mostrarAlerta(id ? 'Categoria atualizada.' : 'Categoria cadastrada.');
        novaCategoria();
        await carregarTudo();
    } catch(e) { mostrarAlerta(e.message, 'danger'); }
});

document.getElementById('vendaForm').addEventListener('submit', async e => {
    e.preventDefault();
    try {
        await api('vendas.php', {
            method: 'POST',
            body: JSON.stringify({
                produto_id: Number(document.getElementById('vendaProduto').value),
                quantidade: Number(document.getElementById('vendaQuantidade').value)
            })
        });
        mostrarAlerta('Venda registrada com sucesso.');
        novaVenda();
        await carregarTudo();
    } catch(e) { mostrarAlerta(e.message, 'danger'); }
});

async function editarProduto(id) {
    const p = await api(`produtos.php?id=${id}`);
    document.getElementById('produtoId').value = p.id;
    document.getElementById('produtoNome').value = p.nome;
    document.getElementById('produtoCategoria').value = p.categoria_id;
    document.getElementById('produtoValor').value = p.valor_unitario;
    document.getElementById('produtoEstoque').value = p.estoque;
    document.getElementById('produtos').scrollIntoView({ behavior:'smooth' });
}

async function excluirProduto(id) {
    if (!confirm('Excluir este produto?')) return;
    try {
        await api(`produtos.php?id=${id}`, { method:'DELETE' });
        mostrarAlerta('Produto excluído.');
        await carregarTudo();
    } catch(e) { mostrarAlerta(e.message, 'danger'); }
}

function novoProduto() {
    document.getElementById('produtoForm').reset();
    document.getElementById('produtoId').value = '';
}

async function editarCategoria(id) {
    const c = await api(`categorias.php?id=${id}`);
    document.getElementById('categoriaId').value = c.id;
    document.getElementById('categoriaNome').value = c.nome;
    document.getElementById('categorias').scrollIntoView({ behavior:'smooth' });
}

async function excluirCategoria(id) {
    if (!confirm('Excluir esta categoria?')) return;
    try {
        await api(`categorias.php?id=${id}`, { method:'DELETE' });
        mostrarAlerta('Categoria excluída.');
        await carregarTudo();
    } catch(e) { mostrarAlerta(e.message, 'danger'); }
}

function novaCategoria() {
    document.getElementById('categoriaForm').reset();
    document.getElementById('categoriaId').value = '';
}

function novaVenda() {
    document.getElementById('vendaForm').reset();
}

async function editarVenda(id, quantidadeAtual) {
    const novaQuantidade = prompt('Nova quantidade:', quantidadeAtual);
    if (novaQuantidade === null) return;
    const quantidade = Number(novaQuantidade);
    if (!Number.isInteger(quantidade) || quantidade <= 0) {
        mostrarAlerta('Informe uma quantidade inteira maior que zero.', 'danger');
        return;
    }

    try {
        await api(`vendas.php?id=${id}`, {
            method: 'PUT',
            body: JSON.stringify({ quantidade })
        });
        mostrarAlerta('Venda atualizada e estoque recalculado.');
        await carregarTudo();
    } catch(e) { mostrarAlerta(e.message, 'danger'); }
}

async function excluirVenda(id) {
    if (!confirm('Excluir a venda? O estoque será restaurado.')) return;
    try {
        await api(`vendas.php?id=${id}`, { method:'DELETE' });
        mostrarAlerta('Venda excluída e estoque restaurado.');
        await carregarTudo();
    } catch(e) { mostrarAlerta(e.message, 'danger'); }
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, c => ({
        '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'
    }[c]));
}

async function carregarTudo() {
    try {
        await carregarDashboard();
        await carregarCategorias();
        await carregarProdutos();
        await carregarVendas();
    } catch(e) {
        mostrarAlerta(e.message, 'danger');
    }
}

carregarTudo();
