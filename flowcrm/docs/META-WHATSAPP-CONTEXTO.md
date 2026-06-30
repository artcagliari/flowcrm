# Contexto — WhatsApp Meta Cloud API (FlowCRM)

Documento corrigido para colar no Claude Code ou outro agente.  
**Repositório:** `flowcrm/` (Laravel 12 + React 19)

---

## Objetivo

Configurar e testar **WhatsApp Cloud API (Meta)** no FlowCRM até:

1. Webhook verificado na Meta (GET)
2. Envio de mensagem pelo CRM
3. Recebimento no Inbox com lead automático
4. POST inbound validado por **X-Hub-Signature-256** (HMAC)

---

## ⚠️ Correções importantes (não errar)

| Mitos | Realidade |
|-------|-----------|
| `?token=` na Callback URL para POST | **Errado.** Meta **não** repassa query string no POST de mensagens |
| Auth do POST via query param | **Errado.** Auth = header `X-Hub-Signature-256` com **App Secret** |
| Verify Token na URL | **Errado.** Verify Token vai em **campo separado** no painel Meta; a Meta envia como `hub.verify_token` no **GET** de verificação |

### URLs corretas

```
GET  /api/webhooks/whatsapp/{company_id}   → verificação (hub.challenge)
POST /api/webhooks/whatsapp/{company_id}   → mensagens (sem ?token=)
```

**Nota técnica (GET):** a Meta envia `hub.mode`, `hub.verify_token`, `hub.challenge`. O **PHP** converte pontos em underscores em `$_GET` (`hub.mode` → `hub_mode`). O controller lê ambas as formas por segurança — não é o Laravel que faz essa conversão.

**Segurança (POST):** em ambientes **não-local** (staging/produção), `WHATSAPP_META_APP_SECRET` é **obrigatório**. Sem ele, POST com payload Meta retorna **500** (não passa silenciosamente). Validação via `X-Hub-Signature-256` apenas.

**Callback URL na Meta (cole exatamente assim):**
```
https://SEU-NGROK.ngrok-free.dev/api/webhooks/whatsapp/2
```

**Verify Token na Meta (campo separado):** mesmo valor de `WHATSAPP_WEBHOOK_TOKEN` no `.env`

---

## Variáveis `.env` obrigatórias (backend)

```env
# Verify Token — campo "Verify token" no painel Meta (GET apenas)
WHATSAPP_WEBHOOK_TOKEN=meu_token_secreto_123

# Meta Cloud API
WHATSAPP_META_TOKEN=EAAxxxx...           # token de acesso (24h no sandbox; depois System User)
WHATSAPP_META_PHONE_NUMBER_ID=123456789
WHATSAPP_META_API_VERSION=v19.0
WHATSAPP_META_APP_SECRET=abc123...       # OBRIGATÓRIO — valida X-Hub-Signature-256 no POST

FRONTEND_URL=http://localhost:5173
```

**Token permanente (fazer cedo, evita retrabalho):**
1. Meta Business Manager → Configurações → Usuários do sistema
2. Criar System User → Gerar token com permissão `whatsapp_business_messaging`
3. Usar esse token no FlowCRM (Integrações → Meta → Token)

---

## O que já está implementado

### Backend
- `MetaCloudApiProvider` — envio + parse payload Meta
- `MetaWebhookVerifier` — valida `X-Hub-Signature-256`
- `WhatsappWebhookController::verify()` — GET hub.challenge
- `WhatsappWebhookController::receive()` — POST com assinatura Meta + `providerFor(company)`
- Provider por empresa em `company_integrations` (provider `whatsapp`)
- Auto-criação de lead em mensagem de número desconhecido
- Testes: `php artisan test --filter=WhatsappTest` (10 testes)

### Frontend
- `/integrations` — wizard Meta (Phone Number ID, Token, instruções webhook)
- `/whatsapp` — Inbox

### Rotas
```
GET  /api/webhooks/whatsapp/{company}     verify
POST /api/webhooks/whatsapp/{company}     receive
GET  /api/whatsapp/settings
PUT  /api/whatsapp/settings
POST /api/whatsapp/test
```

---

## Como rodar local

```bash
# API
cd flowcrm/backend && php artisan serve --port=8000

# Fila (obrigatório para envio)
php artisan queue:work

# Frontend
cd flowcrm/frontend && npm run dev

# HTTPS público para Meta
ngrok http 8000
```

**Login:** `empresa@crm.com` / `password`

**Descobrir company_id:**
```bash
php artisan tinker --execute="echo App\Models\Company::where('name','like','%Demo%')->value('id');"
```

---

## Passo a passo Meta

### 1. Criar app
- https://developers.facebook.com → Criar app → **Empresa**
- Adicionar produto **WhatsApp** → Configurar

### 2. Credenciais (WhatsApp → API Setup)
- **Phone Number ID**
- **Access Token** (temporário 24h ou System User permanente)
- **App Secret** (Configurações do app → Básico) → `WHATSAPP_META_APP_SECRET`

### 3. Número de teste
- Adicionar seu celular em **"To"** e confirmar código SMS
- Sandbox só envia para números verificados

### 4. Webhook na Meta
- WhatsApp → Configuração → Webhook → Editar
- **Callback URL:** `https://NGROK/api/webhooks/whatsapp/{COMPANY_ID}` (sem query params)
- **Verify token:** valor de `WHATSAPP_WEBHOOK_TOKEN`
- Assinar campo **messages**
- Clicar **Verify and save**

### 5. FlowCRM
- Integrações → WhatsApp → Provider **Meta Cloud API**
- Preencher Phone Number ID + Token → Ativar → Salvar
- Testar envio para número verificado
- Responder no celular → ver Inbox + lead criado

---

## Critérios de sucesso

- [ ] GET verify retorna `hub.challenge` (200 text/plain)
- [ ] Meta mostra webhook ✅ verificado
- [ ] POST com assinatura inválida → 401
- [ ] POST com assinatura válida → mensagem no Inbox
- [ ] Envio pelo CRM chega no WhatsApp
- [ ] `queue:work` rodando durante testes de envio

---

## Tarefas opcionais para o agente

1. Confirmar `company_id` real e testar verify com ngrok
2. Exibir App Secret configurado na UI (sem expor valor)
3. Documentar troca para System User Token
4. Commit das correções de segurança Meta (sem `.env`)

---

## Comandos de teste manual (antes do ngrok)

Substitua `{COMPANY_ID}` e configure `WHATSAPP_WEBHOOK_TOKEN` + `WHATSAPP_META_APP_SECRET` no `.env`.

```bash
# 1. GET verify — deve retornar TESTE123 em text/plain (nao JSON)
curl -v "http://127.0.0.1:8000/api/webhooks/whatsapp/{COMPANY_ID}?hub.mode=subscribe&hub.verify_token=meu_token_secreto_123&hub.challenge=TESTE123"

# 2. POST com assinatura valida (payload vazio Meta)
BODY='{"object":"whatsapp_business_account","entry":[]}'
SECRET="seu_app_secret"
# Use PHP para gerar HMAC identico ao servidor (openssl no macOS pode formatar diferente)
SIG=$(php -r 'echo "sha256=".hash_hmac("sha256", $argv[1], $argv[2]);' "$BODY" "$SECRET")
curl -v -X POST "http://127.0.0.1:8001/api/webhooks/whatsapp/{COMPANY_ID}" \
  -H "Content-Type: application/json" \
  -H "X-Hub-Signature-256: $SIG" \
  -d "$BODY"

# 3. POST com mensagem real (valida parseInbound)
BODY='{"object":"whatsapp_business_account","entry":[{"changes":[{"value":{"contacts":[{"profile":{"name":"Teste Meta"}}],"messages":[{"from":"5511999887766","id":"wamid.test","text":{"body":"Oi do curl"}}]}}]}]}'
SIG=$(php -r 'echo "sha256=".hash_hmac("sha256", $argv[1], $argv[2]);' "$BODY" "$SECRET")
curl -v -X POST "http://127.0.0.1:8001/api/webhooks/whatsapp/{COMPANY_ID}" \
  -H "Content-Type: application/json" \
  -H "X-Hub-Signature-256: $SIG" \
  -d "$BODY"
# Esperado: {"status":"ok","received":true} + mensagem no Inbox / lead criado
```

## Comandos de teste automatizado

```bash
cd flowcrm/backend && php artisan test --filter=WhatsappTest
```

---

*Última atualização: correção auth Meta (HMAC POST + verify token separado, sem ?token= na URL).*
