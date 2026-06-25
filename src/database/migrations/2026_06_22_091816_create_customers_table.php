<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 30)->unique();
            $table->string('customer_name', 100);
            $table->text('address');
            $table->foreignId('area_id')->constrained('areas');

            $table->string('customer_type', 10); // CASH, CREDIT
            $table->string('status', 20)->default('ACTIVE');
            // PENDING_APPROVAL, ACTIVE, REJECTED, INACTIVE

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('radius_tolerance_meter')->nullable();

            // Hanya diisi bila customer_type = CREDIT
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->smallInteger('credit_term_days')->nullable();

            // Data pemilik
            $table->string('owner_name', 100)->nullable();
            $table->string('owner_phone', 20)->nullable();
            $table->string('owner_nik', 20)->nullable();
            $table->string('owner_name_ktp', 100)->nullable();
            $table->text('owner_address_ktp')->nullable();

            // Onboarding & approval
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();
            $table->text('approval_notes')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE customers ADD CONSTRAINT chk_credit_fields CHECK (
            (customer_type = \'CASH\') OR
            (customer_type = \'CREDIT\' AND credit_limit IS NOT NULL AND credit_term_days IS NOT NULL)
        )');

        DB::statement('CREATE INDEX idx_customers_area ON customers(area_id) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_customers_status ON customers(status) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_customers_type ON customers(customer_type, status)');
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
