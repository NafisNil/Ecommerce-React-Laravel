<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            Schema::create('variation_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index()->constrained('products')->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });

            Schema::create('variation_type_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_type_id')->index()->constrained('variation_types')->onDelete('cascade');
            $table->string('name');
   
            $table->timestamps();
        });

            Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index()->constrained('products')->onDelete('cascade');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->json('variation_type_option_ids'); // Store selected options as JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
