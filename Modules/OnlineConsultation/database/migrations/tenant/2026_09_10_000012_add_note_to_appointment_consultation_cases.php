<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_consultation_cases', function (Blueprint $table) {
            $table->text('appointment_note')->nullable()->after('reopen_reason');
            $table->foreignId('note_author_id')->nullable()->after('appointment_note')->constrained('users')->nullOnDelete();
            $table->string('note_author_role', 20)->nullable()->after('note_author_id');
            $table->dateTimeTz('note_created_at')->nullable()->after('note_author_role');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_consultation_cases', function (Blueprint $table) {
            $table->dropForeign(['note_author_id']);
            $table->dropColumn(['appointment_note', 'note_author_id', 'note_author_role', 'note_created_at']);
        });
    }
};
