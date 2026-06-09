<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_renew_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('tenant_admin_user_id')->nullable();
            $table->string('tenant_admin_name')->nullable();
            $table->string('tenant_admin_mobile', 20)->nullable();
            $table->string('transaction_code', 50)->nullable()->unique();
            $table->string('gateway')->nullable();
            $table->string('reference_id')->nullable();
            $table->tinyInteger('status')->default(2)->comment('1 = success | 2 = pending | 0 = reject');
            $table->tinyInteger('paid_by')->default(1)->comment('1 = online | 2 = card to card | 3 = by admin');
            $table->bigInteger('cost')->default(0);
            $table->string('currency', 10)->default('IRT');
            $table->timestamp('previous_expires_at')->nullable();
            $table->timestamp('renewed_until')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('detail')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_renew_transactions');
    }
};
