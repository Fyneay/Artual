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
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->text('signature_data'); // Подпись от CryptoPro
            $table->string('certificate_name');
            $table->string('certificate_subject');
            $table->string('signature_hash')->unique(); // Для проверки уникальности
            $table->unsignedBigInteger('signed_by');
            $table->timestamps();
            
            $table->foreign('signed_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->index('signature_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropForeign(['signed_by']);
            $table->dropIndex(['signature_hash']);
        });
        Schema::dropIfExists('signatures');
    }
};
