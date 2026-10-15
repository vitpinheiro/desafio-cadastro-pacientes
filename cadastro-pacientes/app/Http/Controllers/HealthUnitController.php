<?php

namespace App\Http\Controllers;

use App\Models\HealthUnit;
use Illuminate\Http\JsonResponse;

class HealthUnitController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(HealthUnit::orderBy('name')->get());
    }
}
