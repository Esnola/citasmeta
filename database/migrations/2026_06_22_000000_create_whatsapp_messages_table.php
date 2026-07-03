<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->index();
            $table->foreignId('appointment_id')->nullable()->index();
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('telefono', 40);
            $table->dateTime('scheduled_for')->index();
            $table->text('message');
            $table->string('source', 20)->default('manual');
            $table->string('status', 20)->default('pending')->index();
            $table->dateTime('sent_at')->nullable();
            $table->text('last_error')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->json('provider_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('provider_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
