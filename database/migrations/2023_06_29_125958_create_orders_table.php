<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users');
            $table->string('subtotal', 256);
            $table->string('total', 256);
            $table->integer('billing_address_id')->nullable();
            $table->integer('shipping_address_id')->nullable();
            $table->enum('order_status', ['paid', 'checkout', 'canceled', 'failed', 'expired']);
            $table->integer('transaction_id');
            $table->string('payment_mode', 64);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
