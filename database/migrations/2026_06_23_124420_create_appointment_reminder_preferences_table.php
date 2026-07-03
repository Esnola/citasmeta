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
        Schema::create('appointment_reminder_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20);
            $table->unsignedTinyInteger('lead_days');
            $table->boolean('enabled')->default(false);
            $table->timestamps();

            $table->unique(['channel', 'lead_days']);
            $table->index(['channel', 'enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_reminder_preferences');
    }
};
