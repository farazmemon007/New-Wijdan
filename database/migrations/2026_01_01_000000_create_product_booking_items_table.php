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
      
           

        // Sales items table
        Schema::create('product_booking_items', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('booking_id'); // FK to bookings table
    $table->unsignedBigInteger('warehouse_id')->nullable();
    $table->unsignedBigInteger('product_id')->nullable();
    $table->decimal('stock', 12, 2)->default(0);
    $table->decimal('price_level', 12, 2)->default(0);
    $table->decimal('sales_price', 12, 2)->default(0);
    $table->decimal('sales_qty', 12, 2)->default(0);
    $table->decimal('retail_price', 12, 2)->default(0);
    $table->decimal('discount_percent', 5, 2)->default(0);
    $table->decimal('discount_amount', 12, 2)->default(0);
    $table->decimal('amount', 12, 2)->default(0);

    $table->timestamps();

    // Foreign key
    $table->foreign('booking_id')->references('id')->on('productbookings')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_booking_items');
    }
};
