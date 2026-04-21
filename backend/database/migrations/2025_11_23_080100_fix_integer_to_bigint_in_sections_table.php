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
        $this->dropForeignKeys('sections', ['user_id', 'type_id']);

        Schema::table('sections', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->unsignedBigInteger('type_id')->nullable()->change();
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('type_id')
                ->references('id')
                ->on('types_sections')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropForeignKeys('sections', ['user_id', 'type_id']);

        Schema::table('sections', function (Blueprint $table) {
            $table->integer('user_id')->change();
            $table->integer('type_id')->nullable()->change();
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('type_id')
                ->references('id')
                ->on('types_sections')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
};
