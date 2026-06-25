<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_realizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_plan_id')->constrained('visit_plans');

            // Check-in
            $table->decimal('checkin_latitude', 10, 7)->nullable();
            $table->decimal('checkin_longitude', 10, 7)->nullable();
            $table->decimal('checkin_accuracy_meter', 8, 2)->nullable();
            $table->timestamp('checkin_at')->nullable();
            $table->boolean('gps_unavailable')->default(false);
            $table->boolean('gps_low_accuracy')->default(false);
            $table->unsignedBigInteger('photo_media_id')->nullable();

            // Check-out
            $table->decimal('checkout_latitude', 10, 7)->nullable();
            $table->decimal('checkout_longitude', 10, 7)->nullable();
            $table->timestamp('checkout_at')->nullable();

            $table->uuid('idempotency_key')->nullable()->unique();
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement('CREATE INDEX idx_visit_realizations_plan ON visit_realizations(visit_plan_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_realizations');
    }
};
