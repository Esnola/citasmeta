<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->text('message');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('whatsapp_templates')->insert([
            [
                'key' => 'clinical_reminder',
                'label' => 'Recordatorio clínica',
                'message' => 'Hola [NOMBRE] te recordamos que el día [DIA] tienes una cita a las [HORA] ; saludos Clínica Dental Eugenia',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'formal_reminder',
                'label' => 'Recordatorio formal',
                'message' => 'Estimado/a [NOMBRE] [APELLIDOS], le recordamos su cita el [DIA] a las [HORA]. Saludos, Clínica Dental Eugenia',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'short_reminder',
                'label' => 'Recordatorio breve',
                'message' => 'Hola [NOMBRE], recuerde su cita el [DIA] a las [HORA]. Tel: [TELEFONO]',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
