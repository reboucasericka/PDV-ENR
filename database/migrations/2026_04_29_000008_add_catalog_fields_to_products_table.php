<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('categories')
                ->nullOnDelete();

            $table->string('sku')->nullable()->unique()->after('name');
            $table->text('description')->nullable()->after('sku');
            $table->string('image')->nullable()->after('price');
            $table->string('button_color')->nullable()->after('image');
            $table->unsignedInteger('sort_order')->default(0)->after('button_color');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['sku', 'description', 'image', 'button_color', 'sort_order']);
        });
    }
};
