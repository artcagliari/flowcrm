# Plano de Inovação — FlowCRM

**Objetivo:** transformar o FlowCRM em um CRM comercial brasileiro competitivo, com diferenciação por **WhatsApp nativo**, **IA em português** e **preço justo** para PMEs.

**Horizonte:** 6–12 meses  
**Público-alvo:** PMEs brasileiras (1–50 funcionários), equipes comerciais, vendas consultivas e serviços B2B

---

## 1. Diagnóstico atual

### Forças (já construídas)

| Ativo | Valor competitivo |
|-------|-------------------|
| Multi-tenant SaaS | Escalar sem reescrever |
| Admin + planos + Stripe | Modelo de receita pronto |
| Pipeline + Deals + Leads | Core B2B no backend |
| Google Calendar OAuth | Diferencial operacional |
| WhatsApp (API + webhook + bot) | Essencial no Brasil |
| Webhooks outbound | Ecossistema n8n/Zapier |
| LGPD (export + anonymize) | Confiança regulatória |
| Automações + sequências (backend) | Base para IA e workflows |
| Campos customizados (backend) | Adaptar a qualquer vertical |
| OpenAPI | Integrações e parceiros |
| 28 testes automatizados | Base para evoluir com segurança |

### Fraquezas (gaps vs mercado)

| Gap | Impacto |
|-----|---------|
| Sem Kanban visual de pipeline | Perde para Pipedrive/RD/Agendor |
| Inbox WhatsApp sem UI | Principal canal BR subutilizado |
| Sem IA na interface | Mercado 2026 exige scoring, resumos, sugestões |
| Sem módulo Contas/Empresas B2B | Venda B2B é conta + múltiplos contatos |
| README e docs desalinhados | Onboarding confuso |
| Sem integração e-mail | Follow-up comercial incompleto |
| Sem forecast visual | Gestores não veem previsão de receita |
| Mobile não otimizado | Vendedor em campo prejudicado |

### Oportunidade de mercado (Brasil)

- **70%+** das PMEs já usam CRM; mercado em bilhões de reais
- **WhatsApp** é o canal #1 de vendas — quem integrar bem ganha
- **IA em CRM** aumenta em 83% a chance de bater meta de vendas
- Concorrentes cobram caro por automação/IA (HubSpot, Salesforce)
- Espaço para CRM **brasileiro, LGPD-first, preço em BRL**

---

## 2. Visão de produto

> **FlowCRM — o CRM comercial que sua equipe usa de verdade, com WhatsApp e IA em português.**

### Proposta de valor

1. **Simples de adotar** — setup em dias, não meses
2. **WhatsApp no centro** — conversa vira dado estruturado no CRM
3. **IA que vende** — scoring, próxima ação, mensagens sugeridas
4. **Preço previsível** — sem surpresa ao escalar equipe
5. **LGPD nativo** — exportação, anonimização, auditoria

### Posicionamento vs concorrentes

| Concorrente | FlowCRM diferencia por |
|-------------|------------------------|
| RD Station | Menos marketing, mais operação comercial + WhatsApp |
| Pipedrive | Pipeline + WhatsApp + IA em PT-BR, preço BRL |
| HubSpot | Sem custo explosivo ao crescer; foco PME |
| Agendor | Mais moderno, API aberta, automações visuais |
| Salesforce | 10x mais simples, 5x mais barato |

---

## 3. Segmentação de planos (monetização)

### Starter — R$ 49–99/mês (até 3 usuários)

- Leads, clientes, pipeline básico
- Tarefas e agenda
- WhatsApp link (`wa.me`)
- Relatórios simples
- 1 integração (Google Calendar)

### Pro — R$ 99–199/usuário/mês ⭐ volume principal

- Tudo do Starter
- **Inbox WhatsApp API** integrada
- Oportunidades (deals) + forecast básico
- Automações visuais
- Sequências de follow-up
- Import/export ilimitado
- **IA básica:** resumo de lead/cliente, próxima ação, mensagem sugerida

### Business — R$ 199–349/usuário/mês

- Tudo do Pro
- **Lead scoring preditivo**
- Distribuição de leads (round-robin)
- SLA e filas de atendimento
- Audit log completo na UI
- Integrações: e-mail, ERP (webhook)
- **IA avançada:** deal health, coaching, enriquecimento de dados

### Enterprise — sob consulta

- SSO (Google Workspace / Microsoft)
- White-label
- API ilimitada + SLA
- Agentes de IA com aprovação humana
- Suporte dedicado + onboarding

---

## 4. Roadmap por fases

### Fase 1 — Fundação B2B (0–6 semanas)

**Objetivo:** produto utilizável por equipe comercial real

| # | Entrega | Prioridade | Esforço |
|---|---------|------------|---------|
| 1.1 | Atualizar README e docs (este pacote) | Alta | Baixo |
| 1.2 | **Kanban visual** do pipeline (drag-and-drop leads/deals) | Alta | Médio |
| 1.3 | Vincular deals a clientes/leads na UI | Alta | Baixo |
| 1.4 | Dashboard comercial: conversão, pipeline value, ranking vendedores | Alta | Médio |
| 1.5 | Forecast simples (soma deals × probabilidade) | Média | Baixo |
| 1.6 | Polir UX de Leads → Converter → Oportunidade → Agenda | Alta | Baixo |

**KPI de sucesso:** empresa demo consegue simular ciclo de venda completo em < 15 min

---

### Fase 2 — WhatsApp como diferencial (6–10 semanas)

**Objetivo:** canal #1 do Brasil integrado de verdade

| # | Entrega | Prioridade | Esforço |
|---|---------|------------|---------|
| 2.1 | **Reativar Inbox WhatsApp** na UI (API já existe) | Crítica | Médio |
| 2.2 | Conversa → criar lead/cliente/deal automaticamente | Alta | Médio |
| 2.3 | Templates de mensagem na UI (`message-templates`) | Alta | Baixo |
| 2.4 | Histórico WhatsApp na timeline do cliente | Alta | Baixo |
| 2.5 | Provider Evolution API documentado + wizard de setup | Alta | Médio |
| 2.6 | Bot comercial: qualificar lead, agendar reunião | Média | Médio |
| 2.7 | Limite de mensagens por plano (custo Meta API) | Média | Baixo |

**KPI:** 80% das interações comerciais registradas no CRM sem digitação manual

---

### Fase 3 — IA comercial Premium (10–16 semanas)

**Objetivo:** features que justificam upgrade Pro/Business

| # | Entrega | Tier | Esforço |
|---|---------|------|---------|
| 3.1 | **Resumo automático** de lead/cliente (histórico em 3 linhas) | Pro | Médio |
| 3.2 | **Próxima ação sugerida** (deal parado, follow-up atrasado) | Pro | Médio |
| 3.3 | **Geração de mensagem** WhatsApp/e-mail personalizada | Pro | Médio |
| 3.4 | **Lead scoring** por regras + ML simples | Business | Alto |
| 3.5 | **Deal health** (risco de perder o negócio) | Business | Alto |
| 3.6 | **Forecast preditivo** com IA | Business | Alto |
| 3.7 | Busca semântica no CRM ("clientes que pediram desconto") | Business | Alto |

**Stack IA sugerida:**

- OpenAI GPT-4o / Claude para geração e resumos
- Embeddings para busca semântica (pgvector ou Pinecone)
- Scoring: regras primeiro, ML depois (precisa de histórico de conversões)

**KPI:** 30% dos usuários Pro usam IA semanalmente; NPS +10

---

### Fase 4 — Automação e integrações (16–22 semanas)

| # | Entrega | Prioridade |
|---|---------|------------|
| 4.1 | UI de automações visuais (trigger → condição → ação) | Alta |
| 4.2 | UI de webhooks (reativar nas Integrações) | Média |
| 4.3 | Sequências de follow-up multicanal (e-mail + WhatsApp) | Alta |
| 4.4 | Integração Gmail/Outlook (sync e-mails na timeline) | Alta |
| 4.5 | Campos customizados na UI | Média |
| 4.6 | Metas de vendas na UI (`SalesGoals.jsx` já existe) | Média |
| 4.7 | Presets de webhook: Slack, Discord, n8n | Baixa |

---

### Fase 5 — Escala e Enterprise (22–36 semanas)

| # | Entrega |
|---|---------|
| 5.1 | Módulo **Contas** (empresa cliente B2B + múltiplos contatos) |
| 5.2 | RBAC avançado (papéis customizados por tenant) |
| 5.3 | SSO SAML / Google Workspace |
| 5.4 | White-label (logo, cores, domínio) |
| 5.5 | **Agentes de IA** com aprovação humana |
| 5.6 | Conversation intelligence (análise de WhatsApp/calls) |
| 5.7 | App mobile (PWA ou React Native) |
| 5.8 | Marketplace de integrações |

---

## 5. Matriz impacto × esforço

```text
Alto impacto, baixo esforço (FAZER JÁ)
├── Kanban pipeline
├── Dashboard comercial
├── Reativar inbox WhatsApp
├── Resumo IA de cliente
└── Templates de mensagem

Alto impacto, alto esforço (PLANEJAR)
├── Lead scoring preditivo
├── Inbox WhatsApp + Meta API produção
├── Integração e-mail
├── Agentes de IA
└── Módulo Contas B2B

Baixo impacto (ADIAR)
├── Metas de vendas avançadas
├── IoT integration
├── White-label (até ter clientes enterprise)
└── Múltiplos perfis profissionais (psicólogo, advogado)
```

---

## 6. Diferenciais de IA (detalhamento)

### Tier 1 — Quick wins (Pro)

| Feature | Input | Output | Onde na UI |
|---------|-------|--------|------------|
| Resumo do cliente | Timeline + notas + deals | 3 linhas de contexto | Ficha do cliente (topo) |
| Próxima ação | Status, última interação, deal | "Ligar hoje", "Enviar proposta" | Dashboard + ficha |
| Mensagem sugerida | Contexto + template | Texto WhatsApp/e-mail | Botão ao lado do WhatsApp |
| Classificar lead | Texto do interesse | Etapa sugerida no pipeline | Ao criar lead |

### Tier 2 — Revenue intelligence (Business)

| Feature | Descrição |
|---------|-----------|
| Lead scoring | Pontua 0–100 por probabilidade de conversão |
| Deal health | Verde/amarelo/vermelho por risco de perda |
| Forecast IA | Previsão de receita do mês com base no pipeline |
| Coaching | "Você tem 3 deals parados há 5+ dias" |
| Enriquecimento | Completar CNPJ, setor, porte via API pública |

### Tier 3 — Agentes (Enterprise)

| Agente | Função |
|--------|--------|
| Prospector | Busca e qualifica leads |
| Follow-up | Executa sequência multicanal |
| Atendimento | Triagem WhatsApp + encaminhamento |
| Relatórios | "Quanto vendemos na região Sul?" |

**Modelo de cobrança IA:** créditos por uso (como Outreach/Microsoft Copilot) ou incluso no Business com limite mensal.

---

## 7. Arquitetura técnica para IA

```text
┌─────────────────────────────────────────────────┐
│                  Frontend React                  │
│  [Resumo] [Sugestão] [Scoring badge] [Chat IA]  │
└─────────────────────┬───────────────────────────┘
                      │ API
┌─────────────────────▼───────────────────────────┐
│              Laravel AI Service                  │
│  ┌──────────┐ ┌──────────┐ ┌──────────────────┐ │
│  │ Summarizer│ │ Scorer  │ │ MessageGenerator │ │
│  └─────┬────┘ └────┬─────┘ └────────┬─────────┘ │
│        │           │                │           │
│  ┌─────▼───────────▼────────────────▼─────────┐ │
│  │           Context Builder                   │ │
│  │  (timeline + deals + whatsapp + notes)      │ │
│  └─────────────────────┬──────────────────────┘ │
└────────────────────────┼────────────────────────┘
                         │
         ┌───────────────┼───────────────┐
         ▼               ▼               ▼
    OpenAI API      Embeddings DB     Rules Engine
```

**Princípios:**

1. IA sempre em **português brasileiro**
2. Contexto montado a partir de dados do tenant (nunca vazar entre empresas)
3. Ações destrutivas sempre com **aprovação humana**
4. Log de prompts/respostas para auditoria (LGPD)
5. Modo offline: regras simples quando API de IA indisponível

---

## 8. Go-to-market

### Fase beta (primeiros 10 clientes)

- Nicho: **agências de serviços**, **consultorias**, **distribuidoras B2B**
- Oferta: Pro gratuito por 3 meses em troca de feedback
- Canal: indicação, LinkedIn, grupos de PME

### Lançamento público

- Landing page: "CRM com WhatsApp e IA em português"
- Comparação de preço vs RD/Pipedrive/HubSpot
- Trial 14 dias sem cartão
- Onboarding guiado: importar CSV → conectar WhatsApp → primeiro deal

### Métricas norte

| Métrica | Meta 6 meses | Meta 12 meses |
|---------|--------------|---------------|
| Empresas ativas | 50 | 200 |
| MRR | R$ 15k | R$ 80k |
| Churn mensal | < 5% | < 3% |
| NPS | > 40 | > 50 |
| Conversão trial → pago | > 20% | > 30% |
| Uso semanal IA (Pro+) | — | > 25% |

---

## 9. Riscos e mitigações

| Risco | Mitigação |
|-------|-----------|
| Custo WhatsApp API | Repassar por plano; começar com Evolution API |
| IA sem dados limpos | Scoring por regras primeiro; ML após 100+ conversões |
| Feature creep | Roadmap por fases; dizer não a verticais cedo |
| Concorrência gratuita (HubSpot/RD) | Diferenciar por WhatsApp + IA PT-BR + suporte local |
| Complexidade técnica | 28 testes como rede de segurança; CI obrigatório |
| LGPD com IA | Não enviar dados sensíveis a LLM; anonimizar prompts |

---

## 10. Investimento estimado (esforço)

| Fase | Duração | Foco principal |
|------|---------|----------------|
| Fase 1 — Fundação B2B | 6 semanas | Kanban, dashboard, UX |
| Fase 2 — WhatsApp | 4 semanas | Inbox, templates, bot |
| Fase 3 — IA Premium | 6 semanas | Resumos, scoring, mensagens |
| Fase 4 — Automação | 6 semanas | Workflows, e-mail, campos |
| Fase 5 — Enterprise | 14 semanas | Contas, SSO, agentes |

**Total até produto competitivo no mercado:** ~22 semanas (Fases 1–4)  
**Total até enterprise-ready:** ~36 semanas

---

## 11. Próximos 3 passos imediatos

1. **Kanban de pipeline** — maior gap vs concorrentes, API já existe
2. **Reativar Inbox WhatsApp** — diferencial BR, backend pronto
3. **Resumo IA na ficha do cliente** — primeiro feature premium visível, valida willingness to pay

---

## 12. Conclusão

O FlowCRM tem **~60% da infraestrutura** de um CRM comercial moderno já construída. O caminho para inovar não é reescrever — é **ativar o que está no backend**, **completar a UI comercial** e **adicionar IA como camada premium**.

A oportunidade está em ser o **CRM brasileiro acessível com WhatsApp e IA em português** — um espaço que RD, HubSpot e Salesforce deixam caro e complexo para a PME.

---

*Documento vivo — revisar a cada fase concluída.*
