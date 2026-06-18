# Inovações Implementadas — FlowCRM

Registro das melhorias entregues a partir do [PLANO-INOVACAO.md](./PLANO-INOVACAO.md).

**Data:** junho/2026
**Fase do roadmap:** Fase 1 — Fundação B2B (núcleo concluído)

---

## 1. Pipeline Kanban visual

**Rota:** `/pipeline`
**Antes:** apenas configuração de etapas em lista.
**Agora:** board comercial com colunas por etapa do funil.

- Drag-and-drop de **leads** e **oportunidades** entre etapas
- Ao mover, persiste `lead_stage_id` e registra histórico (`LeadStageHistory`)
- Métricas no topo: leads no funil, oportunidades abertas, valor no pipeline, forecast ponderado
- Totais por coluna (quantidade + valor ponderado)
- Configuração de etapas movida para `/pipeline/settings`

**Arquivos:**
- `frontend/src/pages/PipelineBoard.jsx` (novo)
- `frontend/src/pages/PipelineSettings.jsx` (link cruzado)
- `backend/app/Http/Controllers/Api/PipelineBoardController.php` (novo)

---

## 2. Dashboard comercial

**Rota:** `/dashboard`

- Novos cards: **oportunidades abertas**, **forecast ponderado**, **deals ganhos no mês**
- Gráfico **leads por etapa** do pipeline
- Seção **Próximas ações sugeridas** (tarefas vencidas, leads sem contato)

**Arquivos:**
- `frontend/src/pages/Dashboard.jsx`
- `backend/app/Http/Controllers/Api/DashboardController.php` (métricas de deal + `pipeline_by_stage`)
- `frontend/src/api/dashboard.js` (`getMyDashboard`)

---

## 3. Assistente comercial (IA por regras)

Camada de inteligência tier **Pro**, exibida na ficha de Cliente e Lead.

- **Resumo** automático do contexto (status, deals, tempo sem contato)
- **Próxima ação** sugerida (tarefa vencida, reunião, retomar contato)
- **Mensagem de WhatsApp** sugerida (com botão copiar)
- **Sinais**: deals abertos, valor do pipeline, forecast, temperatura

Motor atual por regras (`engine: rules`), pronto para evoluir para LLM (ver Prompt 3.1).

**Arquivos:**
- `backend/app/Services/CrmInsightsService.php` (novo)
- `backend/app/Http/Controllers/Api/InsightsController.php` (novo)
- `frontend/src/components/shared/AiInsightsCard.jsx` (novo)
- `frontend/src/api/insights.js` (novo)
- Integração em `ClientDetails.jsx` e `LeadDetails.jsx`

**Endpoints:**
- `GET /api/clients/{id}/insights`
- `GET /api/leads/{id}/insights`

---

## 4. WhatsApp Inbox

**Rota:** `/whatsapp`

- Lista de conversas com contato, prévia da última mensagem e não lidas
- Painel de mensagens com envio em tempo real (enfileira via provider)
- Vínculo com lead/cliente da conversa
- Indicador de provider: **conectado** vs **modo log (dev)**

**Arquivos:**
- `frontend/src/pages/WhatsappInbox.jsx` (novo)
- Consome API WhatsApp já existente (`backend/.../WhatsappController.php`)

---

## 5. Navegação e rotas

- Novo item de menu **WhatsApp** (`frontend/src/config/profession.js`)
- Rotas adicionadas: `/pipeline` (kanban), `/pipeline/settings`, `/whatsapp`
- `frontend/src/routes/AppRoutes.jsx`

---

## 6. Backend — resumo de endpoints novos

| Método | Rota | Função |
|--------|------|--------|
| GET | `/api/pipelines/board` | Dados do Kanban (colunas, leads, deals, totais) |
| GET | `/api/clients/{id}/insights` | Insights comerciais do cliente |
| GET | `/api/leads/{id}/insights` | Insights comerciais do lead |

Além de:
- Histórico de etapa ao atualizar lead (`LeadStageTracker` em `LeadController::update`)
- Métricas de deal e funil no `DashboardController`

---

## 7. Documento de continuidade

Criado [`PROMPTS-DE-MELHORIA.md`](./PROMPTS-DE-MELHORIA.md) com 15+ prompts prontos, organizados por fase, para continuar a evolução (IA com LLM, lead scoring, automações visuais, módulo Contas B2B, PWA).

---

## 8. Validação

- **Backend:** `php artisan test` → 28 testes passando (126 asserts)
- **Frontend:** `npm run build` → build OK

---

## 9. Mapa do roadmap

| Fase | Status |
|------|--------|
| Fase 1 — Fundação B2B | Núcleo concluído (Kanban, dashboard, insights, inbox) |
| Fase 2 — WhatsApp diferencial | Inbox base entregue; falta templates, auto-CRM, wizard |
| Fase 3 — IA Premium | Base por regras entregue; falta LLM, scoring, deal health |
| Fase 4 — Automação | Pendente (ver prompts) |
| Fase 5 — Enterprise | Pendente (ver prompts) |

---

*Próximo passo recomendado: Prompt 1.3 (fluxo Lead → Deal na UI) do `PROMPTS-DE-MELHORIA.md`.*
