# Como Usar o FlowCRM

Guia prático para rodar, configurar e operar o CRM no dia a dia.

---

## 1. Requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+
- (Opcional) ngrok — para testar Google Calendar em localhost

---

## 2. Instalação local

### Backend

```bash
cd flowcrm/backend
composer install
cp .env.example .env
php artisan key:generate
```

Configure o MySQL no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=flowcrm
DB_USERNAME=root
DB_PASSWORD=
```

Suba o banco e os dados demo:

```bash
php artisan migrate:fresh --seed
```

Inicie os serviços (em terminais separados):

```bash
# API
php artisan serve --host=127.0.0.1 --port=8000

# Fila (WhatsApp, e-mails, webhooks)
php artisan queue:work

# Scheduler (sync Google Calendar a cada hora)
php artisan schedule:work
```

### Frontend

```bash
cd flowcrm/frontend
npm install
```

Crie `.env` (ou use o existente):

```env
VITE_API_URL=http://localhost:8000/api
VITE_BACKEND_URL=http://localhost:8000
```

```bash
npm run dev
```

Acesse: **http://localhost:5173**

---

## 3. Login

| Perfil | E-mail | Senha | Acesso |
|--------|--------|-------|--------|
| Super admin | `admin@crm.com` | `password` | `/admin` |
| Empresa demo | `empresa@crm.com` | `password` | Dashboard comercial |

O super admin gerencia empresas em **Admin → Empresas**.  
Cada empresa tem seu próprio ambiente isolado (multi-tenant).

---

## 4. Fluxo comercial recomendado

```text
Lead (prospecção)
    ↓ qualificar
Converter em Cliente
    ↓
Criar Oportunidade (deal)
    ↓
Agendar Reunião na Agenda
    ↓
Tarefas de follow-up
    ↓
Fechar deal (ganho/perdido)
```

### 4.1 Leads

**Menu → Leads**

1. Clique em **Novo** e preencha nome, telefone, WhatsApp, origem e interesse
2. Use o botão **WhatsApp** na linha para abrir conversa (`wa.me`)
3. Quando qualificado, clique em **Converter em cliente**
4. Para descartar: **Descartar** (com motivo opcional)

Na ficha do lead (`/leads/:id`):

- Abas: Visão geral, Histórico, Tarefas, Agenda, Documentos, Notas
- **Agendar reunião** cria compromisso vinculado ao lead
- **Encaminhar para cliente** na conversão

### 4.2 Clientes

**Menu → Clientes**

- Cadastro com status (encaminhado, ativo, em atendimento, etc.)
- **Select de status** inline na tabela e na ficha
- Botão WhatsApp ao lado do contato
- Na ficha: tarefas, agenda, financeiro, documentos, notas, timeline

**LGPD (ficha do cliente):**

- **Exportar dados** → JSON com todos os dados do cliente
- **Anonimizar** → pseudonimização irreversível

### 4.3 Oportunidades (Deals)

**Menu → Oportunidades**

1. **Nova oportunidade** — título, valor, probabilidade, data prevista de fechamento
2. Marcar como **Ganho** ou **Perdido** (com motivo de perda)
3. Acompanhe valor e probabilidade na tabela

### 4.4 Pipeline

**Menu → Pipeline**

- Configure as etapas do funil comercial
- Etapas padrão ao criar empresa:
  - Novo lead → Qualificação → Proposta enviada → Negociação → Fechado ganho/perdido
- Adicione etapas com nome e cor

### 4.5 Agenda

**Menu → Agenda**

1. **Novo compromisso**
2. Busque **cliente ou lead pelo nome** (auto-seleção se houver um único resultado)
3. Defina data, horário, tipo (reunião, demo, visita, etc.)
4. **Concluir** ou **Cancelar** compromissos na listagem

Compromissos vinculados a cliente/lead aparecem na timeline e na ficha.

### 4.6 Tarefas

**Menu → Tarefas**

- Crie tarefas com prioridade e prazo
- Vincule a cliente ou lead via busca por nome
- Marque como concluída na listagem

### 4.7 Financeiro

**Menu → Financeiro**

- Pagamentos (receitas) e despesas
- Status: pendente, pago, atrasado
- Vincule pagamentos a clientes na ficha do cliente

### 4.8 Relatórios

**Menu → Relatórios**

- Gráficos de receita, leads, conversão
- Export PDF via API (`/api/reports/pdf`)

### 4.9 Automações

**Menu → Automações**

- Regras do tipo: quando [evento] → executar [ação]
- Ex.: lead criado → criar tarefa, enviar webhook

### 4.10 Usuários

**Menu → Usuários**

- Convide membros da equipe comercial
- Papéis: admin da empresa, usuário

---

## 5. Integrações

**Menu → Integrações**

### Google Agenda

**Pré-requisitos no servidor (.env`):**

```env
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://SEU-TUNEL.ngrok-free.dev/api/integrations/google-calendar/callback
FRONTEND_URL=http://localhost:5173
```

**No Google Cloud Console:**

1. Ative **Google Calendar API**
2. Tela de consentimento OAuth → tipo **Externo** → adicione seu e-mail em **Usuários de teste**
3. Credencial OAuth → Aplicativo Web → URI de redirect = valor exato de `GOOGLE_REDIRECT_URI`

**No CRM:**

1. Integrações → **Conectar com Google**
2. Autorize com a conta Google de teste
3. Compromissos futuros da Agenda sincronizam automaticamente
4. Mantenha `php artisan schedule:work` rodando

**Dev local com ngrok:**

```bash
ngrok http 8000
# Use a URL HTTPS gerada no GOOGLE_REDIRECT_URI e no Google Cloud
```

### Import / Export CSV

- Exportar clientes ou leads em CSV
- Importar planilhas pela mesma tela

### API (OpenAPI)

- Link para `openapi.json` — documentação da API REST
- Autenticação: `Authorization: Bearer {token}` + header `X-Company-ID`

---

## 6. WhatsApp

### Uso atual (sem Meta API)

- Botão **WhatsApp** nos leads e clientes abre `wa.me` com o número cadastrado
- Não requer configuração Meta

### Uso avançado (webhook + inbox API)

No `.env` do backend:

```env
WHATSAPP_PROVIDER=log          # ou evolution / meta
WHATSAPP_WEBHOOK_TOKEN=seu-token-secreto
```

Webhook público:

```text
POST /api/webhooks/whatsapp/{company_id}?token=WHATSAPP_WEBHOOK_TOKEN
```

Requer `php artisan queue:work` para envio de respostas.

> A aba Inbox WhatsApp foi removida da UI. A API de conversas continua disponível para integração futura.

---

## 7. Painel Admin (super admin)

**URL:** `/admin`

| Ação | Onde |
|------|------|
| Criar empresa | Admin → Empresas → Nova |
| Suspender empresa | Ficha da empresa |
| Redefinir senha do admin | Ficha da empresa |
| Gerenciar planos | Admin → Planos |

Novas empresas recebem:

- Funil comercial padrão (6 etapas)
- Plano e trial configurados
- Admin principal criado no formulário

---

## 8. Busca global

A API `GET /api/search?query=...` alimenta os campos de autocomplete:

- Busca clientes e leads por nome, e-mail, telefone
- Mínimo 2 caracteres
- Auto-seleção quando há um único resultado

---

## 9. Atalhos de desenvolvimento

```bash
# Testes
cd flowcrm/backend && php artisan test

# Build frontend
cd flowcrm/frontend && npm run build

# Limpar cache de config (após mudar .env)
php artisan config:clear

# Reset completo do banco
php artisan migrate:fresh --seed
```

### Nginx (produção local)

Arquivo: `flowcrm/deploy/nginx-flowcrm.conf`

- Frontend estático em `:8080`
- Proxy `/api` → `localhost:8000`
- Build com `VITE_API_URL=/api`

---

## 10. Problemas comuns

| Problema | Solução |
|----------|---------|
| Google `redirect_uri_mismatch` | URI no Google Cloud deve ser **idêntica** ao `GOOGLE_REDIRECT_URI` |
| Google `access_denied` | Adicione seu e-mail em Usuários de teste no OAuth |
| Status do cliente não salva | Verifique token e empresa ativa; API aceita update parcial |
| WhatsApp não responde | `queue:work` rodando? Token do webhook correto? |
| 401 em todas as requests | Faça login novamente; token expira |
| Agenda não sincroniza Google | `schedule:work` ativo? Google conectado em Integrações? |

---

## 11. Estrutura do menu (empresa)

```text
Dashboard
Leads
Clientes
Oportunidades
Pipeline
Tarefas
Agenda
Documentos
Financeiro
Relatórios
Notificações
Usuários
Integrações
Configurações
```

---

## 12. Suporte

- Código: https://github.com/artcagliari/flowcrm
- Documentação técnica: `RELATORIO-IMPLEMENTACAO.md`
- Roadmap: `PLANO-INOVACAO.md`
