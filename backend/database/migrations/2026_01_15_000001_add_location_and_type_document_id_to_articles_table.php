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
        Schema::table('articles', function (Blueprint $table) {
            $table->string('location')->nullable()->after('list_period_id');
            $table->unsignedBigInteger('type_document_id')->nullable()->after('location');
            
            $table->foreign('type_document_id')
                ->references('id')
                ->on('types_document')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['type_document_id']);
            $table->dropColumn(['location', 'type_document_id']);
        });
    }
};
