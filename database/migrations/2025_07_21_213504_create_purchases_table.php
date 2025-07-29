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
      Schema::create('purchases', function (Blueprint $table) {
    $table->id(); // Add primary key if not already

    $table->text('invoice_no')->nullable();
    $table->text('supplier')->nullable();
    $table->text('purchase_date')->nullable();
    $table->text('warehouse_id')->nullable();
    $table->text('item_category')->nullable();
    $table->text('item_name')->nullable();
    $table->text('quantity')->nullable();
    $table->text('price')->nullable();
    $table->text('total')->nullable();
    $table->text('note')->nullable();
    $table->text('unit')->nullable();
    $table->text('total_price')->nullable();
    $table->text('discount')->nullable();
    $table->text('Payable_amount')->nullable();
    $table->text('paid_amount')->nullable();
    $table->text('due_amount')->nullable();
    $table->text('status')->nullable();
    $table->text('is_return')->nullable();

    $table->timestamps();
    $table->softDeletes();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
