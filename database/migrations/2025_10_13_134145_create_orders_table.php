<?php

use App\Models\User;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_price', 10, 2);
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(User::class, 'vendor_user_id');
            $table->string('status');
            $table->string('stripe_session_id')->nullable();
            $table->decimal('online_payment_commission', 10, 2)->default(0);
            $table->decimal('website_commission', 10, 2)->default(0);
            $table->decimal('vendor_subtotal', 10, 2)->default(0);
            $table->string('payment_intent')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            //$table->decimal('total_price', 10, 2);
            $table->json('variation_type_option_ids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_items');
    }
};
