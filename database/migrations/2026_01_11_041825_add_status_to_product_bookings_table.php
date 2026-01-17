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
        Schema::table('productbookings', function (Blueprint $table) {
            $table->string('status')
                  ->default('pending');
                //   ->after('total_balance'); // agar kisi aur column ke baad chahiye ho to change kar sakte ho
        });
    }

    public function down(): void
    {
        Schema::table('productbookings', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
