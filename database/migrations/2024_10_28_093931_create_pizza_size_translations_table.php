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
        Schema::create('pizza_size_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pizza_size_id')->constrained()->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('name');
            
            $table->foreign('locale')->references('locale')->on('languages')->cascadeOnDelete();
            $table->unique(['pizza_size_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pizza_size_translations');
    }
};
