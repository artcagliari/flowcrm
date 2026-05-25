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
admin@flowcrm.test
password
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
