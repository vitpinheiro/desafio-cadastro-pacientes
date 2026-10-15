<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cadastro e consulta de pacientes por unidade de saúde.">
    <title>Pacientes | Cadastro Saúde</title>
    <link rel="stylesheet" href="{{ asset('css/patients.css') }}">
    <script src="{{ asset('js/patients.js') }}" defer></script>
</head>
<body data-api-url="{{ url('/api') }}">
    <div class="layout">
        <aside class="sidebar" aria-label="Menu principal">
            <a class="brand" href="{{ url('/') }}">
                <span class="brand-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z" />
                        <path d="M3 12h4l2-4 4 8 2-4h6" />
                    </svg>
                </span>
                CADASTRO SAÚDE
            </a>
            <p class="menu-label">ATENDIMENTO</p>
            <nav><a class="nav-link active" href="{{ url('/') }}" aria-current="page"><span aria-hidden="true">▦</span> Pacientes</a></nav>
            <p class="sidebar-note">Cadastro e consulta<br>por unidade de saúde</p>
        </aside>

        <main>
            <header class="page-header">
                <div><p class="eyebrow">ATENDIMENTO / PACIENTES</p><h1>Pacientes</h1><p class="subtitle">Gerencie os pacientes vinculados às unidades de saúde.</p></div>
                <span class="header-tag">Gestão de pacientes</span>
            </header>

            <div id="mensagem" class="message" role="status" aria-live="polite" hidden></div>

            <section class="panel form-panel" aria-labelledby="titulo-formulario">
                <div class="section-heading"><h2 id="titulo-formulario">Cadastro de paciente</h2><span class="required-note"><span>*</span> Campos obrigatórios</span></div>
                <form id="formulario-paciente" novalidate>
                    <div class="form-grid">
                        <div class="field field-name"><label for="nome">Nome completo <span>*</span></label><input id="nome" name="name" maxlength="255" autocomplete="name" placeholder="Informe o nome do paciente" required aria-describedby="erro-name"><small class="field-error" id="erro-name"></small></div>
                        <div class="field"><label for="cpf">CPF <span>*</span></label><input id="cpf" name="cpf" inputmode="numeric" maxlength="14" placeholder="000.000.000-00" required aria-describedby="erro-cpf"><small class="field-error" id="erro-cpf"></small></div>
                        <div class="field"><label for="nascimento">Data de nascimento <span>*</span></label><input type="date" id="nascimento" name="birth_date" required aria-describedby="erro-birth_date"><small class="field-error" id="erro-birth_date"></small></div>
                        <div class="field"><label for="telefone">Telefone <span class="optional">(opcional)</span></label><input type="tel" id="telefone" name="phone" autocomplete="tel" maxlength="20" placeholder="(11) 99999-9999" aria-describedby="erro-phone"><small class="field-error" id="erro-phone"></small></div>
                        <div class="field field-unit"><label for="unidade">Unidade de saúde <span>*</span></label><select id="unidade" name="health_unit_id" required disabled aria-describedby="erro-health_unit_id"><option value="">Carregando unidades...</option></select><small class="field-error" id="erro-health_unit_id"></small></div>
                        <div class="form-actions"><button type="button" id="cancelar-edicao" class="button secondary" hidden>Cancelar edição</button><button type="submit" id="salvar" class="button primary" disabled>Salvar paciente</button></div>
                    </div>
                </form>
            </section>

            <section class="panel list-panel" aria-labelledby="titulo-lista">
                <div class="list-toolbar">
                    <div><h2 id="titulo-lista">Pacientes cadastrados</h2><p>Consulte e mantenha os cadastros atualizados.</p></div>
                    <form id="formulario-busca" class="search-form" role="search"><label for="busca" class="sr-only">Buscar paciente pelo nome</label><input type="search" id="busca" maxlength="255" placeholder="Buscar pelo nome..."><button class="button search-button" type="submit">Buscar</button><button class="clear-search" type="button" id="limpar-busca" hidden>Limpar</button></form>
                </div>
                <div class="table-frame">
                    <div class="table-scroll"><table><caption class="sr-only">Pacientes e suas unidades de saúde</caption><thead><tr><th scope="col">Nome do paciente</th><th scope="col">CPF</th><th scope="col">Unidade de saúde</th><th scope="col" class="actions-column">Ações</th></tr></thead><tbody id="lista-pacientes"><tr><td colspan="4" class="empty-state">Carregando pacientes...</td></tr></tbody></table></div>
                    <footer class="table-footer"><span id="total-pacientes" aria-live="polite">Carregando registros...</span><button type="button" id="tentar-novamente" class="button secondary" hidden>Tentar novamente</button><span class="footer-note">Cadastro Saúde</span></footer>
                </div>
            </section>
            <noscript><p class="message error">Ative o JavaScript do navegador para cadastrar e consultar pacientes.</p></noscript>
        </main>
    </div>

    <dialog id="confirmacao-exclusao" aria-labelledby="titulo-exclusao" aria-describedby="descricao-exclusao">
        <h2 id="titulo-exclusao">Excluir paciente?</h2><p id="descricao-exclusao"></p><p class="dialog-note">Esta ação não pode ser desfeita.</p>
        <div class="dialog-actions"><button type="button" class="button secondary" id="cancelar-exclusao" autofocus>Cancelar</button><button type="button" class="button danger" id="confirmar-exclusao">Excluir paciente</button></div>
    </dialog>
</body>
</html>
