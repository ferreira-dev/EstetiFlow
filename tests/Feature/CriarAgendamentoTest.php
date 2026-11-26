<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\ItemAgendamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaCenarioAgendamento;
use Tests\TestCase;

/**
 * Task 1.1 (preço/duração/nome vêm do banco) e 1.4 (reserva dupla).
 */
class CriarAgendamentoTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAgendamento;

    private array $cenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criarRoles();
        $this->cenario = $this->criarProfissionalComServico();
    }

    private function amanhaAs(string $hora): string
    {
        return now()->addDay()->format('Y-m-d') . " {$hora}:00";
    }

    public function test_payload_adulterado_grava_snapshot_com_dados_reais_do_servico(): void
    {
        $cliente = $this->criarCliente();

        $this->actingAs($cliente)->post('/agendamentos', [
            'servico_profissional_id' => $this->cenario['servicoProfissional']->id,
            'data_hora_inicio'        => $this->amanhaAs('10:00'),
            'preco'                   => 0.01,
            'tempo_execucao_minutos'  => 1,
            'nome_servico'            => 'Serviço adulterado',
        ])->assertRedirect(route('agendamentos'))->assertSessionHasNoErrors();

        $agendamento = Agendamento::sole();
        $item        = ItemAgendamento::sole();

        $this->assertSame('150.00', $agendamento->valor_total);
        $this->assertSame($this->cenario['profissional']->id, $agendamento->profissional_id);
        $this->assertEquals(60, $agendamento->data_hora_inicio->diffInMinutes($agendamento->data_hora_fim));
        $this->assertSame('150.00', $item->preco_praticado);
        $this->assertSame(60, $item->tempo_execucao_minutos);
        $this->assertSame('Corte Feminino', $item->nome_servico);
        $this->assertSame($cliente->id, $agendamento->historico()->sole()->alterado_por_id);
    }

    public function test_vinculo_inativo_e_rejeitado(): void
    {
        $this->cenario['servicoProfissional']->update(['ativo' => false]);

        $this->actingAs($this->criarCliente())->post('/agendamentos', [
            'servico_profissional_id' => $this->cenario['servicoProfissional']->id,
            'data_hora_inicio'        => $this->amanhaAs('10:00'),
        ])->assertSessionHasErrors('data_hora_inicio');

        $this->assertDatabaseCount('agendamentos', 0);
    }

    public function test_estabelecimento_com_agenda_online_desligada_rejeita_reserva_do_cliente(): void
    {
        $this->cenario['estabelecimento']->update(['agenda_online' => false]);

        $this->actingAs($this->criarCliente())->post('/agendamentos', [
            'servico_profissional_id' => $this->cenario['servicoProfissional']->id,
            'data_hora_inicio'        => $this->amanhaAs('10:00'),
        ])->assertSessionHasErrors('data_hora_inicio');

        $this->assertDatabaseCount('agendamentos', 0);
    }

    public function test_estabelecimento_inativo_ou_profissional_sem_agendamentos_rejeita_reserva(): void
    {
        $cliente = $this->criarCliente();
        $payload = [
            'servico_profissional_id' => $this->cenario['servicoProfissional']->id,
            'data_hora_inicio'        => $this->amanhaAs('10:00'),
        ];

        $this->cenario['profissional']->update(['aceita_agendamentos' => false]);
        $this->actingAs($cliente)->post('/agendamentos', $payload)->assertSessionHasErrors('data_hora_inicio');

        $this->cenario['profissional']->update(['aceita_agendamentos' => true]);
        $this->cenario['estabelecimento']->update(['ativo' => false]);
        $this->actingAs($cliente)->post('/agendamentos', $payload)->assertSessionHasErrors('data_hora_inicio');

        $this->assertDatabaseCount('agendamentos', 0);
    }

    public function test_profissional_agenda_manualmente_mesmo_com_agenda_online_desligada(): void
    {
        $this->cenario['estabelecimento']->update(['agenda_online' => false]);
        $cliente = $this->criarCliente();

        $this->actingAs($this->cenario['usuario'])->post('/profissional/agendamentos', [
            'cliente_id'              => $cliente->id,
            'servico_profissional_id' => $this->cenario['servicoProfissional']->id,
            'data_hora_inicio'        => $this->amanhaAs('10:00'),
            'preco'                   => 1,
        ])->assertRedirect(route('profissional.agendamentos.profissional'))->assertSessionHasNoErrors();

        $agendamento = Agendamento::sole();
        $this->assertSame('150.00', $agendamento->valor_total);
        $this->assertSame($cliente->id, $agendamento->cliente_id);
        $this->assertSame($this->cenario['usuario']->id, $agendamento->historico()->sole()->alterado_por_id);
    }

    public function test_profissional_nao_agenda_servico_de_outro_profissional(): void
    {
        $outro = $this->criarProfissionalComServico();

        $this->actingAs($this->cenario['usuario'])->post('/profissional/agendamentos', [
            'cliente_id'              => $this->criarCliente()->id,
            'servico_profissional_id' => $outro['servicoProfissional']->id,
            'data_hora_inicio'        => $this->amanhaAs('10:00'),
        ])->assertSessionHasErrors('data_hora_inicio');

        $this->assertDatabaseCount('agendamentos', 0);
    }

    public function test_segundo_agendamento_sobreposto_e_rejeitado(): void
    {
        $sp = $this->cenario['servicoProfissional']->id;

        $this->actingAs($this->criarCliente())->post('/agendamentos', [
            'servico_profissional_id' => $sp,
            'data_hora_inicio'        => $this->amanhaAs('10:00'),
        ])->assertSessionHasNoErrors();

        // Sobreposição parcial (10:30–11:30 x 10:00–11:00)
        $this->actingAs($this->criarCliente())->post('/agendamentos', [
            'servico_profissional_id' => $sp,
            'data_hora_inicio'        => $this->amanhaAs('10:30'),
        ])->assertSessionHasErrors(['data_hora_inicio' => 'Já existe um agendamento confirmado para este horário.']);

        $this->assertDatabaseCount('agendamentos', 1);
    }
}
