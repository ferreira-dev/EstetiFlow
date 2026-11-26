<?php

namespace App\Notifications\Agendamento;

use Illuminate\Notifications\Messages\MailMessage;

/** Para o cliente: confirmação da reserva feita pela página pública. */
class ReservaConfirmadaNotification extends NotificacaoAgendamento
{
    public function toMail(object $notifiable): MailMessage
    {
        $mensagem = (new MailMessage)
            ->subject("Reserva confirmada — {$this->estabelecimento()}")
            ->greeting("Olá, {$this->primeiroNome($notifiable->nome_completo)}!")
            ->line("Sua reserva em **{$this->estabelecimento()}** está confirmada.")
            ->line("**Serviço:** {$this->servico()}")
            ->line("**Quando:** {$this->dataHora()}");

        if ($endereco = $this->endereco()) {
            $mensagem->line("**Onde:** {$endereco}");
        }

        return $mensagem
            ->action('Ver meus agendamentos', route('agendamentos'))
            ->line('Se não puder comparecer, cancele pelo app para liberar o horário.')
            ->salutation('Equipe EstetiFlow');
    }
}
