<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users');
            $table->foreignId('customer_id')->constrained('customers');
            $table->smallInteger('day_of_week'); // 1=Senin ... 7=Minggu
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamp('updated_at')->nullable();
        });

        DB::statement('ALTER TABLE visit_schedules ADD CONSTRAINT chk_day_of_week CHECK (day_of_week BETWEEN 1 AND 7)');
        DB::statement('CREATE INDEX idx_visit_schedules_lookup ON visit_schedules(day_of_week, is_active, salesman_id) WHERE is_active = TRUE');
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_schedules');
    }
};
