<?php

namespace Tests\Feature;

use App\Models\ContaReceber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContaReceberTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_calcula_os_quatro_indicadores(): void
    {
        $this->conta([
            'cliente' => 'Cliente Recebido',
            'valor' => 100.00,
            'data_vencimento' => today()->subDays(2),
            'data_pagamento' => today(),
            'status' => 'pago',
        ]);

        $this->conta([
            'cliente' => 'Cliente Vencido',
            'valor' => 50.00,
            'data_vencimento' => today()->subDay(),
        ]);

        $this->conta([
            'cliente' => 'Cliente A Vencer',
            'valor' => 30.00,
            'data_vencimento' => today()->addDays(5),
        ]);

        $this->conta([
            'cliente' => 'Cliente Cancelado',
            'valor' => 999.00,
            'data_vencimento' => today()->addDays(3),
            'status' => 'cancelado',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertViewHas('totalRecebido', 100.0)
            ->assertViewHas('totalAReceber', 80.0)
            ->assertViewHas('totalVencido', 50.0)
            ->assertViewHas('totalAVencer', 30.0);
    }

    public function test_filtro_vencido_retorna_apenas_pendentes_em_atraso(): void
    {
        $this->conta(['cliente' => 'Cliente Vencido', 'data_vencimento' => today()->subDay()]);
        $this->conta(['cliente' => 'Cliente Futuro', 'data_vencimento' => today()->addDays(5)]);
        $this->conta([
            'cliente' => 'Cliente Recebido',
            'data_vencimento' => today()->subDay(),
            'data_pagamento' => today(),
            'status' => 'pago',
        ]);

        $this->get(route('contas-receber.index', ['status' => 'vencido']))
            ->assertOk()
            ->assertSee('Cliente Vencido')
            ->assertDontSee('Cliente Futuro')
            ->assertDontSee('Cliente Recebido');
    }

    public function test_registrar_recebimento_marca_conta_como_recebida(): void
    {
        $conta = $this->conta(['cliente' => 'Receber Teste']);

        $this->from(route('contas-receber.index'))
            ->post(route('contas-receber.receber', $conta), [
                'data_pagamento' => '2026-11-07',
            ])
            ->assertRedirect(route('contas-receber.index'))
            ->assertSessionHas('success', 'Recebimento registrado com sucesso.');

        $conta->refresh();

        $this->assertSame('pago', $conta->status);
        $this->assertSame('2026-11-07', $conta->data_pagamento->format('Y-m-d'));
    }

    public function test_conta_cancelada_nao_pode_ser_recebida(): void
    {
        $conta = $this->conta(['status' => 'cancelado']);

        $this->from(route('contas-receber.index'))
            ->post(route('contas-receber.receber', $conta))
            ->assertRedirect(route('contas-receber.index'))
            ->assertSessionHas('warning');

        $this->assertSame('cancelado', $conta->fresh()->status);
    }

    public function test_crud_de_conta_a_receber(): void
    {
        $this->post(route('contas-receber.store'), [
            'cliente' => 'Cliente CRUD',
            'descricao' => 'Cadastro',
            'valor' => 250.00,
            'data_vencimento' => today()->addDays(10)->toDateString(),
            'status' => 'pendente',
        ])->assertRedirect(route('contas-receber.index'));

        $conta = ContaReceber::where('cliente', 'Cliente CRUD')->firstOrFail();

        $this->put(route('contas-receber.update', $conta), [
            'cliente' => 'Cliente CRUD Editado',
            'descricao' => 'Alterado',
            'valor' => 300.00,
            'data_vencimento' => today()->addDays(12)->toDateString(),
            'status' => 'pendente',
        ])->assertRedirect(route('contas-receber.index'));

        $this->assertDatabaseHas('contas_receber', [
            'id' => $conta->id,
            'cliente' => 'Cliente CRUD Editado',
            'valor' => 300.00,
        ]);

        $this->delete(route('contas-receber.destroy', $conta))
            ->assertRedirect(route('contas-receber.index'));

        $this->assertDatabaseMissing('contas_receber', ['id' => $conta->id]);
    }

    public function test_cadastro_via_ajax_retorna_json_e_normaliza_valor(): void
    {
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->post(route('contas-receber.store'), [
            'cliente' => 'Cliente AJAX Teste',
            'valor' => '1.234,56',
            'data_vencimento' => today()->addDays(10)->toDateString(),
            'status' => 'pendente',
            '_filtros' => ['status' => 'pendente', 'busca' => 'AJAX'],
            '_page' => '1',
        ]);

        $response->assertOk()->assertJson([
            'type' => 'success',
        ]);

        $this->assertStringContainsString('busca=AJAX', $response->json('url'));
        $this->assertStringContainsString('status=pendente', $response->json('url'));

        $this->assertStringContainsString('Cliente AJAX Teste', $response->json('html'));
        $this->assertStringContainsString('1.234,56', $response->json('html'));

        $this->assertDatabaseHas('contas_receber', [
            'cliente' => 'Cliente AJAX Teste',
            'valor' => 1234.56,
        ]);
    }

    public function test_validacao_via_ajax_retorna_422_json(): void
    {
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->post(route('contas-receber.store'), [
            'valor' => '10,00',
            'status' => 'pendente',
            'data_vencimento' => today()->addDays(10)->toDateString(),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['cliente']);
    }

    private function conta(array $atributos = []): ContaReceber
    {
        return ContaReceber::create(array_merge([
            'cliente' => 'Cliente Teste',
            'descricao' => 'Conta de teste',
            'valor' => 100.00,
            'data_vencimento' => today()->addDays(5)->toDateString(),
            'status' => 'pendente',
        ], $atributos));
    }
}
