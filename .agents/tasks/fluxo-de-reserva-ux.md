# Task 2 — Fluxo de Reserva e UX (Conversão)

- [ ] **Status geral: task concluída** (marcar quando todos os itens abaixo estiverem `[x]` ou `[-]`)

> **Prioridade:** 🟠 Alta — afeta diretamente se o cliente final consegue reservar e se o profissional confia no sistema.
> **Por quê (Lean):** a métrica principal do MVP é "reservas online por estabelecimento". Cada atrito neste fluxo derruba essa métrica e contamina o aprendizado.
> **Protocolo:** ver [README.md](README.md#-protocolo-de-execução-para-agentes-de-ia). Fazer as perguntas-chave antes de implementar.

---

## 2.1 Horários disponíveis ignoram agendamentos existentes

- [ ] **Item concluído**

**Problema**
- [ServicoItem.vue](../../resources/js/Components/Features/ServicoItem.vue) gera os slots considerando apenas expediente e bloqueios. Horários já ocupados aparecem como livres.
- O cliente escolhe, clica em Confirmar e só então recebe "Já existe um agendamento" — a pior experiência no momento mais importante do funil.

**Sugestão de ajuste**
- Opção A (Recomendada no MVP): enviar na prop da página pública apenas os **intervalos ocupados** dos próximos N dias (`data_hora_inicio`, `data_hora_fim`, sem dados do cliente) e filtrar no front junto com os bloqueios.
- Opção B: endpoint/partial reload que calcula os slots no backend por data (`router.reload({ only: [...] , data: { data } })`), reaproveitando `verificarDisponibilidade` — fonte única de verdade, mas mais trabalho.
- Em ambos: ao receber erro de conflito, recarregar os slots e manter o drawer aberto.

**Perguntas-chave**
1. Slots calculados no front com intervalos ocupados (A, Recomendado) ou no backend por data (B)?
2. Janela de agendamento futura: quantos dias à frente permitir? (Recomendado: 30 ou 60 dias — hoje o picker considera 90)
3. Intervalo entre slots: manter 30 min fixo ou usar a duração do serviço / configurável pelo profissional? (Recomendado: manter 30 min)
4. Exibir slots ocupados desabilitados (cliente vê que está cheio) ou ocultá-los?

**Critérios de aceite**
- [ ] Horário ocupado não aparece como disponível na página pública.
- [ ] Nenhum dado pessoal de outros clientes é enviado ao front.
- [ ] Em caso de conflito, slots são recarregados sem fechar o drawer.

**Como testar**
- Seed/fixture com agendamento às 14h → página pública não oferece 14h (nem slots que se sobreponham, considerando a duração do serviço).
- Inspecionar a resposta Inertia (DevTools → Network) e confirmar ausência de nome/telefone de clientes.

**Registro de execução**
> _(preencher)_

---

## 2.2 Login obrigatório e perda de contexto na reserva

- [ ] **Item concluído**

**Problema**
- Fluxo atual: Reservar → dialog "Faça login" → `/login` → (talvez) `/registro` → redireciona para **Home**. O cliente perde a página do estabelecimento e o serviço escolhido.
- Para um link divulgado no Instagram, exigir criação de conta antes de reservar é o principal ponto de abandono.

**Sugestão de ajuste**
- Mínimo (obrigatório): salvar a URL de origem (`session()->put('url.intended', ...)` ou query `?redirect=`) e, após login/registro, voltar para `/agendar/{slug}` com o serviço reabrindo o drawer (ex.: query `?servico=ID`). Oferecer link "Criar conta" no próprio dialog.
- Ideal para MVP (avaliar): **reserva como convidado** com nome + telefone, reaproveitando a estrutura de pré-cadastro (usuário sem senha). Depende da solução segura do item 1.3 de [seguranca-e-integridade.md](seguranca-e-integridade.md).

**Perguntas-chave**
1. Manter login obrigatório com retorno ao contexto (Recomendado como primeiro passo) ou implementar já a reserva como convidado?
2. Registro do cliente: simplificar o formulário (nome, telefone, e-mail, senha) e esconder a escolha cliente/profissional quando vier de uma página de reserva?
3. Login/registro dentro do próprio Drawer (sem sair da página) ou páginas separadas com retorno?
4. Se reserva como convidado: o telefone vira identificador único do cliente por estabelecimento?

**Critérios de aceite**
- [ ] Após login ou registro iniciados a partir de uma reserva, o usuário volta para a página do estabelecimento com o drawer do serviço aberto.
- [ ] Dialog de login oferece também "Criar conta".
- [ ] (Se escolhido) reserva como convidado funcionando sem expor contas existentes.

**Como testar**
- Manual em aba anônima: abrir `/agendar/{slug}` → Reservar → criar conta → deve voltar ao drawer do serviço.
- Teste de feature: login com `url.intended` definida redireciona para a página do estabelecimento.

**Registro de execução**
> _(preencher)_

---

## 2.3 Ninguém é notificado de uma reserva

- [ ] **Item concluído**

**Problema**
- O profissional só descobre uma reserva nova se abrir o painel. O cliente não recebe confirmação nem lembrete.
- A documentação classifica notificações como pós-MVP, mas sem elas **o profissional não confia no sistema** e volta à agenda de papel/WhatsApp — o experimento falha por um motivo que não é a hipótese.

**Sugestão de ajuste (versão Lean)**
- E-mail via fila (Redis/queue já existem) com `Notification` do Laravel:
  - Profissional: nova reserva e cancelamento pelo cliente.
  - Cliente: confirmação da reserva e cancelamento pelo profissional.
- Alternativa/complemento sem custo: botão **"Enviar confirmação no WhatsApp"** no card do agendamento do profissional, abrindo `wa.me/55{telefone}` com mensagem pronta (data, hora, serviço, endereço).
- Lembrete automático (D-1) e push/SMS ficam fora do escopo (ver [nao-fazer-agora.md](nao-fazer-agora.md)).
- Registrar envios na tabela `notificacoes` já existente é opcional.

**Perguntas-chave**
1. Canal: e-mail (Recomendado), botão WhatsApp manual, ou ambos?
2. Envio síncrono ou via fila? (Recomendado: fila — exige worker em produção, ver [infra-e-lgpd.md](infra-e-lgpd.md))
3. Gravar histórico em `notificacoes` agora ou não? (Recomendado: não no MVP)
4. Template: Markdown mail padrão do Laravel com a cor do estabelecimento ou layout simples neutro?

**Critérios de aceite**
- [ ] Profissional recebe e-mail ao receber nova reserva e ao ter reserva cancelada pelo cliente.
- [ ] Cliente recebe e-mail de confirmação e de cancelamento pelo profissional.
- [ ] Textos em PT-BR com data/hora no fuso correto.
- [ ] (Se escolhido) botão WhatsApp com mensagem pronta no card do profissional.

**Como testar**
- Teste de feature com `Notification::fake()`: criar e cancelar reservas → notificações corretas para os destinatários corretos.
- Manual com Mailhog: reservar pela página pública e conferir os dois e-mails.

**Registro de execução**
> _(preencher)_

---

## 2.4 Conteúdo falso na página pública

- [ ] **Item concluído**

**Problema**
- Em [EstabelecimentoDetail.vue](../../resources/js/Pages/EstabelecimentoDetail.vue), `caracteristicas` (Wi-Fi Gratuito, Estacionamento, Cartão de Crédito…) é um array **fixo no código** exibido para todos os estabelecimentos — informação potencialmente falsa para clientes reais.
- Foto de capa usa fallback do Unsplash (também em `EstabelecimentoCard.vue`); não há upload de imagem.
- Todo serviço usa o ícone de tesoura em [ServicoItem.vue](../../resources/js/Components/Features/ServicoItem.vue), inclusive manicure, massagem etc.

**Sugestão de ajuste**
- Características: remover o bloco (Recomendado) ou torná-las configuráveis (lista fixa de opções marcáveis no painel — exige coluna JSON/migration).
- Capa: upload simples (disk `public` + `storage:link`, validação de tipo/tamanho) no `Profissional/Estabelecimento.vue`; sem foto → layout com a cor da marca + iniciais/nome, sem imagem genérica.
- Ícone do serviço: mapear pela `categoria` usando `categorias.js` (PrimeIcons apenas).

**Perguntas-chave**
1. Características: remover agora (Recomendado) ou tornar configuráveis?
2. Upload de capa entra neste MVP ou basta um layout bom sem imagem? (Recomendado: upload simples — a página é a vitrine do profissional)
3. Se upload: armazenar local (`storage/app/public`) ou S3-compatível desde já? (Recomendado: local + backup)
4. Upload também de logo/foto do profissional ou só capa? (Recomendado: só capa)

**Critérios de aceite**
- [ ] Nenhuma informação fixa/falsa exibida na página pública.
- [ ] Sem dependência de imagens externas do Unsplash.
- [ ] (Se escolhido) upload de capa funcionando, com validação.
- [ ] Ícone do serviço coerente com a categoria.

**Como testar**
- Manual: estabelecimento sem capa e sem características → página com aparência intencional, sem placeholders genéricos.
- Teste de feature (se upload): `Storage::fake('public')` + upload válido/inválido.

**Registro de execução**
> _(preencher)_

---

## 2.5 Onboarding do profissional e "Copiar meu link"

- [ ] **Item concluído**

**Problema**
- Logo após cadastrar o estabelecimento, ele já aparece em `/estabelecimentos` e o link público funciona, mesmo **sem serviços e sem horários** → página vazia para o cliente.
- Não há guia de primeiros passos; o profissional não sabe o que falta configurar.
- O link público — que é o produto — não tem destaque no painel.

**Sugestão de ajuste**
- Card de **checklist** no topo do Dashboard enquanto incompleto: Dados do estabelecimento → Serviços (≥ 1) → Horários (≥ 1 dia) → Compartilhar link.
- Botão **"Copiar meu link"** (e "Compartilhar no WhatsApp") em destaque no Dashboard e na tela do estabelecimento.
- Estabelecimento só listado/agendável quando tiver ≥ 1 serviço ativo e ≥ 1 horário (critério calculado via query/accessor, sem migration). Página pública de estabelecimento incompleto exibe "Em breve / agenda ainda não configurada" com contato.

**Perguntas-chave**
1. Critério de "publicável": ≥ 1 serviço ativo + ≥ 1 dia de horário é suficiente?
2. Checklist como card no Dashboard (Recomendado) ou wizard bloqueante após o registro?
3. Página pública de estabelecimento incompleto: 404, aviso "em breve" ou mostrar com reserva desabilitada? (Recomendado: aviso + botão WhatsApp)
4. Botões de compartilhamento: copiar link + WhatsApp bastam, ou incluir QR Code (nova lib)? (Recomendado: sem QR Code agora)

**Critérios de aceite**
- [ ] Checklist visível até o profissional completar os passos.
- [ ] Botão "Copiar meu link" com feedback (toast) e compartilhamento via WhatsApp.
- [ ] Estabelecimento incompleto não aparece na listagem nem aceita reservas.

**Como testar**
- Manual: registrar novo profissional do zero → seguir o checklist → conferir que o estabelecimento só aparece em `/estabelecimentos` ao final.
- Teste de feature: `EstabelecimentoService::listar` não retorna estabelecimento sem serviços/horários.

**Registro de execução**
> _(preencher)_
