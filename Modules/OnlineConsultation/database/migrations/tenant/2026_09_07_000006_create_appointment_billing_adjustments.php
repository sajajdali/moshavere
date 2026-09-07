<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_billing_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_record_id');
            $table->uuid('request_token')->unique();
            $table->unsignedSmallInteger('previous_unused_minutes');
            $table->unsignedSmallInteger('corrected_unused_minutes');
            $table->bigInteger('amount_change');
            $table->foreignId('wallet_transaction_id');
            $table->foreignId('actor_id')->nullable();
            $table->text('reason');
            $table->timestamps();
            $table->index(['billing_record_id', 'created_at'], 'billing_adjust_record_created_idx');
            $table->foreign('billing_record_id', 'billing_adjust_record_fk')->references('id')->on('appointment_billing_records')->restrictOnDelete();
            $table->foreign('wallet_transaction_id', 'billing_adjust_wallet_fk')->references('id')->on('user_wallets')->restrictOnDelete();
            $table->foreign('actor_id', 'billing_adjust_actor_fk')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('appointment_billing_adjustments'); }
};
