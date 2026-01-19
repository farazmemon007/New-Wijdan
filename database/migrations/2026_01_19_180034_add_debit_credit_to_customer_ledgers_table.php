<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customer_ledgers', function (Blueprint $table) {
            $table->decimal('total_debit', 15, 2)->default(0)->after('opening_balance');
            $table->decimal('total_credit', 15, 2)->default(0)->after('total_debit');
        });
    }

    public function down()
    {
        Schema::table('customer_ledgers', function (Blueprint $table) {
            $table->dropColumn(['total_debit', 'total_credit']);
        });
    }
};

