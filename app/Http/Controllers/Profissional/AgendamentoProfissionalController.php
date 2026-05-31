<?php

namespace App\Http\Controllers\Profissional;

use App\Http\Controllers\Controller;
use App\Services\AgendamentoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Lista os agendamentos do profissional logado com filtro opcional por status.
     */
    public function index(Request $request): Response
    {
        $profissional = Auth::user()->profissional;

        $status = $request->input('status', 'todos');

        $agendamentos = $this->service->listarDoProfissional($profissional->id, $status);

        return Inertia::render('Profissional/AgendamentosProfissional', [
            'agendamentos'          => $agendamentos,
            'filtroAtual'           => $status,
            'servicos'              => $profissional->servicosProfissionais()->with('servico')->get(),
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
     * Endpoint para buscar clientes (por e-mail ou telefone)
     */
    public function buscarCliente(Request $request)
    {
        $q = $request->input('q');
        if (empty($q) || strlen($q) < 3) {
            return response()->json([]);
        }

        $clientes = User::where('tipo', 'cliente')
            ->where(function ($query) use ($q) {
                $query->where('email', 'like', "%{$q}%")
                      ->orWhereHas('perfil', function ($query2) use ($q) {
                          $query2->where('telefone', 'like', "%{$q}%");
                      });
            })
            ->with('perfil')
            ->limit(10)
            ->get()
            ->map(function ($c) {
                return [
                    'id'              => $c->id,
                    'nome_completo'   => $c->nome_completo,
                    'email'           => $c->email,
                    'telefone'        => $c->perfil->telefone ?? null,
                    'data_nascimento' => $c->perfil->data_nascimento ?? null,
                ];
            });

        return response()->json($clientes);
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
    public function criarAgendamento(Request $request): RedirectResponse
    {
        $profissional = Auth::user()->profissional;

        $validated = $request->validate([
            'cliente_id'             => ['required', 'exists:users,id'],
            'servico_id'             => ['required', 'exists:servicos,id'],
            'data_hora_inicio'       => ['required', 'date', 'after:now'],
            'preco'                  => ['required', 'numeric', 'min:0'],
            'tempo_execucao_minutos' => ['required', 'integer', 'min:1'],
            'nome_servico'           => ['required', 'string', 'max:150'],
        ]);

        try {
            $this->service->criar(array_merge($validated, [
                'profissional_id' => $profissional->id,
            ]));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['data_hora_inicio' => $e->getMessage()]);
        }

        return redirect()->route('profissional.agendamentos.profissional')->with('success', 'Reserva criada com sucesso!');
    }
}
