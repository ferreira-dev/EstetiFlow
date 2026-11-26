<?php

namespace App\Notifications\Agendamento;

use App\Models\Agendamento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Base dos e-mails de reserva: enviados pela fila, só após o commit da transação,
 * com data/hora no fuso da aplicação (America/Sao_Paulo) e textos em PT-BR.
 */
abstract class NotificacaoAgendamento extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Agendamento $agendamento,
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    protected function dataHora(): string
    {
        return $this->agendamento->data_hora_inicio
            ->copy()
            ->setTimezone(config('app.timezone'))
            ->locale('pt_BR')
            ->translatedFormat('l, d \d\e F \à\s H:i');
    }

    protected function servico(): string
    {
        return $this->agendamento->itens->pluck('nome_servico')->join(', ') ?: 'Serviço';
    }

    protected function estabelecimento(): string
    {
        return $this->agendamento->profissional->estabelecimento->nome_fantasia ?? 'EstetiFlow';
    }

    protected function endereco(): ?string
    {
        $e = $this->agendamento->profissional->estabelecimento;

        if (!$e) {
            return null;
        }

        $endereco = collect([$e->logradouro, $e->numero, $e->bairro, $e->cidade, $e->estado])->filter()->join(', ');

        return $endereco !== '' ? $endereco : null;
    }

    protected function primeiroNome(?string $nome): string
    {
        return strtok((string) $nome, ' ') ?: '';
    }
}
