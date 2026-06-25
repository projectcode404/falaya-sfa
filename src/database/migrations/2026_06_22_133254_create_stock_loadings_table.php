<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_loadings', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 30)->unique();
            $table->foreignId('salesman_id')->constrained('users');
            $table->date('operational_date');

            $table->string('status', 20)->default('DRAFT');
            // DRAFT, POSTED, CANCELLED

            $table->uuid('idempotency_key')->nullable()->unique();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamp('updated_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->index(['salesman_id', 'operational_date'], 'idx_stock_loadings_salesman_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_loadings');
    }
};
