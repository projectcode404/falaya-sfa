<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_unloadings', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 30)->unique();
            $table->foreignId('salesman_id')->constrained('users');
            $table->date('operational_date');

            $table->string('status', 20)->default('DRAFT');
            // DRAFT, POSTED

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->timestamp('posted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_unloadings');
    }
};
