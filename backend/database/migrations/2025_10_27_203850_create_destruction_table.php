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
        Schema::create('destruction', function (Blueprint $table) {
            $table->id();
            $table->string("name")->unique();
            $table->integer("article_id");
            $table->integer("created_by");
            $table->timestamps();

            $table->foreign("article_id")
            ->references("id")
            ->on("articles")
            ->onDelete("cascade")
            ->onUpdate("cascade");

            $table->foreign("created_by")
            ->references("id")
            ->on("users")
            ->onDelete("cascade")
            ->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destruction', function (Blueprint $table) {
            $table->dropForeign(['article_id', 'created_by']);
        });
        Schema::dropIfExists('destruction');
    }
};
