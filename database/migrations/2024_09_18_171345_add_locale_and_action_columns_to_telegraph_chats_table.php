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
        Schema::table('telegraph_chats', function (Blueprint $table) {
            $table->string('locale')->nullable();
            $table->string('action')->nullable();
            $table->json('action_data')->nullable();
            $table->string('user_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('last_message_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegraph_chats', function (Blueprint $table) {
            $table->dropColumn('locale');
            $table->dropColumn('action');
            $table->dropColumn('action_data');
            $table->dropColumn('user_id');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('username');
            $table->dropColumn('last_message_id');
        });
    }
};
