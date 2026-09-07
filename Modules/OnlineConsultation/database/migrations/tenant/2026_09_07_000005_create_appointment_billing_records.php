<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_practitioners', function (Blueprint $table) {
            $table->unsignedBigInteger('hourly_rate')->nullable()->after('fee');
        });

        Schema::create('appointment_billing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointment_users')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('practitioner_id')->constrained('users')->restrictOnDelete();
            $table->string('consultation_type', 20)->index();
            $table->unsignedBigInteger('hourly_rate_snapshot');
            $table->unsignedSmallInteger('reserved_minutes');
            $table->unsignedInteger('answered_talk_seconds')->default(0);
            $table->unsignedSmallInteger('system_unused_minutes')->default(0);
            $table->unsignedSmallInteger('approved_unused_minutes')->default(0);
            $table->unsignedBigInteger('total_paid_amount')->default(0);
            $table->unsignedBigInteger('used_amount')->default(0);
            $table->unsignedBigInteger('suggested_refund_amount')->default(0);
            $table->unsignedBigInteger('refunded_amount')->default(0);
            $table->string('refund_status', 20)->default('pending')->index();
            $table->foreignId('wallet_transaction_id')->nullable()->constrained('user_wallets')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('adjustment_reason')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'created_at'], 'billing_patient_created_idx');
            $table->index(['practitioner_id', 'created_at'], 'billing_practitioner_created_idx');
        });

        Schema::create('appointment_billing_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_record_id')->constrained('appointment_billing_records')->restrictOnDelete();
            $table->string('action', 30)->index();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('system_unused_minutes');
            $table->unsignedSmallInteger('approved_unused_minutes');
            $table->unsignedBigInteger('amount');
            $table->text('reason')->nullable();
            $table->json('snapshot');
            $table->timestamps();
            $table->index(['billing_record_id', 'created_at'], 'billing_audit_record_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_billing_audits');
        Schema::dropIfExists('appointment_billing_records');
        Schema::table('consultation_practitioners', fn (Blueprint $table) => $table->dropColumn('hourly_rate'));
    }
};
