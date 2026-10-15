<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $obrigatorio = $this->isMethod('patch') ? 'sometimes' : 'required';

        return [
            'name' => [$obrigatorio, 'required', 'string', 'max:255'],
            'cpf' => [$obrigatorio, 'required', 'string', 'regex:/^[0-9]{11}$/', Rule::unique('patients', 'cpf')->ignore($this->route('patient'))],
            'birth_date' => [$obrigatorio, 'required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'health_unit_id' => [$obrigatorio, 'required', 'integer', 'exists:health_units,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto.',
            'max' => 'O campo :attribute deve ter no máximo :max caracteres.',
            'cpf.regex' => 'O CPF deve conter exatamente 11 dígitos numéricos, sem pontuação.',
            'cpf.unique' => 'Já existe um paciente cadastrado com este CPF.',
            'birth_date.date_format' => 'Informe a data de nascimento no formato AAAA-MM-DD.',
            'birth_date.before_or_equal' => 'A data de nascimento não pode ser posterior à data atual.',
            'health_unit_id.integer' => 'Selecione uma unidade de saúde válida.',
            'health_unit_id.exists' => 'A unidade de saúde informada não existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'cpf' => 'CPF',
            'birth_date' => 'data de nascimento',
            'phone' => 'telefone',
            'health_unit_id' => 'unidade de saúde',
        ];
    }
}
