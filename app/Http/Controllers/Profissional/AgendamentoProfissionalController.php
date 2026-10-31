<?php

namespace App\Http\Controllers\Profissional;

use App\Http\Controllers\Controller;
use App\Http\Requests\CriarAgendamentoRequest;
use App\Services\AgendamentoService;
use App\Services\ConviteCadastroService;
use App\Support\Telefone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\Perfil;

class AgendamentoProfissionalController extends Controller
{
    public function __construct(
        private AgendamentoService $service,
    ) {}

    /**
     * Lista os agendamentos do profissional logado com filtros opcionais.
     */
    public function index(Request $request): Response
    {
        $profissional = Auth::user()->profissional;

        $filtros = $request->only(['status', 'cliente', 'data_inicio', 'data_fim']);
        if (empty($filtros['status'])) {
            $filtros['status'] = 'todos';
        }

        $agendamentos = $this->service->listarDoProfissional($profissional->id, $filtros);

        return Inertia::render('Profissional/AgendamentosProfissional', [
            'agendamentos'          => $agendamentos,
            'filtros'               => $filtros,
            'servicos'              => $profissional->servicosProfissionais()->where('ativo', true)->with('servico')->get(),
            'horariosFuncionamento' => $profissional->horariosFuncionamento()->get(),
            'bloqueios'             => $profissional->bloqueiosAgenda()->get(),
        ]);
    }

    /**
     * Altera o status de um agendamento com validação de transição.
     */
    public function alterarStatus(Request $request, int $id): RedirectResponse
    {
        $profissional = Auth::user()->profissional;

        $validated = $request->validate([
            'status' => ['required', 'in:confirmado,em_atendimento,concluido,cancelado_profissional,nao_compareceu'],
        ]);

        try {
            $this->service->alterarStatus(
                $id,
                $profissional->id,
                $validated['status'],
                Auth::id(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Status do agendamento atualizado com sucesso!');
    }

    /**
     * Endpoint para buscar clientes (por nome, e-mail ou telefone).
     *
     * Isolamento multi-tenant (LGPD):
     * - Busca parcial apenas na carteira do profissional (clientes que já agendaram com ele).
     * - Fora da carteira, só match exato de e-mail ou telefone, com dados mascarados.
     */
    public function buscarCliente(Request $request): JsonResponse
    {
        $profissionalId = Auth::user()->profissional->id;

        $q = trim((string) $request->input('q'));
        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        $termoLike = '%' . addcslashes($q, '%_\\') . '%';
        $telefone  = Telefone::normalizar($q);
        $daCarteira = fn ($query) => $query->where('profissional_id', $profissionalId);

        $carteira = User::where('tipo', 'cliente')
            ->whereHas('agendamentos', $daCarteira)
            ->where(function ($query) use ($termoLike, $telefone) {
                $query->where('nome_completo', 'like', $termoLike)
                      ->orWhere('email', 'like', $termoLike)
                      ->when($telefone, fn ($q1) => $q1->orWhereHas('perfil', function ($q2) use ($telefone) {
                          $q2->where('telefone', 'like', "%{$telefone}%");
                      }));
            })
            ->with('perfil')
            ->limit(10)
            ->get()
            ->map(fn (User $c) => [
                'id'              => $c->id,
                'nome_completo'   => $c->nome_completo,
                'email'           => $c->email,
                'telefone'        => $c->perfil->telefone ?? null,
                'data_nascimento' => $c->perfil->data_nascimento ?? null,
                'fora_carteira'   => false,
            ]);

        $buscaPorEmail    = filter_var($q, FILTER_VALIDATE_EMAIL) !== false;
        $buscaPorTelefone = !$buscaPorEmail && $telefone !== null && strlen($telefone) >= 10;

        $foraCarteira = collect();
        if ($buscaPorEmail || $buscaPorTelefone) {
            $foraCarteira = User::where('tipo', 'cliente')
                ->whereDoesntHave('agendamentos', $daCarteira)
                ->when($buscaPorEmail, fn ($query) => $query->where('email', $q))
                ->when($buscaPorTelefone, fn ($query) => $query->whereHas('perfil', function ($q2) use ($telefone) {
                    $q2->where('telefone', $telefone);
                }))
                ->with('perfil')
                ->limit(5)
                ->get()
                ->map(fn (User $c) => [
                    'id'              => $c->id,
                    'nome_completo'   => $c->nome_completo,
                    'email'           => null,
                    'telefone'        => Telefone::mascarar($c->perfil->telefone ?? null),
                    'data_nascimento' => null,
                    'fora_carteira'   => true,
                ]);
        }

        return response()->json($carteira->concat($foraCarteira)->values());
    }

    /**
     * Gera o link de "completar cadastro" de um cliente pré-cadastrado da carteira
     * do profissional, com mensagem pronta para envio pelo WhatsApp.
     */
    public function gerarConviteCadastro(int $clienteId, ConviteCadastroService $convites): JsonResponse
    {
        $profissionalId = Auth::user()->profissional->id;

        $cliente = User::preCadastro()
            ->whereKey($clienteId)
            ->whereHas('agendamentos', fn ($q) => $q->where('profissional_id', $profissionalId))
            ->with('perfil')
            ->firstOrFail();

        $link     = $convites->gerar($cliente, Auth::user());
        $nome     = Str::before($cliente->nome_completo, ' ');
        $horas    = ConviteCadastroService::VALIDADE_HORAS;
        $mensagem = "Olá, {$nome}! Complete seu cadastro no EstetiFlow para acompanhar seus agendamentos: {$link} (link válido por {$horas} horas).";
        $telefone = $cliente->perfil->telefone ?? null;

        return response()->json([
            'link'         => $link,
            'whatsapp_url' => $telefone ? "https://wa.me/55{$telefone}?text=" . rawurlencode($mensagem) : null,
        ]);
    }

    /**
     * Pré-cadastrar um novo cliente.
     */
    public function preCadastrarCliente(Request $request)
    {
        $validated = $request->validate([
            'nome_completo'   => ['required', 'string', 'max:150'],
            'telefone'        => ['required', 'string', 'max:20'],
            'data_nascimento' => ['required', 'date'],
        ]);

        $user = User::create([
            'nome_completo' => $validated['nome_completo'],
            'email'         => null,
            'password'      => null,
            'tipo'          => 'cliente',
        ]);

        Perfil::create([
            'usuario_id'      => $user->id,
            'telefone'        => $validated['telefone'],
            'data_nascimento' => $validated['data_nascimento'],
        ]);

        $user->assignRole('cliente');

        return response()->json([
            'id'              => $user->id,
            'nome_completo'   => $user->nome_completo,
            'email'           => $user->email,
            'telefone'        => $validated['telefone'],
            'data_nascimento' => $validated['data_nascimento'],
        ]);
    }

    /**
     * Criar um agendamento manual.
     */
    public function criarAgendamento(CriarAgendamentoRequest $request): RedirectResponse
    {
        $profissional = Auth::user()->profissional;

        try {
            $this->service->criar(
                array_merge($request->validated(), ['alterado_por_id' => Auth::id()]),
                $profissional->id,
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['data_hora_inicio' => $e->getMessage()]);
        }

        return redirect()->route('profissional.agendamentos.profissional')->with('success', 'Reserva criada com sucesso!');
    }
}
