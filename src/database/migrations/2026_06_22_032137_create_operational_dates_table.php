<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_dates', function (Blueprint $table) {
            $table->id();
            $table->date('current_date_value');
            $table->boolean('is_closing_in_progress')->default(false);
            $table->timestamp('updated_at')->useCurrent();
        });

        // Seed singleton row langsung di migration
        DB::table('operational_dates')->insert([
            'current_date_value' => now()->toDateString(),
            'is_closing_in_progress' => false,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_dates');
    }
};
