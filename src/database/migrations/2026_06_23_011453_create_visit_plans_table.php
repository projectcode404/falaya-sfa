<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users');
            $table->foreignId('customer_id')->constrained('customers');
            $table->date('operational_date');

            $table->boolean('is_planned')->default(true);
            // TRUE = dari visit_schedules, FALSE = unplanned

            $table->foreignId('area_id_snapshot')->constrained('areas');
            // snapshot area Customer saat Visit Plan dibuat

            $table->foreignId('visit_schedule_id')->nullable()->constrained('visit_schedules');
            // NULL jika unplanned

            $table->string('status', 20)->default('PLANNED');
            // PLANNED, IN_PROGRESS, COMPLETED, NO_ORDER, OUTLET_CLOSED, SKIPPED

            $table->foreignId('created_by')->nullable()->constrained('users');
            // NULL jika auto-generate sistem
            $table->timestamp('created_at')->useCurrent();

            $table->unique(
                ['customer_id', 'salesman_id', 'operational_date'],
                'uq_visit_plan_per_day'
            );
        });

        DB::statement('CREATE INDEX idx_visit_plans_salesman_date ON visit_plans(salesman_id, operational_date)');
        DB::statement('CREATE INDEX idx_visit_plans_status ON visit_plans(operational_date, status)');
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_plans');
    }
};
