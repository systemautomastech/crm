<?php

namespace Automas\WhatsAppChat\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsappContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_no',
        'name',
        'user_id',
        'creator_id',
        'created_by'
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'creator_id' => 'integer',
            'created_by' => 'integer',
        ];
    }

    public function whatsapp_chat()
    {
        return $this->hasMany(WhatsappChat::class, 'whatsapp_contact_id');
    }

    public function lastChat()
    {
        return $this->hasOne(WhatsappChat::class, 'whatsapp_contact_id')->latest('id');
    }

    public function unseen()
    {
        return $this->hasOne(WhatsappChat::class, 'whatsapp_contact_id')->where('is_seen', 0);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}