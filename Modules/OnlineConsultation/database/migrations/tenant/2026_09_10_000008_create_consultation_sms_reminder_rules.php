<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('consultation_sms_reminder_rules')) {
            Schema::create('consultation_sms_reminder_rules', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('recipient_type', 20);
                $table->unsignedInteger('minutes_before');
                $table->string('template')->nullable();
                $table->text('message_text')->nullable();
                $table->boolean('active')->default(false)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('consultation_sms_deliveries', 'reminder_rule_id')) {
            Schema::table('consultation_sms_deliveries', function (Blueprint $table) {
                $table->foreignId('reminder_rule_id')->nullable()->after('id')
                    ->constrained('consultation_sms_reminder_rules')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('consultation_sms_deliveries', 'recipient_type')) {
            Schema::table('consultation_sms_deliveries', fn (Blueprint $table) => $table->string('recipient_type', 20)->nullable()->after('type'));
        }
        if (! Schema::hasColumn('consultation_sms_deliveries', 'rule_title')) {
            Schema::table('consultation_sms_deliveries', fn (Blueprint $table) => $table->string('rule_title')->nullable()->after('recipient_type'));
        }
        if (! Schema::hasIndex('consultation_sms_deliveries', 'csd_recipient_idx')) {
            Schema::table('consultation_sms_deliveries', fn (Blueprint $table) => $table->index('recipient_type', 'csd_recipient_idx'));
        }
        if (! Schema::hasIndex('consultation_sms_deliveries', 'csd_appointment_recipient_idx')) {
            Schema::table('consultation_sms_deliveries', fn (Blueprint $table) => $table->index(['appointment_id', 'recipient_type'], 'csd_appointment_recipient_idx'));
        }

        $setting = static fn (int $key, mixed $default = null) => Schema::hasTable('settings')
            ? DB::table('settings')->where('setting_key', $key)->value('setting_value') ?? $default
            : $default;
        $enabled = static fn (mixed $value): bool => filter_var($value, FILTER_VALIDATE_BOOL);

        $firstTemplate = trim((string) $setting(501, ''));
        $finalTemplate = trim((string) $setting(507, ''));
        $practitionerTemplate = trim((string) $setting(510, ''));

        $rules = [
            ['title' => 'یادآوری اول بیمار', 'recipient_type' => 'patient', 'minutes_before' => max(1, (int) $setting(502, 180)), 'template' => $firstTemplate ?: null, 'active' => $firstTemplate !== '' && $enabled($setting(500, false))],
            ['title' => 'یادآوری نهایی بیمار', 'recipient_type' => 'patient', 'minutes_before' => max(1, (int) $setting(508, 15)), 'template' => $finalTemplate ?: null, 'active' => $finalTemplate !== '' && $enabled($setting(506, false))],
            ['title' => 'یادآوری نوبت برای مشاور', 'recipient_type' => 'practitioner', 'minutes_before' => $practitionerTemplate !== '' ? max(1, (int) $setting(511, 20)) : 20, 'template' => $practitionerTemplate ?: null, 'active' => $practitionerTemplate !== '' && $enabled($setting(509, false))],
        ];

        $secondTemplate = trim((string) $setting(504, ''));
        if ($secondTemplate !== '') {
            $rules[] = ['title' => 'یادآوری دوم بیمار', 'recipient_type' => 'patient', 'minutes_before' => max(1, (int) $setting(505, 60)), 'template' => $secondTemplate, 'active' => $enabled($setting(503, false))];
        }

        foreach ($rules as $rule) {
            DB::table('consultation_sms_reminder_rules')->insert($rule + ['created_at' => now(), 'updated_at' => now()]);
        }

        $legacyTypes = [
            'patient_first_reminder' => ['patient', 'یادآوری اول بیمار'],
            'patient_second_reminder' => ['patient', 'یادآوری دوم بیمار'],
            'patient_final_reminder' => ['patient', 'یادآوری نهایی بیمار'],
            'practitioner_appointment_reminder' => ['practitioner', 'یادآوری نوبت برای مشاور'],
        ];
        foreach ($legacyTypes as $type => [$recipientType, $title]) {
            $ruleId = DB::table('consultation_sms_reminder_rules')->where('title', $title)->value('id');
            DB::table('consultation_sms_deliveries')->where('type', $type)->update([
                'reminder_rule_id' => $ruleId,
                'recipient_type' => $recipientType,
                'rule_title' => $title,
            ]);
        }
        DB::table('consultation_sms_deliveries')->where('type', 'practitioner_daily_report')->update([
            'recipient_type' => 'practitioner', 'rule_title' => 'گزارش پایان روز مشاور',
        ]);
    }

    public function down(): void
    {
        Schema::table('consultation_sms_deliveries', function (Blueprint $table) {
            $table->dropForeign(['reminder_rule_id']);
            $table->dropIndex('csd_appointment_recipient_idx');
            $table->dropIndex('csd_recipient_idx');
            $table->dropColumn(['reminder_rule_id', 'recipient_type', 'rule_title']);
        });
        Schema::dropIfExists('consultation_sms_reminder_rules');
    }
};
