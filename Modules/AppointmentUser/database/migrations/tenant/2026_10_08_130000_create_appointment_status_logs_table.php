<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // بدون کلید خارجی؛ تا با حذف نوبت یا کاربر، تاریخچه باقی بماند
        Schema::create('appointment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_user_id')->index();
            $table->string('tracking_code', 50)->nullable()->index();
            // created | status_changed | deleted | restored
            $table->string('event', 20)->index();
            $table->tinyInteger('from_status')->nullable();
            $table->tinyInteger('to_status')->nullable();

            // کسی که تغییر را انجام داده (نسخه نگه‌داری شده از نام در لحظه تغییر)
            $table->unsignedBigInteger('changed_by')->nullable()->index();
            $table->string('changed_by_name')->nullable();
            // web | console | api (بدون ورود کاربر مثل بازگشت از درگاه پرداخت: system)
            $table->string('source', 20)->nullable();
            $table->string('ip', 45)->nullable();

            // اطلاعات نوبت در لحظه تغییر
            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->string('patient_name')->nullable();
            $table->string('patient_mobile', 20)->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable()->index();
            $table->string('doctor_name')->nullable();
            $table->date('date_visit')->nullable();
            $table->string('start_time', 20)->nullable();

            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_status_logs');
    }
};
