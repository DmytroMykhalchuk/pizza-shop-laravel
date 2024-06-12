<?php

use App\Models\Order;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id()->startingValue(10000);
            $table->foreignId('telegraph_chat_id')->nullable()->constrained('telegraph_chats')->nullOnDelete();
            $table->enum('delivery_type', Order::DELIVERY_TYPES);
            $table->enum('payment_type', Order::PAYMENT_TYPES);
            $table->boolean('is_paid');
            $table->datetime('paid_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
