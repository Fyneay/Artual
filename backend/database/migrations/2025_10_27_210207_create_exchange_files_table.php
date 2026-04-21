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
        Schema::create('exchange_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("exchange_id");
            $table->string("filename");
            $table->string("path");
            $table->string("mime_type")->nullable();
            $table->integer("file_size")->nullable();
            $table->timestamps();
        
            $table->foreign("exchange_id")
                ->references("id")
                ->on("exchange")
                ->onDelete("cascade")
                ->onUpdate("cascade");
        
            $table->index(["exchange_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exchange_files', function (Blueprint $table) {
            $table->dropForeign(['exchange_id', 'created_by']);
        });
        Schema::dropIfExists('exchange_files');
    }
};
