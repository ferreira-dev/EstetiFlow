---
name: Project Context - SaaS Agendamento Estético
description: Contexto completo do MVP de agendamento para serviços estéticos, incluindo stack tecnológica, arquitetura, padrões de código e boas práticas
---

# SaaS Agendamento Estético - Contexto do Projeto

## 🎯 Visão Geral do Produto

### O que é
Um **SaaS multi-tenant** para agendamento de serviços estéticos em geral:
- Barbearias
- Salões de beleza / Cabeleireiros
- Manicure e pedicure
- Design de sobrancelhas
- Maquiagem
- Estética em geral

### Modelo de Negócio
**Marketplace B2B2C:**
- **Estabelecimentos** cadastram seus profissionais e serviços
- **Profissionais** gerenciam suas agendas e serviços
- **Clientes** encontram estabelecimentos próximos e agendam serviços

---

## 📋 Funcionalidades do MVP

### Para Profissionais
- ✅ Relatório financeiro básico (dia/semana/mês)
- ✅ Cadastro de serviços (nome, preço, tempo de execução)
- ✅ Grade de horários de funcionamento
- ✅ Visualizar e gerenciar agendamentos
- ✅ Confirmar/cancelar agendamentos

### Para Clientes
- ✅ Buscar estabelecimentos próximos (geolocalização)
- ✅ Consultar horários disponíveis
- ✅ Agendar serviços
- ✅ Desmarcar agendamentos
- ✅ Consultar histórico de agendamentos

### Funcionalidades Futuras (Pós-MVP)
- 📅 Cadastro de múltiplos funcionários por estabelecimento
- 💎 Planos e pacotes de serviços
- ⭐ Sistema de avaliações
- 📧 Notificações push/email/SMS
- 🎁 Programas de fidelidade

---

## 🛠️ Stack Tecnológica

### Frontend
- **Framework:** Vue 3 (Composition API) integrado via **Inertia.js**
- **UI Library:** PrimeVue 4.2
- **Tema:** Aura (tema oficial do PrimeVue)
- **CSS Framework:** Tailwind CSS 3.x
- **Build Tool:** Vite (via `laravel-vite-plugin`)
- **Package Manager:** npm
- **Bridge:** `@inertiajs/vue3` — sem Vue Router, sem Pinia, sem Axios

### Backend (Ativo)
- **Framework:** Laravel 11 (monolito com Inertia.js)
- **Banco de Dados:** MySQL 8.0
- **ORM:** Eloquent (Laravel)
- **Autenticação:** Sessão Laravel (`Auth::attempt` + `auth` middleware)
- **Bridge SSR:** `inertiajs/inertia-laravel` v2
- **Storage:** AWS S3 ou similar (para imagens — futuro)

### Infraestrutura
- **Containerização:** Docker + Docker Compose (6 containers: webserver, php-fpm, node, mysql, redis, mailhog)
- **Versionamento:** Git
- **CI/CD:** GitHub Actions (futuro)

---

## 🏗️ Arquitetura — Monolito Laravel + Inertia

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
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   └── RegistroController.php
│   │   └── Profissional/
│   │       ├── ProfissionalController.php   # gestão do estabelecimento
│   │       └── ServicoController.php        # CRUD de serviços do profissional
│   └── Middleware/
│       └── HandleInertiaRequests.php   # compartilha auth + roles global
├── Models/
│   ├── User.php                       # HasRoles (Spatie Permission)
│   ├── Estabelecimento.php
│   ├── Profissional.php
│   ├── Servico.php
│   ├── ServicoProfissional.php
│   ├── Agendamento.php
│   └── ...                            # demais 14 tabelas
├── Services/
│   ├── EstabelecimentoService.php
│   └── AgendamentoService.php
routes/
│   └── web.php                        # rotas Inertia (públicas + auth + profissional)
resources/
├── css/
│   └── app.css                        # design system (dark mode, glassmorphism)
├── js/
│   ├── app.js                         # bootstrap Inertia + PrimeVue
│   ├── Components/
│   │   ├── Features/                  # AgendamentoCard, ServicoItem, etc.
│   │   ├── Layout/                    # AppHeader, AppFooter, AppSidebar
│   │   └── Profissional/              # ServicoFormDialog
│   ├── Constants/                     # categorias.js
│   ├── Layouts/                       # DefaultLayout, AuthLayout, DashboardLayout
│   ├── Pages/
│   │   ├── Home.vue, Login.vue, Registro.vue, Estabelecimentos.vue, etc.
│   │   └── Profissional/              # Estabelecimento.vue, Servicos.vue
│   └── Utils/                         # formatters.js
└── views/
    └── app.blade.php                  # template raiz Inertia
```

### Padrões de Nomenclatura

#### Arquivos e Pastas — Frontend (`resources/js/`)
- **Componentes Vue:** `PascalCase.vue` (ex: `AgendamentoCard.vue`)
- **Pastas de componentes:** `PascalCase` (ex: `Components/Features/`, `Components/Layout/`)
- **Utils:** `camelCase.js` (ex: `formatters.js`) dentro de `Utils/` (PascalCase)
- **Constants:** `camelCase.js` dentro de `Constants/` (PascalCase)

#### Arquivos e Pastas — Backend (`app/`)
- **Controllers:** `PascalCase.php` (ex: `EstabelecimentoController.php`)
- **Models:** `PascalCase.php` singular (ex: `Estabelecimento.php`)
- **Services:** `PascalCase.php` (ex: `EstabelecimentoService.php`)

#### Código
- **Variáveis/Funções:** `camelCase`
- **Constantes:** `UPPER_SNAKE_CASE`
- **Classes:** `PascalCase`
- **Componentes PrimeVue:** sem prefixo `P` — importados diretamente (ex: `Button`, `DataTable`)

---

## 🎨 Design System (PrimeVue + Tailwind)

### Tema Aura
O projeto usa o **tema Aura** do PrimeVue, que já vem com:
- Design moderno e clean
- Suporte a modo claro/escuro
- Paleta de cores harmoniosa
- Componentes estilizados por padrão

### Integração PrimeVue + Tailwind

#### Quando usar PrimeVue:
- ✅ Componentes complexos: `DataTable`, `Calendar`, `Dialog`, `Chart`
- ✅ Formulários: `InputText`, `Dropdown`, `AutoComplete`, `MultiSelect`
- ✅ Navegação: `Menubar`, `TabView`, `Steps`
- ✅ Feedback: `Toast`, `Message`, `ProgressBar`

#### Quando usar Tailwind:
- ✅ Layout e espaçamento: `flex`, `grid`, `p-4`, `m-2`, `gap-4`
- ✅ Cores customizadas: `bg-blue-500`, `text-gray-700`
- ✅ Responsividade: `md:flex`, `lg:grid-cols-3`
- ✅ Utilitários: `rounded-lg`, `shadow-md`, `hover:bg-gray-100`

#### Exemplo de uso combinado:
```vue
<template>
  <div class="p-6 bg-gray-50 rounded-lg shadow-md">
    <PButton 
      label="Agendar" 
      icon="pi pi-calendar" 
      class="w-full md:w-auto"
      @click="agendar"
    />
  </div>
</template>
```

### Tokens de Design (Aura)

#### Cores Principais
- `primary`: Cor principal da aplicação (definida no tema)
- `surface`: Fundo de cards e containers
- `text-color`: Cor padrão de texto

#### Espaçamentos
Use os tokens do PrimeVue quando possível:
- `p-1` a `p-8` (do PrimeVue, não Tailwind)
- Ou Tailwind: `p-4`, `px-6`, `py-3`

### Componentes PrimeVue Mais Usados no Projeto

| Componente | Uso Principal |
|------------|---------------|
| `PButton` | Ações primárias e secundárias |
| `PDataTable` | Listagem de agendamentos, serviços |
| `PCalendar` | Seleção de data/hora |
| `PInputText` | Campos de texto |
| `PDropdown` | Seleção de profissionais, serviços |
| `PDialog` | Modals de confirmação, formulários |
| `PToast` | Notificações de sucesso/erro |
| `PCard` | Cards de serviços, profissionais |
| `PAvatar` | Foto de perfil |
| `PBadge` | Status de agendamento |
| `PChip` | Tags de categorias |
| `PSkeleton` | Loading states |

### 📚 Consulta Obrigatória ao MCP PrimeVue

**IMPORTANTE:** Sempre que for desenvolver componentes que utilizem PrimeVue, o agente **DEVE consultar o MCP do PrimeVue** para:

✅ **Verificar a API correta do componente:**
- Props disponíveis e seus tipos
- Events emitidos
- Slots disponíveis
- Métodos expostos

✅ **Garantir uso adequado:**
- Exemplos de uso recomendados
- Configurações específicas do tema Aura
- Integração com Tailwind CSS

✅ **Evitar erros comuns:**
- Props deprecadas
- Configurações incompatíveis
- Problemas de versão (PrimeVue 4.2)

#### Como consultar o MCP:
```bash
# O MCP está configurado em .antigravity/mcp.json
# Use list_resources e read_resource para consultar componentes
```

#### Exemplo de fluxo:
1. **Antes de usar `PDataTable`:**
   - Consultar MCP para ver props disponíveis
   - Verificar como implementar paginação
   - Confirmar eventos de seleção

2. **Antes de usar `PCalendar`:**
   - Verificar formato de data aceito
   - Consultar opções de localização (pt-BR)
   - Confirmar integração com formulários

**⚠️ Nunca assuma a API de um componente PrimeVue sem consultar o MCP primeiro!**

---

## 🗄️ Integração com Banco de Dados

### Mapeamento: Frontend ↔ Backend ↔ Database

#### Exemplo: Agendamento
```
[Frontend Component]
AgendamentoForm.vue
      ↓
[Service Layer]
agendamentoService.js → POST /api/agendamentos
      ↓
[Backend API]
POST /api/agendamentos → Controller
      ↓
[Database]
Inserção nas tabelas:
- agendamentos (registro principal)
- itens_agendamentos (serviços do agendamento)
- historico_agendamentos (auditoria)
```

### Entities/Models Principais

> **📖 Documentação completa do schema:** Consulte `.agents/skills/saas-database-schema/SKILL.md`
> para ver todas as tabelas, tipos de dados, relacionamentos e regras de negócio detalhadas.

O projeto possui **14 tabelas** organizadas em **5 módulos**:

| Módulo | Tabelas |
|--------|---------|
| Autenticação | `usuarios`, `perfis` |
| Estabelecimentos | `estabelecimentos`, `profissionais`, `servicos`, `servicos_profissionais` |
| Agenda | `horarios_funcionamento`, `bloqueios_agenda`, `agendamentos`, `itens_agendamentos`, `historico_agendamentos` |
| Financeiro | `relatorios_financeiros` |
| Engajamento (futuro) | `avaliacoes`, `notificacoes` |

---

## 📝 Padrões de Código

### Vue 3 Composition API com Inertia

#### Template Structure (Pages)
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
    agendamentos: { type: Array, default: () => [] },
})

// 4. Auth global (compartilhado via HandleInertiaRequests)
const user = computed(() => usePage().props.auth?.user)

// 5. Estado local
const termo = ref('')

// 6. Navegação com Inertia
function buscar() {
    router.visit('/estabelecimentos', { data: { q: termo.value } })
}
</script>

<template>
  <!-- Use <Link href="/rota"> em vez de <router-link> -->
  <Link href="/estabelecimentos">Ver todos</Link>
</template>
```

#### Regras Inertia
- ✅ Dados → sempre via `defineProps` (vêm do Controller)
- ✅ Navegação → `<Link href="/rota">` ou `router.visit('/rota')`
- ✅ Formulários → `useForm({ campo: '' })` + `form.post('/rota')`
- ✅ Auth global → `usePage().props.auth.user`
- ❌ Não usar `vue-router`, `pinia`, `axios`
- ❌ Não buscar dados no `onMounted` — sempre vêm via props

### Backend Service Pattern (Laravel)

```php
// app/Services/EstabelecimentoService.php
class EstabelecimentoService
{
    public function listar(string $q = '', string $categoria = ''): Collection
    {
        return Estabelecimento::query()
            ->when($q, fn($query) => $query->where('nome', 'like', "%{$q}%"))
            ->when($categoria, fn($query) => $query->whereHas('servicos',
                fn($q2) => $q2->where('categoria', $categoria)
            ))
            ->get();
    }
}
```

---

## 🔐 Autenticação e Autorização

### Fluxo de Autenticação (Laravel Session)
1. POST `/login` com `email` + `password`
2. Laravel `Auth::attempt()` cria sessão
3. `HandleInertiaRequests::share()` expõe `auth.user` globalmente
4. Rotas protegidas usam middleware `auth` do Laravel
5. Logout via POST `/logout` + `Auth::logout()`

### Como verificar auth no frontend
```javascript
// Em qualquer componente Vue
import { usePage } from '@inertiajs/vue3'
const user = usePage().props.auth?.user  // null se não autenticado
```

### Proteção de Rotas (Laravel)
```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/agendamentos', [AgendamentoController::class, 'index']);
});
```

---

## 🎯 Boas Práticas

### Performance
- ✅ Use `v-show` para toggles frequentes, `v-if` para condicionais raras
- ✅ Implemente lazy loading de rotas
- ✅ Use `<Suspense>` para async components
- ✅ Otimize imagens (WebP, compressão)
- ✅ Implemente virtual scrolling para listas grandes (PrimeVue VirtualScroller)

### Acessibilidade
- ✅ Use labels apropriados em formulários
- ✅ Adicione `aria-labels` quando necessário
- ✅ Garanta navegação por teclado
- ✅ Use cores com contraste adequado
- ✅ Teste com leitores de tela

### SEO (Futuro - SSR)
- Considerar Nuxt 3 para SSR se necessário
- Meta tags apropriadas
- URLs amigáveis

### Testes (Futuro)
- **Unit:** Vitest
- **E2E:** Playwright ou Cypress
- **Componentes:** @vue/test-utils

---

## 🚀 Fluxo de Desenvolvimento

### 1. Feature Nova
1. **Criar branch:** `git checkout -b feature/nome-feature`
2. **Criar componente:** Em `components/features/[modulo]`
3. **Criar service:** Se necessário, em `services/`
4. **Criar composable:** Se lógica reutilizável
5. **Criar rota:** Em `router/index.js`
6. **Testar:** Manualmente (futuro: testes automatizados)
7. **Commit:** `git commit -m "feat: descrição da feature"`
8. **Push:** `git push origin feature/nome-feature`

### 2. Bug Fix
1. **Criar branch:** `git checkout -b fix/nome-bug`
2. **Corrigir:** Identificar e corrigir o problema
3. **Testar:** Garantir que não quebrou nada
4. **Commit:** `git commit -m "fix: descrição do bug fix"`
5. **Push:** `git push origin fix/nome-bug`

### 3. Commit Messages (Conventional Commits)
- `feat:` - Nova funcionalidade
- `fix:` - Correção de bug
- `docs:` - Documentação
- `style:` - Formatação, não afeta código
- `refactor:` - Refatoração de código
- `test:` - Adição de testes
- `chore:` - Manutenção, configuração

---

## 📊 State Management — Inertia Props

Com Inertia.js, **não há Pinia**. O estado é gerenciado pela combinação:

| Necessidade | Solução |
|-------------|---------|
| Dados da página (lista, detalhe) | `defineProps` — vêm do Controller |
| Estado de autenticação | `usePage().props.auth` — compartilhado via `HandleInertiaRequests` |
| Estado local (toggle, filtro, input) | `ref()` / `reactive()` Vue 3 |
| Navegação | `router.visit()` ou `<Link href>` |
| Formulários | `useForm()` do Inertia |
| Notificar após ação | `useToast()` do PrimeVue |

### Exemplo — Dados via props
```javascript
// Controller passa dados:
Inertia::render('Agendamentos', ['agendamentos' => $agendamentos])

// Componente recebe:
const props = defineProps({ agendamentos: { type: Array, default: () => [] } })

// Filtra localmente:
const confirmados = computed(() => props.agendamentos.filter(a => a.status === 'confirmado'))
```

---

## 🌐 Internacionalização (Futuro)

### PrimeVue Locale
```javascript
// main.js
import { createApp } from 'vue'
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura'

const app = createApp(App)

app.use(PrimeVue, {
  theme: {
    preset: Aura
  },
  locale: {
    startsWith: 'Começa com',
    contains: 'Contém',
    notContains: 'Não contém',
    endsWith: 'Termina com',
    equals: 'Igual',
    notEquals: 'Diferente',
    // ... mais traduções
  }
})
```

---

## 🔧 Utilitários Comuns

### Formatação de Data/Hora
```javascript
// utils/dateHelpers.js
import { format, parseISO, addMinutes } from 'date-fns'
import { ptBR } from 'date-fns/locale'

export const formatarData = (data) => {
  return format(parseISO(data), 'dd/MM/yyyy', { locale: ptBR })
}

export const formatarHora = (hora) => {
  return format(parseISO(hora), 'HH:mm', { locale: ptBR })
}

export const calcularHoraFim = (horaInicio, duracaoMinutos) => {
  const inicio = parseISO(horaInicio)
  return addMinutes(inicio, duracaoMinutos)
}
```

### Formatação de Moeda
```javascript
// utils/formatters.js
export const formatarMoeda = (valor) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(valor)
}
```

### Validação
```javascript
// utils/validators.js
export const validarEmail = (email) => {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return regex.test(email)
}

export const validarTelefone = (telefone) => {
  const regex = /^\(?[1-9]{2}\)? ?(?:[2-8]|9[0-9])[0-9]{3}\-?[0-9]{4}$/
  return regex.test(telefone)
}

export const validarCPF = (cpf) => {
  cpf = cpf.replace(/[^\d]/g, '')
  if (cpf.length !== 11) return false
  // Lógica de validação de CPF
  return true
}
```

---

## 📱 Responsividade

### Breakpoints (Tailwind)
- `sm`: 640px
- `md`: 768px
- `lg`: 1024px
- `xl`: 1280px
- `2xl`: 1536px

### Abordagem Mobile-First
```vue
<template>
  <!-- Mobile: stack vertical -->
  <!-- Desktop: grid horizontal -->
  <div class="flex flex-col md:grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    <AgendamentoCard v-for="agendamento in agendamentos" :key="agendamento.id" />
  </div>
</template>
```

---

## 🎨 Referências de Design

### Inspirações
- **Calendly** - Fluxo de agendamento simples
- **Fresha** - Marketplace de beleza
- **Agendor** - Interface limpa e profissional

### Paleta de Cores (Sugestão - ajustar conforme branding)
```javascript
// tailwind.config.js (customização adicional)
module.exports = {
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          // ...
          900: '#0c4a6e',
        }
      }
    }
  }
}
```

---

## 🐛 Debugging e Logs

### Console Logs (Remover em produção)
```javascript
if (import.meta.env.DEV) {
  console.log('Debug info:', data)
}
```

### Vue DevTools
- Instalar extensão do navegador
- Inspecionar componentes, Pinia stores, router

---

## 📦 Dependências Principais

```json
{
  "dependencies": {
    "vue": "^3.5.0",
    "@inertiajs/vue3": "^2.0.0",
    "primevue": "^4.2.0",
    "@primevue/themes": "^4.2.0",
    "primeicons": "^7.0.0",
    "lucide-vue-next": "latest",
    "date-fns": "^3.0.0"
  },
  "devDependencies": {
    "@vitejs/plugin-vue": "^5.0.0",
    "laravel-vite-plugin": "^1.0.0",
    "vite": "^6.0.0",
    "tailwindcss": "^3.4.0",
    "autoprefixer": "^10.4.0",
    "postcss": "^8.4.0"
  }
}
```

> **Removidos:** `vue-router`, `pinia`, `axios` (substituídos pelo Inertia.js)

---

## 🎯 Checklist de Qualidade para Cada Feature

Antes de considerar uma feature completa:

- [ ] Código segue os padrões estabelecidos
- [ ] Componentes são reutilizáveis quando apropriado
- [ ] Nomenclatura consistente
- [ ] Tratamento de erros implementado
- [ ] Loading states implementados
- [ ] Responsividade testada (mobile + desktop)
- [ ] Acessibilidade básica garantida
- [ ] PrimeVue + Tailwind usados corretamente
- [ ] Sem console.logs em código de produção
- [ ] Comentários em lógica complexa

---

## 🚀 Estado Atual e Próximos Passos do MVP

### 0. Arquitetura de Banco de Dados (14 tabelas, 5 módulos)

> **📖 Documentação completa e autoritativa do schema:**
> Consulte `.agents/skills/saas-database-schema/SKILL.md` para ver todas as tabelas com
> tipos de dados detalhados, relacionamentos, regras de negócio e correspondência com os seeders.

A arquitetura foi projetada para **MySQL 8.0 / MariaDB** com visão de longo prazo:

| Módulo | Tabelas | Função |
|--------|---------|--------|
| **Autenticação** | `usuarios`, `perfis` | Login unificado + dados complementares |
| **Estabelecimentos** | `estabelecimentos`, `profissionais`, `servicos`, `servicos_profissionais` | Negócios, prestadores e catálogo de serviços |
| **Agenda** | `horarios_funcionamento`, `bloqueios_agenda`, `agendamentos`, `itens_agendamentos`, `historico_agendamentos` | Gestão completa de agendamentos e disponibilidade |
| **Financeiro** | `relatorios_financeiros` | Dados pré-processados para dashboards |
| **Engajamento** *(futuro)* | `avaliacoes`, `notificacoes` | Avaliações pós-serviço e comunicados |

---

### 1. Estado Atual — O que foi implementado vs O que falta

#### ✅ Implementado e funcional
| Funcionalidade | Arquivo(s) | Tabelas | Status |
|---------------|-----------|---------|--------|
| Listagem de estabelecimentos | `Estabelecimentos.vue`, `EstabelecimentoCard.vue` | `estabelecimentos` | ✅ Funcional (seeders) |
| Detalhe do estabelecimento | `EstabelecimentoDetail.vue` | `estabelecimentos`, `profissionais`, `servicos_profissionais` | ✅ Funcional |
| Listagem de serviços por profissional | `ServicoItem.vue` | `servicos`, `servicos_profissionais` | ✅ Funcional |
| Busca de estabelecimentos (nome/categoria) | `SearchBar.vue`, `Estabelecimentos.vue` | `estabelecimentos`, `servicos` | ✅ Funcional |
| Login real (email/senha) | `Login.vue`, `LoginController.php` | `usuarios` | ✅ `Auth::attempt` + sessão |
| Redirect pós-login por role | `LoginController.php` | `usuarios`, `model_has_roles` | ✅ Profissional → `/profissional/estabelecimento` |
| Registro (cliente + profissional) | `Registro.vue`, `RegistroController.php` | `usuarios`, `perfis`, `profissionais` | ✅ Funcional (Spatie Permission) |
| Cadastro de estabelecimento pelo profissional | `Profissional/Estabelecimento.vue`, `ProfissionalController.php` | `estabelecimentos`, `profissionais` | ✅ Funcional |
| CRUD de serviços do profissional | `Profissional/Servicos.vue`, `ServicoFormDialog.vue`, `ServicoController.php` | `servicos`, `servicos_profissionais` | ✅ Funcional (catálogo + criação) |
| DashboardLayout (painel profissional) | `DashboardLayout.vue` | — | ✅ Menu lateral com "Meu Estabelecimento" e "Meus Serviços" |
| Menu condicional "Painel Profissional" | `AppHeader.vue`, `AppSidebar.vue` | `auth.roles` | ✅ Visível apenas para role `profissional` |
| Fluxo de agendamento (calendário + horários) | `ServicoItem.vue` (Drawer) | `agendamentos` | ✅ Funcional |
| Listagem de agendamentos | `Agendamentos.vue`, `AgendamentoCard.vue` | `agendamentos` | ✅ Funcional |
| Cancelamento de agendamento | `AgendamentoCard.vue` (ConfirmDialog) | `agendamentos` | ✅ Funcional |
| Categorias de serviço | `categorias.js`, `CategoriaItem.vue` | `servicos.categoria` | ✅ Constantes locais |
| Header/Footer/Sidebar responsivos | `AppHeader.vue`, `AppFooter.vue`, `AppSidebar.vue` | — | ✅ Funcional |
| Schema completo (14 tabelas) | Migrations + Models + Seeders | Todas | ✅ Migrations executadas |
| Spatie Permission (roles + permissions) | `User.php` + seeders | `roles`, `permissions`, `model_has_roles` | ✅ 3 roles: admin, profissional, cliente |

#### ⚠️ Parcialmente implementado — Precisa adequar
| Funcionalidade | O que falta | Tabelas envolvidas |
|---------------|------------|-------------------|
| **Exibição de profissionais no detalhe público** | O detalhe do estabelecimento lista os profissionais e seus serviços via pivot, mas o fluxo de agendamento ainda não seleciona o profissional explicitamente antes do horário. | `profissionais`, `servicos_profissionais` |
| **Horários de funcionamento** | Os horários estão hardcoded em `EstabelecimentoDetail.vue`. Devem vir de `horarios_funcionamento` (por profissional). Também impacta o cálculo de slots. | `horarios_funcionamento` |
| **Cálculo de disponibilidade** | Precisa consultar: horários do profissional + bloqueios + agendamentos existentes + tempo de execução do serviço. | `horarios_funcionamento`, `bloqueios_agenda`, `agendamentos`, `servicos_profissionais` |
| **Status detalhado de agendamentos** | Frontend usa status simplificado. A arquitetura prevê 7 status. Adaptar `AgendamentoCard.vue`. | `agendamentos.status` |
| **Geolocalização** | Colunas lat/lng existem nas tabelas. Falta implementar busca por proximidade no frontend/backend. | `estabelecimentos.latitude`, `estabelecimentos.longitude` |

#### ❌ Não implementado — Criar do zero
| Funcionalidade | Tabelas envolvidas | Prioridade |
|---------------|-------------------|-----------|
| **Configuração de Horários** — grade de funcionamento semanal pelo profissional | `horarios_funcionamento` | 🔴 Alta |
| **Dashboard do Profissional** — agenda do dia, próximos atendimentos, estatísticas | `agendamentos`, `profissionais` | 🔴 Alta |
| **Gestão de Bloqueios** — marcar férias, folgas, intervalos | `bloqueios_agenda` | 🟡 Média |
| **Relatório Financeiro** — visualização por dia/semana/mês | `relatorios_financeiros`, `agendamentos`, `itens_agendamentos` | 🟡 Média |
| **Página de Perfil** — editar dados pessoais, endereço, foto | `perfis` | 🟡 Média |
| **Avaliações** — nota + comentário pós-serviço | `avaliacoes` | 🟢 Baixa (pós-MVP) |
| **Notificações** — lembretes e avisos | `notificacoes` | 🟢 Baixa (pós-MVP) |

---

### 2. Estratégia de Dados

#### Estado Atual (Laravel + Inertia + MySQL)
- **Monolito completo** — Controllers + Services (PHP) entregam dados via `Inertia::render()` como props
- **Banco de dados real** — 14 tabelas com migrations executadas, seeders com dados ricos
- **Autenticação real** — `Auth::attempt()` com sessão Laravel + Spatie Permission (roles & permissions)
- **Dados estáticos** mantidos inline:
  - Categorias de serviço → `Constants/categorias.js` (enum no banco, constantes no frontend)
  - Horários de funcionamento → hardcoded em `EstabelecimentoDetail.vue` (até implementar tela de gestão)
- **Vite** (node container) serve assets em desenvolvimento, `laravel-vite-plugin` integra com Blade

#### Próximos Passos de Dados
1. **Configuração de Horários** — tela para profissional cadastrar grade semanal (`horarios_funcionamento`)
2. **Cálculo de disponibilidade real** — baseado em horários + bloqueios + agendamentos existentes
3. **Aprimorar fluxo de agendamento** — selecionar profissional, snapshot de preço em `itens_agendamentos`
4. **Geolocalização** — busca por proximidade usando lat/lng do `estabelecimentos`

---

### 3. Roadmap de Implementação

#### ✅ Concluído
- Arquitetura frontend Vue 3 + PrimeVue + Tailwind CSS
- Migração para monolito Laravel 11 + Inertia.js
- Schema completo (14 tabelas com migrations, models, relationships)
- Seeders com dados ricos (estabelecimentos, profissionais, serviços, agendamentos, horários)
- Autenticação real via sessão Laravel + Spatie Permission (3 roles)
- Registro de cliente e profissional (`Registro.vue` + `RegistroController`)
- Cadastro de estabelecimento pelo profissional (`Profissional/Estabelecimento.vue`)
- CRUD de serviços do profissional (`Profissional/Servicos.vue` + `ServicoController`)
- DashboardLayout com menu lateral (Meu Estabelecimento + Meus Serviços)
- Login com redirect condicional por role
- Menu "Painel Profissional" condicional no header e sidebar
- 6 containers Docker (webserver, php-fpm, node, mysql, redis, mailhog)

#### Fase Atual: Completar Painel do Profissional
1. Configurar grade de horários semanal (`horarios_funcionamento`)
2. Criar dashboard com agenda do dia e próximos atendimentos
3. Bloqueios de agenda (férias, folgas)

#### Próxima Fase: Aprimorar Fluxo de Agendamento
1. Selecionar profissional antes do horário no detalhe do estabelecimento
2. Cálculo real de disponibilidade (horários - bloqueios - agendamentos - tempo execução)
3. Usar `itens_agendamentos` para snapshot de preço
4. Implementar 7 status com transições válidas

#### Fase: Relatórios e Engajamento
1. `RelatorioFinanceiro` — page + jobs para `relatorios_financeiros`
2. Geolocalização (busca por proximidade)
3. Avaliações pós-serviço + notificações

#### Fase: Polimento
1. PWA (manifest, service worker)
2. Testes E2E com Playwright
3. CI/CD via GitHub Actions

---

### 4. Decisões Técnicas Registradas

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

**🎯 Skill atualizada com contexto completo! Consulte esta seção para retomar o trabalho em qualquer momento.**

