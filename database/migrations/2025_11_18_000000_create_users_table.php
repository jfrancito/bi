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
        // Tabla users adaptada a tu estructura SQL Server
        if (Schema::hasTable('users')) {
            if (isset($this->command)) {
                $this->command->info('⚠ La tabla users ya existe. Omitiendo creación...');
            }
        } else {
            // Verificar que la tabla padre existe
            if (!Schema::hasTable('WEB.rols')) {
                if (isset($this->command)) {
                    $this->command->error('✗ Error: La tabla WEB.rols debe existir primero.');
                }
                throw new \Exception('La tabla WEB.rols no existe. Ejecuta primero su migración.');
            }
            
            Schema::create('users', function (Blueprint $table) {
                // Campos de tu estructura original
                $table->string('id', 20)->primary();
                $table->string('passwordmobil', 50);
                $table->timestamp('created_at')->useCurrent();
                $table->string('rol_id', 20);
                $table->string('usuarioisl_id', 20);
                $table->smallInteger('IndAuditado')->default(0);
                $table->smallInteger('activo')->default(1);
                $table->dateTime('FechaCreacion')->useCurrent();
                
                // Campos adicionales para Laravel Authentication
                $table->string('name', 100)->nullable();
                $table->string('email', 100)->unique()->nullable();
                $table->dateTime('email_verified_at')->nullable();
                $table->string('password', 255)->nullable(); // Para bcrypt de Laravel
                $table->rememberToken();
                $table->dateTime('FechaModificacion')->nullable();
                
                // Foreign key
                $table->foreign('rol_id')
                      ->references('id')
                      ->on('WEB.rols');
            });
            
            if (isset($this->command)) {
                $this->command->info('✓ Tabla users creada exitosamente.');
            }
        }
        
        // Tabla password_reset_tokens
        if (Schema::hasTable('password_reset_tokens')) {
            if (isset($this->command)) {
                $this->command->info('⚠ La tabla password_reset_tokens ya existe. Omitiendo creación...');
            }
        } else {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email', 100)->primary();
                $table->string('token', 255);
                $table->dateTime('created_at')->nullable();
            });
            
            if (isset($this->command)) {
                $this->command->info('✓ Tabla password_reset_tokens creada exitosamente.');
            }
        }
        
        // Tabla sessions
        if (Schema::hasTable('sessions')) {
            if (isset($this->command)) {
                $this->command->info('⚠ La tabla sessions ya existe. Omitiendo creación...');
            }
        } else {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id', 255)->primary();
                $table->string('user_id', 20)->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('payload'); // text en lugar de longText para SQL Server
                $table->integer('last_activity')->index();
                
                // Foreign key a users
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            });
            
            if (isset($this->command)) {
                $this->command->info('✓ Tabla sessions creada exitosamente.');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sessions')) {
            Schema::drop('sessions');
            if (isset($this->command)) {
                $this->command->info('✓ Tabla sessions eliminada.');
            }
        }
        
        if (Schema::hasTable('password_reset_tokens')) {
            Schema::drop('password_reset_tokens');
            if (isset($this->command)) {
                $this->command->info('✓ Tabla password_reset_tokens eliminada.');
            }
        }
        
        if (Schema::hasTable('users')) {
            Schema::drop('users');
            if (isset($this->command)) {
                $this->command->info('✓ Tabla users eliminada.');
            }
        }
    }
};