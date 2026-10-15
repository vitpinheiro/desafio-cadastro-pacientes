# Cadastro de pacientes

Feito por **Maria Vitória Rodrigues da Silva Pinheiro** para o **Desafio programador(a) PHP Júnior**.

## Visão geral

O desafio consiste em desenvolver uma aplicação web simples para cadastrar e consultar pacientes vinculados a unidades de saúde. O objetivo é verificar a compreensão dos fundamentos das tecnologias, a capacidade de implementar funcionalidades e a organização do trabalho.

A entrega deve priorizar funcionamento e clareza. Não é necessário criar uma interface sofisticada ou adotar arquiteturas complexas.

## Tecnologias utilizadas

Aplicação para cadastrar e consultar pacientes vinculados a unidades de saúde.
Desenvolvida com Laravel 10, MySQL/MariaDB e HTML, CSS e JavaScript puro.
O frontend se comunica com a API por meio de `fetch`.

## Pré-requisitos

- PHP 8.1 ou superior compatível com Laravel 10, com as extensões exigidas pelo Laravel e `pdo_mysql`.
- Composer 2.
- MySQL ou MariaDB em execução.
- Git para clonar o projeto.

Não é necessário Docker, Node.js ou executar um build do frontend. Os arquivos CSS e JavaScript são servidos diretamente pelo Laravel.

## Instalação

No terminal, clone o repositório e entre na pasta da aplicação:

```powershell
git clone https://github.com/vitpinheiro/desafio-cadastro-pacientes.git
cd desafio-cadastro-pacientes/cadastro-pacientes
composer install
```

Crie o arquivo de configuração local. No PowerShell:

```powershell
Copy-Item .env.example .env
```

No Linux ou macOS, use `cp .env.example .env`.
Se já existe um `.env` configurado, mantenha-o.

## Banco de dados

Crie um banco vazio pelo cliente MySQL, HeidiSQL ou outra ferramenta de sua preferência:

```sql
CREATE DATABASE cadastro_pacientes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Edite o `.env` com os dados do seu ambiente:

```dotenv
APP_NAME="Cadastro Saúde"
APP_URL=http://127.0.0.1:8015

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cadastro_pacientes
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

Os valores de usuário e senha acima são exemplos; substitua pelas credenciais locais.
Depois, ainda na pasta `cadastro-pacientes`, execute:

```powershell
php artisan key:generate
php artisan config:clear
php artisan migrate --seed
```

As migrations criam as tabelas e suas restrições. O `HealthUnitSeeder` cadastra três unidades de saúde em Natal. Ao executar novamente, procura cada unidade pelo nome e atualiza sua cidade, sem duplicar unidades com o mesmo nome. Alterar o nome no arquivo cria uma nova unidade; renomear uma unidade existente exige atualizar seu registro no banco:

```powershell
php artisan db:seed --class=HealthUnitSeeder
```

## Abrir a aplicação

Na pasta `cadastro-pacientes`, execute:

```powershell
php artisan serve --port=8015
```

Acesse **http://127.0.0.1:8015** e mantenha o terminal aberto.
A página inicial contém o formulário, a pesquisa e a lista de pacientes.
Para encerrar o servidor, pressione `Ctrl+C`.

## Funcionalidades

- Cadastro de paciente com nome, CPF, data de nascimento, telefone opcional e unidade de saúde.
- Listagem com nome, CPF e unidade, além de busca parcial pelo nome.
- Edição e exclusão com confirmação na interface.
- Carregamento das unidades de saúde pela API.
- Máscara visual de CPF, enviado à API somente com números.
- Mensagens de sucesso e erros de validação em português.
- CPF obrigatório com 11 dígitos numéricos e único no banco; os dígitos verificadores não são validados, conforme o enunciado.
- Rejeição de nascimento no futuro e de unidade inexistente.
- Relacionamento entre `Patient` e `HealthUnit`, UUID único nas unidades e chave estrangeira nos pacientes.

Os itens obrigatórios estão implementados. A listagem não possui paginação e o telefone não possui máscara visual, ambos diferenciais opcionais.
A conferência visual em navegador deve ser feita localmente; a verificação automatizada cobre a API.

## API

Envie `Accept: application/json` e, nas requisições com corpo, `Content-Type: application/json`.

| Método | Rota | Operação |
| --- | --- | --- |
| GET | `/api/patients` | Listar pacientes com a unidade vinculada |
| GET | `/api/patients?search=Maria` | Buscar por parte do nome |
| POST | `/api/patients` | Cadastrar paciente |
| GET | `/api/patients/{id}` | Consultar paciente |
| PUT | `/api/patients/{id}` | Atualizar os dados do paciente |
| PATCH | `/api/patients/{id}` | Atualizar somente os campos enviados |
| DELETE | `/api/patients/{id}` | Excluir paciente |
| GET | `/api/health-units` | Listar unidades de saúde |

Exemplo de corpo para cadastro ou atualização completa:

```json
{
  "name": "Maria da Silva",
  "cpf": "01234567890",
  "birth_date": "1990-05-20",
  "phone": "84999999999",
  "health_unit_id": 1
}
```

Use um `health_unit_id` retornado pela listagem de unidades. O telefone pode ser omitido ou enviado como `null`.
Listagens retornam arrays JSON; cadastro, consulta e edição retornam o objeto do paciente com `health_unit`.
O cadastro retorna `201`, consultas e edições retornam `200`, exclusão retorna `204` sem corpo, registro inexistente retorna `404` e dados inválidos retornam `422`, com erros em `errors` agrupados pelo campo.

## Consultas SQL

O arquivo [sql/consultas.sql](sql/consultas.sql), na raiz do repositório, contém:

1. Pacientes com o nome da unidade, usando `INNER JOIN`.
2. Quantidade de pacientes por unidade, incluindo unidades vazias, usando `LEFT JOIN`, `COUNT` e `GROUP BY`.
3. Busca parcial pelo nome, usando `LIKE` e a variável `@termo`.

Abra o arquivo em um cliente MySQL/MariaDB, selecione o banco configurado no `.env` e execute as instruções em ordem. A busca usa `Maria` como exemplo; altere o valor de `@termo` para pesquisar outro nome.

## Testes

Os testes da API verificam cadastro, consulta, edição, exclusão, busca parcial, CPF duplicado, campos inválidos, listagem de unidades e respostas `404`.

Para executá-los, prefira um banco separado, por exemplo `cadastro_pacientes_test`. Crie-o no MySQL e copie o `.env` para `.env.testing`, alterando `APP_ENV=testing` e `DB_DATABASE=cadastro_pacientes_test` nesse arquivo. Não use o banco de produção.

Na pasta da aplicação:

```powershell
php artisan config:clear
php artisan migrate --env=testing
php artisan test
```

Os testes de pacientes usam transações para desfazer os registros criados durante cada teste. Sem `.env.testing`, o Laravel pode usar o banco configurado no `.env`.

## Organização

```text
README.md
sql/consultas.sql
cadastro-pacientes/
  app/Http/Controllers/   Controllers da API
  app/Http/Requests/      Validações dos pacientes
  app/Models/            Patient e HealthUnit
  database/migrations/   Estrutura do banco
  database/seeders/      Unidades de saúde iniciais
  public/css/            Estilos da interface
  public/js/             Integração com a API
  resources/views/       Página de pacientes
  routes/                Rotas web e API
  tests/Feature/         Testes da aplicação
```

O `.gitignore` exclui configurações locais, credenciais, `vendor` e `node_modules` do versionamento.
