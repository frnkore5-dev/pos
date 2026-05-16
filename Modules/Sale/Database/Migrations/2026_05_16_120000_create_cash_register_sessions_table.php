<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_register_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('opening_amount')->comment('Amount in minor units (x100)');
            $table->unsignedBigInteger('closing_amount_counted')->nullable()->comment('Counted cash at close');
            $table->bigInteger('expected_cash_amount')->nullable()->comment('Expected cash at close');
            $table->bigInteger('cash_difference')->nullable()->comment('closing_counted - expected');
            $table->text('opening_note')->nullable();
            $table->text('closing_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_register_sessions');
    }
};
