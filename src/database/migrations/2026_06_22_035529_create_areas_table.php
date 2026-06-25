<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('area_name', 100);
            $table->string('area_code', 20)->unique();
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        DB::statement('CREATE INDEX idx_areas_active ON areas(is_active) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
