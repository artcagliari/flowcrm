<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('ativo')->after('password');
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('legal_name')->nullable();
            $table->string('document', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 16)->default('#4F8CFF');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('company_user', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->primary(['company_id', 'user_id']);
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('lead_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 190);
            $table->string('phone', 32)->nullable();
            $table->string('whatsapp', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('document', 32)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 120)->nullable();
            $table->string('profession', 120)->nullable();
            $table->string('origin', 120)->nullable();
            $table->string('status', 40)->default('ativo')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lead_stage_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 190);
            $table->string('phone', 32)->nullable();
            $table->string('whatsapp', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('origin', 120)->nullable();
            $table->string('interest', 190)->nullable();
            $table->string('temperature', 20)->default('morno');
            $table->string('status', 40)->default('novo')->index();
            $table->decimal('estimated_value', 12, 2)->default(0);
            $table->string('lost_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamp('next_action_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('priority', 20)->default('media');
            $table->string('status', 30)->default('pendente');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 190);
            $table->string('type', 40)->default('reuniao');
            $table->string('status', 40)->default('agendado');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('reminder_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 190);
            $table->string('category', 60)->default('outros');
            $table->string('path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description', 190);
            $table->decimal('amount', 12, 2);
            $table->string('category', 120)->nullable();
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('payment_method', 40)->nullable();
            $table->string('status', 30)->default('pendente');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('description', 190);
            $table->decimal('amount', 12, 2);
            $table->string('category', 120)->nullable();
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('payment_method', 40)->nullable();
            $table->string('status', 30)->default('pendente');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title', 190);
            $table->text('body')->nullable();
            $table->string('type', 80)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('subject');
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('color', 16)->default('#4F8CFF');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('client_tag', function (Blueprint $table) {
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['client_id', 'tag_id']);
        });

        Schema::create('lead_tag', function (Blueprint $table) {
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['lead_id', 'tag_id']);
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->decimal('monthly_price', 10, 2);
            $table->unsignedInteger('max_users')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->string('status', 30)->default('trial');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('setting_key', 120);
            $table->json('setting_value')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'setting_key']);
        });
    }

    public function down(): void
    {
        foreach ([
            'settings', 'subscriptions', 'plans', 'lead_tag', 'client_tag', 'tags',
            'activities', 'notifications', 'expenses', 'payments', 'documents',
            'notes', 'appointments', 'tasks', 'leads', 'clients', 'lead_stages',
            'role_permission', 'company_user', 'permissions', 'roles', 'companies',
            'personal_access_tokens',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
