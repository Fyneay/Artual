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
        Schema::table('exchange', function (Blueprint $table) {
            $table->string('fund_name')->nullable()->after('reason');
            $table->string('receiving_organization')->nullable()->after('fund_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exchange', function (Blueprint $table) {
            $table->dropColumn(['fund_name', 'receiving_organization']);
        });
    }
};