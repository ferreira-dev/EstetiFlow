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
- **Framework:** Vue 3 (Composition API)
- **UI Library:** PrimeVue 4.2
- **Tema:** Aura (tema oficial do PrimeVue)
- **CSS Framework:** Tailwind CSS 3.x
- **Build Tool:** Vite
- **Package Manager:** npm

### Backend (Futuro)
- **API:** Laravel 11
- **Banco de Dados:** MySQL 8.0 / MariaDB
- **ORM:** Eloquent (Laravel)
- **Autenticação:** JWT
- **Storage:** AWS S3 ou similar (para imagens)

### Infraestrutura
- **Containerização:** Docker + Docker Compose
- **Versionamento:** Git
- **CI/CD:** GitHub Actions (futuro)

---

## 🏗️ Arquitetura Frontend

### Estrutura de Pastas

```
src/
├── assets/              # Imagens, fontes, ícones
├── components/          # Componentes reutilizáveis
│   ├── common/         # Componentes genéricos (Button, Card, etc)
│   ├── layout/         # Layout components (Header, Sidebar, Footer)
│   └── features/       # Componentes específicos de features
│       ├── agendamentos/
│       ├── servicos/
│       └── profissionais/
├── composables/         # Composition API reusables
│   ├── useAuth.js
│   ├── useGeolocation.js
│   └── useAgendamentos.js
├── layouts/            # Layouts de páginas
│   ├── DefaultLayout.vue
│   ├── AuthLayout.vue
│   └── DashboardLayout.vue
├── pages/              # Views/Páginas
│   ├── auth/
│   ├── cliente/
│   └── profissional/
├── router/             # Configuração de rotas
│   └── index.js
├── stores/             # Pinia stores (state management)
│   ├── auth.js
│   ├── agendamentos.js
│   └── profissionais.js
├── services/           # API calls e serviços externos
│   ├── api.js
│   ├── agendamentoService.js
│   └── estabelecimentoService.js
├── utils/              # Funções utilitárias
│   ├── formatters.js
│   ├── validators.js
│   └── dateHelpers.js
├── styles/             # Estilos globais
│   ├── main.css
│   └── tailwind.css
├── App.vue
└── main.js
```

### Padrões de Nomenclatura

#### Arquivos e Pastas
- **Componentes Vue:** `PascalCase.vue` (ex: `AgendamentoCard.vue`)
- **Composables:** `use[Nome].js` (ex: `useAgendamentos.js`)
- **Stores:** `camelCase.js` (ex: `agendamentos.js`)
- **Services:** `camelCaseService.js` (ex: `agendamentoService.js`)
- **Utils:** `camelCase.js` (ex: `dateHelpers.js`)
- **Pastas:** `kebab-case` (ex: `features/agendamentos`)

#### Código
- **Variáveis/Funções:** `camelCase`
- **Constantes:** `UPPER_SNAKE_CASE`
- **Classes:** `PascalCase`
- **Componentes PrimeVue:** Sempre prefixo `P` (ex: `<PButton>`, `<PDataTable>`)

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

Com base na arquitetura de banco de dados criada:

1. **Usuario** (`usuarios`)
   - Autenticação unificada (cliente + profissional)
   
2. **Estabelecimento** (`estabelecimentos`)
   - Dados do negócio + geolocalização
   
3. **Profissional** (`profissionais`)
   - Prestador vinculado a estabelecimento
   
4. **Servico** (`servicos`)
   - Catálogo de serviços
   
5. **Agendamento** (`agendamentos`)
   - Registro de agendamentos
   
6. **HorarioFuncionamento** (`horarios_funcionamento`)
   - Grade de horários por profissional
   
7. **RelatorioFinanceiro** (`relatorios_financeiros`)
   - Dados pré-processados para dashboards

---

## 📝 Padrões de Código

### Vue 3 Composition API

#### Template Structure
```vue
<script setup>
// 1. Imports
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAgendamentos } from '@/composables/useAgendamentos'

// 2. Composables
const route = useRoute()
const router = useRouter()
const { agendamentos, fetchAgendamentos } = useAgendamentos()

// 3. Reactive State
const loading = ref(false)
const searchTerm = ref('')

// 4. Computed
const filteredAgendamentos = computed(() => {
  return agendamentos.value.filter(a => 
    a.cliente.includes(searchTerm.value)
  )
})

// 5. Methods
const handleAgendar = async () => {
  loading.value = true
  try {
    await agendarServico()
    // success
  } catch (error) {
    // error handling
  } finally {
    loading.value = false
  }
}

// 6. Lifecycle Hooks
onMounted(() => {
  fetchAgendamentos()
})
</script>

<template>
  <!-- Template aqui -->
</template>

<style scoped>
/* Estilos específicos do componente */
</style>
```

### Composables Pattern

Crie composables para lógica reutilizável:

```javascript
// composables/useAgendamentos.js
import { ref } from 'vue'
import { agendamentoService } from '@/services/agendamentoService'

export function useAgendamentos() {
  const agendamentos = ref([])
  const loading = ref(false)
  const error = ref(null)

  const fetchAgendamentos = async () => {
    loading.value = true
    try {
      agendamentos.value = await agendamentoService.getAll()
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  return {
    agendamentos,
    loading,
    error,
    fetchAgendamentos
  }
}
```

### Service Layer Pattern

Centralize chamadas de API:

```javascript
// services/agendamentoService.js
import api from './api'

export const agendamentoService = {
  async getAll() {
    const { data } = await api.get('/agendamentos')
    return data
  },

  async getById(id) {
    const { data } = await api.get(`/agendamentos/${id}`)
    return data
  },

  async create(agendamento) {
    const { data } = await api.post('/agendamentos', agendamento)
    return data
  },

  async update(id, agendamento) {
    const { data } = await api.put(`/agendamentos/${id}`, agendamento)
    return data
  },

  async delete(id) {
    await api.delete(`/agendamentos/${id}`)
  },

  // Métodos específicos
  async cancelar(id, motivo) {
    const { data } = await api.post(`/agendamentos/${id}/cancelar`, { motivo })
    return data
  },

  async confirmar(id) {
    const { data } = await api.post(`/agendamentos/${id}/confirmar`)
    return data
  }
}
```

---

## 🔐 Autenticação e Autorização

### Fluxo de Autenticação
1. Login → API retorna JWT
2. Armazenar token no `localStorage`
3. Incluir token em todas as requisições (via interceptor)
4. Redirecionar para dashboard apropriado (cliente vs profissional)

### Route Guards
```javascript
// router/index.js
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const userType = to.meta.userType // 'cliente' ou 'profissional'
  const isAuthenticated = !!localStorage.getItem('token')
  const currentUserType = localStorage.getItem('userType')

  if (requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (userType && currentUserType !== userType) {
    next('/unauthorized')
  } else {
    next()
  }
})
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

## 📊 State Management (Pinia)

### Quando criar uma Store
- ✅ Estado compartilhado entre múltiplos componentes
- ✅ Estado que persiste entre navegações
- ✅ Lógica complexa de negócio

### Exemplo: Store de Agendamentos
```javascript
// stores/agendamentos.js
import { defineStore } from 'pinia'
import { agendamentoService } from '@/services/agendamentoService'

export const useAgendamentosStore = defineStore('agendamentos', {
  state: () => ({
    agendamentos: [],
    currentAgendamento: null,
    loading: false,
    error: null
  }),

  getters: {
    agendamentosAtivos: (state) => 
      state.agendamentos.filter(a => a.status !== 'cancelado'),
    
    totalAgendamentos: (state) => state.agendamentos.length
  },

  actions: {
    async fetchAgendamentos() {
      this.loading = true
      try {
        this.agendamentos = await agendamentoService.getAll()
      } catch (err) {
        this.error = err.message
      } finally {
        this.loading = false
      }
    },

    async createAgendamento(data) {
      const novo = await agendamentoService.create(data)
      this.agendamentos.push(novo)
      return novo
    },

    async cancelarAgendamento(id, motivo) {
      await agendamentoService.cancelar(id, motivo)
      const index = this.agendamentos.findIndex(a => a.id === id)
      if (index !== -1) {
        this.agendamentos[index].status = 'cancelado'
      }
    }
  }
})
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
    "vue": "^3.4.0",
    "vue-router": "^4.2.0",
    "pinia": "^2.1.0",
    "primevue": "^4.2.0",
    "@primevue/themes": "^4.2.0",
    "primeicons": "^7.0.0",
    "axios": "^1.6.0",
    "date-fns": "^3.0.0"
  },
  "devDependencies": {
    "@vitejs/plugin-vue": "^5.0.0",
    "vite": "^5.0.0",
    "tailwindcss": "^3.4.0",
    "autoprefixer": "^10.4.0",
    "postcss": "^8.4.0"
  }
}
```

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

## 🚀 Próximos Passos do MVP

## 🚀 Próximos Passos e Estado Atual do MVP

### 0. Resumo da Arquitetura de Banco (14 tabelas, 5 módulos)

A arquitetura de banco foi projetada para MySQL/MariaDB com visão de longo prazo. Abaixo um resumo:

#### Módulo de Autenticação
| Tabela | Colunas-chave | Função |
|--------|--------------|--------|
| `usuarios` | id, nome_completo, email, senha_hash, tipo(cliente/profissional/admin), ativo, deleted_at | Autenticação unificada para todos os perfis |
| `perfis` | usuario_id, foto_url, data_nascimento, genero, endereço completo, latitude, longitude | Dados complementares do usuário |

#### Módulo de Estabelecimentos
| Tabela | Colunas-chave | Função |
|--------|--------------|--------|
| `estabelecimentos` | id, nome_fantasia, cnpj, descricao, foto_capa_url, telefone, endereço completo, latitude, longitude, ativo, deleted_at | Dados dos negócios (salões, barbearias, clínicas) |
| `profissionais` | id, usuario_id, estabelecimento_id, nome_profissional, especialidade, foto_url, aceita_agendamentos, deleted_at | Prestadores vinculados a estabelecimentos |
| `servicos` | id, nome, descricao, categoria(enum), ativo | Catálogo global de serviços |
| `servicos_profissionais` | profissional_id, servico_id, preco, tempo_execucao_minutos, ativo | Pivot N:N — preço e tempo específicos por profissional |

#### Módulo de Agenda
| Tabela | Colunas-chave | Função |
|--------|--------------|--------|
| `horarios_funcionamento` | profissional_id, dia_semana(0-6), hora_inicio, hora_fim | Grade semanal de cada profissional |
| `bloqueios_agenda` | profissional_id, data_inicio, data_fim, motivo, tipo(ferias/folga/almoco) | Bloqueios pontuais na agenda |
| `agendamentos` | cliente_id, profissional_id, data_hora_inicio, data_hora_fim, status(7 opções), valor_total, cancelado_por_id, motivo_cancelamento, deleted_at | Registro de agendamentos |
| `itens_agendamentos` | agendamento_id, servico_id, nome_servico, preco_praticado, tempo_execucao_minutos | Snapshot dos serviços no momento do agendamento |
| `historico_agendamentos` | agendamento_id, status_anterior, status_novo, alterado_por_id | Auditoria de mudanças de status |

#### Módulo Financeiro
| Tabela | Colunas-chave | Função |
|--------|--------------|--------|
| `relatorios_financeiros` | profissional_id, data_referencia, periodo_tipo(dia/semana/mes), total_agendamentos, receita_total, receita_confirmada | Dados pré-processados via queues |

#### Módulo de Engajamento (Futuro)
| Tabela | Colunas-chave | Função |
|--------|--------------|--------|
| `avaliacoes` | cliente_id, estabelecimento_id, profissional_id, agendamento_id, nota(1-5), comentario | Avaliações pós-serviço |
| `notificacoes` | usuario_id, tipo(push/email/sms), assunto, mensagem, agendamento_id, lida | Comunicados e lembretes |

#### Relacionamentos Principais
```
1 estabelecimentos : N profissionais
1 profissionais : N servicos_profissionais
1 servicos : N servicos_profissionais
1 profissionais : N horarios_funcionamento
1 profissionais : N bloqueios_agenda
1 profissionais : N agendamentos
1 usuarios (cliente) : N agendamentos
1 agendamentos : N itens_agendamentos
1 agendamentos : N historico_agendamentos
```

---

### 1. Estado Atual — O que foi implementado vs O que falta

#### ✅ Implementado (compatível com a arquitetura)
| Funcionalidade | Arquivo(s) | Tabelas que consome | Status |
|---------------|-----------|---------------------|--------|
| Listagem de estabelecimentos | `EstabelecimentosPage.vue`, `EstabelecimentoCard.vue` | `estabelecimentos` | ✅ Funcional (dados mock) |
| Detalhe do estabelecimento | `EstabelecimentoDetailPage.vue` | `estabelecimentos`, `servicos` | ✅ Funcional |
| Listagem de serviços por estabelecimento | `ServicoItem.vue` | `servicos` | ✅ Funcional |
| Busca de estabelecimentos (nome/categoria) | `SearchBar.vue`, `EstabelecimentosPage.vue` | `estabelecimentos`, `servicos` | ✅ Funcional |
| Login simples (email/senha) | `LoginPage.vue`, `stores/auth.js` | `usuarios` | ✅ Mock (localStorage) |
| Fluxo de agendamento (calendário + horários) | `ServicoItem.vue` (Drawer) | `agendamentos` | ✅ Funcional via JSON Server |
| Listagem de agendamentos (confirmados/finalizados) | `AgendamentosPage.vue`, `AgendamentoCard.vue` | `agendamentos` | ✅ Funcional |
| Cancelamento de agendamento | `AgendamentoCard.vue` (ConfirmDialog) | `agendamentos` | ✅ Funcional |
| Estatísticas do usuário (total investido, favorito) | `AgendamentosPage.vue`, `stores/agendamentos.js` | `agendamentos` | ✅ Computed localmente |
| Categorias de serviço | `categorias.js`, `CategoriaItem.vue` | `servicos.categoria` | ✅ Constantes locais |
| Navegação completa (router + layouts + auth guard) | `router/index.js`, `App.vue`, `DefaultLayout.vue`, `AuthLayout.vue` | — | ✅ Funcional |
| Header/Footer/Sidebar responsivos | `AppHeader.vue`, `AppFooter.vue`, `AppSidebar.vue` | — | ✅ Funcional |

#### ⚠️ Parcialmente implementado — Precisa adequar
| Funcionalidade | O que falta | Tabelas envolvidas |
|---------------|------------|-------------------|
| **Profissionais** | Frontend não exibe profissionais individuais. Atualmente os serviços são vinculados diretamente ao estabelecimento. Na arquitetura real, o fluxo é: Estabelecimento → Profissional → Serviço (via `servicos_profissionais`). Criar componente `ProfissionalCard.vue` e adaptar `ServicoItem.vue` para exibir o profissional vinculado. | `profissionais`, `servicos_profissionais` |
| **Horários de funcionamento** | Os horários estão hardcoded em `EstabelecimentoDetailPage.vue`. Devem vir de `horarios_funcionamento` (por profissional, não por estabelecimento). Também impacta o cálculo de slots no `ServicoItem.vue`. | `horarios_funcionamento` |
| **Cálculo de disponibilidade** | O filtro de horários disponíveis em `ServicoItem.vue` é simplificado. Na arquitetura real, precisa consultar: horários do profissional + bloqueios + agendamentos existentes + tempo de execução do serviço. | `horarios_funcionamento`, `bloqueios_agenda`, `agendamentos`, `servicos_profissionais` |
| **Status detalhado de agendamentos** | Frontend usa apenas `confirmado`/`finalizado`. A arquitetura prevê 7 status (pendente, confirmado, em_atendimento, concluido, cancelado_cliente, cancelado_profissional, nao_compareceu). Adaptar `AgendamentoCard.vue`. | `agendamentos.status` |
| **Dados do agendamento** | Mock atual salva dados denormalizados (servico/estabelecimento inline). Na arquitetura real usa `itens_agendamentos` com snapshot de preço + `profissional_id`. | `agendamentos`, `itens_agendamentos` |
| **Autenticação** | Login é 100% mock (aceita qualquer email/senha). Precisa de JWT real + diferenciação de perfil (cliente/profissional/admin). | `usuarios` |
| **Geolocalização** | Não implementada. Arquitetura tem lat/lng em `estabelecimentos`. Precisa de `useGeolocation.js` composable e busca por proximidade. | `estabelecimentos.latitude`, `estabelecimentos.longitude` |

#### ❌ Não implementado — Criar do zero
| Funcionalidade | Tabelas envolvidas | Prioridade |
|---------------|-------------------|-----------|
| **Dashboard do Profissional** — painel com agenda do dia, próximos atendimentos, estatísticas | `agendamentos`, `profissionais`, `servicos_profissionais` | 🔴 Alta |
| **Gestão de Serviços** — CRUD de serviços pelo profissional (nome, preço, tempo) | `servicos`, `servicos_profissionais` | 🔴 Alta |
| **Configuração de Horários** — grade de funcionamento semanal | `horarios_funcionamento` | 🔴 Alta |
| **Gestão de Bloqueios** — marcar férias, folgas, intervalos | `bloqueios_agenda` | 🟡 Média |
| **Relatório Financeiro** — visualização por dia/semana/mês | `relatorios_financeiros`, `agendamentos`, `itens_agendamentos` | 🟡 Média |
| **Tela de Registro** — cadastro de novos usuários (cliente e profissional) | `usuarios`, `perfis` | 🔴 Alta |
| **Página de Perfil** — editar dados pessoais, endereço, foto | `perfis` | 🟡 Média |
| **Avaliações** — nota + comentário pós-serviço | `avaliacoes` | 🟢 Baixa (pós-MVP) |
| **Notificações** — lembretes e avisos | `notificacoes` | 🟢 Baixa (pós-MVP) |
| **DashboardLayout.vue** — layout exclusivo para painel do profissional | — | 🔴 Alta |

---

### 2. Estratégia de Dados — Mock → API Real

#### Estado Atual (Desenvolvimento Frontend)
- **JSON Server** (`db.json` na raiz do projeto) fornece API REST mock na porta 3001
- **Dados estáticos** também existem inline em alguns componentes:
  - Horários de funcionamento → hardcoded em `EstabelecimentoDetailPage.vue`
  - Categorias de serviço → `constants/categorias.js`
  - Características do estabelecimento → array fixo em `EstabelecimentoDetailPage.vue`
  - Avaliação/rating → dados ficam no mock de `db.json`
- **Login mock** → aceita qualquer credencial, salva token fictício no localStorage
- **Agendamentos** → CRUD funcional via JSON Server (POST/PATCH/DELETE)
- Variável de ambiente: `VITE_API_URL` (default: `http://localhost:3001`)
- Scripts: `npm run dev` (Vite) / `npm run mock` (JSON Server)

#### Próximo Passo: API Laravel 11
Quando criarmos a API real:
1. **Substituir JSON Server** por API Laravel 11 com Eloquent ORM
2. **Manter a mesma interface** dos services (`estabelecimentoService.js`, `agendamentoService.js`, etc.) — apenas trocar a `baseURL` e ajustar payloads se necessário
3. **Implementar autenticação JWT real** (Laravel Sanctum ou Passport)
4. **Criar migrations** para as 14 tabelas da arquitetura
5. **Criar seeders** com dados de teste equivalentes ao `db.json`
6. **Adaptar stores Pinia** para consumir a nova estrutura de resposta da API

#### O que NÃO muda ao migrar para Laravel:
- Componentes Vue (apenas consomem dados via services)
- Router e guards (apenas verificam token)
- Estilos e design system
- Utilitários e formatters

#### O que PRECISA mudar:
- `services/api.js` → baseURL apontando para Laravel
- `services/*.js` → ajustar endpoints e payloads para a API real (ex: incluir `profissional_id`)
- `stores/auth.js` → usar endpoint real de login/registro com JWT
- Componentes → exibir dados de `profissionais` e `servicos_profissionais` que não existem no mock

---

### 3. Roadmap de Implementação

#### Fase 2: Autenticação e Registro (próxima)
1. Criar `RegistroPage.vue` (cadastro de cliente)
2. Criar `RegistroProfissionalPage.vue` (cadastro de profissional + vínculo com estabelecimento)
3. Adaptar `stores/auth.js` para diferenciar perfis (cliente/profissional/admin)
4. Criar route guard por tipo de perfil (`meta.requiresRole`)

#### Fase 3: Dashboard do Profissional
1. Criar `DashboardLayout.vue` (header lateral, menu de painel)
2. Criar `DashboardPage.vue` (agenda do dia, próximos atendimentos)
3. Criar `ServicosGestaoPage.vue` (CRUD de `servicos_profissionais`)
4. Criar `HorariosPage.vue` (configuração de grade semanal)
5. Criar `BloqueiosPage.vue` (cadastro de férias/folgas)

#### Fase 4: Aprimoramento do Fluxo de Agendamento
1. Adaptar `ServicoItem.vue` para selecionar profissional antes do horário
2. Implementar cálculo real de disponibilidade (horários - bloqueios - agendamentos existentes - tempo do serviço)
3. Criar `itens_agendamentos` no payload do agendamento
4. Implementar os 7 status com transições válidas
5. Adaptar `AgendamentoCard.vue` para exibir profissional e status detalhado

#### Fase 5: API Laravel 11
1. Criar projeto Laravel 11 (API-only)
2. Implementar migrations para as 14 tabelas
3. Criar Models + Relationships Eloquent
4. Implementar Controllers (RESTful) e Form Requests
5. Configurar autenticação JWT (Sanctum)
6. Criar seeders baseados no `db.json`
7. Atualizar `services/api.js` para apontar ao Laravel
8. Adaptar services para nova estrutura de response

#### Fase 6: Relatórios e Engajamento
1. Implementar `RelatorioFinanceiroPage.vue`
2. Job/queue para processamento de `relatorios_financeiros`
3. Implementar geolocalização (`useGeolocation.js` + busca por proximidade)
4. Implementar sistema de avaliações pós-serviço
5. Implementar notificações básicas

#### Fase 7: Polimento
1. Implementar sistema de theming escalável (presets PrimeVue: cores rosa, azul, etc.)
2. Otimizar responsividade e espaçamentos (fix dos 10% de layout)
3. Implementar PWA (manifest, service worker)
4. Testes E2E com Playwright
5. CI/CD via GitHub Actions

---

### 4. Decisões Técnicas Registradas

| Decisão | Escolha | Justificativa |
|---------|---------|---------------|
| Dark mode | `.dark-mode` class-based | Escalável para troca de tema via config |
| Login MVP | Email/senha simples | OAuth posterior |
| Nomenclatura | PT-BR em todo frontend | Consistência com banco e público-alvo |
| Ícones | Lucide + PrimeIcons | Lucide para customizados, PrimeIcons para integração PrimeVue |
| Design system | Glassmorphism + stat cards + Aura | Premium, moderno, dark-first |
| Mock API | JSON Server | Navegabilidade antes de criar backend |
| Backend futuro | Laravel 11 | Maturidade, Eloquent, Sanctum, queues |
| Referência visual | Projeto React BarberLab | Figma com acesso protegido (403) |

---

**🎯 Skill atualizada com contexto completo! Consulte esta seção para retomar o trabalho em qualquer momento.**

