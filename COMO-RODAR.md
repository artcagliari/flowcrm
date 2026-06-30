# FlowCRM — Como rodar localmente

CRM comercial fullstack com pipeline Kanban, dashboard, assistente de IA por regras, inbox WhatsApp e integrações (Google Agenda, Meta Cloud API).

**Repositório:** [github.com/artcagliari/flowcrm](https://github.com/artcagliari/flowcrm)

---

## Stack

| Camada | Tecnologias |
|--------|-------------|
| Backend | PHP 8.2+, Laravel 12, Sanctum, MySQL 8 |
| Frontend | React 19, Vite 8, Tailwind CSS, Recharts |
| Infra local | Fila em banco (`queue:work`), scheduler opcional |

---

## Requisitos

- **PHP** 8.2 ou superior (extensões: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`)
- **Composer** 2.x
- **Node.js** 18+ e npm
- **MySQL** 8+ com banco `flowcrm` criado (ou permissão para o usuário criar)

```bash
# Exemplo: criar o banco no MySQL
mysql -u root -e "CREATE DATABASE IF NOT EXISTS flowcrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## Instalação (5 minutos)

### 1. Clonar o repositório

```bash
git clone https://github.com/artcagliari/flowcrm.git
cd flowcrm/flowcrm
```

### 2. Backend (API Laravel)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Ajuste o MySQL no `.env` se necessário:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=flowcrm
DB_USERNAME=root
DB_PASSWORD=
```

Suba tabelas e dados de demonstração:

```bash
php artisan migrate:fresh --seed
php artisan serve --host=127.0.0.1 --port=8000
```

A API ficará em **http://localhost:8000/api**.

### 3. Frontend (React + Vite)

Em outro terminal:

```bash
cd flowcrm/frontend
npm install
cp .env.example .env
npm run dev
```

O app abre em **http://localhost:5173**.

Variáveis do frontend (já vêm no `.env.example`):

```env
VITE_API_URL=http://localhost:8000/api
VITE_BACKEND_URL=http://localhost:8000
```

---

## Login de demonstração

| Perfil | E-mail | Senha | O que explorar |
|--------|--------|-------|----------------|
| Empresa comercial | `empresa@crm.com` | `password` | Dashboard, leads, pipeline, WhatsApp inbox |
| Super admin | `admin@crm.com` | `password` | Painel `/admin` — gestão de empresas e planos |

O seed também cria leads, clientes, oportunidades, tarefas e compromissos para navegar sem cadastrar dados manualmente.

---

## O que testar após subir

| Funcionalidade | Rota / onde |
|----------------|-------------|
| Dashboard comercial | `/dashboard` |
| Pipeline Kanban (drag-and-drop) | `/pipeline` |
| Leads e conversão em cliente | `/leads` |
| Assistente de IA (regras) | Ficha de um lead ou cliente |
| Inbox WhatsApp | `/whatsapp` (modo `log` em dev) |
| Integrações | `/integrations` (Google Agenda, CSV, API) |
| Admin multi-tenant | `/admin` (super admin) |

---

## Serviços em background (opcional)

Para **envio de WhatsApp**, e-mails e jobs assíncronos, rode em um terceiro terminal:

```bash
cd flowcrm/backend
php artisan queue:work
```

Para **sincronização automática com Google Calendar** (após conectar em Integrações):

```bash
php artisan schedule:work
```

> Em desenvolvimento, o provider padrão de WhatsApp é `log` — mensagens são registradas no banco sem chamar API externa.

---

## Atalho: tudo de uma vez (backend)

Na pasta `backend`, o Composer já define um script que sobe API, fila, logs e Vite juntos (requer `npm install` no frontend antes):

```bash
cd flowcrm/backend
composer install
# .env configurado + migrate --seed feitos
composer dev
```

---

## Validar a instalação

```bash
# Testes automatizados da API
cd flowcrm/backend && php artisan test

# Build de produção do frontend
cd flowcrm/frontend && npm run build
```

---

## Configurações avançadas (opcional)

### WhatsApp Meta Cloud API

No `.env` do backend:

```env
WHATSAPP_PROVIDER=meta
WHATSAPP_WEBHOOK_TOKEN=seu-verify-token
WHATSAPP_META_TOKEN=...
WHATSAPP_META_PHONE_NUMBER_ID=...
WHATSAPP_META_APP_SECRET=...
```

Webhook: `GET/POST /api/webhooks/whatsapp/{company_id}`  
Detalhes: [flowcrm/docs/META-WHATSAPP-CONTEXTO.md](flowcrm/docs/META-WHATSAPP-CONTEXTO.md)

### Google Agenda

```env
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=http://localhost:8000/api/integrations/google-calendar/callback
FRONTEND_URL=http://localhost:5173
```

Em localhost, use [ngrok](https://ngrok.com/) se o Google exigir HTTPS no redirect.

---

## Problemas comuns

| Sintoma | Solução |
|---------|---------|
| Erro de conexão com MySQL | Confira `DB_*` no `.env` e se o serviço MySQL está ativo |
| Frontend não carrega dados | Verifique se a API está em `:8000` e `VITE_API_URL` aponta para `/api` |
| 401 após login | Token expirado — faça logout e login novamente |
| WhatsApp não envia | Rode `php artisan queue:work` |
| `redirect_uri_mismatch` (Google) | URI no Google Cloud deve ser idêntica ao `GOOGLE_REDIRECT_URI` |

---

## Estrutura do projeto

```text
flowcrm/
├── backend/          # Laravel API REST + Sanctum
├── frontend/         # React + Vite + Tailwind
├── docs/             # Documentação técnica e roadmap
└── deploy/           # Exemplo nginx para produção
```

Mais detalhes de uso do produto: [flowcrm/docs/COMO-USAR.md](flowcrm/docs/COMO-USAR.md)
