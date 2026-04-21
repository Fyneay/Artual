<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('article_files', function (Blueprint $table) {
            $table->string('status')
                  ->default('pending')
                  ->after('file_size')
                  ->comment('Статус проверки: pending, checking, approved, rejected');
            $table->string('threat_name')
                  ->nullable()
                  ->after('status')
                  ->comment('Название угрозы, если файл заражен');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_files', function (Blueprint $table) {
            $table->dropColumn(['status', 'threat_name']);
        });
    }
};
