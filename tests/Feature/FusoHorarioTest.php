<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\CriaCenarioAgendamento;
use Tests\TestCase;

/**
 * Task 1.5 — fuso horário (America/Sao_Paulo) e idioma (pt_BR).
 */
class FusoHorarioTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAgendamento;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->criarRoles();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_aplicacao_configurada_para_brasil(): void
    {
        $this->assertSame('America/Sao_Paulo', config('app.timezone'));
        $this->assertSame('pt_BR', app()->getLocale());
    }

    public function test_reserva_a_noite_para_daqui_a_pouco_e_aceita(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 20:30:00', 'America/Sao_Paulo'));
        $cenario = $this->criarProfissionalComServico(horaInicio: '08:00', horaFim: '23:30');

        $this->actingAs($this->criarCliente())->post('/agendamentos', [
            'servico_profissional_id' => $cenario['servicoProfissional']->id,
            'data_hora_inicio'        => '2026-11-10 22:00:00',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('agendamentos', ['data_hora_inicio' => '2026-11-10 22:00:00']);
    }

    public function test_dashboard_hoje_correto_apos_21h(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 21:15:00', 'America/Sao_Paulo'));
        $cenario = $this->criarProfissionalComServico();

        $this->criarAgendamentoDireto(
            $this->criarCliente(),
            $cenario['profissional'],
            Carbon::parse('2026-11-10 22:00:00')
        )->update(['status' => 'confirmado']);

        $this->actingAs($cenario['usuario'])
            ->get('/profissional/dashboard')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Profissional/Dashboard')
                ->has('agendamentosHoje', 1));
    }

    public function test_mensagens_de_validacao_em_portugues(): void
    {
        $this->post('/login', [])
            ->assertSessionHasErrors([
                'email'    => 'O campo e-mail é obrigatório.',
                'password' => 'O campo senha é obrigatório.',
            ]);
    }
}
