'use strict';

const api = document.body.dataset.apiUrl;
const formulario = document.querySelector('#formulario-paciente');
const lista = document.querySelector('#lista-pacientes');
const mensagem = document.querySelector('#mensagem');
const salvar = document.querySelector('#salvar');
const unidade = document.querySelector('#unidade');
const busca = document.querySelector('#busca');
const cancelarEdicao = document.querySelector('#cancelar-edicao');
const dialogo = document.querySelector('#confirmacao-exclusao');
const confirmarExclusao = document.querySelector('#confirmar-exclusao');
const tentarNovamente = document.querySelector('#tentar-novamente');
let pacienteEmEdicao = null;
let pacienteParaExcluir = null;
let pacientes = [];
let carregamentoAtual = 0;
let enviando = false;

async function requisicao(caminho, opcoes = {}) {
    const resposta = await fetch(`${api}${caminho}`, {
        ...opcoes,
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', ...opcoes.headers },
    });
    if (resposta.status === 204) return null;
    const dados = await resposta.json();
    if (!resposta.ok) {
        const erro = new Error(dados.message || 'Não foi possível concluir a solicitação.');
        erro.campos = dados.errors;
        erro.status = resposta.status;
        throw erro;
    }
    return dados;
}

function avisar(texto, erro = false) {
    mensagem.textContent = texto;
    mensagem.className = erro ? 'message error' : 'message';
    mensagem.hidden = false;
}

function limparErros() {
    formulario.querySelectorAll('.field-error').forEach(campo => { campo.textContent = ''; });
    formulario.querySelectorAll('[aria-invalid]').forEach(campo => campo.removeAttribute('aria-invalid'));
}

function mostrarErro(erro) {
    if (erro.campos) {
        for (const [nome, erros] of Object.entries(erro.campos)) {
            const campo = formulario.elements.namedItem(nome);
            const aviso = document.getElementById(`erro-${nome}`);
            if (aviso && campo) {
                aviso.textContent = erros.join(' ');
                campo.setAttribute('aria-invalid', 'true');
            }
        }
        avisar(Object.values(erro.campos).flat().join(' '), true);
        formulario.querySelector('[aria-invalid="true"]')?.focus();
    } else {
        avisar(erro.status === 404 ? 'Este paciente não foi encontrado. Atualize a lista e tente novamente.' : 'Não foi possível concluir a solicitação. Verifique sua conexão e tente novamente.', true);
    }
}

function formatarCpf(valor) {
    return valor.replace(/\D/g, '').slice(0, 11)
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1-$2');
}

function estadoTabela(texto) {
    const linha = document.createElement('tr');
    const celula = document.createElement('td');
    celula.colSpan = 4;
    celula.className = 'empty-state';
    const icone = document.createElement('span');
    icone.className = 'empty-icon';
    icone.textContent = '▤';
    icone.setAttribute('aria-hidden', 'true');
    celula.append(icone, document.createTextNode(texto));
    linha.append(celula);
    lista.replaceChildren(linha);
}

function renderizarPacientes() {
    lista.replaceChildren();
    document.querySelector('#total-pacientes').textContent = `${pacientes.length} ${pacientes.length === 1 ? 'paciente encontrado' : 'pacientes encontrados'}`;
    if (!pacientes.length) {
        estadoTabela(busca.value.trim() ? 'Nenhum paciente encontrado para esta busca.' : 'Nenhum paciente cadastrado. Utilize o formulário acima para começar.');
        return;
    }

    for (const paciente of pacientes) {
        const linha = document.createElement('tr');
        const nome = document.createElement('td');
        nome.className = 'patient-name';
        nome.textContent = paciente.name;
        const cpf = document.createElement('td');
        cpf.className = 'cpf-cell';
        cpf.textContent = formatarCpf(paciente.cpf);
        const unidadeCelula = document.createElement('td');
        const etiqueta = document.createElement('span');
        etiqueta.className = 'unit-badge';
        etiqueta.textContent = paciente.health_unit.name;
        unidadeCelula.append(etiqueta);
        const acoes = document.createElement('td');
        const botoes = document.createElement('div');
        botoes.className = 'row-actions';
        for (const [acao, rotulo] of [['editar', 'Editar'], ['excluir', 'Excluir']]) {
            const botao = document.createElement('button');
            botao.type = 'button';
            botao.className = acao === 'excluir' ? 'row-button delete' : 'row-button';
            botao.textContent = rotulo;
            botao.setAttribute('aria-label', `${rotulo} paciente ${paciente.name}`);
            botao.addEventListener('click', () => {
                if (enviando) return;
                if (acao === 'editar') editarPaciente(paciente);
                else solicitarExclusao(paciente);
            });
            botoes.append(botao);
        }
        acoes.append(botoes);
        linha.append(nome, cpf, unidadeCelula, acoes);
        lista.append(linha);
    }
}

async function carregarPacientes() {
    const identificador = ++carregamentoAtual;
    tentarNovamente.hidden = true;
    lista.setAttribute('aria-busy', 'true');
    estadoTabela('Carregando pacientes...');
    document.querySelector('#total-pacientes').textContent = 'Carregando registros...';
    try {
        const dados = await requisicao(`/patients?search=${encodeURIComponent(busca.value.trim())}`);
        if (identificador !== carregamentoAtual) return;
        pacientes = dados;
        renderizarPacientes();
    } catch (erro) {
        if (identificador !== carregamentoAtual) return;
        estadoTabela('Não foi possível carregar os pacientes. Tente novamente.');
        document.querySelector('#total-pacientes').textContent = 'Falha ao carregar';
        tentarNovamente.hidden = false;
        mostrarErro(erro);
    } finally {
        if (identificador === carregamentoAtual) lista.removeAttribute('aria-busy');
    }
}

async function carregarUnidades() {
    try {
        const unidades = await requisicao('/health-units');
        unidade.replaceChildren(new Option('Selecione a unidade', ''));
        unidades.forEach(item => unidade.add(new Option(`${item.name} — ${item.city}`, item.id)));
        unidade.disabled = false;
        salvar.disabled = unidades.length === 0;
        if (!unidades.length) avisar('Nenhuma unidade de saúde disponível para cadastro.', true);
    } catch (erro) {
        unidade.replaceChildren(new Option('Não foi possível carregar as unidades', ''));
        avisar('Não foi possível carregar as unidades de saúde. Atualize a página para tentar novamente.', true);
    }
}

function reiniciarFormulario() {
    formulario.reset();
    pacienteEmEdicao = null;
    limparErros();
    document.querySelector('#titulo-formulario').textContent = 'Cadastro de paciente';
    salvar.textContent = 'Salvar paciente';
    cancelarEdicao.hidden = true;
}

function editarPaciente(paciente) {
    if (unidade.disabled || unidade.options.length < 2) {
        avisar('Aguarde o carregamento das unidades de saúde. Se o problema continuar, atualize a página.', true);
        return;
    }
    limparErros();
    mensagem.hidden = true;
    pacienteEmEdicao = paciente.id;
    formulario.elements.name.value = paciente.name;
    formulario.elements.cpf.value = formatarCpf(paciente.cpf);
    formulario.elements.birth_date.value = paciente.birth_date;
    formulario.elements.phone.value = paciente.phone || '';
    unidade.value = paciente.health_unit_id;
    document.querySelector('#titulo-formulario').textContent = 'Editar paciente';
    salvar.textContent = 'Salvar alterações';
    cancelarEdicao.hidden = false;
    formulario.scrollIntoView({ behavior: 'smooth', block: 'center' });
    formulario.elements.name.focus({ preventScroll: true });
}

formulario.addEventListener('submit', async evento => {
    evento.preventDefault();
    if (enviando || salvar.disabled) return;
    limparErros();
    mensagem.hidden = true;
    enviando = true;
    salvar.disabled = true;
    cancelarEdicao.disabled = true;
    salvar.textContent = 'Salvando...';
    const editando = pacienteEmEdicao !== null;
    const dados = {
        name: formulario.elements.name.value.trim(),
        cpf: formulario.elements.cpf.value.replace(/\D/g, ''),
        birth_date: formulario.elements.birth_date.value,
        phone: formulario.elements.phone.value.replace(/\D/g, '') || null,
        health_unit_id: unidade.value,
    };
    try {
        await requisicao(editando ? `/patients/${pacienteEmEdicao}` : '/patients', {
            method: editando ? 'PUT' : 'POST', body: JSON.stringify(dados),
        });
        reiniciarFormulario();
        avisar(editando ? 'Paciente atualizado com sucesso.' : 'Paciente cadastrado com sucesso.');
        await carregarPacientes();
    } catch (erro) {
        mostrarErro(erro);
    } finally {
        enviando = false;
        salvar.disabled = false;
        cancelarEdicao.disabled = false;
        salvar.textContent = pacienteEmEdicao === null ? 'Salvar paciente' : 'Salvar alterações';
    }
});

function solicitarExclusao(paciente) {
    pacienteParaExcluir = paciente;
    document.querySelector('#descricao-exclusao').textContent = `Deseja excluir o cadastro de ${paciente.name}?`;
    dialogo.showModal();
}

document.querySelector('#cancelar-exclusao').addEventListener('click', () => dialogo.close());
dialogo.addEventListener('cancel', evento => { if (enviando) evento.preventDefault(); });
dialogo.addEventListener('close', () => { pacienteParaExcluir = null; });
confirmarExclusao.addEventListener('click', async () => {
    if (!pacienteParaExcluir || enviando) return;
    enviando = true;
    confirmarExclusao.disabled = true;
    document.querySelector('#cancelar-exclusao').disabled = true;
    confirmarExclusao.textContent = 'Excluindo...';
    try {
        const id = pacienteParaExcluir.id;
        await requisicao(`/patients/${id}`, { method: 'DELETE' });
        if (pacienteEmEdicao === id) reiniciarFormulario();
        dialogo.close();
        avisar('Paciente excluído com sucesso.');
        await carregarPacientes();
    } catch (erro) {
        dialogo.close();
        mostrarErro(erro);
    } finally {
        enviando = false;
        confirmarExclusao.disabled = false;
        document.querySelector('#cancelar-exclusao').disabled = false;
        confirmarExclusao.textContent = 'Excluir paciente';
    }
});

cancelarEdicao.addEventListener('click', () => { reiniciarFormulario(); mensagem.hidden = true; });
document.querySelector('#formulario-busca').addEventListener('submit', evento => {
    evento.preventDefault();
    document.querySelector('#limpar-busca').hidden = !busca.value;
    carregarPacientes();
});
document.querySelector('#limpar-busca').addEventListener('click', () => {
    busca.value = '';
    document.querySelector('#limpar-busca').hidden = true;
    carregarPacientes();
});
busca.addEventListener('input', () => { document.querySelector('#limpar-busca').hidden = !busca.value; });
tentarNovamente.addEventListener('click', carregarPacientes);
formulario.elements.cpf.addEventListener('input', evento => { evento.target.value = formatarCpf(evento.target.value); });
const hoje = new Date();
formulario.elements.birth_date.max = `${hoje.getFullYear()}-${String(hoje.getMonth() + 1).padStart(2, '0')}-${String(hoje.getDate()).padStart(2, '0')}`;
carregarUnidades();
carregarPacientes();
