<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->date('operational_date')->unique();
            $table->foreignId('closed_by')->constrained('users');
            $table->timestamp('closed_at')->useCurrent();
            $table->integer('total_salesman_active');
            $table->integer('total_visit_skipped')->default(0);
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
