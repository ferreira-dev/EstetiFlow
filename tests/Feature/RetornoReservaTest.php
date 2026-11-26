<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CriaCenarioAgendamento;
use Tests\TestCase;

/**
 * Task 2.2 — login/registro iniciados numa reserva voltam para a página do estabelecimento.
 */
class RetornoReservaTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAgendamento;

    private string $retorno;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criarRoles();
        $cenario       = $this->criarProfissionalComServico();
        $this->retorno = $cenario['estabelecimento']->url_publica . '?servico=' . $cenario['servicoProfissional']->id;
    }

    public function test_login_a_partir_da_reserva_volta_para_o_estabelecimento(): void
    {
        $cliente = $this->criarCliente();

        $this->get('/login?redirect=' . urlencode($this->retorno))
            ->assertInertia(fn (Assert $page) => $page->component('Login')->where('retornoReserva', $this->retorno));

        $this->post('/login', ['email' => $cliente->email, 'password' => 'senha123'])
            ->assertRedirect(url($this->retorno));
    }

    public function test_registro_a_partir_da_reserva_cria_cliente_e_volta_para_o_estabelecimento(): void
    {
        $this->get('/registro?redirect=' . urlencode($this->retorno))
            ->assertInertia(fn (Assert $page) => $page->component('Registro')->where('retornoReserva', $this->retorno));

        $this->post('/registro', [
            'tipo'                  => 'cliente',
            'nome_completo'         => 'Nova Cliente',
            'email'                 => 'nova@cliente.test',
            'password'              => 'senha123',
            'password_confirmation' => 'senha123',
        ])->assertRedirect(url($this->retorno));

        $this->assertTrue(User::where('email', 'nova@cliente.test')->sole()->hasRole('cliente'));
    }

    public function test_registro_de_profissional_ignora_retorno_da_reserva(): void
    {
        $this->get('/registro?redirect=' . urlencode($this->retorno));

        $this->post('/registro', [
            'tipo'                  => 'profissional',
            'nome_completo'         => 'Novo Profissional',
            'email'                 => 'novo@profissional.test',
            'password'              => 'senha123',
            'password_confirmation' => 'senha123',
        ])->assertRedirect('/profissional/estabelecimento');
    }

    public function test_redirect_externo_ou_invalido_e_ignorado(): void
    {
        $cliente = $this->criarCliente();

        foreach (['https://malicioso.test/agendar/x', '//malicioso.test', '/perfil', '/agendar/x?servico=1&y=2'] as $url) {
            $this->get('/login?redirect=' . urlencode($url))
                ->assertInertia(fn (Assert $page) => $page->where('retornoReserva', null));
        }

        $this->post('/login', ['email' => $cliente->email, 'password' => 'senha123'])
            ->assertRedirect(route('home'));
    }
}
