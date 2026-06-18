<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('profession_mode', 24)->default('empresa')->after('type');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->string('google_event_id')->nullable()->after('notes');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->timestamp('anonymized_at')->nullable()->after('last_contact_at');
        });

        Schema::table('whatsapp_conversations', function (Blueprint $table) {
            $table->json('bot_state')->nullable()->after('unread_count');
        });

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->string('sensitivity_level', 20)->default('normal')->after('body');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->string('sensitivity_level', 20)->default('normal')->after('content');
        });

        Schema::create('professional_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('profession_mode', 24);
            $table->string('title', 190);
            $table->string('status', 40)->default('ativo')->index();
            $table->string('process_number', 80)->nullable();
            $table->date('deadline')->nullable();
            $table->string('procedural_status', 80)->nullable();
            $table->string('action_type', 120)->nullable();
            $table->string('session_frequency', 24)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_cases');

        Schema::table('notes', fn (Blueprint $t) => $t->dropColumn('sensitivity_level'));
        Schema::table('whatsapp_messages', fn (Blueprint $t) => $t->dropColumn('sensitivity_level'));
        Schema::table('whatsapp_conversations', fn (Blueprint $t) => $t->dropColumn('bot_state'));
        Schema::table('clients', fn (Blueprint $t) => $t->dropColumn('anonymized_at'));
        Schema::table('appointments', fn (Blueprint $t) => $t->dropColumn('google_event_id'));
        Schema::table('companies', fn (Blueprint $t) => $t->dropColumn('profession_mode'));
    }
};
