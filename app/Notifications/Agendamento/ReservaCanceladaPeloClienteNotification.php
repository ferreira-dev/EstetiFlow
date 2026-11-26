<?php

namespace App\Notifications\Agendamento;

use Illuminate\Notifications\Messages\MailMessage;

/** Para o profissional: o cliente cancelou a reserva. */
class ReservaCanceladaPeloClienteNotification extends NotificacaoAgendamento
{
    public function toMail(object $notifiable): MailMessage
    {
        $cliente = $this->agendamento->cliente->nome_completo ?? 'Cliente';

        return (new MailMessage)
            ->subject("Reserva cancelada: {$this->servico()} — {$this->dataHora()}")
            ->greeting("Olá, {$this->primeiroNome($notifiable->nome_completo)}!")
            ->line("**{$cliente}** cancelou a reserva abaixo. O horário voltou a ficar disponível na sua página.")
            ->line("**Serviço:** {$this->servico()}")
            ->line("**Quando:** {$this->dataHora()}")
            ->action('Ver agendamentos', route('profissional.agendamentos.profissional'))
            ->salutation('Equipe EstetiFlow');
    }
}
