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
        Schema::create('lists_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Название срока хранения');
            $table->integer('retention_period')->comment('Срок хранения в годах');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lists_periods');
    }
};
