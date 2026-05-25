# FlowCRM Backend

Laravel API REST para o FlowCRM.

## Stack

- Laravel 12
- MySQL
- Laravel Sanctum com Bearer tokens
- Eloquent ORM
- Migrations, Models, Controllers API, Form Requests, Resources, Middleware, Policies, Seeders e Factories

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Auth

Use `POST /api/login` com:

```json
{
  "email": "admin@flowcrm.test",
  "password": "password"
}
```

Envie nas rotas privadas:

```http
Authorization: Bearer TOKEN
X-Company-ID: 1
```
