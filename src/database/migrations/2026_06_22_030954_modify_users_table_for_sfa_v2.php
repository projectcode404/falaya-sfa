<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at', 'remember_token']);
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('role', 20)->default('SALESMAN')->after('phone');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('current_session_token', 255)->nullable()->after('is_active');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'is_active', 'current_session_token']);
            $table->dropSoftDeletes();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
        });
    }
};
