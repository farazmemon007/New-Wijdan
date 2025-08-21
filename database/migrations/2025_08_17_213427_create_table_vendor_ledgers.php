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
        Schema::create('vendor_ledgers', function (Blueprint $table) {
            $table->id();
            $table->text('admin_or_user_id');
            $table->foreignId('vendor_id')->references('id')->on('vendors')->casecdeOnDelete();

            $table->text('opening_balance');
            $table->text('previous_balance');
            $table->text('closing_balance');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_vendor_ledgers');
    }
};
