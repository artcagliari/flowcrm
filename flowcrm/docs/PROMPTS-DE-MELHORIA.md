# Prompts de Melhoria — FlowCRM

Prompts prontos para usar no Cursor (ou outro agente de IA) e continuar a evolução do produto conforme o [PLANO-INOVACAO.md](./PLANO-INOVACAO.md).

**Como usar:** copie o bloco da fase desejada, cole no chat e ajuste detalhes se necessário.

---

## Fase 1 — Fundação B2B (parcialmente implementada)

### ✅ Já feito nesta iteração
- Kanban visual do pipeline (`/pipeline`) com drag-and-drop
- Dashboard comercial com forecast e próximas ações
- Assistente comercial por regras (resumo, próxima ação, mensagem sugerida)
- Inbox WhatsApp (`/whatsapp`)
- API `GET /pipelines/board`, `GET /clients/{id}/insights`, `GET /leads/{id}/insights`

### Prompt 1.1 — Polir Kanban e deals

```
No FlowCRM (Laravel 12 + React 19), melhore o Pipeline Kanban em `frontend/src/pages/PipelineBoard.jsx`:

1. Permitir criar deal direto em uma coluna (modal rápido com título, valor, cliente/lead)
2. Ao arrastar para coluna "Fechado ganho/perdido", disparar winDeal/loseDeal ou marcar lead como perdido
3. Mostrar avatar/inicial do responsável (owner) nos cards
4. Adicionar filtro por vendedor (owner_id) no board
5. Manter padrão visual existente (glass, rounded-2xl, tailwind)
6. Backend: se necessário, estender `PipelineBoardController` com filtro owner_id
7. Rodar `php artisan test` e `npm run build` ao final
```

### Prompt 1.2 — Forecast e ranking no Dashboard

```
Expanda o dashboard comercial do FlowCRM:

1. Backend `DashboardController`: adicionar ranking de vendedores (leads convertidos, deals ganhos, forecast por owner_id)
2. Frontend `Dashboard.jsx`: seção "Performance da equipe" com tabela ou barras
3. Card de conversão funil: leads → clientes → deals ganhos (taxa %)
4. Link rápido para /pipeline e /deals nos cards
5. Testes feature para novos campos do dashboard
```

### Prompt 1.3 — Fluxo Lead → Deal na UI

```
Implemente fluxo comercial completo no FlowCRM:

1. Em `LeadDetails.jsx`: botão "Criar oportunidade" que pré-preenche deal com lead_id, valor estimado e etapa atual
2. Em `ClientDetails.jsx`: listar deals do cliente e botão criar oportunidade
3. Em `Deals.jsx`: select de cliente/lead com EntityAutocomplete, select de etapa do pipeline
4. Backend: garantir que deals herdam pipeline_id da empresa
5. Atualizar `COMO-USAR.md` com o fluxo
```

---

## Fase 2 — WhatsApp como diferencial

### Prompt 2.1 — Inbox WhatsApp completo

```
Melhore o WhatsApp Inbox do FlowCRM (`frontend/src/pages/WhatsappInbox.jsx`):

1. Botão "Nova conversa" com busca de lead/cliente e startConversation API
2. Templates de mensagem: integrar `message-templates` API com dropdown no composer
3. Badge de não lidas no Sidebar (contagem global via API)
4. Na timeline do cliente/lead, mostrar mensagens WhatsApp (TimelineBuilder já suporta)
5. Empty state com link para Integrações explicando Evolution API vs Meta Cloud
6. Tratar erros de provider não configurado com mensagem clara
```

### Prompt 2.2 — Conversa vira CRM automaticamente

```
No backend FlowCRM, ao receber webhook WhatsApp (`WhatsappWebhookController`):

1. Se número desconhecido, criar lead automaticamente com origem "WhatsApp"
2. Se cliente existir, vincular conversa ao client_id
3. Registrar activity na timeline
4. Disparar automação `whatsapp.message_received` se configurada
5. Adicionar testes em `WhatsappTest.php` para os 3 cenários
```

### Prompt 2.3 — Wizard de setup WhatsApp

```
Crie wizard de configuração WhatsApp em `Integrations.jsx`:

1. Seleção de provider: Log (dev), Evolution API, Meta Cloud API
2. Campos por provider (URL, token, phone_id) salvos em `integrations` API
3. Botão "Testar conexão" que envia mensagem de teste
4. Documentar variáveis no `.env.example`
5. UI em português, sem expor secrets no frontend
```

---

## Fase 3 — IA comercial Premium

### Prompt 3.1 — Upgrade IA para LLM (Pro)

```
Substitua/estenda `CrmInsightsService` no FlowCRM para suportar OpenAI:

1. Criar `AiInsightsService` que monta contexto via TimelineBuilder (cliente/lead)
2. Config `OPENAI_API_KEY` e `AI_INSIGHTS_ENABLED=true` no .env
3. Endpoint mantém mesmo contrato: summary, next_action, suggested_message, signals
4. Fallback para regras se API indisponível ou plano Starter
5. `PlanLimiter`: feature `ai_insights` só no Pro+
6. Log de prompts/respostas em tabela `ai_insight_logs` (LGPD: sem dados sensíveis)
7. Frontend `AiInsightsCard`: badge "IA" vs "Regras"
```

### Prompt 3.2 — Lead scoring preditivo (Business)

```
Implemente lead scoring no FlowCRM:

1. `LeadScorer` service: regras (temperatura, dias sem contato, tarefas atrasadas, valor estimado)
2. Job `ScoreCompanyLeads` rodando no scheduler
3. Badge de score 0-100 na lista de leads e no Kanban
4. Dashboard: widget "Leads quentes" (score > 70)
5. Testes unitários para LeadScorer
```

### Prompt 3.3 — Deal health e coaching

```
Adicione Deal Health ao FlowCRM:

1. Service calcula risco: dias parado, probabilidade vs etapa, sem tarefa futura
2. Cor verde/amarelo/vermelho nos cards do Kanban e em Deals.jsx
3. Seção "Coaching" no dashboard: "3 deals parados há 5+ dias"
4. Endpoint GET /deals/health-summary
```

---

## Fase 4 — Automação e integrações

### Prompt 4.1 — UI de automações visuais

```
Crie builder visual de automações em `Automations.jsx`:

1. Trigger: lead.created, deal.won, client.status_changed, whatsapp.message_received
2. Condições: etapa, origem, valor mínimo
3. Ações: mover etapa, criar tarefa, enviar webhook, enfileirar WhatsApp
4. Usar API existente `automations` + `AutomationEngine`
5. Preview JSON do workflow antes de salvar
```

### Prompt 4.2 — Campos customizados na UI

```
Exponha custom fields no FlowCRM:

1. Página em Configurações: CRUD de campos por entidade (lead, client, deal)
2. Render dinâmico em formulários de Lead, Cliente, Deal
3. Salvar via `custom-fields/values` API
4. Mostrar na ficha do cliente/lead
```

### Prompt 4.3 — Metas de vendas

```
Ative `SalesGoals.jsx` com backend completo:

1. CRUD metas por vendedor/mês (valor e quantidade de deals)
2. Dashboard: barra de progresso vs meta
3. Notificação quando atingir 80% e 100%
```

---

## Fase 5 — Enterprise

### Prompt 5.1 — Módulo Contas B2B

```
Adicione módulo Account (empresa cliente) ao FlowCRM:

1. Migration: accounts (company_id, name, cnpj, segment, size)
2. clients.account_id — múltiplos contatos por conta
3. UI: página Contas, deals vinculados à conta
4. Relatório receita por conta
```

### Prompt 5.2 — PWA mobile

```
Transforme o frontend FlowCRM em PWA:

1. vite-plugin-pwa, manifest, ícones
2. Layout responsivo para Pipeline e WhatsApp Inbox
3. Offline: cache de lista de leads (read-only)
```

---

## Prompts transversais (qualidade)

### Testes E2E do funil comercial

```
Adicione teste Feature `CommercialFunnelTest.php` no FlowCRM:

1. Login empresa demo
2. Criar lead → mover etapa no board API → converter cliente → criar deal → marcar ganho
3. Verificar insights retornam summary
4. Verificar dashboard stats atualizados
```

### Documentação

```
Atualize README.md e COMO-USAR.md do FlowCRM:

1. Remover referências a advogado/psicólogo
2. Documentar /pipeline (kanban), /whatsapp, assistente comercial
3. Screenshots ou diagrama mermaid do fluxo comercial
```

### Performance do board

```
Otimize GET /pipelines/board no FlowCRM:

1. Eager loading adequado
2. Cache Redis 30s por company_id (invalidar em update lead/deal)
3. Paginação por coluna se > 50 itens
```

---

## Ordem recomendada de execução

1. **Prompt 1.3** — fluxo Lead → Deal (completa fundação)
2. **Prompt 2.1** — inbox WhatsApp polido
3. **Prompt 3.1** — IA com LLM (monetização Pro)
4. **Prompt 4.1** — automações visuais
5. **Prompt 3.2** — lead scoring

---

*Gerado a partir do PLANO-INOVACAO.md — revisar após cada sprint.*
