# Task 3 — Arquitetura e Estado

- [ ] **Status geral: task concluída** (marcar quando todos os itens abaixo estiverem `[x]` ou `[-]`)

> **Prioridade:** 🟡 Média/Alta — `3.1` e `3.4` entram na Semana 1 junto com a [Task 1](seguranca-e-integridade.md); `3.2` e `3.3` na Semana 2.
> **Por quê (Lean):** dados errados no Dashboard/Financeiro geram aprendizado errado; ausência de testes torna cada ajuste pós-feedback arriscado.
> **Protocolo:** ver [README.md](README.md#-protocolo-de-execução-para-agentes-de-ia). Fazer as perguntas-chave antes de implementar.

---

## 3.1 Models inteiros expostos como props do Inertia

- [ ] **Item concluído**

**Problema**
- Páginas públicas recebem os models Eloquent completos. Ex.: `EstabelecimentoDetail` recebe `profissionais` com `usuario_id`, `bloqueiosAgenda` com `motivo`, timestamps, `deleted_at`; `Estabelecimentos` recebe toda a árvore de profissionais/serviços. Tudo fica visível no HTML (`data-page`).
- Origem: [EstabelecimentoService.php](../../app/Services/EstabelecimentoService.php) e [EstabelecimentoController.php](../../app/Http/Controllers/EstabelecimentoController.php).

**Sugestão de ajuste**
- Criar **API Resources** (`EstabelecimentoPublicoResource`, `ServicoPublicoResource` etc.) com apenas os campos usados pela UI, ou `select`/`only` explícitos.
- Bloqueios na página pública: enviar só intervalos (`data_inicio`, `data_fim`, `hora_inicio`, `hora_fim`, `dias_semana`, `recorrente`), sem `motivo`/`tipo`.
- Revisar também o shared prop `auth` (hoje ok) e as props das páginas do painel.

**Perguntas-chave**
1. API Resources (Recomendado, padrão Laravel) ou arrays montados no Service?
2. Escopo: só páginas públicas agora (Recomendado) ou também painel do profissional?
3. Unificar com a prop de intervalos ocupados do item 2.1 de [fluxo-de-reserva-ux.md](fluxo-de-reserva-ux.md) num único payload de "agenda pública"?

**Critérios de aceite**
- [ ] Props públicas não contêm `usuario_id`, `motivo`, `deleted_at`, `created_at`/`updated_at` desnecessários nem dados de outros usuários.
- [ ] Páginas públicas renderizam igual ao comportamento anterior.

**Como testar**
- Manual: ver código-fonte de `/agendar/{slug}` e inspecionar o JSON em `data-page`.
- Teste de feature com `assertInertia` verificando ausência das chaves sensíveis.

**Registro de execução**
> _(preencher)_

---

## 3.2 Agendamentos passados nunca saem de "confirmado"

- [ ] **Item concluído**

**Problema**
- O financeiro "realizado" depende do profissional clicar **Iniciar → Concluir** em cada atendimento. Na prática isso não acontece no dia a dia de um salão.
- Resultado: agendamentos passados ficam `confirmado` indefinidamente, o Financeiro mostra receita "prevista" errada e o Dashboard fica poluído. A aba "Histórico" do cliente já precisa de gambiarra para tratar registros "expirados" (ver PROJETO.md §5.5).

**Sugestão de ajuste**
- Permitir transição direta `confirmado → concluido` no `AgendamentoService::$transicoesValidas` (o passo `em_atendimento` vira opcional).
- Opção A: comando agendado (`routes/console.php`, `Schedule`) que marca como `concluido` agendamentos `confirmado` com `data_hora_fim` passada há X horas, gravando `HistoricoAgendamento` com autor "sistema".
- Opção B: bloco "Pendentes de baixa" no Dashboard com ações em lote (Concluído / Não compareceu).
- Recomendado: A + ação "Não compareceu" ainda disponível por N dias após a conclusão automática? → decidir nas perguntas (implica permitir `concluido → nao_compareceu`, que hoje é terminal).

**Perguntas-chave**
1. Fechamento automático por job (A), lista de pendentes com ação em lote (B), ou ambos? (Recomendado: A)
2. Após quantas horas do fim do atendimento concluir automaticamente? (Recomendado: 2h)
3. Permitir corrigir para `nao_compareceu` depois da conclusão automática? (altera a regra de status terminal documentada)
4. Autor do histórico automático: `alterado_por_id` nulo (exige migration) ou um usuário "sistema" fixo? (Recomendado: usuário sistema via seeder, sem migration)

**Critérios de aceite**
- [ ] Transição `confirmado → concluido` permitida.
- [ ] Agendamentos passados não ficam presos em `confirmado` (job ou ação em lote).
- [ ] Histórico registrado para toda mudança automática.
- [ ] PROJETO.md §5 e SKILL de schema atualizados com as novas regras.

**Como testar**
- Teste de feature com `Carbon::setTestNow()`: rodar o comando e conferir status + histórico.
- Manual: `php artisan schedule:run` (com permissão) e verificar Dashboard/Financeiro.

**Registro de execução**
> _(preencher)_

---

## 3.3 Modelo suporta N profissionais, fluxo suporta 1

- [ ] **Item concluído**

**Problema**
- O schema permite vários profissionais por estabelecimento, mas o registro cria **um profissional por usuário** e não existe convite de equipe. A página pública agrega serviços de todos os profissionais e o `profissional_id` é inferido.
- O roadmap atual ("Fase Atual: Polimento do Fluxo do Cliente") prioriza a "seleção guiada de profissional" — trabalho para um cenário que o produto ainda nem permite criar.

**Sugestão de ajuste**
- **Decisão de produto:** assumir oficialmente "**1 estabelecimento = 1 profissional**" no MVP.
- Atualizar `.agents/docs/PROJETO.md`: mover "Seleção guiada de profissional" para pós-MVP e registrar a decisão na seção 8 (Decisões Técnicas).
- Simplificar o código onde a multiplicidade gera ambiguidade (ex.: `horariosFuncionamento` agregado no `EstabelecimentoController::renderDetalhe`), sem remover colunas/tabelas.
- Se os primeiros clientes pedirem equipe → aprendizado validado → nova task.

**Perguntas-chave**
1. Confirma a decisão "1 estabelecimento = 1 profissional" para o MVP?
2. Simplificar o código agora ou apenas documentar a decisão e não mexer? (Recomendado: documentar + simplificar só o que gera bug)
3. Exibir o nome do profissional na página pública (ex.: "Atendimento com Ana") ou só o estabelecimento?

**Critérios de aceite**
- [ ] Decisão registrada em PROJETO.md (seções 1, 6, 7 e 8).
- [ ] Roadmap atualizado (seleção de profissional fora da fase atual).
- [ ] (Se escolhido) simplificações aplicadas sem regressão no fluxo público.

**Como testar**
- Revisão do documento + fluxo manual de reserva pela página pública após qualquer simplificação.

**Registro de execução**
> _(preencher)_

---

## 3.4 Ausência de testes automatizados

- [ ] **Item concluído**

**Problema**
- Só existem `tests/Feature/ExampleTest.php` e `tests/Unit/ExampleTest.php`. As regras críticas (disponibilidade, transições, isolamento entre tenants, snapshot de preço) não têm cobertura.
- Num MVP, os ajustes pós-feedback serão rápidos e frequentes — sem testes, cada ajuste pode quebrar a reserva sem ninguém perceber.

**Sugestão de ajuste**
- Suíte enxuta de **10–15 testes de feature** (PHPUnit, já configurado), com `RefreshDatabase` e factories mínimas (`Estabelecimento`, `Profissional`, `ServicoProfissional`, `HorarioFuncionamento`, `Agendamento`):
  - `verificarDisponibilidade`: fora do expediente, dia sem atendimento, bloqueio pontual, bloqueio recorrente, conflito, slot válido.
  - Criação de agendamento com preço/tempo vindos do banco (item 1.1).
  - Transições de status válidas/inválidas.
  - Isolamento: profissional A não altera/visualiza agendamento do B; busca de clientes (item 1.2).
  - Cliente não cancela agendamento de outro cliente.
- Banco de teste: SQLite em memória ou MySQL dedicado (verificar compatibilidade de enums/JSON).

**Perguntas-chave**
1. Banco de teste: SQLite em memória (rápido) ou MySQL dedicado no Docker (fiel à produção)? (Recomendado: MySQL `testing` no container existente, se enums/JSON derem problema no SQLite)
2. PHPUnit (já instalado, Recomendado) ou migrar para Pest (nova dependência)?
3. Escrever os testes antes (TDD) ou junto de cada item da Task 1? (Recomendado: junto de cada item; este item cobre o restante)
4. Incluir testes de front (Vitest) agora? (Recomendado: não)

**Critérios de aceite**
- [ ] Factories mínimas criadas.
- [ ] Cenários listados acima cobertos e passando.
- [ ] Comando de execução documentado (ex.: `docker compose exec php-fpm php artisan test`).

**Como testar**
- `docker compose exec php-fpm php artisan test` → todos verdes.

**Registro de execução**
> _(preencher)_
