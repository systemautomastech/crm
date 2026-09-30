<?php

namespace Automas\WhatsAppChat\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsappChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'whatsapp_contact_id',
        'is_send',
        'is_seen',
        'message',
        'image_path',
        'creator_id',
        'created_by'
    ];

    protected function casts(): array
    {
        return [
            'whatsapp_contact_id' => 'integer',
            'is_send' => 'boolean',
            'is_seen' => 'boolean',
            'creator_id' => 'integer',
            'created_by' => 'integer',
        ];
    }

    public function contact()
    {
        return $this->belongsTo(WhatsappContact::class, 'whatsapp_contact_id');
    }
}