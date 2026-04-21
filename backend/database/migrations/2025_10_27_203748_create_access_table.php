<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

     //Добавить связь с статусом
    public function up(): void
    {
        Schema::create('access', function (Blueprint $table) {
            $table->id();
            $table->string("name")->unique();
            $table->integer("created_by");
            $table->integer("granted_by");
            $table->integer("article_id");
            $table->date("access_date");
            $table->date("close_date")->nullable();
            $table->string("reason")->nullable();
            $table->timestamps();

            $table->foreign("created_by")
            ->references("id")
            ->on("users")
            ->onDelete("cascade")
            ->onUpdate("cascade");

            $table->foreign("granted_by")
            ->references("id")
            ->on("users")
            ->onDelete("cascade")
            ->onUpdate("cascade");

            $table->foreign("article_id")
            ->references("id")
            ->on("articles")
            ->onDelete("cascade")
            ->onUpdate("cascade");

            $table->index(["granted_by", "article_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access', function (Blueprint $table) {
            $table->dropForeign(["created_by", "granted_by", "article_id"]);
            $table->dropIndex(["granted_by", "article_id"]);
        });
        Schema::dropIfExists('access');
    }
};
