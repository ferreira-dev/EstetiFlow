<?php

namespace Tests\Feature;

use App\Models\Perfil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\CriaCenarioAgendamento;
use Tests\TestCase;

/**
 * Task 1.2 — isolamento da busca de clientes entre estabelecimentos (LGPD).
 */
class BuscarClienteTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAgendamento;

    private array $profA;
    private array $profB;
    private $clienteA;
    private $clienteB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criarRoles();

        $this->profA = $this->criarProfissionalComServico();
        $this->profB = $this->criarProfissionalComServico();

        $this->clienteA = $this->criarCliente(['email' => 'ana.souza@gmail.com'], '(11) 98888-7777', '1990-05-10');
        $this->clienteB = $this->criarCliente(['email' => 'bruna.lima@gmail.com'], '11977776666', '1985-01-20');

        $this->criarAgendamentoDireto($this->clienteA, $this->profA['profissional']);
        $this->criarAgendamentoDireto($this->clienteB, $this->profB['profissional']);
    }

    private function buscar(string $q)
    {
        return $this->actingAs($this->profA['usuario'])
            ->getJson('/profissional/agendamentos/buscar-cliente?q=' . urlencode($q));
    }

    public function test_busca_parcial_nao_encontra_cliente_de_outro_profissional(): void
    {
        $this->buscar('bruna')->assertOk()->assertExactJson([]);
        $this->buscar('gmail')->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $this->clienteA->id);
        $this->buscar('97777')->assertOk()->assertExactJson([]);
    }

    public function test_cliente_da_carteira_retorna_dados_completos(): void
    {
        $this->buscar('ana.sou')
            ->assertOk()
            ->assertJsonPath('0.email', 'ana.souza@gmail.com')
            ->assertJsonPath('0.telefone', '11988887777')
            ->assertJsonPath('0.fora_carteira', false)
            ->assertJsonPath('0.data_nascimento', fn ($data) => str_starts_with($data, '1990-05-10'));
    }

    public function test_match_exato_fora_da_carteira_retorna_dados_mascarados(): void
    {
        $esperado = [[
            'id'              => $this->clienteB->id,
            'nome_completo'   => $this->clienteB->nome_completo,
            'email'           => null,
            'telefone'        => '(11) *****-6666',
            'data_nascimento' => null,
            'fora_carteira'   => true,
        ]];

        $this->buscar('(11) 97777-6666')->assertOk()->assertExactJson($esperado);
        $this->buscar('bruna.lima@gmail.com')->assertOk()->assertExactJson($esperado);
    }

    public function test_telefone_e_normalizado_na_gravacao_e_na_busca(): void
    {
        $this->assertSame('11988887777', Perfil::where('usuario_id', $this->clienteA->id)->value('telefone'));

        $this->buscar('(11) 98888')->assertOk()->assertJsonPath('0.id', $this->clienteA->id);
    }

    public function test_rota_de_busca_tem_throttle(): void
    {
        $middlewares = Route::getRoutes()->getByName('profissional.agendamentos.buscarCliente')->gatherMiddleware();

        $this->assertContains('throttle:30,1', $middlewares);
    }
}
