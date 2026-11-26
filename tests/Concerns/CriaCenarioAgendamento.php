<?php

namespace Tests\Concerns;

use App\Models\Agendamento;
use App\Models\Estabelecimento;
use App\Models\HorarioFuncionamento;
use App\Models\Perfil;
use App\Models\Profissional;
use App\Models\Servico;
use App\Models\ServicoProfissional;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Monta o cenário mínimo (estabelecimento, profissional, serviço, cliente) para os testes de feature.
 */
trait CriaCenarioAgendamento
{
    protected function criarRoles(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('cliente', 'web');
        Role::findOrCreate('profissional', 'web');
    }

    protected function criarCliente(array $dados = [], ?string $telefone = null, ?string $dataNascimento = null): User
    {
        $user = User::create(array_merge([
            'nome_completo' => 'Cliente ' . Str::random(5),
            'email'         => Str::lower(Str::random(8)) . '@cliente.test',
            'password'      => 'senha123',
            'tipo'          => 'cliente',
        ], $dados));

        Perfil::create([
            'usuario_id'      => $user->id,
            'telefone'        => $telefone,
            'data_nascimento' => $dataNascimento,
        ]);

        $user->assignRole('cliente');

        return $user;
    }

    /**
     * Cria estabelecimento + usuário profissional + profissional + serviço (R$ 150, 60 min) + grade semanal.
     *
     * @return array{estabelecimento: Estabelecimento, usuario: User, profissional: Profissional, servicoProfissional: ServicoProfissional}
     */
    protected function criarProfissionalComServico(array $estabelecimento = [], string $horaInicio = '08:00', string $horaFim = '20:00'): array
    {
        $slug = 'studio-' . Str::lower(Str::random(6));

        $est = Estabelecimento::create(array_merge([
            'nome_fantasia'     => 'Studio ' . $slug,
            'url_personalizada' => $slug,
            'agenda_online'     => true,
            'ativo'             => true,
        ], $estabelecimento));

        $usuario = User::create([
            'nome_completo' => 'Profissional ' . $slug,
            'email'         => "{$slug}@profissional.test",
            'password'      => 'senha123',
            'tipo'          => 'profissional',
        ]);
        $usuario->assignRole('profissional');

        $profissional = Profissional::create([
            'usuario_id'          => $usuario->id,
            'estabelecimento_id'  => $est->id,
            'nome_profissional'   => $usuario->nome_completo,
            'aceita_agendamentos' => true,
        ]);

        $servico = Servico::create(['nome' => 'Corte Feminino', 'categoria' => 'cabelo']);

        $servicoProfissional = ServicoProfissional::create([
            'profissional_id'        => $profissional->id,
            'servico_id'             => $servico->id,
            'preco'                  => 150.00,
            'tempo_execucao_minutos' => 60,
        ]);

        foreach (range(0, 6) as $dia) {
            HorarioFuncionamento::create([
                'profissional_id' => $profissional->id,
                'dia_semana'      => $dia,
                'hora_inicio'     => $horaInicio,
                'hora_fim'        => $horaFim,
            ]);
        }

        return [
            'estabelecimento'     => $est,
            'usuario'             => $usuario,
            'profissional'        => $profissional,
            'servicoProfissional' => $servicoProfissional,
        ];
    }

    /**
     * Insere um agendamento direto no banco (vincula o cliente à carteira do profissional).
     */
    protected function criarAgendamentoDireto(User $cliente, Profissional $profissional, ?Carbon $inicio = null): Agendamento
    {
        $inicio ??= now()->subDays(7)->setTime(10, 0);

        return Agendamento::create([
            'cliente_id'       => $cliente->id,
            'profissional_id'  => $profissional->id,
            'data_hora_inicio' => $inicio,
            'data_hora_fim'    => $inicio->copy()->addHour(),
            'status'           => 'concluido',
            'valor_total'      => 150,
        ]);
    }
}
