<?php

namespace Tests\Feature;

use App\Models\HealthUnit;
use Database\Seeders\HealthUnitSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HealthUnitSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_atualiza_cidade_sem_duplicar_unidade_ou_alterar_seus_identificadores(): void
    {
        $this->seed(HealthUnitSeeder::class);
        $unidade = HealthUnit::where('name', 'UBS Centro')->firstOrFail();
        $total = HealthUnit::count();
        $uuid = $unidade->uuid;
        $unidade->update(['city' => 'Cidade anterior']);

        $this->seed(HealthUnitSeeder::class);

        $this->assertSame($total, HealthUnit::count());
        $this->assertSame('Natal RN', $unidade->fresh()->city);
        $this->assertSame($uuid, $unidade->fresh()->uuid);
        $this->assertSame(1, HealthUnit::where('name', 'UBS Centro')->count());
    }
}
