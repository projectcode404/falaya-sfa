<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salesman_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('area_id')->constrained('areas');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement('CREATE INDEX idx_salesman_areas_active ON salesman_areas(user_id, area_id) WHERE is_active = TRUE');
    }

    public function down(): void
    {
        Schema::dropIfExists('salesman_areas');
    }
};
