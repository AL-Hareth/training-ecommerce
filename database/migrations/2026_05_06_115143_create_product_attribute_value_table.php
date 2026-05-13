<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attribute_value', function (Blueprint $table) {
            $table->foreignUuid('product_id')->constrained('products', 'id')->cascadeOnDelete();
            $table->foreignUuid('attribute_value_id')->constrained('attribute_values', 'id')->cascadeOnDelete();
            $table->primary(['product_id', 'attribute_value_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_value');
    }
};
