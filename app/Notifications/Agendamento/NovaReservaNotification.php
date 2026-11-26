<?php

namespace App\Notifications\Agendamento;

use Illuminate\Notifications\Messages\MailMessage;

/** Para o profissional: o cliente fez uma reserva pela página pública. */
class NovaReservaNotification extends NotificacaoAgendamento
{
    public function toMail(object $notifiable): MailMessage
    {
        $cliente = $this->agendamento->cliente->nome_completo ?? 'Cliente';

        return (new MailMessage)
            ->subject("Nova reserva: {$this->servico()} — {$this->dataHora()}")
            ->greeting("Olá, {$this->primeiroNome($notifiable->nome_completo)}!")
            ->line("**{$cliente}** fez uma reserva online.")
            ->line("**Serviço:** {$this->servico()}")
            ->line("**Quando:** {$this->dataHora()}")
            ->action('Ver agendamentos', route('profissional.agendamentos.profissional'))
            ->salutation('Equipe EstetiFlow');
    }
}
