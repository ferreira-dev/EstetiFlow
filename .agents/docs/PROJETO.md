# EstetiFlow — Documentação Técnica Consolidada

> **Fonte de verdade** do projeto. Contém visão geral, stack, arquitetura, padrões, fluxo de agendamento, estado atual e roadmap.
> Para o schema de banco de dados, consulte: `.agents/skills/saas-database-schema/SKILL.md`

---

## 1. Visão Geral do Produto

### O que é
Um **SaaS multi-tenant** para agendamento de serviços estéticos em geral:
barbearias, salões de beleza, cabeleireiros, manicure/pedicure, design de sobrancelhas, maquiagem e estética em geral.

### Modelo de Negócio
**Marketplace B2B2C:**
- **Estabelecimentos** cadastram seus profissionais e serviços
- **Profissionais** gerenciam suas agendas, serviços e finanças
- **Clientes** encontram estabelecimentos próximos e agendam serviços

### Funcionalidades do MVP

**Para Profissionais:**
- ✅ Dashboard com agenda do dia, cards de receita e próximos atendimentos
- ✅ Relatório financeiro (receita realizada vs prevista, filtro dia/semana/mês)
- ✅ Cadastro de serviços (nome, preço, tempo de execução)
- ✅ Grade de horários de funcionamento semanal
- ✅ Gestão de bloqueios (folgas pontuais + recorrentes)
- ✅ Visualizar e gerenciar agendamentos (confirmar/recusar/iniciar/concluir/cancelar)
- ✅ Editar dados do estabelecimento

**Para Clientes:**
- ✅ Buscar estabelecimentos (nome/categoria)
- ✅ Consultar horários disponíveis (cálculo real de disponibilidade)
- ✅ Agendar serviços
- ✅ Cancelar agendamentos
- ✅ Consultar histórico de agendamentos
- ✅ Editar perfil e alterar senha

**Funcionalidades Futuras (Pós-MVP):**
- 📅 Seleção guiada de profissional no agendamento público
- 🌍 Busca por geolocalização (proximidade)
- ⭐ Sistema de avaliações pós-serviço
- 📧 Notificações push/email/SMS
- 💎 Planos e pacotes de serviços
- 🎁 Programas de fidelidade

---

## 2. Stack Tecnológica

| Camada | Tecnologia |
|--------|------------|
| **Frontend** | Vue 3 (Composition API) + Inertia.js + PrimeVue 4.2 (Aura) + Tailwind CSS 3.x |
| **Backend** | Laravel 11 (monolito com Inertia) |
| **Banco** | MySQL 8.0 — 14 tabelas, 5 módulos |
| **Auth** | Sessão Laravel (`Auth::attempt`) + Spatie Permission (3 roles) |
| **Build** | Vite (`laravel-vite-plugin`) |
| **Infra** | Docker Compose (6 containers: webserver, php-fpm, node, mysql, redis, mailhog) |
| **Bridge** | `@inertiajs/vue3` — sem Vue Router, sem Pinia, sem Axios |

---

## 3. Arquitetura e Estrutura de Pastas

### Fluxo de dados

```
Browser → Nginx → PHP-FPM (Laravel)
                     ↓
              routes/web.php
                     ↓
              Controller → Service → Model (Eloquent)
                     ↓
              Inertia::render('PageName', $props)
                     ↓
              resources/views/app.blade.php (template raiz)
                     ↓
              Vue 3 (Inertia) → defineProps() → componentes
```

### Estrutura de Pastas

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── EstabelecimentoController.php
│   │   ├── AgendamentoController.php
│   │   ├── PerfilController.php
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   └── RegistroController.php
│   │   └── Profissional/
│   │       ├── DashboardController.php
│   │       ├── ProfissionalController.php
│   │       ├── ServicoController.php
│   │       ├── HorarioController.php
│   │       ├── BloqueioController.php
│   │       ├── AgendamentoProfissionalController.php
│   │       └── FinanceiroController.php
│   └── Middleware/
│       └── HandleInertiaRequests.php
├── Models/         # 14 models (User, Estabelecimento, Profissional, Servico, etc.)
├── Services/
│   ├── EstabelecimentoService.php
│   └── AgendamentoService.php
routes/
│   └── web.php
resources/
├── css/
│   └── app.css                    # design system (dark mode, glassmorphism)
├── js/
│   ├── app.js                     # bootstrap Inertia + PrimeVue
│   ├── Components/
│   │   ├── Features/              # AgendamentoCard, ServicoItem, etc.
│   │   ├── Layout/                # AppHeader, AppFooter, AppSidebar
│   │   └── Profissional/          # ServicoFormDialog
│   ├── Constants/                 # categorias.js
│   ├── Layouts/                   # DefaultLayout, AuthLayout, DashboardLayout
│   ├── Pages/
│   │   ├── Home.vue, Login.vue, Registro.vue, Estabelecimentos.vue
│   │   ├── EstabelecimentoDetail.vue, Agendamentos.vue, Perfil.vue
│   │   └── Profissional/          # Dashboard, Estabelecimento, Servicos,
│   │                              # Horarios, Bloqueios, AgendamentosProfissional, Financeiro
│   └── Utils/                     # formatters.js
└── views/
    └── app.blade.php              # template raiz Inertia
```

### Menu do Painel Profissional (DashboardLayout)

| Rota | Ícone | Label |
|------|-------|-------|
| `/profissional/dashboard` | `pi-home` | Dashboard |
| `/profissional/estabelecimento` | `pi-building` | Meu Estabelecimento |
| `/profissional/servicos` | `pi-list` | Meus Serviços |
| `/profissional/horarios` | `pi-clock` | Meus Horários |
| `/profissional/bloqueios` | `pi-ban` | Bloqueios |
| `/profissional/agendamentos` | `pi-calendar` | Agendamentos |
| `/profissional/financeiro` | `pi-chart-bar` | Financeiro |

---

## 4. Padrões de Código

### Nomenclatura

**Arquivos e Pastas — Frontend (`resources/js/`):**
- Componentes Vue: `PascalCase.vue` (ex: `AgendamentoCard.vue`)
- Pastas: `PascalCase` (ex: `Components/Features/`)
- Utils/Constants: `camelCase.js` dentro de pastas `PascalCase`

**Arquivos e Pastas — Backend (`app/`):**
- Controllers: `PascalCase.php` (ex: `EstabelecimentoController.php`)
- Models: `PascalCase.php` singular (ex: `Estabelecimento.php`)
- Services: `PascalCase.php` (ex: `AgendamentoService.php`)

**Código:**
- Variáveis/Funções: `camelCase`
- Constantes: `UPPER_SNAKE_CASE`
- Classes: `PascalCase`
- Componentes PrimeVue: sem prefixo `P` (ex: `Button`, `DataTable`)

### Vue 3 + Inertia — Regras

```vue
<script setup>
// 1. Imports Inertia
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

// 2. Layout persistente (evita re-render)
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
defineOptions({ layout: DefaultLayout })

// 3. Props vindas do Controller (substituem services/stores)
const props = defineProps({
    estabelecimentos: { type: Array, default: () => [] },
})

// 4. Auth global
const user = computed(() => usePage().props.auth?.user)

// 5. Navegação
router.visit('/rota', { data: { q: termo.value } })
</script>
```

**✅ Usar:**
- Dados → `defineProps` (vêm do Controller)
- Navegação → `<Link href="/rota">` ou `router.visit()`
- Formulários → `useForm()` do Inertia
- Auth → `usePage().props.auth.user`
- Estado local → `ref()` (preferir `ref` sobre `reactive`)
- Notificações → `useToast()` do PrimeVue

**❌ Não usar:** `vue-router`, `pinia`, `axios`

### Backend — Service Pattern

```php
// Controller delega lógica para Service
// app/Services/AgendamentoService.php
class AgendamentoService
{
    public function verificarDisponibilidade(...) { ... }
    public function criarAgendamento(...) { ... }
    public function alterarStatus(...) { ... }
}
```

### Autenticação

1. POST `/login` → `Auth::attempt()` → sessão Laravel
2. `HandleInertiaRequests::share()` → expõe `auth.user` + `auth.roles` globalmente
3. Rotas protegidas → middleware `auth` (cliente + profissional) ou `auth` + `role:profissional`
4. Frontend → `usePage().props.auth?.user` (null se não autenticado)

---

## 5. Fluxo de Agendamento

> Documento de referência para o módulo de agendamentos: transições de status, regras de negócio e estado atual do MVP.

### 5.1 Diagrama de Status

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

### 5.2 Transições válidas

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

### 5.3 O que funciona hoje

| Funcionalidade | Implementação | Observação |
|---------------|---------------|------------|
| **Criar agendamento** | `POST /agendamentos` | Status vai direto para `confirmado`, pulando `pendente` intencionalmente no MVP |
| **Cancelar pelo cliente** | `DELETE /agendamentos/{id}` | Grava `cancelado_cliente` + `HistoricoAgendamento` |
| **Snapshot de preço** | `ItemAgendamento` | `nome_servico`, `preco_praticado` e `tempo_execucao_minutos` preservados no momento do agendamento |
| **Auditoria de status** | `HistoricoAgendamento` | Cada mudança de status grava `status_anterior`, `status_novo`, `alterado_por_id` |
| **Listagem do cliente** | `GET /agendamentos` | Eager load de `profissional.estabelecimento` e `itens` |
| **Confirmar agendamento** | `PUT /profissional/agendamentos/{id}/status` | Profissional confirma — `pendente → confirmado` |
| **Recusar agendamento** | `PUT /profissional/agendamentos/{id}/status` | Profissional recusa — `pendente → cancelado_profissional` |
| **Iniciar atendimento** | `PUT /profissional/agendamentos/{id}/status` | `confirmado → em_atendimento` |
| **Concluir atendimento** | `PUT /profissional/agendamentos/{id}/status` | `em_atendimento → concluido` |
| **Cancelar pelo profissional** | `PUT /profissional/agendamentos/{id}/status` | `confirmado → cancelado_profissional` |
| **Marcar não compareceu** | `PUT /profissional/agendamentos/{id}/status` | `confirmado → nao_compareceu` |
| **Transições validadas** | `AgendamentoService::alterarStatus()` | Matriz de transições válidas impede saltos de status ilegais |
| **Painel do profissional** | `GET /profissional/agendamentos` | Lista com filtro por status + ações contextuais por card |
| **Disponibilidade real** | `AgendamentoService::verificarDisponibilidade()` | Valida: horário de funcionamento + bloqueios pontuais + bloqueios recorrentes + conflitos de agenda |

### 5.4 Simplificações do MVP (a evoluir)

| Situação | MVP atual | Comportamento futuro planejado |
|----------|-----------|-------------------------------|
| Status inicial | Criação vai direto para `confirmado` | `pendente` até profissional aceitar manualmente (infraestrutura já pronta no painel) |
| `nao_compareceu` automático | Só via ação manual do profissional no painel | Job/queue que marca automaticamente N horas após a data passar |
| Notificações | Não implementadas | Lembretes por push/email/SMS ao cliente e profissional |
| Seleção de profissional | O `serviço_profissional_id` é inferido automaticamente (primeiro disponível) | Cliente escolhe explicitamente o profissional antes de selecionar o slot (Step 0 no Drawer) |

### 5.5 Regra de Exibição — "Meus Agendamentos" (Cliente)

**Lógica de abas (implementada):**

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

**Labels e cores por status:**

| Status | Label exibido | Cor semântica |
|--------|--------------|---------------|
| `pendente` | Pendente | Amarelo (warning) |
| `confirmado` | Confirmado | Verde (success) |
| `em_atendimento` | Em Andamento | Azul (info) |
| `concluido` | Concluído | Cinza (secondary) |
| `cancelado_cliente` | Cancelado | Vermelho (danger) |
| `cancelado_profissional` | Cancelado pelo Profissional | Vermelho (danger) |
| `nao_compareceu` | Não Compareceu | Laranja (warn) |

### 5.6 Tabelas envolvidas

```
agendamentos
  ├── itens_agendamentos        (snapshot de preço e tempo por serviço)
  ├── historico_agendamentos    (auditoria de cada mudança de status)
  └── avaliacoes                (pós-serviço — pós-MVP)
```

**Relacionamentos para exibição ao cliente** (`AgendamentoCard.vue`):
```
agendamentos.profissional_id
  → profissionais.estabelecimento_id
    → estabelecimentos.nome_fantasia / foto_capa_url / logradouro...
agendamentos.itens
  → itens_agendamentos.nome_servico / preco_praticado
```

**Relacionamentos para exibição ao profissional** (`AgendamentosProfissional.vue` e `Dashboard.vue`):
```
agendamentos.cliente_id
  → usuarios.nome_completo
agendamentos.itens
  → itens_agendamentos.nome_servico / preco_praticado / tempo_execucao_minutos
agendamentos.valor_total   → usado nos cards de receita do Dashboard
```

**Relacionamentos para verificação de disponibilidade** (`AgendamentoService::verificarDisponibilidade()`):
```
agendamentos.profissional_id
  → horarios_funcionamento.dia_semana / hora_inicio / hora_fim
  → bloqueios_agenda (pontuais: data_inicio/data_fim)
  → bloqueios_agenda (recorrentes: hora_inicio/hora_fim/dias_semana)
  → agendamentos existentes (conflito de slot: status IN pendente/confirmado/em_atendimento)
```

---

## 6. Estado Atual — Implementado vs Pendente

### ✅ Implementado e funcional

| Funcionalidade | Arquivo(s) | Status |
|---------------|-----------|--------|
| Listagem de estabelecimentos | `Estabelecimentos.vue`, `EstabelecimentoCard.vue` | ✅ |
| Detalhe do estabelecimento | `EstabelecimentoDetail.vue` | ✅ |
| Listagem de serviços por profissional | `ServicoItem.vue` | ✅ |
| Busca de estabelecimentos (nome/categoria) | `SearchBar.vue`, `Estabelecimentos.vue` | ✅ |
| Login real (email/senha) | `Login.vue`, `LoginController.php` | ✅ |
| Redirect pós-login por role | `LoginController.php` | ✅ |
| Registro (cliente + profissional) | `Registro.vue`, `RegistroController.php` | ✅ |
| Cadastro de estabelecimento | `Profissional/Estabelecimento.vue`, `ProfissionalController.php` | ✅ |
| CRUD de serviços | `Profissional/Servicos.vue`, `ServicoFormDialog.vue`, `ServicoController.php` | ✅ |
| Configuração de horários | `Profissional/Horarios.vue`, `HorarioController.php` | ✅ |
| Gestão de bloqueios (pontuais + recorrentes) | `Profissional/Bloqueios.vue`, `BloqueioController.php` | ✅ |
| Gestão de agendamentos (profissional) | `Profissional/AgendamentosProfissional.vue`, `AgendamentoProfissionalController.php` | ✅ |
| Cálculo de disponibilidade | `AgendamentoService::verificarDisponibilidade()` | ✅ |
| Horários dinâmicos no detalhe público | `EstabelecimentoController.php` → prop | ✅ |
| Bloqueios recorrentes | Migration `alter_bloqueios_agenda_add_recorrencia` | ✅ |
| Fluxo de agendamento (calendário + horários) | `ServicoItem.vue` (Drawer) | ✅ |
| Listagem de agendamentos (cliente) | `Agendamentos.vue`, `AgendamentoCard.vue` | ✅ |
| Cancelamento de agendamento (cliente) | `AgendamentoCard.vue` (ConfirmDialog) | ✅ |
| **Dashboard do profissional** | `Profissional/Dashboard.vue`, `DashboardController.php` | ✅ |
| **Painel financeiro** | `Profissional/Financeiro.vue`, `FinanceiroController.php` | ✅ |
| **Página de perfil + alterar senha** | `Perfil.vue`, `PerfilController.php` | ✅ |
| DashboardLayout (7 itens no menu) | `DashboardLayout.vue` | ✅ |
| Menu condicional "Painel Profissional" | `AppHeader.vue`, `AppSidebar.vue` | ✅ |
| Categorias de serviço | `categorias.js`, `CategoriaItem.vue` | ✅ |
| Header/Footer/Sidebar responsivos | `AppHeader.vue`, `AppFooter.vue`, `AppSidebar.vue` | ✅ |
| Schema completo (14 tabelas) | Migrations + Models + Seeders | ✅ |
| Spatie Permission (3 roles) | `User.php` + seeders | ✅ |

### ⚠️ Parcialmente implementado — Precisa adequar

| Funcionalidade | O que falta |
|---------------|------------|
| **Seleção de profissional no agendamento** | Cliente escolhe serviço mas não seleciona profissional explicitamente. `ServicoItem.vue` já recebe `horarios_funcionamento` e `bloqueios`, mas a seleção guiada falta. |
| **Status detalhado no card do cliente** | `AgendamentoCard.vue` usa status simplificado. Falta exibir todos os 7 status com ícones e cores corretos. |
| **Snapshot de preço (garantia)** | Agendamentos criam itens com snapshot, mas deve ser auditado em todos os fluxos. |
| **Geolocalização** | Colunas `latitude`/`longitude` existem em `estabelecimentos`. Falta busca por proximidade no frontend/backend. |

### ❌ Não implementado — Criar do zero

| Funcionalidade | Prioridade |
|---------------|-----------|
| **Avaliações** — nota + comentário pós-serviço | 🟢 Baixa (pós-MVP) |
| **Notificações** — lembretes e avisos | 🟢 Baixa (pós-MVP) |
| **Job automático `nao_compareceu`** — marcar automaticamente quando data passa | 🟡 Média |
| **Busca por geolocalização** — proximidade via lat/lng | 🟡 Média |

---

## 7. Roadmap de Implementação

### ✅ Concluído
- Arquitetura frontend Vue 3 + PrimeVue + Tailwind CSS
- Migração para monolito Laravel 11 + Inertia.js
- Schema completo (14 tabelas com migrations, models, relationships)
- Seeders com dados ricos
- Autenticação real via sessão Laravel + Spatie Permission (3 roles)
- Registro de cliente e profissional
- Cadastro de estabelecimento pelo profissional
- CRUD de serviços do profissional
- Configuração de horários — grade semanal com toggle por dia
- Gestão de bloqueios — pontuais e recorrentes por dia/hora
- Gestão de agendamentos do profissional — confirmar/recusar/iniciar/concluir/cancelar
- Horários dinâmicos no detalhe público (removido hardcode)
- Cálculo de disponibilidade com 3 camadas de validação
- Dashboard do profissional (stats + agenda do dia + receita)
- Painel financeiro (receita realizada vs prevista, filtros dia/semana/mês)
- Página de perfil (editar dados pessoais e senha)
- DashboardLayout com menu lateral (7 itens)
- 6 containers Docker (webserver, php-fpm, node, mysql, redis, mailhog)

### Fase Atual: Polimento do Fluxo do Cliente
1. Aprimorar seleção de profissional no fluxo de agendamento público (Step 0 no Drawer)
2. Atualizar `AgendamentoCard.vue` do cliente para todos os 7 status
3. Garantir snapshot de preço em todos os fluxos

### Próxima Fase: Geolocalização e Automações
1. Busca por proximidade usando lat/lng de `estabelecimentos`
2. Job automático para marcar `nao_compareceu` (queue)
3. Status `pendente` como default (profissional confirma manualmente)

### Fase: Engajamento (Pós-MVP)
1. Avaliações pós-serviço
2. Notificações (lembretes, avisos)
3. PWA (manifest, service worker)
4. Testes E2E com Playwright
5. CI/CD via GitHub Actions

---

## 8. Decisões Técnicas Registradas

| Decisão | Escolha | Justificativa |
|---------|---------|---------------|
| Dark mode | `.dark-mode` class-based | Escalável para troca de tema via config |
| Arquitetura | Monolito Laravel + Inertia.js | Complexidade gerenciável, SSR nativo futuro |
| Autenticação | Sessão Laravel + Spatie Permission | Mais simples com Inertia; JWT apenas quando API pública |
| State Management | Inertia props + `usePage()` | Elimina Pinia e Axios; dados sempre frescos do server |
| Roles & Permissions | Spatie `laravel-permission` | Padrão Laravel, flexível, sem reinventar a roda |
| Nomenclatura | PT-BR em todo projeto | Consistência com banco e público-alvo |
| Ícones | PrimeIcons (exclusivo) | Regra do projeto: proibido FontAwesome, Heroicons, etc. |
| Design system | Glassmorphism + stat cards + Aura | Premium, moderno, dark-first |
| Mock de dados | Seeders Laravel | Dados no banco real em vez de JSON Server |
| Serviços | Catálogo global + criação pelo profissional | Abordagem híbrida: selecionar existente OU criar novo |
| Referência visual | Projeto React BarberLab | Figma com acesso protegido (403) |

---

## 9. Referências

| Documento | Caminho | Conteúdo |
|-----------|---------|----------|
| **Schema de Banco** | `.agents/skills/saas-database-schema/SKILL.md` | 14 tabelas, tipos de dados, relacionamentos, regras de negócio |
| **Arquitetura de Banco** | `.agents/skills/database-architect/SKILL.md` | Metodologia de modelagem MySQL/MariaDB (skill comportamental) |
| **Guia PrimeVue 4** | `.agents/rules/primevue4-guide.txt` | Dicas específicas para componentes PrimeVue |
| **Padrões do Projeto** | `.agents/rules/project-standards.md` | Rules de código, segurança e automação |
