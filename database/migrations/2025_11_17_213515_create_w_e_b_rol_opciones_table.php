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
        if (Schema::hasTable('WEB.rolopciones')) {
            if (isset($this->command)) {
                $this->command->info('⚠ La tabla WEB.rolopciones ya existe. Omitiendo creación...');
            }
            return;
        }
        
        // Verificar que las tablas padre existen
        if (!Schema::hasTable('WEB.rols')) {
            if (isset($this->command)) {
                $this->command->error('✗ Error: La tabla WEB.rols debe existir primero.');
            }
            throw new \Exception('La tabla WEB.rols no existe. Ejecuta primero su migración.');
        }
        
        if (!Schema::hasTable('WEB.opciones')) {
            if (isset($this->command)) {
                $this->command->error('✗ Error: La tabla WEB.opciones debe existir primero.');
            }
            throw new \Exception('La tabla WEB.opciones no existe. Ejecuta primero su migración.');
        }
        
        Schema::create('WEB.rolopciones', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->integer('orden');
            $table->integer('ver');
            $table->integer('anadir');
            $table->integer('modificar');
            $table->integer('eliminar');
            $table->integer('todas');
            $table->timestamp('created_at')->useCurrent();
            $table->string('rol_id', 20);
            $table->string('opcion_id', 20);
            
            // Foreign keys
            $table->foreign('rol_id')
                  ->references('id')
                  ->on('WEB.rols');
                  
            $table->foreign('opcion_id')
                  ->references('id')
                  ->on('WEB.opciones');
        });
        
          /**
     * =====================================================
     * INSERTAR REGISTROS INICIALES
     * =====================================================
     */
        DB::table('WEB.rolopciones')->insert([
        [
            'id'         => '1CIX00000001',
            'orden'      => 1,
            'ver'        => 1,
            'anadir'     => 1,
            'modificar'  => 1,
            'eliminar'   => 1,
            'todas'      => 1,
            'rol_id'     => '1CIX00000001',
            'opcion_id'  => '1CIX00000001',
        ],
        [
            'id'         => '1CIX00000002',
            'orden'      => 2,
            'ver'        => 1,
            'anadir'     => 1,
            'modificar'  => 1,
            'eliminar'   => 1,
            'todas'      => 1,
            'rol_id'     => '1CIX00000001',
            'opcion_id'  => '1CIX00000002',
        ],
        [
            'id'         => '1CIX00000003',
            'orden'      => 3,
            'ver'        => 1,
            'anadir'     => 1,
            'modificar'  => 1,
            'eliminar'   => 1,
            'todas'      => 1,
            'rol_id'     => '1CIX00000001',
            'opcion_id'  => '1CIX00000003',
        ],
        [
            'id'         => '1CIX00000004',
            'orden'      => 4,
            'ver'        => 1,
            'anadir'     => 1,
            'modificar'  => 1,
            'eliminar'   => 1,
            'todas'      => 1,
            'rol_id'     => '1CIX00000001',
            'opcion_id'  => '1CIX00000004',
        ],
        [
            'id'         => '1CIX00000005',
            'orden'      => 5,
            'ver'        => 1,
            'anadir'     => 1,
            'modificar'  => 1,
            'eliminar'   => 1,
            'todas'      => 1,
            'rol_id'     => '1CIX00000001',
            'opcion_id'  => '1CIX00000005',
        ],
    ]);


        if (isset($this->command)) {
            $this->command->info('✓ Tabla WEB.rolopciones creada exitosamente.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('WEB.rolopciones')) {
            Schema::drop('WEB.rolopciones');
            if (isset($this->command)) {
                $this->command->info('✓ Tabla WEB.rolopciones eliminada.');
            }
        } else {
            if (isset($this->command)) {
                $this->command->warn('⚠ La tabla WEB.rolopciones no existe.');
            }
        }
    }
};