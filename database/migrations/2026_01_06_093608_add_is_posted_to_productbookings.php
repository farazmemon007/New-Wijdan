<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_add_is_posted_to_productbookings.php

public function up()
{
    Schema::table('productbookings', function (Blueprint $table) {
        $table->boolean('is_posted')->default(0)->after('total_balance');
        $table->timestamp('posted_at')->nullable()->after('is_posted');
    });
}

public function down()
{
    Schema::table('productbookings', function (Blueprint $table) {
        $table->dropColumn(['is_posted', 'posted_at']);
    });
}

};
