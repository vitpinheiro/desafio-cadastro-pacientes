<?php

namespace Tests\Feature;

use App\Models\HealthUnit;
use App\Models\Patient;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PatientApiTest extends TestCase
{
    use DatabaseTransactions;

    private function dadosPaciente(array $dados = []): array
    {
        $unidade = HealthUnit::create(['name' => 'Unidade de teste', 'city' => 'São Paulo']);

        return array_merge([
            'name' => 'Maria Teste',
            'cpf' => '01234567890',
            'birth_date' => '1990-05-20',
            'health_unit_id' => $unidade->id,
        ], $dados);
    }

    public function test_cadastra_consulta_edita_e_exclui_paciente(): void
    {
        $dados = $this->dadosPaciente();
        $resposta = $this->postJson('/api/patients', $dados)
            ->assertCreated()
            ->assertJsonPath('cpf', '01234567890')
            ->assertJsonPath('birth_date', '1990-05-20')
            ->assertJsonPath('health_unit.name', 'Unidade de teste');

        $id = $resposta->json('id');
        $this->getJson('/api/patients/'.$id)->assertOk()->assertJsonPath('name', 'Maria Teste');
        $this->putJson('/api/patients/'.$id, array_merge($dados, ['name' => 'Maria Silva']))
            ->assertOk()->assertJsonPath('name', 'Maria Silva');
        $this->patchJson('/api/patients/'.$id, ['phone' => '11999999999'])
            ->assertOk()->assertJsonPath('phone', '11999999999');
        $this->deleteJson('/api/patients/'.$id)->assertNoContent();
        $this->getJson('/api/patients/'.$id)->assertNotFound();
    }

    public function test_rejeita_cpf_duplicado_no_cadastro_e_na_edicao(): void
    {
        $dados = $this->dadosPaciente();
        Patient::create($dados);
        $this->postJson('/api/patients', $dados)->assertUnprocessable()->assertJsonValidationErrors('cpf');

        $outro = Patient::create(array_merge($dados, ['cpf' => '98765432100']));
        $this->patchJson('/api/patients/'.$outro->id, ['cpf' => $dados['cpf']])
            ->assertUnprocessable()->assertJsonValidationErrors('cpf');
    }

    public function test_valida_campos_obrigatorios_e_dados_invalidos(): void
    {
        $this->postJson('/api/patients', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'cpf', 'birth_date', 'health_unit_id']);

        $dados = $this->dadosPaciente([
            'cpf' => '123.456.789-00',
            'birth_date' => now()->addDay()->toDateString(),
            'health_unit_id' => 0,
        ]);
        $this->postJson('/api/patients', $dados)->assertUnprocessable()
            ->assertJsonValidationErrors(['cpf', 'birth_date', 'health_unit_id']);
        $dados['cpf'] = 'abcdefghijk';
        $this->postJson('/api/patients', $dados)->assertUnprocessable()->assertJsonValidationErrors('cpf');
    }

    public function test_busca_parcial_e_listagem_de_unidades(): void
    {
        $dados = $this->dadosPaciente(['name' => 'Paciente BuscaUnicaSilva']);
        $paciente = Patient::create($dados);
        $this->getJson('/api/patients?search=BuscaUnica')->assertOk()
            ->assertJsonCount(1)->assertJsonPath('0.id', $paciente->id)
            ->assertJsonPath('0.health_unit.name', 'Unidade de teste');
        $this->getJson('/api/health-units')->assertOk()->assertJsonFragment(['id' => $dados['health_unit_id']]);
    }

    public function test_registro_inexistente_retorna_404_em_json_sem_cabecalho_accept(): void
    {
        $this->get('/api/patients/0')->assertNotFound()->assertHeader('Content-Type', 'application/json');
        $this->putJson('/api/patients/0', [])->assertNotFound();
        $this->deleteJson('/api/patients/0')->assertNotFound();
    }
}
