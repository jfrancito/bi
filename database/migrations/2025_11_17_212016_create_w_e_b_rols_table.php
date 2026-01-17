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
        if (Schema::hasTable('WEB.rols')) {
            if (isset($this->command)) {
                $this->command->info('⚠ La tabla WEB.rols ya existe. Omitiendo creación...');
            }
            return;
        }
        
        Schema::create('WEB.rols', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('nombre', 100);
            $table->integer('activo')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });
        
         // Insertar registro requerido
        DB::table('WEB.rols')->insert([
            'id'         => '1CIX00000001',
            'nombre'     => 'Super Admin',
            'activo'     => 1
        ]);

        if (isset($this->command)) {
            $this->command->info('✓ Tabla WEB.rols creada exitosamente.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('WEB.rols')) {
            Schema::drop('WEB.rols');
            if (isset($this->command)) {
                $this->command->info('✓ Tabla WEB.rols eliminada.');
            }
        } else {
            if (isset($this->command)) {
                $this->command->warn('⚠ La tabla WEB.rols no existe.');
            }
        }
    }
};