<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('bay_id')->constrained()->cascadeOnDelete();

            // Stored in UTC; shown in config('studio.timezone').
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedTinyInteger('players');

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');

            $table->string('status');
            $table->dateTime('hold_expires_at')->nullable();

            $table->unsignedInteger('subtotal_cents');
            $table->unsignedInteger('tax_cents');
            $table->unsignedInteger('total_cents');

            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable();

            $table->string('door_code', 6)->nullable();

            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['bay_id', 'starts_at', 'ends_at']);
            $table->index(['status', 'hold_expires_at']);
            $table->index('door_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
