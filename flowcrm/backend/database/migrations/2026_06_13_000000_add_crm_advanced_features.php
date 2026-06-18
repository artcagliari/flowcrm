<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('assignment_mode', 40)->default('manual')->after('max_users');
            $table->foreignId('last_assigned_user_id')->nullable()->after('assignment_mode')->constrained('users')->nullOnDelete();
            $table->string('stripe_customer_id')->nullable()->after('last_assigned_user_id');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('max_leads')->nullable()->after('max_users');
            $table->string('stripe_price_id')->nullable()->after('max_leads');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->after('ends_at');
            $table->string('stripe_status')->nullable()->after('stripe_subscription_id');
        });

        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::table('lead_stages', function (Blueprint $table) {
            $table->foreignId('pipeline_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->string('color', 16)->default('#4F8CFF')->after('position');
            $table->boolean('is_won')->default(false)->after('color');
            $table->boolean('is_lost')->default(false)->after('is_won');
        });

        Schema::create('loss_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedSmallInteger('score')->default(0)->after('temperature');
            $table->foreignId('pipeline_id')->nullable()->after('lead_stage_id')->constrained()->nullOnDelete();
            $table->foreignId('lost_reason_id')->nullable()->after('lost_reason')->constrained('loss_reasons')->nullOnDelete();
            $table->timestamp('stage_entered_at')->nullable()->after('next_action_at');
        });

        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipeline_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_stage_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 190);
            $table->decimal('value', 12, 2)->default(0);
            $table->unsignedTinyInteger('probability')->default(50);
            $table->date('expected_close_date')->nullable();
            $table->string('status', 40)->default('aberto')->index();
            $table->foreignId('lost_reason_id')->nullable()->constrained('loss_reasons')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lead_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_stage_id')->nullable()->constrained('lead_stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->nullable()->constrained('lead_stages')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('entered_at');
            $table->timestamp('left_at')->nullable();
            $table->timestamps();
        });

        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('trigger_type', 80);
            $table->json('trigger_config')->nullable();
            $table->string('action_type', 80);
            $table->json('action_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('channel', 40)->default('whatsapp');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('follow_up_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('trigger_type', 80)->default('lead_created');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('follow_up_sequence_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained('follow_up_sequences')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('delay_days')->default(1);
            $table->string('action_type', 80);
            $table->json('action_config')->nullable();
            $table->timestamps();
        });

        Schema::create('sequence_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained('follow_up_sequences')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_step')->default(0);
            $table->string('status', 40)->default('active');
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type', 40);
            $table->string('name', 120);
            $table->string('field_type', 40)->default('text');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type', 40);
            $table->unsignedBigInteger('entity_id');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['custom_field_id', 'entity_type', 'entity_id']);
        });

        Schema::create('sales_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('target_amount', 12, 2)->default(0);
            $table->unsignedInteger('target_deals')->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'user_id', 'year', 'month']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80);
            $table->string('entity_type', 80)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->json('events');
            $table->string('secret', 64);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('event', 80);
            $table->json('payload');
            $table->string('status', 40)->default('pending');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamps();
        });

        Schema::create('company_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 40);
            $table->json('credentials')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique(['company_id', 'provider']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->json('mentions')->nullable()->after('description');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->json('mentions')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('notes', fn (Blueprint $table) => $table->dropColumn('mentions'));
        Schema::table('tasks', fn (Blueprint $table) => $table->dropColumn('mentions'));

        Schema::dropIfExists('company_integrations');
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('sales_goals');
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('sequence_enrollments');
        Schema::dropIfExists('follow_up_sequence_steps');
        Schema::dropIfExists('follow_up_sequences');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('automations');
        Schema::dropIfExists('lead_stage_histories');
        Schema::dropIfExists('deals');

        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lost_reason_id');
            $table->dropConstrainedForeignId('pipeline_id');
            $table->dropColumn(['score', 'stage_entered_at']);
        });

        Schema::dropIfExists('loss_reasons');

        Schema::table('lead_stages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pipeline_id');
            $table->dropColumn(['color', 'is_won', 'is_lost']);
        });

        Schema::dropIfExists('pipelines');

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['stripe_subscription_id', 'stripe_status']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['max_leads', 'stripe_price_id']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_assigned_user_id');
            $table->dropColumn(['assignment_mode', 'stripe_customer_id']);
        });
    }
};
