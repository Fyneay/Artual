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
        Schema::create('article_destruction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('destruction_id');
            $table->timestamps();
            
            $table->foreign('article_id')
                  ->references('id')
                  ->on('articles')
                  ->onDelete('cascade');
            
            $table->foreign('destruction_id')
                  ->references('id')
                  ->on('destruction')
                  ->onDelete('cascade');
            
            $table->unique(['article_id', 'destruction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_destruction', function (Blueprint $table) {
            $table->dropForeign(['article_id']);
            $table->dropForeign(['destruction_id']);
            $table->dropUnique(['article_id', 'destruction_id']);
        });
        Schema::dropIfExists('article_destruction');
    }
};
