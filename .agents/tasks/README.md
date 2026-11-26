# Tasks — Prontidão do MVP para Produção

> Origem: análise de prontidão do MVP sob a ótica **Lean Startup** (2026-10-03).
> Objetivo: deixar o EstetiFlow **minimamente pronto** para receber os primeiros clientes reais, sem construir funcionalidades além do necessário para validar a hipótese principal.

## Hipótese a validar

> *"Profissionais de estética usam um link de agendamento online e os clientes deles reservam por ali."*

**Foco estratégico:** o cliente pagante é o **profissional**; o produto é o **link `/agendar/{slug}`** divulgado no Instagram/WhatsApp. Marketplace/busca/Home são secundários.

## Veredito da análise

**Ainda não está apto para produção.** O núcleo de valor existe, mas há bloqueadores de segurança/integridade, falhas de conversão no fluxo de reserva e ausência de infra mínima. Estimativa: **1–2 semanas**.

---

## Índice

| Ordem | Arquivo | Escopo | Status |
|-------|---------|--------|--------|
| 1 | [seguranca-e-integridade.md](seguranca-e-integridade.md) | Bloqueadores: preço adulterável, vazamento entre tenants, sequestro de conta, reserva dupla, timezone, recuperação de senha | [x] |
| 2 | [fluxo-de-reserva-ux.md](fluxo-de-reserva-ux.md) | Slots ocupados, login no meio da reserva, notificações, conteúdo falso, onboarding do profissional | [ ] |
| 3 | [arquitetura-e-estado.md](arquitetura-e-estado.md) | Props expostas, status que não fecham, 1 profissional por estabelecimento, testes | [ ] |
| 4 | [infra-e-lgpd.md](infra-e-lgpd.md) | Ambiente de produção, backups, monitoramento, seeder de roles, termos/privacidade | [ ] |
| 5 | [metricas-e-validacao.md](metricas-e-validacao.md) | O "Medir" do Lean: métricas, piloto com conhecidos, roteiro de testes | [ ] |
| — | [nao-fazer-agora.md](nao-fazer-agora.md) | Lista de funcionalidades **congeladas** até haver aprendizado validado | referência |

### Ordem sugerida

1. **Semana 1 — Segurança:** task 1 inteira + `3.1` + `3.4` (testes).
2. **Semana 2 — Conversão e operação:** task 2 + `3.2` + task 4 + task 5 (instrumentação).
3. **Depois:** piloto com 3–5 profissionais por 2–4 semanas (task 5, seção "Piloto").

---

## 🤖 Protocolo de execução (para agentes de IA)

Quando o usuário pedir algo como **"execute a task `nome-da-task.md`"** (ou um item específico, ex.: "execute o item 1.3 de seguranca-e-integridade.md"):

### 1. Ler
- Ler o arquivo da task **inteiro**, além de `.agents/docs/PROJETO.md`, `.agents/rules/project-standards.md` e, se envolver banco, `.agents/skills/saas-database-schema/SKILL.md`.
- Ler os arquivos de código citados no item **antes** de propor qualquer coisa — o código pode ter mudado desde a análise. Se o problema já não existir, informar o usuário e marcar como `concluído (já resolvido)`.

### 2. Confirmar a abordagem — **obrigatório**
- Antes de escrever código, fazer **3 ou 4 perguntas-chave** ao usuário (via `AskUserQuestion`), partindo da seção **"Perguntas-chave"** do item.
- As perguntas devem cobrir as decisões que mudam a implementação: UX/UI, arquitetura, libs/dependências, regras de negócio, migrations.
- Sempre oferecer uma opção marcada como **(Recomendado)**, coerente com a filosofia Lean (menor solução que resolve o problema).
- Se a task tiver vários itens, agrupar no máximo 4 perguntas por rodada, item a item.

### 3. Executar
- Implementar apenas o escopo do item. Não puxar nada de [nao-fazer-agora.md](nao-fazer-agora.md).
- Respeitar as restrições do projeto: sem rodar migrations, sem `npm`/`composer` que alterem lock files, sem editar `.env` (sugerir a variável), sem commit/push — tudo isso **exige permissão explícita**.

### 4. Validar
- Executar os passos da seção **"Como testar"** do item (testes automatizados quando existirem + verificação manual descrita).
- Reportar falhas com a saída real; nunca marcar como concluído algo que não passou.

### 5. Marcar como concluído
- Marcar `[x]` em cada **critério de aceite** atendido.
- Marcar `[x]` no checkbox **"Item concluído"** do item.
- Preencher o **"Registro de execução"** do item: data, respostas das perguntas-chave e decisões/desvios relevantes (1–4 linhas).
- Quando todos os itens do arquivo estiverem concluídos, marcar o **status geral** no topo do arquivo e na tabela do índice acima.
- Se uma decisão alterar arquitetura/fluxo documentado, atualizar `.agents/docs/PROJETO.md`.

### Legenda

- `[ ]` pendente · `[x]` concluído · `[-]` descartado pelo usuário (registrar motivo no Registro de execução)
