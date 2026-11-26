# Task 5 — Métricas e Validação com Usuários Reais (o "Medir" do Lean)

- [ ] **Status geral: task concluída** (marcar quando todos os itens abaixo estiverem `[x]` ou `[-]`)

> **Prioridade:** 🟠 Alta — sem medição não existe ciclo **Construir → Medir → Aprender**; só "construir".
> **Por quê (Lean):** a decisão de *perseverar* ou *pivotar* depende de dados de uso real, não de opinião. Defina as métricas **antes** do primeiro cliente entrar.
> **Protocolo:** ver [README.md](README.md#-protocolo-de-execução-para-agentes-de-ia). Fazer as perguntas-chave antes de implementar.

---

## 5.1 Instrumentação das métricas-chave

- [ ] **Item concluído**

**Problema**
- Não há como saber, hoje, se o produto está sendo usado de verdade. O Dashboard mostra métricas **do profissional**, não **do produto**.

**Métricas propostas (todas deriváveis do banco atual)**

| Métrica | Tipo | Definição |
|---------|------|-----------|
| **Reservas online / estabelecimento / semana** | ⭐ North Star | Agendamentos criados pelo cliente (`historico_agendamentos.alterado_por_id = cliente_id` na criação) |
| Ativação | Funil | % de profissionais registrados que completaram o onboarding (estabelecimento + serviço + horário) |
| Online vs. manual | Adoção | % de agendamentos criados pelo cliente vs. pelo profissional no painel |
| Retenção semanal | Retenção | Profissionais com ≥ 1 agendamento (qualquer origem) na semana N e N+1 |
| Cancelamento / no-show | Qualidade | % `cancelado_*` e `nao_compareceu` sobre o total |

**Sugestão de ajuste**
- Opção A (Recomendada): página `/admin/metricas` protegida por `role:admin` (role já existe) com cards e uma tabela semanal, calculados por queries diretas.
- Opção B: apenas um comando artisan `metricas:semana` que imprime a tabela no terminal.
- Para diferenciar "online vs. manual" de forma confiável, considerar uma coluna `origem` (`online`/`painel`) em `agendamentos` (**migration exige permissão**) em vez de inferir pelo histórico.
- Opcional: analytics de página (Plausible/Umami/GA4) só na página pública `/agendar/{slug}` para medir visitas → reservas (taxa de conversão).

**Perguntas-chave**
1. Página admin (A, Recomendado) ou comando artisan (B)?
2. Adicionar coluna `origem` em `agendamentos` (migration) ou inferir pelo histórico?
3. Analytics de página: Plausible/Umami (privacidade, sem banner de cookies) ou GA4 (grátis, exige consentimento)? Ou nenhum por enquanto?
4. Quais metas definem sucesso do piloto? (Sugestão abaixo em 5.3)

**Critérios de aceite**
- [ ] As 5 métricas da tabela disponíveis (página ou comando).
- [ ] Recorte semanal e por estabelecimento.
- [ ] Acesso restrito a admin.
- [ ] (Se escolhido) analytics na página pública com taxa visita → reserva.

**Como testar**
- Com os dados do seeder, conferir manualmente 2–3 números contra consultas SQL diretas.
- Teste de feature: usuário não-admin recebe 403 em `/admin/metricas`.

**Registro de execução**
> _(preencher)_

---

## 5.2 Roteiro de testes com conhecidos (antes do piloto)

- [ ] **Item concluído**

**Objetivo:** encontrar falhas de uso **antes** de colocar o negócio de alguém em risco. Rodar com 2–3 pessoas próximas (não precisam ser da área), em sessão observada de ~30 min cada.

**Preparação**
- [ ] Ambiente de staging/produção com HTTPS e e-mail real (Task 4).
- [ ] Planilha simples para anotar: tarefa, concluiu? (sim/não/com ajuda), tempo, onde travou, frase dita pela pessoa.
- [ ] Regra do observador: **não ajudar, não explicar** — só anotar. Pedir que a pessoa pense em voz alta.

**Roteiro — Profissional (no celular e no desktop)**
1. Criar conta como profissional a partir da Home.
2. Cadastrar o estabelecimento e escolher a cor da marca.
3. Cadastrar 3 serviços com preços e durações diferentes.
4. Configurar horários da semana e um bloqueio (ex.: almoço recorrente).
5. Copiar o link público e enviá-lo para si mesmo no WhatsApp.
6. Criar um agendamento manual para um cliente novo (pré-cadastro).
7. Confirmar, concluir e marcar "não compareceu" em agendamentos diferentes; conferir Dashboard e Financeiro.

**Roteiro — Cliente (somente celular, a partir do link no WhatsApp)**
1. Abrir o link recebido e entender o que o estabelecimento oferece.
2. Reservar um serviço criando a conta no meio do caminho.
3. Tentar reservar um horário que outra pessoa acabou de ocupar.
4. Ver a reserva em "Meus agendamentos" e cancelá-la.
5. Fazer logout, "esquecer a senha" e recuperá-la.

**Critérios de saída (pronto para o piloto)**
- [ ] Todas as tarefas concluídas **sem ajuda** por ao menos 2 de 3 pessoas.
- [ ] Nenhum erro 500 ou dado incorreto (preço, horário, fuso) observado.
- [ ] Problemas encontrados registrados como novas tasks ou corrigidos.

**Perguntas-chave**
1. Quem serão os 2–3 testadores e em quais dispositivos (Android/iOS)?
2. Sessões presenciais, por chamada com compartilhamento de tela, ou não-moderadas (gravação)?
3. O agente deve gerar a planilha/formulário de anotação (ex.: `.agents/tasks/piloto/roteiro-observacao.md`)?

**Registro de execução**
> _(preencher: data das sessões · principais achados · tasks geradas)_

---

## 5.3 Piloto com os primeiros profissionais

- [ ] **Item concluído**

**Objetivo:** validar a hipótese com uso real por **3–5 profissionais** durante **2–4 semanas**.

**Seleção**
- Profissionais conhecidos ou indicados, que **já recebem agendamentos por WhatsApp/Instagram** (são os que mais sentem a dor).
- Preferir perfis diferentes (ex.: barbeiro, manicure, designer de sobrancelha) para comparar.

**Execução**
- Onboarding feito **junto** com o profissional (presencial ou chamada): observar onde trava.
- Combinar que ele coloque o link na bio do Instagram e na resposta automática do WhatsApp Business — sem isso, não há tráfego de clientes.
- Check-in semanal de 10–15 min com 3 perguntas fixas:
  1. "Quantos clientes agendaram pelo link esta semana? Algum reclamou?"
  2. "O que você ainda faz fora do sistema (papel/WhatsApp)? Por quê?"
  3. "Se o EstetiFlow sumisse amanhã, quão chateado você ficaria (nada / um pouco / muito)?"
- Registrar pedidos de funcionalidade **sem prometer** — só viram task se aparecerem em ≥ 2 profissionais ou bloquearem o uso.

**Metas sugeridas para decidir perseverar × pivotar** (ajustar na pergunta 4 do item 5.1)

| Sinal | Perseverar | Investigar / pivotar |
|-------|-----------|---------------------|
| Ativação (onboarding completo) | ≥ 80% dos convidados | < 50% |
| Reservas online por estabelecimento/semana (semana 3–4) | ≥ 5 | ≤ 1 |
| Retenção semanal dos profissionais | ≥ 60% ainda ativos na semana 4 | < 40% |
| "Ficaria muito chateado" | ≥ 40% | < 20% |

**Perguntas-chave**
1. Quem são os 3–5 profissionais candidatos e qual o perfil de cada um?
2. O piloto será gratuito? Haverá um preço "de fundador" a ser testado no fim (validar disposição a pagar)?
3. Duração: 2 ou 4 semanas?
4. Onde registrar o aprendizado semanal (ex.: `.agents/tasks/piloto/diario.md`)?

**Critérios de aceite**
- [ ] 3–5 profissionais ativos com o link divulgado.
- [ ] Check-ins semanais registrados.
- [ ] Ao final, decisão documentada: perseverar (e quais 1–3 próximas features) ou pivotar (e por quê).

**Registro de execução**
> _(preencher)_
