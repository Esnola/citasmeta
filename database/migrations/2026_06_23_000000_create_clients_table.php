<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('telefono', 40)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['nombre', 'apellidos']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
