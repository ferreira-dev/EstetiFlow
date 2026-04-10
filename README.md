# EstetiFlow — SaaS de Agendamento para Serviços Estéticos

Plataforma SaaS para agendamento de serviços estéticos (barbearias, salões, clínicas, etc.) com gestão completa para profissionais e experiência de agendamento fluida para clientes.

---

## 🛠️ Stack Tecnológica

| Camada | Tecnologia |
|--------|-----------|
| **Frontend** | Vue 3 (Composition API) + Inertia.js + PrimeVue 4 (Aura) + Tailwind CSS |
| **Backend** | Laravel 11 (monolito com Inertia) |
| **Banco** | MySQL 8.0 |
| **Auth** | Sessão Laravel + Spatie Permission |
| **Infra** | Docker Compose (6 containers) |

---

## 🐳 Setup com Docker

### Pré-requisitos
- Docker + Docker Compose

### Subir o ambiente

```bash
docker compose up -d
```

### Acessar o container Node (para npm)

```bash
docker compose exec node sh
```

### Comandos úteis dentro do container

```bash
# Instalar dependências
npm install

# Dev server (Vite)
npm run dev

# Build produção
npm run build
```

### Acessar o container PHP (para artisan)

```bash
docker compose exec app sh
```

### Comandos artisan úteis

```bash
# Rodar migrations
php artisan migrate

# Rodar seeders (dados de teste)
php artisan db:seed

# Resetar banco (⚠️ apaga tudo)
php artisan migrate:fresh --seed

# Listar rotas
php artisan route:list
```

---

## 📂 Estrutura de Pastas

```
app/Http/Controllers/       # Controllers Laravel
app/Models/                 # Eloquent Models (14 tabelas)
app/Services/               # Business logic services
routes/web.php              # Rotas Inertia
resources/js/
├── Components/             # Componentes Vue reutilizáveis
├── Constants/              # Constantes (categorias, etc.)
├── Layouts/                # DefaultLayout, AuthLayout, DashboardLayout
├── Pages/                  # Páginas Inertia (Home, Login, Profissional/, etc.)
└── Utils/                  # Utilidades (formatters)
```

---

## 📖 Documentação Técnica Detalhada

A documentação completa do projeto vive nas **skills** do agente:

| Documento | Caminho | Conteúdo |
|-----------|---------|----------|
| **Contexto do Projeto** | `.agents/skills/project-context/SKILL.md` | Stack, arquitetura, padrões de código, roadmap, estado atual |
| **Schema de Banco** | `.agents/skills/saas-database-schema/SKILL.md` | 14 tabelas, tipos de dados, relacionamentos, regras de negócio |
| **Arquitetura de Banco** | `.agents/skills/database-architecture/SKILL.md` | Metodologia de modelagem MySQL/MariaDB |

> **⚠️ Fonte de verdade:** Sempre consulte os arquivos acima para contexto técnico. Este README é um resumo de setup.

---

## 🚀 Estado Atual

### ✅ Implementado
- Registro e login com roles (cliente, profissional, admin)
- Painel do profissional (DashboardLayout + menu lateral)
- Cadastro de estabelecimento pelo profissional
- CRUD de serviços (catálogo global + criação personalizada)
- Listagem e detalhe de estabelecimentos
- Agendamento de serviços
- Cancelamento de agendamentos

### 🔜 Próxima fase
- Configuração de horários de funcionamento
- Dashboard com agenda do dia
- Bloqueios de agenda

> Veja o roadmap completo em `.agents/skills/project-context/SKILL.md` → seção "Roadmap de Implementação".
