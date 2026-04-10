---
name: Database Schema - SaaS Agendamento Estético
description: Documentação completa e autoritativa do schema de banco de dados do projeto. Contém todas as tabelas, tipos de dados, relacionamentos e regras de negócio. Consulte esta skill antes de qualquer decisão que envolva o banco de dados.
---

# 🗄️ Database Schema — SaaS Agendamento Estético

> **⚠️ IMPORTANTE:** Esta é a fonte de verdade do schema de banco de dados do projeto.
> Qualquer alteração no schema deve ser discutida com base nesta documentação e executada
> seguindo a skill de arquitetura: `.agents/skills/database-architecture/SKILL.md`.

---

## Visão Geral

- **SGBD:** MySQL 8.0 / MariaDB
- **Charset:** UTF8MB4
- **Engine:** InnoDB
- **Total de tabelas:** 14
- **Módulos:** 5 (Autenticação, Estabelecimentos, Agenda, Financeiro, Engajamento)
- **Nomenclatura:** `snake_case`, plural, PT-BR

---

## 📦 Módulos e Tabelas

```
Módulo de Autenticação      → usuarios, perfis
Módulo de Estabelecimentos  → estabelecimentos, profissionais, servicos, servicos_profissionais
Módulo de Agenda            → horarios_funcionamento, bloqueios_agenda, agendamentos,
                              itens_agendamentos, historico_agendamentos
Módulo Financeiro           → relatorios_financeiros
Módulo de Engajamento       → avaliacoes, notificacoes
```

---

## 🔗 Relacionamentos Principais

```
1 usuarios (cliente)    : N agendamentos
1 usuarios              : 1 perfis
1 estabelecimentos      : N profissionais
1 profissionais         : N servicos_profissionais
1 servicos              : N servicos_profissionais
1 profissionais         : N horarios_funcionamento
1 profissionais         : N bloqueios_agenda
1 profissionais         : N agendamentos
1 agendamentos          : N itens_agendamentos
1 agendamentos          : N historico_agendamentos
1 agendamentos          : N avaliacoes
1 usuarios              : N notificacoes
```

---

## 📋 Schema Detalhado por Tabela

### Módulo de Autenticação

```json
{
  "usuarios": {
    "_descricao": "Autenticação unificada para todos os perfis do sistema (cliente, profissional e admin). Um único registro por pessoa.",
    "id": "bigint unsigned not null primary key auto_increment",
    "nome_completo": "varchar(150) not null",
    "email": "varchar(100) not null unique",
    "senha_hash": "varchar(255) not null",
    "tipo": "enum('cliente', 'profissional', 'admin') not null default 'cliente'",
    "ativo": "tinyint(1) not null default 1",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "deleted_at": "timestamp null",
    "_relacionamentos": [
      "1 usuarios : 1 perfis",
      "1 usuarios (cliente) : N agendamentos",
      "1 usuarios : N notificacoes"
    ]
  },

  "perfis": {
    "_descricao": "Dados complementares do usuário, separados da autenticação. Relação 1:1 com usuarios.",
    "id": "bigint unsigned not null primary key auto_increment",
    "usuario_id": "bigint unsigned not null unique",
    "foto_url": "varchar(500) null",
    "data_nascimento": "date null",
    "genero": "enum('masculino', 'feminino', 'outro', 'prefiro_nao_informar') null",
    "telefone": "varchar(20) null",
    "cep": "char(8) null",
    "logradouro": "varchar(200) null",
    "numero": "varchar(10) null",
    "complemento": "varchar(100) null",
    "bairro": "varchar(100) null",
    "cidade": "varchar(100) null",
    "estado": "char(2) null",
    "latitude": "decimal(10,7) null",
    "longitude": "decimal(10,7) null",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "_relacionamentos": [
      "1 perfis : 1 usuarios (usuario_id)"
    ]
  }
}
```

---

### Módulo de Estabelecimentos

```json
{
  "estabelecimentos": {
    "_descricao": "Dados dos negócios cadastrados na plataforma (salões, barbearias, clínicas estéticas, etc.).",
    "id": "bigint unsigned not null primary key auto_increment",
    "nome_fantasia": "varchar(150) not null",
    "cnpj": "char(14) null unique",
    "descricao": "text null",
    "foto_capa_url": "varchar(500) null",
    "telefone_principal": "varchar(20) null",
    "telefone_secundario": "varchar(20) null",
    "cep": "char(8) null",
    "logradouro": "varchar(200) null",
    "numero": "varchar(10) null",
    "complemento": "varchar(100) null",
    "bairro": "varchar(100) null",
    "cidade": "varchar(100) null",
    "estado": "char(2) null",
    "latitude": "decimal(10,7) null",
    "longitude": "decimal(10,7) null",
    "ativo": "tinyint(1) not null default 1",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "deleted_at": "timestamp null",
    "_relacionamentos": [
      "1 estabelecimentos : N profissionais",
      "1 estabelecimentos : N avaliacoes"
    ]
  },

  "profissionais": {
    "_descricao": "Prestadores de serviço vinculados a um estabelecimento. Um usuário do tipo 'profissional' pode ter um registro aqui.",
    "id": "bigint unsigned not null primary key auto_increment",
    "usuario_id": "bigint unsigned not null",
    "estabelecimento_id": "bigint unsigned not null",
    "nome_profissional": "varchar(150) not null",
    "especialidade": "varchar(150) null",
    "foto_url": "varchar(500) null",
    "aceita_agendamentos": "tinyint(1) not null default 1",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "deleted_at": "timestamp null",
    "_relacionamentos": [
      "1 profissionais : 1 usuarios (usuario_id)",
      "1 profissionais : N servicos_profissionais",
      "1 profissionais : N horarios_funcionamento",
      "1 profissionais : N bloqueios_agenda",
      "1 profissionais : N agendamentos",
      "1 profissionais : N avaliacoes"
    ]
  },

  "servicos": {
    "_descricao": "Catálogo global de tipos de serviço disponíveis na plataforma. Não contém preço nem tempo — esses ficam em servicos_profissionais.",
    "id": "bigint unsigned not null primary key auto_increment",
    "nome": "varchar(150) not null",
    "descricao": "text null",
    "categoria": "enum('cabelo', 'barba', 'manicure', 'pedicure', 'sobrancelha', 'cilio', 'estetica', 'massagem', 'maquiagem', 'depilacao', 'hidratacao', 'outro') not null",
    "ativo": "tinyint(1) not null default 1",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "_relacionamentos": [
      "1 servicos : N servicos_profissionais"
    ]
  },

  "servicos_profissionais": {
    "_descricao": "Tabela pivot N:N entre profissionais e servicos. Armazena preço e tempo de execução específicos de cada profissional para cada serviço.",
    "id": "bigint unsigned not null primary key auto_increment",
    "profissional_id": "bigint unsigned not null",
    "servico_id": "bigint unsigned not null",
    "preco": "decimal(10,2) not null",
    "tempo_execucao_minutos": "smallint unsigned not null",
    "ativo": "tinyint(1) not null default 1",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "_relacionamentos": [
      "N servicos_profissionais : 1 profissionais (profissional_id)",
      "N servicos_profissionais : 1 servicos (servico_id)"
    ]
  }
}
```

---

### Módulo de Agenda

```json
{
  "horarios_funcionamento": {
    "_descricao": "Grade semanal de disponibilidade de cada profissional. Um registro por dia da semana por profissional.",
    "id": "bigint unsigned not null primary key auto_increment",
    "profissional_id": "bigint unsigned not null",
    "dia_semana": "tinyint unsigned not null",
    "_dia_semana_nota": "0=Domingo, 1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado",
    "hora_inicio": "time not null",
    "hora_fim": "time not null",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "_relacionamentos": [
      "N horarios_funcionamento : 1 profissionais (profissional_id)"
    ]
  },

  "bloqueios_agenda": {
    "_descricao": "Bloqueios pontuais na agenda do profissional (férias, folgas, almoço, etc.). Impede agendamentos no período.",
    "id": "bigint unsigned not null primary key auto_increment",
    "profissional_id": "bigint unsigned not null",
    "data_inicio": "datetime not null",
    "data_fim": "datetime not null",
    "motivo": "varchar(255) null",
    "tipo": "enum('ferias', 'folga', 'almoco', 'outro') not null default 'outro'",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "_relacionamentos": [
      "N bloqueios_agenda : 1 profissionais (profissional_id)"
    ]
  },

  "agendamentos": {
    "_descricao": "Registro principal de cada agendamento realizado. Conecta cliente, profissional e período. Os serviços ficam em itens_agendamentos.",
    "id": "bigint unsigned not null primary key auto_increment",
    "cliente_id": "bigint unsigned not null",
    "profissional_id": "bigint unsigned not null",
    "data_hora_inicio": "datetime not null",
    "data_hora_fim": "datetime not null",
    "status": "enum('pendente', 'confirmado', 'em_atendimento', 'concluido', 'cancelado_cliente', 'cancelado_profissional', 'nao_compareceu') not null default 'pendente'",
    "valor_total": "decimal(10,2) not null",
    "cancelado_por_id": "bigint unsigned null",
    "motivo_cancelamento": "text null",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "deleted_at": "timestamp null",
    "_relacionamentos": [
      "N agendamentos : 1 usuarios (cliente_id)",
      "N agendamentos : 1 profissionais (profissional_id)",
      "1 agendamentos : N itens_agendamentos",
      "1 agendamentos : N historico_agendamentos",
      "1 agendamentos : N avaliacoes"
    ]
  },

  "itens_agendamentos": {
    "_descricao": "Snapshot dos serviços no momento do agendamento. Preserva preço e tempo praticados, independente de alterações futuras em servicos_profissionais.",
    "id": "bigint unsigned not null primary key auto_increment",
    "agendamento_id": "bigint unsigned not null",
    "servico_id": "bigint unsigned not null",
    "nome_servico": "varchar(150) not null",
    "preco_praticado": "decimal(10,2) not null",
    "tempo_execucao_minutos": "smallint unsigned not null",
    "created_at": "timestamp default current_timestamp",
    "_relacionamentos": [
      "N itens_agendamentos : 1 agendamentos (agendamento_id)",
      "N itens_agendamentos : 1 servicos (servico_id)"
    ]
  },

  "historico_agendamentos": {
    "_descricao": "Auditoria de todas as mudanças de status de um agendamento. Permite rastrear quem alterou e quando.",
    "id": "bigint unsigned not null primary key auto_increment",
    "agendamento_id": "bigint unsigned not null",
    "status_anterior": "enum('pendente', 'confirmado', 'em_atendimento', 'concluido', 'cancelado_cliente', 'cancelado_profissional', 'nao_compareceu') not null",
    "status_novo": "enum('pendente', 'confirmado', 'em_atendimento', 'concluido', 'cancelado_cliente', 'cancelado_profissional', 'nao_compareceu') not null",
    "alterado_por_id": "bigint unsigned not null",
    "created_at": "timestamp default current_timestamp",
    "_relacionamentos": [
      "N historico_agendamentos : 1 agendamentos (agendamento_id)",
      "N historico_agendamentos : 1 usuarios (alterado_por_id)"
    ]
  }
}
```

---

### Módulo Financeiro

```json
{
  "relatorios_financeiros": {
    "_descricao": "Dados pré-processados para dashboards financeiros. Populados via jobs/queues assíncronos para evitar queries pesadas em tempo real.",
    "id": "bigint unsigned not null primary key auto_increment",
    "profissional_id": "bigint unsigned not null",
    "data_referencia": "date not null",
    "periodo_tipo": "enum('dia', 'semana', 'mes') not null",
    "total_agendamentos": "int unsigned not null default 0",
    "receita_total": "decimal(10,2) not null default 0.00",
    "receita_confirmada": "decimal(10,2) not null default 0.00",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "_relacionamentos": [
      "N relatorios_financeiros : 1 profissionais (profissional_id)"
    ]
  }
}
```

---

### Módulo de Engajamento (Futuro — Pós-MVP)

```json
{
  "avaliacoes": {
    "_descricao": "Avaliações pós-serviço feitas pelo cliente. Vinculada ao agendamento para garantir que apenas quem agendou pode avaliar.",
    "id": "bigint unsigned not null primary key auto_increment",
    "cliente_id": "bigint unsigned not null",
    "estabelecimento_id": "bigint unsigned not null",
    "profissional_id": "bigint unsigned not null",
    "agendamento_id": "bigint unsigned not null unique",
    "nota": "tinyint unsigned not null",
    "_nota_nota": "Valor entre 1 e 5",
    "comentario": "text null",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "_relacionamentos": [
      "N avaliacoes : 1 usuarios (cliente_id)",
      "N avaliacoes : 1 estabelecimentos (estabelecimento_id)",
      "N avaliacoes : 1 profissionais (profissional_id)",
      "1 avaliacoes : 1 agendamentos (agendamento_id)"
    ]
  },

  "notificacoes": {
    "_descricao": "Comunicados e lembretes enviados aos usuários. Pode ser push, email ou SMS. Vinculada opcionalmente a um agendamento.",
    "id": "bigint unsigned not null primary key auto_increment",
    "usuario_id": "bigint unsigned not null",
    "tipo": "enum('push', 'email', 'sms') not null",
    "assunto": "varchar(255) not null",
    "mensagem": "text not null",
    "agendamento_id": "bigint unsigned null",
    "lida": "tinyint(1) not null default 0",
    "created_at": "timestamp default current_timestamp",
    "updated_at": "timestamp default current_timestamp on update current_timestamp",
    "_relacionamentos": [
      "N notificacoes : 1 usuarios (usuario_id)",
      "N notificacoes : 1 agendamentos (agendamento_id) — opcional"
    ]
  }
}
```

---

## 📌 Regras de Negócio Importantes

### Status de Agendamento — Transições Válidas
```
pendente           → confirmado | cancelado_cliente | cancelado_profissional
confirmado         → em_atendimento | cancelado_cliente | cancelado_profissional | nao_compareceu
em_atendimento     → concluido
concluido          → (terminal — sem transições)
cancelado_*        → (terminal — sem transições)
nao_compareceu     → (terminal — sem transições)
```

### Soft Delete
As seguintes tabelas utilizam `deleted_at` (soft delete):
- `usuarios`
- `estabelecimentos`
- `profissionais`
- `agendamentos`

### Múltiplos Serviços por Agendamento

Um agendamento pode conter N itens em `itens_agendamentos` (ex: corte + barba).
O `data_hora_fim` do agendamento é calculado somando o `tempo_execucao_minutos` de todos os itens.
O bloco de tempo inteiro fica indisponível para outros clientes.
A UI deve permitir seleção múltipla de serviços **antes** da escolha do horário, exibindo apenas slots que comportem o tempo total somado.

### Snapshot de Preço
A tabela `itens_agendamentos` armazena `nome_servico`, `preco_praticado` e `tempo_execucao_minutos` como snapshot do momento do agendamento. Isso garante que alterações futuras de preço em `servicos_profissionais` não afetam agendamentos já realizados.

### Categorias de Serviço (ENUM)
```
cabelo | barba | manicure | pedicure | sobrancelha | cilio |
estetica | massagem | maquiagem | depilacao | hidratacao | outro
```

### Geolocalização
Tanto `perfis` quanto `estabelecimentos` possuem `latitude` e `longitude` do tipo `decimal(10,7)` para suportar buscas por proximidade.

---

## 🔄 Correspondência com Seeders (dados MVP)

Os seeders em `database/seeders/DatabaseSeeder.php` populam as 4 tabelas MVP com dados equivalentes ao antigo `db.json`. Abaixo a correspondência com o schema real quando a normalização completa for implementada:

| Campo nos Seeders | Tabela/Coluna real |
|------------------|-------------------|
| `users.nome` | `usuarios.nome_completo` |
| `estabelecimentos.imagemUrl` | `estabelecimentos.foto_capa_url` |
| `estabelecimentos.endereco` (string) | `estabelecimentos.logradouro + numero + bairro + cidade + estado` |
| `estabelecimentos.telefones[]` (JSON) | `estabelecimentos.telefone_principal + telefone_secundario` |
| `servicos.estabelecimento_id` (FK direta) | *(no schema completo: vinculado via `servicos_profissionais`)* |
| `servicos.preco` | `servicos_profissionais.preco` |
| `servicos.duracao` | `servicos_profissionais.tempo_execucao_minutos` |
| `agendamentos.usuario_id` | `agendamentos.cliente_id` |
| `agendamentos.data` | `agendamentos.data_hora_inicio` |
| `agendamentos.servico{}` (JSON snapshot) | `itens_agendamentos` (tabela separada) |
| `agendamentos.estabelecimento{}` (JSON snapshot) | Derivado via `profissional_id → estabelecimento_id` |

---

## 📝 Notas para Futuras Alterações

> Antes de propor qualquer alteração neste schema, consulte a skill de arquitetura:
> `.agents/skills/database-architecture/SKILL.md`
>
> Toda mudança deve ser justificada por uma regra de negócio clara e avaliada
> quanto ao impacto nos relacionamentos existentes.

