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
        $this->dropForeignKeys('access', ['created_by', 'granted_by', 'article_id', 'status_id']);

        Schema::table('access', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->change();
            $table->unsignedBigInteger('granted_by')->change();
            $table->unsignedBigInteger('article_id')->change();
            $table->unsignedBigInteger('status_id')->nullable()->change();
        });

        Schema::table('access', function (Blueprint $table) {
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('granted_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('article_id')
                ->references('id')
                ->on('articles')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('status_id')
                ->references('id')
                ->on('status')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropForeignKeys('access', ['created_by', 'granted_by', 'article_id', 'status_id']);

        Schema::table('access', function (Blueprint $table) {
            $table->integer('created_by')->change();
            $table->integer('granted_by')->change();
            $table->integer('article_id')->change();
            $table->integer('status_id')->nullable()->change();
        });

        Schema::table('access', function (Blueprint $table) {
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('granted_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('article_id')
                ->references('id')
                ->on('articles')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('status_id')
                ->references('id')
                ->on('status')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
};
