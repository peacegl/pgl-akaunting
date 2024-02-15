<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('double_entry_accounts', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->nullable()->after('enabled');
            $table->string('created_from', 100)->nullable()->after('enabled');
        });

        Schema::table('double_entry_journals', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->nullable()->after('currency_rate');
            $table->string('created_from', 100)->nullable()->after('currency_rate');
        });

        Schema::table('double_entry_ledger', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->nullable()->after('notes');
            $table->string('created_from', 100)->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
