<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Transaction\app\Models\Transaction;
use Modules\User\Entities\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id') ->nullable()->constrained()->nullOnDelete();

            /*
             * Signed balance change:
             * +100  => credit
             * -50   => debit (purchase)
             */
            $table->bigInteger('amount_change');

            // Balance snapshots for audit/debugging
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');

            $table->string('type', 50); //Type of wallet event : credit | purchase | refund |admin_adjustment
            $table->string('idempotency_key', 80)->nullable();

            $table->json('detail')->nullable();

            $table->timestamps();
            $table->index(['user_id', 'id','created_at']);
            $table->unique('idempotency_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_wallets');
    }
};
