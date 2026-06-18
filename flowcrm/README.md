# FlowCRM

Aplicação fullstack separada em Laravel API e React + Vite.

## Estrutura

```text
flowcrm/
├── backend/
└── frontend/
```

## Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

O `.env.example` já vem preparado para MySQL:

```env
DB_CONNECTION=mysql
DB_DATABASE=flowcrm
DB_USERNAME=root
DB_PASSWORD=
```

Credenciais seed:

```text
Super admin: admin@crm.com / password

Advogado demo: empresa@crm.com / password
Psicologo demo: psicologo@crm.com / password
```

## Frontend

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

URLs padrão:

- API: `http://localhost:8000/api`
- Frontend: `http://localhost:5173`

## Validação

```bash
cd backend && php artisan test
cd frontend && npm run build
```

## Nicho: advogados e psicólogos

O produto foi redirecionado de CRM de vendas B2B para consultórios/escritórios.
Cada empresa define `profession_mode`: `advogado` ou `psicologo`.

- **Lead** = primeiro contato (ex.: WhatsApp)
- **Cliente** = paciente/cliente ativo
- **Caso** (`professional_cases`) = processo (advogado) ou acompanhamento (psicólogo)
- **lead_stages** = status do primeiro contato (não funil de vendas)

## LGPD e retenção de dados

| Dado | Onde | Retenção padrão |
|------|------|-----------------|
| Cadastro cliente/paciente | `clients` | Até exclusão/anonimização |
| Notas clínicas | `notes` (+ `sensitivity_level`) | Até anonimização |
| Mensagens WhatsApp | `whatsapp_messages` | Até anonimização |
| Casos/processos | `professional_cases` | Até encerramento + política do escritório |
| Logs de auditoria | `audit_logs` | 12 meses (recomendado operacional) |

**Exportação:** `GET /api/clients/{id}/export-data` (JSON)  
**Anonimização:** `POST /api/clients/{id}/anonymize` (soft delete + pseudonimização)

Notas e mensagens com dado de saúde devem usar `sensitivity_level: sensitive`.

## WhatsApp (agendamento)

Bot de menu no webhook inbound: cadastra lead, opção 1 agenda consulta/sessão.
Requer `php artisan queue:work` para envio de respostas.

## Google Agenda (OAuth)

### 1. Configurar no Google Cloud Console

1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um projeto (ou use um existente)
3. **APIs e serviços → Biblioteca** → ative **Google Calendar API**
4. **APIs e serviços → Tela de consentimento OAuth**
   - Tipo: Externo (ou Interno se for Workspace)
   - Adicione seu e-mail como usuário de teste (modo de teste)
5. **APIs e serviços → Credenciais → Criar credenciais → ID do cliente OAuth**
   - Tipo: **Aplicativo da Web**
   - URIs de redirecionamento autorizados:
     ```text
     http://localhost:8000/api/integrations/google-calendar/callback
     ```
   - Em produção, adicione também a URL do seu servidor, ex.:
     ```text
     https://api.seudominio.com/api/integrations/google-calendar/callback
     ```
6. Copie **Client ID** e **Client Secret**

### 2. Configurar no backend (.env)

```env
GOOGLE_CLIENT_ID=seu-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=seu-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/integrations/google-calendar/callback
FRONTEND_URL=http://localhost:5173
```

### 3. Conectar no CRM

1. Logue como empresa (`empresa@crm.com` ou `psicologo@crm.com`)
2. Vá em **Integrações**
3. Clique em **Conectar com Google**
4. Autorize a conta Google
5. Volta automaticamente para o CRM — status “Conectado”
6. Use **Sincronizar compromissos** ou aguarde o job horário (`crm:sync-calendars`)

Compromissos futuros da **Agenda** do CRM são enviados para o Google Calendar.
O sync automático requer o scheduler ativo: `php artisan schedule:work`.
