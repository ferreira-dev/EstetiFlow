<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RedefinirSenhaNotification extends Notification
{
    public function __construct(
        public string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $minutos = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

        return (new MailMessage)
            ->subject('EstetiFlow — Redefinição de senha')
            ->greeting("Olá, {$notifiable->nome_completo}!")
            ->line('Recebemos uma solicitação para redefinir a senha da sua conta no EstetiFlow.')
            ->action('Redefinir senha', $url)
            ->line("Este link expira em {$minutos} minutos.")
            ->line('Se você não solicitou a redefinição, nenhuma ação é necessária: sua senha atual continua valendo.')
            ->salutation('Equipe EstetiFlow');
    }
}
