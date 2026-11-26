<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Notifications\Agendamento\NovaReservaNotification;
use App\Notifications\Agendamento\ReservaCanceladaPeloClienteNotification;
use App\Notifications\Agendamento\ReservaCanceladaPeloProfissionalNotification;
use App\Notifications\Agendamento\ReservaConfirmadaNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CriaCenarioAgendamento;
use Tests\TestCase;

/**
 * Task 2.3 — e-mails de reserva para profissional e cliente.
 */
class NotificacoesAgendamentoTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAgendamento;

    private array $cenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criarRoles();
        $this->cenario = $this->criarProfissionalComServico([
            'nome_fantasia' => 'Studio Bela',
            'logradouro'    => 'Rua das Flores',
            'numero'        => '10',
            'cidade'        => 'São Paulo',
        ], '08:00', '23:00');
        Notification::fake();
    }

    private function reservar(User $cliente, string $hora = '21:30'): Agendamento
    {
        $this->actingAs($cliente)->post('/agendamentos', [
            'servico_profissional_id' => $this->cenario['servicoProfissional']->id,
            'data_hora_inicio'        => now()->addDay()->format('Y-m-d') . " {$hora}:00",
        ])->assertSessionHasNoErrors();

        return Agendamento::latest('id')->firstOrFail();
    }

    public function test_reserva_online_notifica_profissional_e_cliente(): void
    {
        $cliente       = $this->criarCliente(['nome_completo' => 'Ana Souza']);
        $agendamento   = $this->reservar($cliente);

        Notification::assertSentTo($this->cenario['usuario'], NovaReservaNotification::class,
            fn ($n) => $n->agendamento->is($agendamento));
        Notification::assertSentTo($cliente, ReservaConfirmadaNotification::class,
            fn ($n) => $n->agendamento->is($agendamento));
        Notification::assertCount(2);

        // Texto em PT-BR e horário no fuso de São Paulo (21:30 local, mesmo após 21h UTC-3)
        $email = (new ReservaConfirmadaNotification($agendamento))->toMail($cliente);
        $this->assertStringContainsString('21:30', implode(' ', $email->introLines));
        $this->assertStringContainsString('Studio Bela', $email->subject);
        $this->assertMatchesRegularExpression('/(segunda|terça|quarta|quinta|sexta|sábado|domingo)/u', implode(' ', $email->introLines));

        $emailProf = (new NovaReservaNotification($agendamento))->toMail($this->cenario['usuario']);
        $this->assertStringContainsString('Ana Souza', implode(' ', $emailProf->introLines));
    }

    public function test_notificacoes_sao_enfileiradas(): void
    {
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new NovaReservaNotification(new Agendamento()));
    }

    public function test_reserva_manual_pelo_painel_nao_envia_email(): void
    {
        $this->actingAs($this->cenario['usuario'])->post('/profissional/agendamentos', [
            'cliente_id'              => $this->criarCliente()->id,
            'servico_profissional_id' => $this->cenario['servicoProfissional']->id,
            'data_hora_inicio'        => now()->addDay()->format('Y-m-d') . ' 10:00:00',
        ])->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_cancelamento_pelo_cliente_notifica_apenas_o_profissional(): void
    {
        $cliente     = $this->criarCliente();
        $agendamento = $this->reservar($cliente, '10:00');
        Notification::fake(); // descarta os e-mails da criação

        $this->actingAs($cliente)->delete("/agendamentos/{$agendamento->id}")->assertSessionHasNoErrors();

        Notification::assertSentTo($this->cenario['usuario'], ReservaCanceladaPeloClienteNotification::class);
        Notification::assertNotSentTo($cliente, ReservaCanceladaPeloClienteNotification::class);
        Notification::assertCount(1);
    }

    public function test_cancelamento_pelo_profissional_notifica_o_cliente(): void
    {
        $cliente     = $this->criarCliente();
        $agendamento = $this->reservar($cliente, '10:00');
        Notification::fake();

        $this->actingAs($this->cenario['usuario'])
            ->put("/profissional/agendamentos/{$agendamento->id}/status", ['status' => 'cancelado_profissional'])
            ->assertSessionHas('success');

        Notification::assertSentTo($cliente, ReservaCanceladaPeloProfissionalNotification::class);
        Notification::assertCount(1);
    }

    public function test_outras_transicoes_e_pre_cadastro_sem_email_nao_notificam(): void
    {
        $preCadastro = User::create(['nome_completo' => 'Sem Email', 'email' => null, 'password' => null, 'tipo' => 'cliente']);
        $agendamento = Agendamento::create([
            'cliente_id'       => $preCadastro->id,
            'profissional_id'  => $this->cenario['profissional']->id,
            'data_hora_inicio' => now()->addDay()->setTime(10, 0),
            'data_hora_fim'    => now()->addDay()->setTime(11, 0),
            'status'           => 'confirmado',
            'valor_total'      => 150,
        ]);

        $this->actingAs($this->cenario['usuario'])
            ->put("/profissional/agendamentos/{$agendamento->id}/status", ['status' => 'cancelado_profissional'])
            ->assertSessionHas('success');

        Notification::assertNothingSent();
    }
}
