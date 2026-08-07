<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('cash_register_id')
                ->nullable()
                ->after('id')
                ->constrained('cash_registers')
                ->nullOnDelete();

            $table->string('payment_method')
                ->default('cash')
                ->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_register_id');
            $table->dropColumn('payment_method');
        });
    }
};
