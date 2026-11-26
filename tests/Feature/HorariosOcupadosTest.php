<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Services\AgendamentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CriaCenarioAgendamento;
use Tests\TestCase;

/**
 * Task 2.1 — horários ocupados na página pública e janela de reserva.
 */
class HorariosOcupadosTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAgendamento;

    private array $cenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criarRoles();
        $this->cenario = $this->criarProfissionalComServico();
    }

    private function agendar(string $status, $inicio): Agendamento
    {
        $cliente = $this->criarCliente(['nome_completo' => 'Maria Sigilosa'], '11988887777');

        return Agendamento::create([
            'cliente_id'       => $cliente->id,
            'profissional_id'  => $this->cenario['profissional']->id,
            'data_hora_inicio' => $inicio,
            'data_hora_fim'    => $inicio->copy()->addHour(),
            'status'           => $status,
            'valor_total'      => 150,
        ]);
    }

    public function test_pagina_publica_envia_apenas_intervalos_ocupados_sem_dados_do_cliente(): void
    {
        $amanha = now()->addDay()->setTime(14, 0);

        $this->agendar('confirmado', $amanha);
        $this->agendar('cancelado_cliente', $amanha->copy()->setTime(16, 0));     // não ocupa
        $this->agendar('concluido', now()->subDays(2)->setTime(10, 0));          // passado
        $this->agendar('confirmado', now()->addDays(AgendamentoService::JANELA_RESERVA_DIAS + 5)->setTime(10, 0)); // fora da janela

        $resposta = $this->get($this->cenario['estabelecimento']->url_publica);

        $resposta->assertInertia(fn (Assert $page) => $page
            ->component('EstabelecimentoDetail')
            ->where('janelaReservaDias', AgendamentoService::JANELA_RESERVA_DIAS)
            ->has('estabelecimento.profissionais.0.agendamentos_ocupados', 1, fn (Assert $ocupado) => $ocupado
                ->where('data_hora_inicio', $amanha->toJSON())
                ->has('data_hora_fim')
                ->has('profissional_id')
                ->missing('cliente_id')
                ->missing('id')
            )
        );

        $conteudo = $resposta->getContent();
        $this->assertStringNotContainsString('Maria Sigilosa', $conteudo);
        $this->assertStringNotContainsString('11988887777', $conteudo);
        $this->assertStringNotContainsString('@cliente.test', $conteudo);
    }

    public function test_cliente_nao_reserva_alem_da_janela(): void
    {
        $data = now()->addDays(AgendamentoService::JANELA_RESERVA_DIAS + 1)->format('Y-m-d') . ' 10:00:00';

        $this->actingAs($this->criarCliente())->post('/agendamentos', [
            'servico_profissional_id' => $this->cenario['servicoProfissional']->id,
            'data_hora_inicio'        => $data,
        ])->assertSessionHasErrors('data_hora_inicio');

        $this->assertDatabaseCount('agendamentos', 0);
    }

    public function test_cliente_reserva_no_ultimo_dia_da_janela(): void
    {
        $data = now()->addDays(AgendamentoService::JANELA_RESERVA_DIAS)->format('Y-m-d') . ' 10:00:00';

        $this->actingAs($this->criarCliente())->post('/agendamentos', [
            'servico_profissional_id' => $this->cenario['servicoProfissional']->id,
            'data_hora_inicio'        => $data,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('agendamentos', 1);
    }

    public function test_profissional_agenda_manualmente_alem_da_janela(): void
    {
        $data = now()->addDays(AgendamentoService::JANELA_RESERVA_DIAS + 10)->format('Y-m-d') . ' 10:00:00';

        $this->actingAs($this->cenario['usuario'])->post('/profissional/agendamentos', [
            'cliente_id'              => $this->criarCliente()->id,
            'servico_profissional_id' => $this->cenario['servicoProfissional']->id,
            'data_hora_inicio'        => $data,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('agendamentos', 1);
    }
}
