# Fluxo de Agendamento — SaaS Estético

> Documento de referência para o módulo de agendamentos: transições de status, regras de negócio e estado atual do MVP.

---

## 1. Diagrama de Status

```
[CLIENTE cria reserva]
         │
         ▼
      pendente ──────────────────────────────────────────┐
         │                                               │
         │ (profissional confirma / MVP: automático)     │
         ▼                                               │
      confirmado ────────────────────────────────────────┤
         │                                               │ cancelado_cliente
         │ (profissional inicia atendimento)             │ cancelado_profissional
         ▼                                               │
   em_atendimento ─────────────────────────────────────►│
         │                                               │
         │ (profissional encerra)     (não apareceu)     │
         ▼                                  │            │
       concluido ◄──────────────────────────┘            │
    (terminal)          nao_compareceu               (terminais)
                          (terminal)
```

### Transições válidas segundo o schema

| De | Para | Ator | Quando |
|----|------|------|--------|
| `pendente` | `confirmado` | Profissional / automático | Reserva aceita |
| `pendente` | `cancelado_cliente` | Cliente | Cancela antes da confirmação |
| `pendente` | `cancelado_profissional` | Profissional | Recusa a reserva |
| `confirmado` | `em_atendimento` | Profissional | Inicia o serviço no painel |
| `confirmado` | `cancelado_cliente` | Cliente | Cancela reserva confirmada |
| `confirmado` | `cancelado_profissional` | Profissional | Cancela pelo painel |
| `confirmado` | `nao_compareceu` | Profissional / Sistema | Cliente não apareceu |
| `em_atendimento` | `concluido` | Profissional | Conclui o serviço |

**Todos os terminais são irreversíveis:** `concluido`, `cancelado_cliente`, `cancelado_profissional`, `nao_compareceu`.

---

## 2. Estado Atual do MVP

### ✅ O que funciona hoje

| Funcionalidade | Implementação | Observação |
|---------------|---------------|------------|
| **Criar agendamento** | `POST /agendamentos` | Status vai direto para `confirmado`, pulando `pendente` intencionalmente no MVP |
| **Cancelar pelo cliente** | `DELETE /agendamentos/{id}` | Grava `cancelado_cliente` + `HistoricoAgendamento` |
| **Snapshot de preço** | `ItemAgendamento` | `nome_servico`, `preco_praticado` e `tempo_execucao_minutos` preservados no momento do agendamento |
| **Auditoria de status** | `HistoricoAgendamento` | Cada mudança de status grava `status_anterior`, `status_novo`, `alterado_por_id` |
| **Listagem do cliente** | `GET /agendamentos` | Eager load de `profissional.estabelecimento` e `itens` |
| **Confirmar agendamento** | `PUT /profissional/agendamentos/{id}/status` | Profissional confirma via painel — `pendente → confirmado` |
| **Recusar agendamento** | `PUT /profissional/agendamentos/{id}/status` | Profissional recusa — `pendente → cancelado_profissional` |
| **Iniciar atendimento** | `PUT /profissional/agendamentos/{id}/status` | Profissional inicia — `confirmado → em_atendimento` |
| **Concluir atendimento** | `PUT /profissional/agendamentos/{id}/status` | Profissional encerra — `em_atendimento → concluido` |
| **Cancelar pelo profissional** | `PUT /profissional/agendamentos/{id}/status` | Profissional cancela — `confirmado → cancelado_profissional` |
| **Marcar não compareceu** | `PUT /profissional/agendamentos/{id}/status` | Profissional marca — `confirmado → nao_compareceu` |
| **Transições validadas** | `AgendamentoService::alterarStatus()` | Matriz de transições válidas impede saltos de status ilegais |
| **Painel do profissional** | `GET /profissional/agendamentos` | Lista com filtro por status + ações contextuais por card |
| **Disponibilidade real** | `AgendamentoService::verificarDisponibilidade()` | Valida: horário de funcionamento + bloqueios pontuais + bloqueios recorrentes + conflitos de agenda |

### ⚠️ Simplificações do MVP (a evoluir)

| Situação | MVP atual | Comportamento futuro planejado |
|----------|-----------|-------------------------------|
| Status inicial | Criação vai direto para `confirmado` | `pendente` até profissional aceitar manualmente (infraestrutura já pronta no painel) |
| `nao_compareceu` automático | Só via ação manual do profissional no painel | Job/queue que marca automaticamente N horas após a data passar |
| Notificações | Não implementadas | Lembretes por push/email/SMS ao cliente e profissional |
| Seleção de profissional no agendamento público | O `serviço_profissional_id` é inferido automaticamente (primeiro disponível) | Cliente escolhe explicitamente o profissional antes de selecionar o slot (Step 0 no Drawer) |

### ❌ Ainda não implementado

- **Seleção guiada de profissional** — Step 0 no `ServicoItem.vue` (Drawer) para o cliente escolher o profissional quando o serviço tiver múltiplos prestadores
- **Transição automática de status** — job/queue que marca `nao_compareceu` automaticamente quando data passa sem ação
- **Notificações** — lembretes por push/email/SMS ao cliente e profissional

---

## 3. Regra de Exibição na Tela "Meus Agendamentos"

### Lógica de abas (implementada)

```
Aba "Próximos"
  → status IN (pendente, confirmado)
  → data_hora_inicio > agora

Aba "Histórico"
  → status IN (concluido, cancelado_cliente, cancelado_profissional, nao_compareceu)
  OU status IN (pendente, confirmado, em_atendimento) COM data_hora_inicio ≤ agora
     ↳ (registros "expirados" — datas passadas sem transição formal de status,
         comum no MVP onde não há job automático de atualização)
```

### Por que agendamentos "confirmados" aparecem no Histórico?

Os seeders criaram agendamentos com datas passadas (ex: `2026-03-01`) e status `confirmado`. No MVP, **não há job automático** que atualize esses registros para `concluido` ou `nao_compareceu` quando a data passa. Por isso, esses registros ficam em um estado "expirado" — semanticamente são histórico, mesmo com status ainda como `confirmado`. A aba "Histórico" os captura pela data, não apenas pelo status.

### Labels e cores por status

> Implementado em `AgendamentoCard.vue` (visão do cliente) e `AgendamentosProfissional.vue` (visão do profissional).

| Status | Label exibido | Cor semântica | Componente |
|--------|--------------|---------------|------------|
| `pendente` | Pendente | Amarelo (warning) | Ambos |
| `confirmado` | Confirmado | Verde (success) | Ambos |
| `em_atendimento` | Em Andamento | Azul (info) | Ambos |
| `concluido` | Concluído | Cinza (secondary) | Ambos |
| `cancelado_cliente` | Cancelado | Vermelho (danger) | Ambos |
| `cancelado_profissional` | Cancelado pelo Profissional | Vermelho (danger) | Ambos |
| `nao_compareceu` | Não Compareceu | Laranja (warn) | Ambos |

---

## 4. Tabelas Envolvidas

```
agendamentos
  ├── itens_agendamentos        (snapshot de preço e tempo por serviço)
  ├── historico_agendamentos    (auditoria de cada mudança de status)
  └── avaliacoes                (pós-serviço — pós-MVP)
```

Relacionamentos para exibição ao **cliente** (`AgendamentoCard.vue`):
```
agendamentos.profissional_id
  → profissionais.estabelecimento_id
    → estabelecimentos.nome_fantasia / foto_capa_url / logradouro...
agendamentos.itens
  → itens_agendamentos.nome_servico / preco_praticado
```

Relacionamentos para exibição ao **profissional** (`AgendamentosProfissional.vue` e `Dashboard.vue`):
```
agendamentos.cliente_id
  → usuarios.nome_completo
agendamentos.itens
  → itens_agendamentos.nome_servico / preco_praticado / tempo_execucao_minutos
agendamentos.valor_total   → usado nos cards de receita do Dashboard
```

Relacionamentos para **verificação de disponibilidade** (`AgendamentoService::verificarDisponibilidade()`):
```
agendamentos.profissional_id
  → horarios_funcionamento.dia_semana / hora_inicio / hora_fim
  → bloqueios_agenda (pontuais: data_inicio/data_fim)
  → bloqueios_agenda (recorrentes: hora_inicio/hora_fim/dias_semana)
  → agendamentos existentes (conflito de slot: status IN pendente/confirmado/em_atendimento)
```
