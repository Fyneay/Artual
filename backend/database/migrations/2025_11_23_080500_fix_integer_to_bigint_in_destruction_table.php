<?php

use Database\Migrations\Traits\DropsForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use DropsForeignKeys;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->dropForeignKeys('destruction', ['article_id', 'created_by']);

        Schema::table('destruction', function (Blueprint $table) {
            $table->unsignedBigInteger('article_id')->change();
            $table->unsignedBigInteger('created_by')->change();
        });

        Schema::table('destruction', function (Blueprint $table) {
            $table->foreign('article_id')
                ->references('id')
                ->on('articles')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropForeignKeys('destruction', ['article_id', 'created_by']);

        Schema::table('destruction', function (Blueprint $table) {
            $table->integer('article_id')->change();
            $table->integer('created_by')->change();
        });

        Schema::table('destruction', function (Blueprint $table) {
            $table->foreign('article_id')
                ->references('id')
                ->on('articles')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
};
