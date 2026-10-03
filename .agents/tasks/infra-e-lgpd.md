# Task 4 — Infraestrutura de Produção e LGPD

- [ ] **Status geral: task concluída** (marcar quando todos os itens abaixo estiverem `[x]` ou `[-]`)

> **Prioridade:** 🟠 Alta — pré-requisito para qualquer cliente real.
> **Por quê (Lean):** perder a agenda de um profissional (sem backup) ou não ver erros em produção (sem monitoramento) encerra o piloto antes de gerar aprendizado.
> **Protocolo:** ver [README.md](README.md#-protocolo-de-execução-para-agentes-de-ia). Fazer as perguntas-chave antes de implementar.
> ⚠️ Itens desta task envolvem `.env`, Docker e servidores: o agente **sugere** valores/comandos e só executa ações restritas com confirmação explícita (ver `project-standards.md` §5).

---

## 4.1 Ambiente de produção

- [ ] **Item concluído**

**Problema**
- O [docker-compose.yml](../../docker-compose.yml) é de desenvolvimento: container `node` roda `npm install && npm run dev`, Mailhog como servidor de e-mail, `.env.example` com `APP_DEBUG=true`/`APP_ENV=local`.
- Não há worker de fila nem scheduler (necessários para os itens 2.3 e 3.2), nem HTTPS, nem SMTP real.

**Sugestão de ajuste**
- `docker-compose.prod.yml` (ou Dockerfile multi-stage) com: build de assets (`npm run build`) na imagem, sem container node em runtime, sem Mailhog; serviços `queue` (`php artisan queue:work`) e `scheduler` (`php artisan schedule:work`).
- Checklist de `.env` de produção (sugerir, não editar): `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` com https, `APP_TIMEZONE=America/Sao_Paulo`, `APP_LOCALE=pt_BR`, `SESSION_SECURE_COOKIE=true`, `MAIL_*` de um SMTP real, `QUEUE_CONNECTION=redis`.
- Script de deploy: `composer install --no-dev --optimize-autoloader`, `php artisan migrate --force` (**manual/permissão**), `config:cache`, `route:cache`, `view:cache`, `storage:link`.
- HTTPS via proxy (Caddy/Traefik/Nginx + Let's Encrypt) ou plataforma gerenciada.

**Perguntas-chave**
1. Onde hospedar: VPS com Docker (Hetzner/DigitalOcean/Contabo), plataforma gerenciada (Laravel Forge/Ploi + VPS) ou PaaS? (Recomendado: VPS + Forge/Ploi ou Docker com Caddy)
2. Provedor de e-mail transacional: Resend, Amazon SES, Brevo ou Mailgun? (Recomendado: o com plano grátis suficiente para o piloto)
3. Domínio próprio já definido? Link público ficará como `dominio.com/agendar/{slug}`?
4. Deploy manual por script ou CI/CD (GitHub Actions) já agora? (Recomendado: script manual no piloto)

**Critérios de aceite**
- [ ] Compose/Dockerfile de produção criado e documentado.
- [ ] Worker de fila e scheduler rodando.
- [ ] Checklist de `.env` de produção documentado (sem segredos versionados).
- [ ] HTTPS ativo; e-mails reais entregues.

**Como testar**
- Subir o ambiente de produção em staging, fazer uma reserva real e confirmar: página via HTTPS, e-mail recebido em caixa real, job processado (`queue:work` logando).
- Forçar um erro e confirmar que `APP_DEBUG=false` não expõe stack trace.

**Registro de execução**
> _(preencher)_

---

## 4.2 Backup e monitoramento de erros

- [ ] **Item concluído**

**Problema**
- Não há rotina de backup do MySQL nem dos uploads; não há monitoramento de exceções. Em produção, uma falha silenciosa no fluxo de reserva passaria despercebida.

**Sugestão de ajuste**
- Backup diário do banco (e de `storage/app/public` se houver upload) com retenção de 7–14 dias, armazenado fora do servidor. Opções: `spatie/laravel-backup` (nova dependência, confirmar) ou `mysqldump` via cron + upload para storage S3-compatível.
- Monitoramento: Sentry (plano grátis) ou Flare; alternativamente log diário + alerta por e-mail.
- Healthcheck simples (`/up` já existe no Laravel 11) com monitor externo gratuito (UptimeRobot/BetterStack).

**Perguntas-chave**
1. Backup via `spatie/laravel-backup` (Recomendado) ou script `mysqldump` + cron?
2. Destino do backup: S3/Backblaze/Cloudflare R2 ou Google Drive?
3. Monitoramento: Sentry (Recomendado), Flare ou só logs?
4. Quem recebe os alertas (e-mail/Telegram)?

**Critérios de aceite**
- [ ] Backup automático diário funcionando, armazenado fora do servidor.
- [ ] **Restauração testada** ao menos uma vez.
- [ ] Exceções de produção chegando à ferramenta de monitoramento.
- [ ] Monitor de uptime configurado.

**Como testar**
- Rodar o backup manualmente, restaurar em banco limpo e conferir os dados.
- Lançar uma exceção de teste e confirmar o alerta.

**Registro de execução**
> _(preencher)_

---

## 4.3 Seeder de produção (roles) separado dos dados fictícios

- [ ] **Item concluído**

**Problema**
- [DatabaseSeeder.php](../../database/seeders/DatabaseSeeder.php) cria roles/permissions **e** usuários, estabelecimentos e agendamentos fictícios no mesmo fluxo. Em produção só as roles são necessárias — e o registro quebra sem elas (`assignRole`).

**Sugestão de ajuste**
- Extrair `RolesPermissionsSeeder` (idempotente: `firstOrCreate`) e `DemoSeeder` (dados fictícios).
- `DatabaseSeeder` chama `RolesPermissionsSeeder` sempre e `DemoSeeder` só quando `app()->environment('local')`.
- Opcional: seeder do catálogo global de `servicos` (útil em produção para o profissional escolher serviços prontos).

**Perguntas-chave**
1. O catálogo global de serviços deve ir para produção (seeder próprio)? (Recomendado: sim)
2. Criar usuário admin em produção via seeder (credenciais vindas do `.env`) ou via comando artisan interativo? (Recomendado: comando artisan)
3. Manter o `DemoSeeder` com os dados atuais ou reduzir para um estabelecimento de demonstração?

**Critérios de aceite**
- [ ] `php artisan db:seed --class=RolesPermissionsSeeder` idempotente.
- [ ] Em `production`, nenhum dado fictício é criado.
- [ ] `migrate:fresh --seed` em local continua gerando os dados de demo.

**Como testar**
- Rodar o seeder de roles duas vezes (com permissão) → sem erro de duplicidade.
- Simular `APP_ENV=production` em ambiente de teste e verificar ausência de dados fictícios.

**Registro de execução**
> _(preencher)_

---

## 4.4 LGPD: Termos de Uso e Política de Privacidade

- [ ] **Item concluído**

**Problema**
- O sistema armazena dados pessoais de terceiros (nome, telefone, e-mail, data de nascimento) e não há Termos de Uso, Política de Privacidade nem registro de aceite.
- Profissionais também cadastram clientes (pré-cadastro), o que exige deixar claro o papel de cada um (controlador/operador).

**Sugestão de ajuste**
- Páginas estáticas `/termos` e `/privacidade` (Inertia, `DefaultLayout`), com links no `AppFooter` e no registro.
- Checkbox obrigatório "Li e aceito os Termos e a Política de Privacidade" no `Registro.vue` e no `CompletarCadastro.vue`, com registro da data de aceite (coluna `termos_aceitos_em` em `usuarios` — **migration exige permissão**).
- Canal de contato para solicitações do titular (e-mail) e procedimento simples de exclusão de conta (pode ser manual no MVP).
- Coletar só o necessário: reavaliar se `data_nascimento` é obrigatória no pré-cadastro.

**Perguntas-chave**
1. Os textos serão fornecidos por você/assessoria, ou o agente gera um rascunho baseado em modelos (revisão humana obrigatória)?
2. Registrar data de aceite em coluna nova (migration) ou apenas o checkbox obrigatório sem persistência? (Recomendado: persistir)
3. Data de nascimento no pré-cadastro: manter obrigatória, tornar opcional ou remover? (Recomendado: opcional)
4. Exclusão de conta: botão self-service no Perfil ou solicitação por e-mail no MVP? (Recomendado: por e-mail)

**Critérios de aceite**
- [ ] Páginas `/termos` e `/privacidade` publicadas e linkadas no rodapé e no registro.
- [ ] Aceite obrigatório no registro e no completar cadastro.
- [ ] Canal de contato para o titular informado.
- [ ] Schema doc atualizado se houver nova coluna.

**Como testar**
- Manual: tentar registrar sem marcar o aceite → bloqueado; com aceite → data gravada.
- Teste de feature para a validação do aceite.

**Registro de execução**
> _(preencher)_
