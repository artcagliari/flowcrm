# Relatório de Implementação — FlowCRM

**Data de referência:** junho/2026  
**Repositório:** `artcagliari/flowcrm`  
**Último commit principal:** `ba9a950` — *Reposiciona FlowCRM como CRM comercial para empresas com recursos avançados*

---

## 1. Resumo executivo

O FlowCRM evoluiu de um CRM genérico de vendas B2B para um produto orientado a **profissionais autônomos** (advogados/psicólogos), passou por consolidação em **modo psicólogo**, e foi **reposicionado como CRM comercial para empresas** (PME).

O estado atual é um **SaaS multi-tenant** com:

- Gestão de **leads, clientes, oportunidades, pipeline, tarefas, agenda, financeiro e documentos**
- **Integrações:** Google Calendar OAuth, WhatsApp (API + webhook), webhooks outbound, import/export CSV, OpenAPI
- **Recursos avançados no backend:** automações, sequências, campos customizados, audit log, LGPD, relatórios PDF
- **28 testes automatizados** passando no backend

---

## 2. Linha do tempo das mudanças

### Fase 1 — Pivot profissional (advogado + psicólogo)

- Introdução de `profession_mode` por empresa
- Módulo `professional_cases` (processos / acompanhamentos)
- UI com vocabulário por profissão (`profession.js`, `useProfessionMode`)
- Páginas específicas: `AdvogadoCases`, `PsicologoCases`
- Estágios de lead adaptados ao consultório/escritório
- Correções P0: migration de notas, Google Calendar sync, LGPD

### Fase 2 — Simplificação operacional

- **WhatsApp direto** via link `wa.me` (`WhatsappActionButton`) — sem depender de Meta inbox
- **Select de status** inline para clientes (`ClientStatusSelect`)
- **Webhooks:** UI removida das Integrações; backend de dispatch mantido
- **Google Calendar OAuth** por empresa (cada cliente conecta a própria conta Google)
- Remoção da aba **Inbox WhatsApp** (sem integração Meta ativa)
- Criação automática de atendimento ao cadastrar paciente (depois revertida na fase empresa)

### Fase 3 — Consolidação psicólogo-only

- Remoção do modo **advogado** (UI, seeder, defaults)
- `profession_mode` forçado para `psicologo` em novas empresas
- Demo único: consultório psicológico (`empresa@crm.com`)

### Fase 4 — Agenda como centro do atendimento

- Remoção da aba **Atendimentos** separada
- Atendimentos/sessões feitos **direto na Agenda**
- `ClientSelect` e `EntityAutocomplete` com **busca por nome** e auto-seleção
- Tipos de compromisso focados em sessão (psicólogo)

### Fase 5 — Reposicionamento para empresa (estado atual)

- `profession_mode` padrão: **`empresa`**
- Vocabulário comercial: Leads, Clientes, Oportunidades, Pipeline
- Menu: Dashboard → Leads → Clientes → Oportunidades → Pipeline → …
- Funil padrão B2B: Novo lead → Qualificação → Proposta → Negociação → Fechado
- Seeder demo: **Empresa Demo Comercial**
- Commit e push para `main` no GitHub

### Fase 6 — Infraestrutura de teste (parcial)

- Configuração **ngrok** para OAuth Google em ambiente local
- Configuração **nginx** (`deploy/nginx-flowcrm.conf`) para servir frontend + proxy API (porta 8080)
- Documentação de setup Google Cloud OAuth no README

---

## 3. Arquitetura técnica

```text
flowcrm/
├── backend/          Laravel 12 API REST + Sanctum
│   ├── app/
│   │   ├── Http/Controllers/Api/   # 40+ controllers
│   │   ├── Models/                 # Multi-tenant (company_id)
│   │   ├── Services/               # Google, WhatsApp, Webhooks, LGPD, etc.
│   │   └── Jobs/                   # Queue: WhatsApp, e-mail, webhooks
│   ├── database/migrations/
│   ├── routes/api.php
│   └── tests/Feature/Api/          # 28 testes
├── frontend/         React 19 + Vite 8 + Tailwind
│   ├── src/pages/                  # Dashboard, Leads, Clients, Deals, etc.
│   ├── src/config/profession.js    # Config workspace "empresa"
│   └── src/api/                    # Clients REST por recurso
└── deploy/
    └── nginx-flowcrm.conf
```

### Stack

| Camada | Tecnologia |
|--------|------------|
| API | Laravel 12, Sanctum, MySQL |
| Frontend | React 19, Vite 8, Tailwind CSS, Recharts |
| Auth | Bearer token + `X-Company-ID` header |
| Filas | Database queue (`php artisan queue:work`) |
| Scheduler | `php artisan schedule:work` (sync Google Calendar) |
| Billing | Stripe webhooks (backend) |

---

## 4. Módulos implementados

### 4.1 Core CRM (UI + API)

| Módulo | Rota frontend | API | Status UI |
|--------|---------------|-----|-----------|
| Dashboard | `/dashboard` | `GET /dashboard` | ✅ |
| Leads | `/leads` | `api/leads` + convert/lost | ✅ |
| Clientes | `/clients` | `api/clients` | ✅ |
| Oportunidades | `/deals` | `api/deals` | ✅ |
| Pipeline | `/pipeline` | `api/pipelines`, `lead-stages` | ✅ |
| Tarefas | `/tasks` | `api/tasks` | ✅ |
| Agenda | `/appointments` | `api/appointments` | ✅ |
| Financeiro | `/finance` | payments, expenses | ✅ |
| Documentos | `/documents` | `api/documents` | ✅ |
| Relatórios | `/reports` | `api/reports` + PDF | ✅ |
| Usuários | `/users` | `api/users` | ✅ |
| Notificações | `/notifications` | `api/notifications` | ✅ |
| Configurações | `/settings` | `api/settings` | ✅ |
| Automações | `/automations` | `api/automations` | ✅ |
| Integrações | `/integrations` | Google, import/export | ✅ |

### 4.2 Admin SaaS

| Recurso | Rota | Descrição |
|---------|------|-----------|
| Painel admin | `/admin` | Métricas globais |
| Empresas | `/admin/companies` | CRUD multi-tenant |
| Planos | `/admin/plans` | Planos e limites |
| Suspender/ativar | API admin | Controle de acesso |

### 4.3 Backend avançado (API disponível; UI parcial)

| Recurso | Endpoint | UI |
|---------|----------|-----|
| Webhooks outbound | `api/webhooks` | ❌ (removida da UI) |
| Campos customizados | `api/custom-fields` | ❌ |
| Tags | `api/tags` | Parcial |
| Sequências follow-up | `api/follow-up-sequences` | ❌ |
| Metas de vendas | `api/sales-goals` | Página existe (`SalesGoals.jsx`) |
| Motivos de perda | `api/loss-reasons` | Usado em Deals |
| Audit log | `api/audit-logs` | ❌ |
| WhatsApp inbox | `api/whatsapp/*` | ❌ (aba removida) |
| OpenAPI | `GET /openapi.json` | Link em Integrações |

---

## 5. Integrações

### Google Calendar

- OAuth 2.0 por empresa (`CompanyIntegration`)
- Cada usuário/empresa conecta **sua própria conta Google**
- Sync automático de compromissos da Agenda → Google Calendar
- Variáveis: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`
- Em dev local: requer **URL pública HTTPS** (ngrok) no redirect URI
- Job agendado: `crm:sync-calendars`

### WhatsApp

- Providers: `log` (dev), `evolution`, `meta`
- Webhook inbound: `POST /api/webhooks/whatsapp/{company}`
- Bot de agendamento (`WhatsappSchedulingBot`) — menu comercial
- Envio outbound via queue
- UI: botão `wa.me` nos leads/clientes (não inbox integrada)

### Webhooks outbound

- Eventos: `lead.created`, `lead.forwarded`, `client.updated`, `appointment.created`, etc.
- Assinatura HMAC
- Configuração via API (`WebhookConfigController`)
- UI de configuração removida das Integrações (backend ativo)

### Import / Export

- CSV de clientes e leads
- Export LGPD JSON por cliente (`/clients/{id}/export-data`)
- Anonimização LGPD (`/clients/{id}/anonymize`)

---

## 6. Mudanças de produto (decisões)

| Decisão | Motivo |
|---------|--------|
| Remover modo advogado | Foco e simplicidade |
| Remover depois modo psicólogo exclusivo | Pivot para mercado empresa maior |
| Agenda como único lugar de atendimento | Evitar duplicidade Atendimentos vs Agenda |
| WhatsApp via link externo | Sem custo Meta API; funcional imediato |
| Webhooks só no backend | Usuário pediu remover UI; dispatch preservado |
| `profession_mode = empresa` | CRM B2B genérico configurável |

---

## 7. Modelo de dados principal

```text
companies (tenant)
├── users (pivot company_user)
├── leads → convert → clients
├── clients
│   ├── tasks, appointments, payments, documents, notes
│   └── timeline (activities)
├── deals (oportunidades)
├── pipelines → lead_stages
├── appointments (+ google_event_id)
├── automations, webhooks, custom_fields
├── whatsapp_conversations → whatsapp_messages
└── company_integrations (google_calendar)
```

---

## 8. Segurança e LGPD

- Multi-tenant com isolamento por `company_id`
- Middleware `current.company` em todas as rotas tenant
- Exportação de dados pessoais (JSON)
- Anonimização com soft delete
- `sensitivity_level` em notas (normal / sensitive)
- Audit log de ações (backend)

---

## 9. Testes

```bash
cd flowcrm/backend && php artisan test
# 28 passed (126 assertions)
```

Cobertura principal:

- Auth e multi-tenant
- CRUD persistência
- Google Calendar connect + sync
- WhatsApp webhook + conversas
- Lead convert → client
- LGPD export + anonymize
- Professional CRM P0

Frontend:

```bash
cd flowcrm/frontend && npm run build
# Build OK
```

---

## 10. Credenciais de demonstração

| Perfil | E-mail | Senha |
|--------|--------|-------|
| Super admin | `admin@crm.com` | `password` |
| Empresa demo | `empresa@crm.com` | `password` |

Após `migrate:fresh --seed`:

- Empresa: **Empresa Demo Comercial**
- Lead exemplo, cliente exemplo, tarefa, reunião e pagamento pendente

---

## 11. O que ficou de fora / débito técnico

| Item | Situação |
|------|----------|
| Kanban visual de pipeline | API existe; página Kanban removida |
| Inbox WhatsApp na UI | Backend pronto; UI removida |
| UI de webhooks | Removida; API ativa |
| Campos customizados na UI | Só API |
| README desatualizado | Ainda menciona advogado/psicólogo |
| `professional_cases` | API existe; sem aba dedicada na UI |
| IA | Não implementada |
| Contas B2B (empresa + contatos) | Não implementado |
| SSO / white-label | Não implementado |

---

## 12. Estrutura de arquivos novos (commit `ba9a950`)

**Backend (+50 arquivos):** controllers de Deal, Pipeline, Integration, Whatsapp, Automation, Webhook, CustomField, AuditLog, etc.; services GoogleCalendar, Whatsapp, WebhookDispatcher, LgpdService, AutomationEngine; migrations WhatsApp e CRM avançado.

**Frontend (+20 arquivos):** Deals, Integrations, Automations, PipelineSettings, ClientSelect, EntityAutocomplete, WhatsappActionButton, profession config, APIs deals/pipelines/whatsapp/advanced.

---

## 13. Commits relevantes

| Commit | Descrição |
|--------|-----------|
| `ba9a950` | Pivot empresa + recursos avançados (156 arquivos) |
| `6107a88` | Melhorias anteriores no CRM |
| `215d0af` | Search, theme, notifications, client details |

---

## 14. Conclusão

O FlowCRM está em estado **MVP avançado de CRM comercial B2B** para PMEs brasileiras, com infraestrutura SaaS sólida (multi-tenant, planos, admin) e integrações-chave (Google Agenda, WhatsApp, webhooks) no backend.

O **gap principal** para competir no mercado empresa é: **UI de pipeline kanban**, **inbox WhatsApp integrada**, **IA comercial** e **polimento do README/documentação** alinhados ao novo posicionamento.

Próximo passo recomendado: ver `PLANO-INOVACAO.md`.
