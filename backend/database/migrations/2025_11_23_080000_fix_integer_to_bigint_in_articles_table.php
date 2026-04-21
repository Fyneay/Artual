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
        $this->dropForeignKeys('articles', ['section_id', 'created_by', 'user_id']);

        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->change();
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->unsignedBigInteger('user_id')->change();
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('user_id')
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
        $this->dropForeignKeys('articles', ['section_id', 'created_by', 'user_id']);

        Schema::table('articles', function (Blueprint $table) {
            $table->integer('section_id')->change();
            $table->integer('created_by')->nullable()->change();
            $table->integer('user_id')->change();
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
};

