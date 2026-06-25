<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('variant', 50)->nullable()->after('product_name');
            $table->string('category', 50)->nullable()->after('variant');
            $table->string('unit', 20)->after('category');
            $table->decimal('selling_price', 15, 2)->after('unit');
            $table->foreignId('created_by')->constrained('users')->after('selling_price');
            $table->foreignId('updated_by')->nullable()->constrained('users')->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn(['variant', 'category', 'unit', 'selling_price']);
        });
    }
};
