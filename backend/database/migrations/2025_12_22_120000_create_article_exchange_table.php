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
        Schema::create('article_exchange', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('exchange_id');
            $table->timestamps();

            $table->foreign('article_id')
                  ->references('id')
                  ->on('articles')
                  ->onDelete('cascade');

            $table->foreign('exchange_id')
                  ->references('id')
                  ->on('exchange')
                  ->onDelete('cascade');

            $table->unique(['article_id', 'exchange_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_exchange', function (Blueprint $table) {
            $table->dropForeign(['article_id']);
            $table->dropForeign(['exchange_id']);
            $table->dropUnique(['article_id', 'exchange_id']);
        });
        Schema::dropIfExists('article_exchange');
    }
};
