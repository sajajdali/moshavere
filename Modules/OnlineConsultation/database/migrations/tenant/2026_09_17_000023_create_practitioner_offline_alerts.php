<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table): void {
            $table->boolean('offline_alert_enabled')->default(false)->after('voip_call_token');
            $table->string('offline_alert_api_url')->nullable()->after('offline_alert_enabled');
            $table->string('offline_alert_route')->nullable()->after('offline_alert_api_url');
        });

        Schema::create('practitioner_offline_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointment_users')->cascadeOnDelete();
            $table->foreignId('practitioner_id')->constrained('consultation_practitioners')->cascadeOnDelete();
            $table->string('request_id')->unique();
            $table->string('extension', 20);
            $table->string('phone', 30);
            $table->string('route_name');
            $table->string('endpoint');
            $table->string('status', 30)->index();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->dateTime('checked_at');
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practitioner_offline_alerts');
        Schema::table('consultation_settings', fn (Blueprint $table) => $table->dropColumn([
            'offline_alert_enabled', 'offline_alert_api_url', 'offline_alert_route',
        ]));
    }
};
