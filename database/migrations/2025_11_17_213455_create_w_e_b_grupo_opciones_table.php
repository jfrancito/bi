<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('WEB.grupoopciones')) {
            if (isset($this->command)) {
                $this->command->info('⚠ La tabla WEB.grupoopciones ya existe. Omitiendo creación...');
            }
            return;
        }
        
        Schema::create('WEB.grupoopciones', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('nombre', 100);
            $table->string('icono', 100);
            $table->integer('orden');
            $table->integer('activo')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });
        
                // 🔥 Insertar el registro solicitado
        DB::table('WEB.grupoopciones')->insert([
            [
                'id'         => '1CIX00000001',
                'nombre'     => 'Usuarios',
                'icono'      => 'mdi-accounts-alt',
                'orden'      => 1,
                'activo'     => 1
            ],
            [
                'id'         => '1CIX00000002',
                'nombre'     => 'Gestion Menu',
                'icono'      => 'mdi mdi-chart',
                'orden'      => 1,
                'activo'     => 1
            ],
        ]);

        if (isset($this->command)) {
            $this->command->info('✓ Tabla WEB.grupoopciones creada exitosamente.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('WEB.grupoopciones')) {
            Schema::drop('WEB.grupoopciones');
            if (isset($this->command)) {
                $this->command->info('✓ Tabla WEB.grupoopciones eliminada.');
            }
        } else {
            if (isset($this->command)) {
                $this->command->warn('⚠ La tabla WEB.grupoopciones no existe.');
            }
        }
    }
};