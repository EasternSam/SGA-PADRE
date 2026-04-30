<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de auditoría de intentos de login.
 * Registra TODOS los intentos (exitosos y fallidos) con IP, user-agent, etc.
 * También agrega campos de seguridad a la tabla users.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Auditoría de Login
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('login_identifier');           // Email, cédula o matrícula usado
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45);             // IPv4 o IPv6
            $table->text('user_agent')->nullable();        // Browser/Device
            $table->boolean('successful')->default(false); // ¿Login exitoso?
            $table->string('failure_reason')->nullable();  // 'invalid_credentials', 'account_locked', 'expired', 'rate_limited'
            $table->timestamp('attempted_at');

            // Índices para consultas rápidas
            $table->index('ip_address', 'la_ip_idx');
            $table->index('attempted_at', 'la_attempted_idx');
            $table->index(['ip_address', 'attempted_at'], 'la_ip_time_idx');
            $table->index(['user_id', 'attempted_at'], 'la_user_time_idx');
            $table->index('successful', 'la_successful_idx');
        });

        // 2. Campos de seguridad en Users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
            if (!Schema::hasColumn('users', 'failed_login_count')) {
                $table->unsignedSmallInteger('failed_login_count')->default(0)->after('last_login_ip');
            }
            if (!Schema::hasColumn('users', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('failed_login_count');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');

        Schema::table('users', function (Blueprint $table) {
            $cols = ['last_login_at', 'last_login_ip', 'failed_login_count', 'locked_until'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
