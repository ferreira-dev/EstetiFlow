<?php

namespace Tests\Feature;

use App\Models\Perfil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\CriaCenarioAgendamento;
use Tests\TestCase;

/**
 * Task 1.3 — sequestro de conta via pré-cadastro.
 */
class ConviteCadastroTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAgendamento;

    private array $cenario;
    private User $preCadastro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->criarRoles();

        $this->cenario = $this->criarProfissionalComServico();

        $this->preCadastro = User::create([
            'nome_completo' => 'Maria Pré-cadastrada',
            'email'         => null,
            'password'      => null,
            'tipo'          => 'cliente',
        ]);
        Perfil::create(['usuario_id' => $this->preCadastro->id, 'telefone' => '11955554444']);
        $this->preCadastro->assignRole('cliente');

        $this->criarAgendamentoDireto($this->preCadastro, $this->cenario['profissional']);
    }

    private function gerarLink(): string
    {
        return $this->actingAs($this->cenario['usuario'])
            ->postJson("/profissional/clientes/{$this->preCadastro->id}/convite")
            ->assertOk()
            ->json('link');
    }

    private function tokenDoLink(string $link): string
    {
        return basename(parse_url($link, PHP_URL_PATH));
    }

    public function test_registro_com_telefone_de_pre_cadastro_cria_conta_nova(): void
    {
        $this->post('/registro', [
            'nome_completo'         => 'Invasor',
            'email'                 => 'invasor@test.com',
            'password'              => 'senha123',
            'password_confirmation' => 'senha123',
            'tipo'                  => 'cliente',
            'telefone'              => '(11) 95555-4444',
        ])->assertRedirect(route('home'));

        $this->assertNull($this->preCadastro->fresh()->password);
        $this->assertNotSame($this->preCadastro->id, User::where('email', 'invasor@test.com')->value('id'));
    }

    public function test_login_de_usuario_sem_senha_nao_gera_link(): void
    {
        $this->preCadastro->update(['email' => 'maria@test.com']);

        $this->post('/login', ['email' => 'maria@test.com', 'password' => 'qualquer'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_token_antigo_baseado_em_encrypt_e_rejeitado(): void
    {
        $this->get('/completar-cadastro/' . encrypt($this->preCadastro->id))
            ->assertRedirect(route('login'));
    }

    public function test_profissional_gera_link_e_cliente_completa_cadastro_uma_unica_vez(): void
    {
        $resposta = $this->actingAs($this->cenario['usuario'])
            ->postJson("/profissional/clientes/{$this->preCadastro->id}/convite")
            ->assertOk();

        $this->assertStringStartsWith('https://wa.me/5511955554444?text=', $resposta->json('whatsapp_url'));
        $token = $this->tokenDoLink($resposta->json('link'));
        $this->assertSame(64, strlen($token));
        $this->assertDatabaseMissing('convites_cadastro', ['token_hash' => $token]);

        auth()->logout();

        $this->get("/completar-cadastro/{$token}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('CompletarCadastro')
                ->where('nome', 'Maria Pré-cadastrada'));

        $this->post('/completar-cadastro', [
            'token'                 => $token,
            'email'                 => 'maria@test.com',
            'password'              => 'senha123',
            'password_confirmation' => 'senha123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($this->preCadastro->fresh());
        $this->assertNotNull($this->preCadastro->fresh()->password);

        auth()->logout();

        // Reuso do link → rejeitado
        $this->get("/completar-cadastro/{$token}")->assertRedirect(route('login'));
        $this->post('/completar-cadastro', [
            'token'                 => $token,
            'email'                 => 'outro@test.com',
            'password'              => 'senha456',
            'password_confirmation' => 'senha456',
        ])->assertRedirect(route('login'));

        $this->assertSame('maria@test.com', $this->preCadastro->fresh()->email);
    }

    public function test_token_expirado_e_rejeitado(): void
    {
        $token = $this->tokenDoLink($this->gerarLink());
        auth()->logout();

        $this->get("/completar-cadastro/{$token}")->assertOk();

        $this->travel(49)->hours();

        $this->get("/completar-cadastro/{$token}")->assertRedirect(route('login'));
    }

    public function test_gerar_novo_link_revoga_o_anterior(): void
    {
        $antigo = $this->tokenDoLink($this->gerarLink());
        $novo   = $this->tokenDoLink($this->gerarLink());

        auth()->logout();

        $this->get("/completar-cadastro/{$antigo}")->assertRedirect(route('login'));
        $this->get("/completar-cadastro/{$novo}")->assertOk();
    }

    public function test_profissional_nao_gera_link_para_cliente_fora_da_carteira(): void
    {
        $outro = $this->criarProfissionalComServico();

        $this->actingAs($outro['usuario'])
            ->postJson("/profissional/clientes/{$this->preCadastro->id}/convite")
            ->assertNotFound();

        $this->assertDatabaseCount('convites_cadastro', 0);
    }
}
