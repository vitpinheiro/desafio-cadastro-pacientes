<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ], [
            'search.string' => 'A busca deve ser um texto.',
            'search.max' => 'A busca deve ter no máximo 255 caracteres.',
        ]);

        $busca = trim($dados['search'] ?? '');
        $pacientes = Patient::with('healthUnit')
            ->when($busca !== '', fn ($consulta) => $consulta->where('name', 'like', '%'.$busca.'%'))
            ->orderBy('name')
            ->get();

        return response()->json($pacientes);
    }

    public function store(PatientRequest $request): JsonResponse
    {
        $paciente = Patient::create($request->validated());

        return response()->json($paciente->load('healthUnit'), 201);
    }

    public function show(Patient $patient): JsonResponse
    {
        return response()->json($patient->load('healthUnit'));
    }

    public function update(PatientRequest $request, Patient $patient): JsonResponse
    {
        $patient->update($request->validated());

        return response()->json($patient->load('healthUnit'));
    }

    public function destroy(Patient $patient): Response
    {
        $patient->delete();

        return response()->noContent();
    }
}
