<?php

namespace App\Notifications\Agendamento;

use Illuminate\Notifications\Messages\MailMessage;

/** Para o cliente: o profissional cancelou/recusou a reserva. */
class ReservaCanceladaPeloProfissionalNotification extends NotificacaoAgendamento
{
    public function toMail(object $notifiable): MailMessage
    {
        $publica = $this->agendamento->profissional->estabelecimento?->url_publica;

        $mensagem = (new MailMessage)
            ->subject("Reserva cancelada — {$this->estabelecimento()}")
            ->greeting("Olá, {$this->primeiroNome($notifiable->nome_completo)}!")
            ->line("Infelizmente **{$this->estabelecimento()}** cancelou a sua reserva.")
            ->line("**Serviço:** {$this->servico()}")
            ->line("**Quando:** {$this->dataHora()}");

        if ($publica) {
            $mensagem->action('Escolher outro horário', url($publica));
        }

        return $mensagem->salutation('Equipe EstetiFlow');
    }
}
