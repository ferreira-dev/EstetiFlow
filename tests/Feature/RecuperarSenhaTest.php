<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\RedefinirSenhaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CriaCenarioAgendamento;
use Tests\TestCase;

/**
 * Task 1.6 — recuperação de senha, throttle de login e usuário inativo.
 */
class RecuperarSenhaTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAgendamento;

    private User $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->criarRoles();
        $this->cliente = $this->criarCliente(['email' => 'cliente@test.com']);
    }

    public function test_fluxo_completo_de_redefinicao_de_senha(): void
    {
        Notification::fake();

        $this->get('/esqueci-senha')->assertOk();

        $this->post('/esqueci-senha', ['email' => 'cliente@test.com'])
            ->assertSessionHas('success');

        $token = null;
        Notification::assertSentTo($this->cliente, RedefinirSenhaNotification::class, function ($notificacao) use (&$token) {
            $token = $notificacao->token;
            $email = $notificacao->toMail($this->cliente);

            return $email->subject === 'EstetiFlow — Redefinição de senha'
                && str_contains($email->actionUrl, '/redefinir-senha/');
        });

        $this->get("/redefinir-senha/{$token}?email=cliente@test.com")->assertOk();

        $this->post('/redefinir-senha', [
            'token'                 => $token,
            'email'                 => 'cliente@test.com',
            'password'              => 'novaSenha1',
            'password_confirmation' => 'novaSenha1',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('novaSenha1', $this->cliente->fresh()->password));

        $this->post('/login', ['email' => 'cliente@test.com', 'password' => 'novaSenha1'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($this->cliente->fresh());
    }

    public function test_token_invalido_nao_redefine_senha(): void
    {
        $this->post('/redefinir-senha', [
            'token'                 => 'token-invalido',
            'email'                 => 'cliente@test.com',
            'password'              => 'novaSenha1',
            'password_confirmation' => 'novaSenha1',
        ])->assertSessionHasErrors(['email' => 'Este link de redefinição de senha é inválido ou expirou.']);

        $this->assertTrue(Hash::check('senha123', $this->cliente->fresh()->password));
    }

    public function test_resposta_neutra_para_email_inexistente_ou_usuario_inativo(): void
    {
        Notification::fake();
        $this->cliente->update(['ativo' => false]);

        $this->post('/esqueci-senha', ['email' => 'naoexiste@test.com'])->assertSessionHas('success');
        $this->post('/esqueci-senha', ['email' => 'cliente@test.com'])->assertSessionHas('success');

        Notification::assertNothingSent();
    }

    public function test_usuario_inativo_nao_autentica(): void
    {
        $this->cliente->update(['ativo' => false]);

        $this->post('/login', ['email' => 'cliente@test.com', 'password' => 'senha123'])
            ->assertSessionHasErrors(['email' => 'As credenciais fornecidas estão incorretas.']);

        $this->assertGuest();
    }

    public function test_login_bloqueado_apos_cinco_tentativas_erradas(): void
    {
        foreach (range(1, 5) as $tentativa) {
            $this->post('/login', ['email' => 'cliente@test.com', 'password' => 'errada'])
                ->assertSessionHasErrors('email');
        }

        // 6ª tentativa, mesmo com a senha correta → bloqueada
        $this->post('/login', ['email' => 'cliente@test.com', 'password' => 'senha123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertStringContainsString('Muitas tentativas', session('errors')->first('email'));
    }
}
