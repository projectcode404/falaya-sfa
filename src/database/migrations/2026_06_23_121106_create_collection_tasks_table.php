<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_plan_id')->nullable()->constrained('visit_plans');
            // NULL jika dibuat manual oleh Admin di luar jadwal visit

            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->constrained('users');
            $table->date('operational_date');

            $table->decimal('total_outstanding_snapshot', 15, 2);
            $table->string('priority', 10)->default('NORMAL');
            // NORMAL, DUE_SOON, OVERDUE

            $table->text('result_notes')->nullable();

            $table->string('status', 20)->default('PLANNED');
            // PLANNED, COLLECTED, NO_PAYMENT, RESCHEDULED

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });

        DB::statement('CREATE INDEX idx_collection_tasks_salesman_date ON collection_tasks(salesman_id, operational_date)');
        DB::statement('CREATE INDEX idx_collection_tasks_status ON collection_tasks(operational_date, status)');
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_tasks');
    }
};
