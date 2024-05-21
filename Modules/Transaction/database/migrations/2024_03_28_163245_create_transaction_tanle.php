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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\User\Entities\User::class)->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('transaction_code', 20)->nullable();
            $table->morphs('transactionable');
            $table->tinyInteger('payment_for')->default(10)->comment('10 = in_person | 20 = online | 30 = both them');
            $table->tinyInteger('status')->default(0)->comment('1 = success | 2 = pending | 0 = reject');
            $table->tinyInteger('paid_by')->comment('1 = online | 2 card to card | 3 = by admin	');
            $table->bigInteger('cost')->default(0);
            $table->bigInteger('total_cost')->default(0);
            $table->bigInteger('discount_amount')->default(0);
            $table->text('discount_code')->nullable();
            $table->json('detail')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
