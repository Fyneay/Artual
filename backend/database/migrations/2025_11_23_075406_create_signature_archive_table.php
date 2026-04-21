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
        Schema::create('signature_archive', function (Blueprint $table) {
            $table->id();
            $table->string('archive_name');
            $table->string('archive_path'); // Путь на SFTP
            $table->unsignedBigInteger('article_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('article_id')
                  ->references('id')
                  ->on('articles')
                  ->onDelete('set null');
            
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signature_archive', function (Blueprint $table) {
            $table->dropForeign(['article_id', 'created_by']);
        });
        Schema::dropIfExists('signature_archive');
    }
};
