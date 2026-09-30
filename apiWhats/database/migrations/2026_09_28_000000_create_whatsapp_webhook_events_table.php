<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 128);
            $table->string('business_account_id', 128)->nullable();
            $table->string('phone_number_id', 128)->nullable();
            $table->json('message_ids')->nullable();
            $table->timestamps();

            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_webhook_events');
    }
};
