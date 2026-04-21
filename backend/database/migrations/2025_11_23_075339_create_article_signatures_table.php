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
        Schema::create('article_signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('signature_id');
            $table->timestamp('signed_at')->useCurrent();
            $table->timestamps();
            
            $table->foreign('article_id')
                  ->references('id')
                  ->on('articles')
                  ->onDelete('cascade');
            
            $table->foreign('signature_id')
                  ->references('id')
                  ->on('signatures')
                  ->onDelete('cascade');
            
            $table->unique(['article_id', 'signature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_signatures', function (Blueprint $table) {
            $table->dropForeign(['article_id', 'signature_id']);
            $table->dropUnique(['article_id', 'signature_id']);
        });
        Schema::dropIfExists('article_signatures');
    }
};
