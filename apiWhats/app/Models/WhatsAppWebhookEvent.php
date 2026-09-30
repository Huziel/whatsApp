<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppWebhookEvent extends Model
{
    protected $table = 'whatsapp_webhook_events';

    protected $fillable = [
        'event_type',
        'business_account_id',
        'phone_number_id',
        'message_ids',
    ];

    protected function casts(): array
    {
        return [
            'message_ids' => 'array',
        ];
    }
}
