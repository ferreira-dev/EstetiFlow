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
| **Auditoria de status** | `HistoricoAgendamento` | Ogni mudança de status grava `status_anterior`, `status_novo`, `alterado_por_id` |
| **Listagem do cliente** | `GET /agendamentos` | Eager load de `profissional.estabelecimento` e `itens` |

### ⚠️ Simplificações do MVP (a evoluir)

| Situação | MVP atual | Comportamento futuro planejado |
|----------|-----------|-------------------------------|
| Status inicial | Criação vai direto para `confirmado` | `pendente` até profissional aceitar manualmente no painel |
| `em_atendimento` | Não utilizado — sem painel do profissional | Profissional usa painel para dar início ao atendimento |
| `concluido` | Só nos seeders / demanda ação do profissional | Profissional encerra o atendimento pelo painel |
| `nao_compareceu` | Não utilizado | Profissional marca via painel ou job automático N horas após a data |
| `cancelado_profissional` | Não utilizado | Profissional cancela pelo painel (Dashboard futuro) |

### ❌ Ainda não implementado

- **Dashboard do profissional** — painel para confirmar, iniciar, encerrar e cancelar atendimentos
- **Transição automática de status** — job/queue que marca `nao_compareceu` automaticamente quando data passa
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

| Status | Label exibido | Cor semântica |
|--------|--------------|---------------|
| `pendente` | Pendente | Amarelo (warning) |
| `confirmado` | Confirmado | Verde (primary) |
| `em_atendimento` | Em Andamento | Azul (info) |
| `concluido` | Concluído | Verde-escuro (success) |
| `cancelado_cliente` | Cancelado | Vermelho (danger) |
| `cancelado_profissional` | Cancelado pelo Profissional | Vermelho (danger) |
| `nao_compareceu` | Não Compareceu | Laranja (warn) |

---

## 4. Tabelas Envolvidas

```
agendamentos
  ├── itens_agendamentos        (snapshot de preço e tempo por serviço)
  ├── historico_agendamentos    (auditoria de cada mudança de status)
  └── avaliacoes                (pós-serviço — pós-MVP)
```

Relacionamentos para exibição ao cliente:
```
agendamentos.profissional_id
  → profissionais.estabelecimento_id
    → estabelecimentos.nome_fantasia / foto_capa_url / logradouro...
agendamentos.itens
  → itens_agendamentos.nome_servico / preco_praticado
```
