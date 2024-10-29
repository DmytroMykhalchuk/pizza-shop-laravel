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
        Schema::create('pizza_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pizza_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('locale')->index();
            $table->text('description');
            $table->text('detail')->nullable();

            $table->foreign('locale')->references('locale')->on('languages')->cascadeOnDelete();
            $table->unique(['pizza_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pizza_translations');
    }
};
