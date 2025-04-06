<?php

use Modules\User\Entities\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\Discount\app\Models\Discount;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Discount::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('transaction_code', 20)->nullable();
            $table->morphs('transactionable');
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
