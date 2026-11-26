<?php

namespace Tests\Feature;

use App\Models\HorarioFuncionamento;
use App\Services\EstabelecimentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CriaCenarioAgendamento;
use Tests\TestCase;

/**
 * Task 2.5 — estabelecimento só listado/agendável quando completo + onboarding do profissional.
 */
class EstabelecimentoPublicavelTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAgendamento;

    private array $completo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criarRoles();
        $this->completo = $this->criarProfissionalComServico(['nome_fantasia' => 'Completo']);
    }

    private function idsListados(): array
    {
        return app(EstabelecimentoService::class)->listar()->pluck('id')->all();
    }

    public function test_listar_nao_retorna_estabelecimento_sem_servico_ativo_ou_sem_horario(): void
    {
        $semServico = $this->criarProfissionalComServico();
        $semServico['servicoProfissional']->update(['ativo' => false]);

        $semHorario = $this->criarProfissionalComServico();
        HorarioFuncionamento::where('profissional_id', $semHorario['profissional']->id)->delete();

        $ids = $this->idsListados();

        $this->assertContains($this->completo['estabelecimento']->id, $ids);
        $this->assertNotContains($semServico['estabelecimento']->id, $ids);
        $this->assertNotContains($semHorario['estabelecimento']->id, $ids);
        $this->assertNotContains($semHorario['estabelecimento']->id, app(EstabelecimentoService::class)->destaques()->pluck('id')->all());
    }

    public function test_pagina_publica_de_estabelecimento_incompleto_mostra_aviso(): void
    {
        $incompleto = $this->criarProfissionalComServico();
        HorarioFuncionamento::where('profissional_id', $incompleto['profissional']->id)->delete();

        $this->get($incompleto['estabelecimento']->url_publica)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('agendaDisponivel', false));

        $this->get($this->completo['estabelecimento']->url_publica)
            ->assertInertia(fn (Assert $page) => $page->where('agendaDisponivel', true));
    }

    public function test_estabelecimento_incompleto_nao_aceita_reserva(): void
    {
        $incompleto = $this->criarProfissionalComServico();
        HorarioFuncionamento::where('profissional_id', $incompleto['profissional']->id)->delete();

        $this->actingAs($this->criarCliente())->post('/agendamentos', [
            'servico_profissional_id' => $incompleto['servicoProfissional']->id,
            'data_hora_inicio'        => now()->addDay()->format('Y-m-d') . ' 10:00:00',
        ])->assertSessionHasErrors('data_hora_inicio');

        $this->assertDatabaseCount('agendamentos', 0);
    }

    public function test_dashboard_informa_passos_do_onboarding_e_link(): void
    {
        $this->actingAs($this->completo['usuario'])->get('/profissional/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->where('onboarding.dados', true)
                ->where('onboarding.servicos', true)
                ->where('onboarding.horarios', true)
                ->where('onboarding.publicavel', true)
                ->where('onboarding.link', url($this->completo['estabelecimento']->url_publica))
            );

        HorarioFuncionamento::where('profissional_id', $this->completo['profissional']->id)->delete();

        $this->actingAs($this->completo['usuario'])->get('/profissional/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->where('onboarding.horarios', false)
                ->where('onboarding.publicavel', false)
            );
    }
}
