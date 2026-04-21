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
        $this->dropForeignKeys('users', ['role_id']);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')
                ->on('users_groups')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropForeignKeys('users', ['role_id']);

        Schema::table('users', function (Blueprint $table) {
            $table->integer('role_id')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')
                ->on('users_groups')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
};
