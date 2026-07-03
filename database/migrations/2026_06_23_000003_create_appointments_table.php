<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('fecha')->index();
            $table->time('hora');
            $table->boolean('enviado')->default(false)->index();
            $table->boolean('entregado')->default(false)->index();
            $table->boolean('cita_activa')->default(true)->index();
            $table->boolean('activo')->default(true)->index();
            $table->dateTime('whatsapp_sent_at')->nullable();
            $table->dateTime('whatsapp_delivered_at')->nullable();
            $table->dateTime('whatsapp_read_at')->nullable();
            $table->index(
                ['client_id', 'enviado', 'activo', 'fecha', 'hora'],
                'appointments_client_status_schedule_index'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
