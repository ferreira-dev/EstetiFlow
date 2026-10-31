<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Payload de criação de agendamento (cliente e painel do profissional).
 *
 * Preço, duração e nome do serviço NÃO fazem parte do payload: são derivados
 * de servicos_profissionais no AgendamentoService::criar.
 */
class CriarAgendamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'servico_profissional_id' => ['required', 'integer', 'exists:servicos_profissionais,id'],
            'data_hora_inicio'        => ['required', 'date', 'after:now'],
        ];

        // No painel o profissional informa para qual cliente é o agendamento
        if ($this->routeIs('profissional.*')) {
            $rules['cliente_id'] = ['required', 'integer', 'exists:users,id'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'servico_profissional_id' => 'serviço',
            'data_hora_inicio'        => 'data e hora',
            'cliente_id'              => 'cliente',
        ];
    }
}
