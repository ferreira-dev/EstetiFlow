# Task 1 — Segurança e Integridade dos Dados

- [ ] **Status geral: task concluída** (marcar quando todos os itens abaixo estiverem `[x]` ou `[-]`)

> **Prioridade:** 🔴 Bloqueadora — nenhum cliente real antes destes itens.
> **Por quê (Lean):** um MVP precisa ser *viável*. Preço adulterado, reserva dupla ou vazamento de dados fazem o profissional abandonar o produto pela falha, não pela proposta — o experimento fica inválido.
> **Protocolo:** ver [README.md](README.md#-protocolo-de-execução-para-agentes-de-ia). Fazer as perguntas-chave antes de implementar.

---

## 1.1 Preço, duração e nome do serviço vêm do navegador

- [ ] **Item concluído**

**Problema**
- Em [AgendamentoController.php](../../app/Http/Controllers/AgendamentoController.php) (`store`), os campos `preco`, `tempo_execucao_minutos` e `nome_servico` são aceitos do request e gravados como snapshot.
- Qualquer cliente, via DevTools, reserva um serviço de R$ 150 por R$ 0,01 ou com 1 minuto de duração (ocupando a agenda de forma incorreta).
- O backend não valida se o `servico_id` pertence ao `profissional_id`, se o vínculo está `ativo`, se o profissional `aceita_agendamentos`, nem se o estabelecimento está `ativo` e com `agenda_online = 1` (essa checagem existe só no front, em `ServicoItem.vue`).
- O mesmo padrão existe em `criarAgendamento` de [AgendamentoProfissionalController.php](../../app/Http/Controllers/Profissional/AgendamentoProfissionalController.php).

**Sugestão de ajuste**
- O request passa a enviar apenas `servico_profissional_id` + `data_hora_inicio`.
- O `AgendamentoService::criar` busca `ServicoProfissional` (com `servico`, `profissional.estabelecimento`) e deriva `profissional_id`, `preco`, `tempo_execucao_minutos` e `nome_servico` do banco.
- Validar no backend: vínculo ativo, profissional aceita agendamentos, estabelecimento ativo e `agenda_online` (apenas no fluxo do cliente; o profissional pode agendar manualmente mesmo com agenda online desligada).
- Ajustar `ServicoItem.vue` e `NovoAgendamentoDialog.vue` para enviar o novo payload (a página pública precisa expor o `id` do `servicos_profissionais`).

**Perguntas-chave**
1. Com `agenda_online` desligada, o profissional ainda pode criar agendamentos manuais pelo painel? (Recomendado: sim)
2. A validação de pertencimento deve viver num **Form Request** dedicado ou continuar inline no controller? (Recomendado: Form Request `CriarAgendamentoRequest`, reutilizável nos dois fluxos)
3. O profissional pode ajustar o preço manualmente no agendamento do painel (desconto/cortesia)? (Recomendado: não no MVP — sempre preço do cadastro)
4. Mensagem ao cliente quando o serviço ficou indisponível entre abrir a página e confirmar: toast genérico ou mensagem específica?

**Critérios de aceite**
- [ ] Payload de criação (cliente e profissional) não contém mais `preco`, `tempo_execucao_minutos`, `nome_servico`.
- [ ] Preço/tempo/nome do snapshot em `itens_agendamentos` vêm sempre de `servicos_profissionais`/`servicos`.
- [ ] Requisição com `servico_profissional_id` de outro profissional, inativo, ou estabelecimento com `agenda_online = 0` é rejeitada (fluxo cliente).
- [ ] Front atualizado e fluxo de reserva funcionando ponta a ponta.

**Como testar**
- Teste de feature: POST `/agendamentos` com payload adulterado (`preco: 0.01`) → snapshot grava o preço real.
- Teste de feature: `servico_profissional_id` inativo / estabelecimento com agenda off → erro de validação.
- Manual: reservar pela página pública e conferir `valor_total` e `itens_agendamentos` no banco.

**Registro de execução**
> _(preencher: data · respostas das perguntas · decisões)_

---

## 1.2 Vazamento de dados de clientes entre estabelecimentos (LGPD)

- [ ] **Item concluído**

**Problema**
- `buscarCliente` em [AgendamentoProfissionalController.php](../../app/Http/Controllers/Profissional/AgendamentoProfissionalController.php) busca em **todos os usuários `cliente` da plataforma** por qualquer trecho (≥ 3 caracteres) de e-mail ou telefone.
- Retorna nome, e-mail, telefone e data de nascimento. Qualquer profissional cadastrado consegue enumerar a base de clientes dos concorrentes — violação de isolamento multi-tenant e da LGPD.

**Sugestão de ajuste**
- Opção A (Recomendada): a busca parcial retorna apenas clientes que **já têm agendamento com aquele profissional/estabelecimento**.
- Opção B: para clientes fora da carteira, só retornar com **match exato** de telefone (normalizado) ou e-mail, e com dados mascarados (ex.: `ma***@gmail.com`, sem data de nascimento).
- Normalizar telefone (apenas dígitos) ao salvar e ao buscar, para o match exato funcionar.
- Aplicar `throttle` na rota.

**Perguntas-chave**
1. Busca restrita à carteira do profissional (A), carteira + match exato global (A+B), ou só match exato? (Recomendado: A+B)
2. No resultado fora da carteira, quais dados exibir? (Recomendado: só nome + telefone mascarado)
3. Normalizar telefones já existentes exige ajuste de dados — fazer via migration de dados ou comando artisan? (Ambos exigem permissão para rodar)
4. Limite de throttle aceitável na busca? (Recomendado: 30 req/min)

**Critérios de aceite**
- [ ] Profissional A não encontra, por busca parcial, cliente que só agendou com profissional B.
- [ ] Data de nascimento não é exposta fora da carteira do profissional.
- [ ] Telefones normalizados na gravação e na busca.
- [ ] Rota com `throttle`.

**Como testar**
- Teste de feature com dois profissionais e clientes distintos: busca do profissional A não retorna clientes do B.
- Manual: no `NovoAgendamentoDialog`, buscar trecho de e-mail de cliente de outro estabelecimento → sem resultado.

**Registro de execução**
> _(preencher)_

---

## 1.3 Sequestro de conta via pré-cadastro

- [ ] **Item concluído**

**Problema**
- Em [RegistroController.php](../../app/Http/Controllers/Auth/RegistroController.php), se alguém se registra informando o **telefone** de um cliente pré-cadastrado (sem senha), é redirecionado para `completar-cadastro` e define e-mail + senha — assumindo a conta, o histórico e os dados pessoais de outra pessoa.
- O token de [CompletarCadastroController.php](../../app/Http/Controllers/Auth/CompletarCadastroController.php) é `encrypt($user->id)`: sem expiração, sem uso único, não revogável.
- O `LoginController` também gera esse token para qualquer e-mail sem senha.

**Sugestão de ajuste**
- Remover a detecção automática por telefone no registro público (ou exigir prova de posse do telefone, ex.: código via WhatsApp/SMS — mais caro).
- Opção Lean (Recomendada): o **profissional** gera o link de "completar cadastro" no painel e o envia ao cliente pelo WhatsApp (`wa.me` com mensagem pronta). O token passa a ser aleatório (`Str::random(64)`), armazenado com hash, com expiração (ex.: 7 dias) e uso único.
- Se o cliente se registrar sozinho com o mesmo telefone, criar conta nova; a fusão de contas fica para depois (ver [nao-fazer-agora.md](nao-fazer-agora.md)).

**Perguntas-chave**
1. Fluxo de vínculo: link gerado pelo profissional e enviado por WhatsApp (Recomendado), verificação por código SMS/WhatsApp (exige provedor pago), ou remover o pré-cadastro do fluxo público?
2. Onde guardar o token: nova tabela `convites_cadastro` ou colunas em `usuarios`? (Exige migration — rodar só com permissão) (Recomendado: tabela própria)
3. Tempo de expiração do link? (Recomendado: 7 dias)
4. Registro público com telefone já pré-cadastrado: criar conta nova silenciosamente ou avisar "peça o link ao seu profissional"?

**Critérios de aceite**
- [ ] Não é possível obter o link de completar cadastro de outra pessoa informando apenas telefone ou e-mail.
- [ ] Token aleatório, com expiração e uso único.
- [ ] Profissional consegue gerar/compartilhar o link a partir do painel.
- [ ] Schema doc atualizado se houver nova tabela/coluna.

**Como testar**
- Teste de feature: registro com telefone de pré-cadastrado não redireciona para `completar-cadastro`.
- Teste de feature: token expirado/usado → rejeitado.
- Manual: gerar link no painel, abrir em aba anônima, completar cadastro, tentar reutilizar o link.

**Registro de execução**
> _(preencher)_

---

## 1.4 Reserva dupla por condição de corrida

- [ ] **Item concluído**

**Problema**
- `AgendamentoService::criar` ([AgendamentoService.php](../../app/Services/AgendamentoService.php)) checa disponibilidade e depois insere dentro de `DB::transaction`, mas **sem lock**. Duas requisições simultâneas para o mesmo horário passam na checagem e ambas são gravadas.

**Sugestão de ajuste**
- Dentro da transação, travar a linha do profissional antes da checagem: `Profissional::whereKey($id)->lockForUpdate()->first()`. Serializa a criação de agendamentos por profissional sem bloquear os demais.
- Alternativa descartada para o MVP: constraint única no banco (não cobre sobreposição parcial de intervalos).

**Perguntas-chave**
1. Lock pessimista na linha do profissional (Recomendado) ou lock nomeado (`GET_LOCK` do MySQL / `Cache::lock` no Redis)?
2. Mensagem ao cliente que "perdeu" a corrida: a mesma de conflito atual ou uma específica ("Esse horário acabou de ser reservado")?
3. Aplicar o mesmo lock na alteração de status/cancelamento? (Recomendado: não necessário no MVP)

**Critérios de aceite**
- [ ] Checagem de disponibilidade + insert ocorrem sob lock por profissional.
- [ ] Teste comprova que o segundo agendamento sobreposto é rejeitado.

**Como testar**
- Teste de feature: criar dois agendamentos sobrepostos em sequência → o segundo falha (cobertura da lógica).
- Manual (opcional): disparar duas requisições simultâneas (ex.: `curl` em paralelo com mesma sessão) e conferir que só uma é gravada.

**Registro de execução**
> _(preencher)_

---

## 1.5 Fuso horário e idioma

- [ ] **Item concluído**

**Problema**
- `.env.example` traz `APP_TIMEZONE=UTC` e `APP_LOCALE=en`.
- O front envia a hora **local** (`yyyy-MM-dd HH:mm:ss`) e o backend interpreta como UTC e compara com `now()` em UTC. Com o `.env` igual ao exemplo:
  - reservas para as próximas ~3h do dia são recusadas por `after:now`;
  - o "hoje" do Dashboard/Financeiro vira o dia seguinte a partir das 21h;
  - mensagens de validação aparecem em inglês.

**Sugestão de ajuste**
- Definir `APP_TIMEZONE=America/Sao_Paulo` e `APP_LOCALE=pt_BR` (**o agente não edita `.env`** — sugerir ao usuário e atualizar apenas `.env.example`).
- Adicionar traduções PT-BR das mensagens de validação (`lang/pt_BR/validation.php`; avaliar pacote `lucascudo/laravel-pt-br-localization` — instalar só com confirmação, pois altera `composer.lock`).
- Conferir o `timezone` da conexão MySQL em `config/database.php` para consistência.

**Perguntas-chave**
1. Fuso fixo `America/Sao_Paulo` para todo o sistema (Recomendado no MVP) ou fuso por estabelecimento (coluna nova)?
2. Traduções: pacote pronto via composer ou arquivo `lang/pt_BR` escrito manualmente? (Recomendado: pacote, com confirmação)
3. Ajustar dados de agendamentos já gravados em UTC? (Recomendado: não — ambiente ainda sem clientes reais; usar `migrate:fresh --seed` com permissão)

**Critérios de aceite**
- [ ] `.env.example` atualizado; usuário orientado sobre o `.env`.
- [ ] Reserva para daqui a 1h é aceita.
- [ ] Dashboard "hoje" correto após 21h.
- [ ] Mensagens de validação em PT-BR.

**Como testar**
- Teste de feature com `Carbon::setTestNow()` às 20h30 de Brasília: reserva para 22h aceita; agenda do dia mostra o dia correto.
- Manual: submeter formulário vazio e conferir mensagens em PT-BR.

**Registro de execução**
> _(preencher)_

---

## 1.6 Recuperação de senha, throttle de login e usuário inativo

- [ ] **Item concluído**

**Problema**
- Não existe "Esqueci minha senha": cliente real que esquece a senha fica preso (e vira suporte para o profissional).
- `POST /login` não tem `throttle` (força bruta).
- A flag `usuarios.ativo` não é verificada no login.

**Sugestão de ajuste**
- Usar o broker nativo do Laravel (`Password::sendResetLink` / `Password::reset`) com duas telas Inertia (`EsqueciSenha.vue`, `RedefinirSenha.vue`) no `AuthLayout`, e link no `Login.vue`.
- Customizar a notificação de reset em PT-BR (com a marca EstetiFlow).
- `throttle:5,1` no POST de login e de reset.
- Bloquear login de `ativo = 0` com mensagem neutra.
- Depende de envio de e-mail funcional (Mailhog em dev; SMTP real em produção — ver [infra-e-lgpd.md](infra-e-lgpd.md)).

**Perguntas-chave**
1. Reset via e-mail com link (Recomendado, nativo) ou código numérico?
2. Pré-cadastrados sem e-mail: tratar no reset ou deixar exclusivamente no fluxo do item 1.3? (Recomendado: fluxo 1.3)
3. Limite de tentativas no login? (Recomendado: 5/min por e-mail+IP)
4. Layout das novas telas: reaproveitar visual do `Login.vue` integralmente?

**Critérios de aceite**
- [ ] Fluxo completo de reset funcionando (solicitar → e-mail → redefinir → login).
- [ ] E-mail de reset em PT-BR.
- [ ] Login com throttle; usuário inativo não autentica.

**Como testar**
- Teste de feature: `Notification::fake()` + solicitar reset → notificação enviada; redefinir com token válido → senha alterada.
- Manual: fluxo completo com Mailhog (`http://localhost:8025` ou porta do `docker-compose.yml`).
- Manual: 6 tentativas de login erradas → bloqueio temporário.

**Registro de execução**
> _(preencher)_
