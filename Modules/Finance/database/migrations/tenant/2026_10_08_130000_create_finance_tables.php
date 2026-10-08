<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // why a patient paid (deposit for the next appointment, treatment fee, ...): managed by the clinic
        Schema::create('finance_payment_purposes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        // payments that the clinic registers by hand (the online ones are in the transactions table)
        Schema::create('finance_payments', function (Blueprint $table) {
            $table->id();
            // the financial history must outlive its related records: nothing here is removed with them
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // the patient
            $table->string('type', 20)->default('payment'); // payment | refund
            $table->string('method', 30); // pos | card_to_card | cash | other
            $table->foreignId('purpose_id')->nullable()->constrained('finance_payment_purposes')->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->dateTime('paid_at');
            $table->string('reference_number', 100)->nullable(); // receipt / tracking number
            $table->foreignId('appointment_user_id')->nullable()->constrained('appointment_users')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'paid_at']);
            $table->index('paid_at');
        });

        $now = now();
        DB::table('finance_payment_purposes')->insert(collect([
            'بیعانه نوبت بعدی',
            'هزینه ویزیت',
            'هزینه خدمات درمانی',
            'تسویه باقی مانده حساب',
            'سایر',
        ])->map(fn ($title, $i) => ['title' => $title, 'is_active' => true, 'sort' => $i + 1, 'created_at' => $now, 'updated_at' => $now])->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_payments');
        Schema::dropIfExists('finance_payment_purposes');
    }
};
