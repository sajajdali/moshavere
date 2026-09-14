<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('appointment_call_logs', fn (Blueprint $table) => $table->text('practitioner_note')->nullable()->after('additional_data')); }
    public function down(): void { Schema::table('appointment_call_logs', fn (Blueprint $table) => $table->dropColumn('practitioner_note')); }
};
