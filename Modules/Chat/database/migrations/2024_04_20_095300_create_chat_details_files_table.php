<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Chat\app\Models\ChatDetail;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_details_files', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\User\Entities\User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\User\Entities\User::class,'answer_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(ChatDetail::class )->nullable()->constrained()->cascadeOnDelete();
            $table->string('original_name')->nullable();
            $table->string('server_name')->nullable();
            $table->string('disk')->nullable();
            $table->string('path')->nullable();
            $table->string('extension')->nullable();
            $table->string('mime')->nullable();
            $table->integer('size')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_details_files');
    }
};
