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
         Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('admin_or_user_id')->nullable();
            $table->text('category_id')->nullable();
            $table->text('sub_category_id')->nullable();
            $table->text('item_code')->nullable();
             $table->text('uom')->nullable();
            $table->text('measurement')->nullable();
            $table->text('unit')->nullable();
            $table->text('item_name')->nullable();
            $table->text('size')->nullable();
            $table->text('opening_carton_quantity')->nullable();
            $table->text('carton_quantity')->nullable();
            $table->text('loose_pieces')->nullable();
            $table->text('pcs_in_carton')->nullable();
            $table->text('wholesale_price')->nullable();
            $table->text('retail_price')->nullable();
            $table->text('initial_stock')->nullable();
            $table->integer('alert_quantity')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
