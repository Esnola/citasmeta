<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('whatsapp_templates')->updateOrInsert(
            ['key' => 'confirmar_cita'],
            [
                'label' => 'Confirmar cita',
                'message' => 'Hola [NOMBRE], te recordamos que el día [DIA] tienes una cita a las [HORA]. Saludos, Clínica Dental Eugenia',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 0,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('whatsapp_templates')
            ->where('key', 'confirmar_cita')
            ->delete();
    }
};
