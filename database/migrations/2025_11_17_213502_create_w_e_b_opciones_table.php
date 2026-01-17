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
        if (Schema::hasTable('WEB.opciones')) {
            if (isset($this->command)) {
                $this->command->info('⚠ La tabla WEB.opciones ya existe. Omitiendo creación...');
            }
            return;
        }
        
        // Verificar que la tabla padre existe
        if (!Schema::hasTable('WEB.grupoopciones')) {
            if (isset($this->command)) {
                $this->command->error('✗ Error: La tabla WEB.grupoopciones debe existir primero.');
            }
            throw new \Exception('La tabla WEB.grupoopciones no existe. Ejecuta primero su migración.');
        }
        
        Schema::create('WEB.opciones', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('nombre', 100);
            $table->string('descripcion', 200);
            $table->string('pagina', 100);
            $table->integer('activo')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->string('grupoopcion_id', 20);
            $table->integer('indopcweb')->default(0);
            $table->integer('visible')->default(1);
            $table->smallInteger('indimagen')->default(0);
            $table->string('imagen', 600)->nullable();
            
            // Foreign key
            $table->foreign('grupoopcion_id')
                  ->references('id')
                  ->on('WEB.grupoopciones');
        });
        
        // ================================
        //       INSERT DE REGISTROS
        // ================================
        DB::table('WEB.opciones')->insert([
            [
                'id' => '1CIX00000001',
                'nombre' => 'Usuarios',
                'descripcion' => 'Usuarios',
                'pagina' => 'gestion-de-usuarios',
                'activo' => 1,
                'grupoopcion_id' => '1CIX00000001',
                'indopcweb' => 0,
                'visible' => 1,
                'indimagen' => 0,
                'imagen' => null,
            ],
            [
                'id' => '1CIX00000002',
                'nombre' => 'Roles',
                'descripcion' => 'Roles',
                'pagina' => 'gestion-de-roles',
                'activo' => 1,
                'grupoopcion_id' => '1CIX00000001',
                'indopcweb' => 0,
                'visible' => 1,
                'indimagen' => 0,
                'imagen' => null,
            ],
            [
                'id' => '1CIX00000003',
                'nombre' => 'Permisos',
                'descripcion' => 'Permisos',
                'pagina' => 'gestion-de-permisos',
                'activo' => 1,
                'grupoopcion_id' => '1CIX00000001',
                'indopcweb' => 0,
                'visible' => 1,
                'indimagen' => 0,
                'imagen' => null,
            ],
            [
                'id' => '1CIX00000004',
                'nombre' => 'Grupo Opciones',
                'descripcion' => 'Gestion Grupo Opciones',
                'pagina' => 'gestion-grupo-opciones',
                'activo' => 1,
                'grupoopcion_id' => '1CIX00000002',
                'indopcweb' => 1,
                'visible' => 1,
                'indimagen' => 0,
                'imagen' => null,
            ],
            [
                'id' => '1CIX00000005',
                'nombre' => 'Opciones',
                'descripcion' => 'Grupo Opciones',
                'pagina' => 'gestion-opciones',
                'activo' => 1,
                'grupoopcion_id' => '1CIX00000002',
                'indopcweb' => 1,
                'visible' => 1,
                'indimagen' => 0,
                'imagen' => null,
            ],
        ]);


        if (isset($this->command)) {
            $this->command->info('✓ Tabla WEB.opciones creada exitosamente.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('WEB.opciones')) {
            Schema::drop('WEB.opciones');
            if (isset($this->command)) {
                $this->command->info('✓ Tabla WEB.opciones eliminada.');
            }
        } else {
            if (isset($this->command)) {
                $this->command->warn('⚠ La tabla WEB.opciones no existe.');
            }
        }
    }
};