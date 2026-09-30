<?php

namespace Automas\Pbx\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PbxSetting extends Model
{
    use HasFactory;

    protected $table = 'pbx_settings';

    protected $fillable = [
        'pbx_name',
        'pbx_host',
        'ami_host',
        'ami_port',
        'ami_username',
        'ami_password',
        'call_report_api_url',
        'call_report_api_key',
        'sip_domain',
        'websocket_url',
        'stun_server',
        'sip_trunk_name',
        'extension_start',
        'extension_end',
        'max_extensions',
        'is_enabled',
        'ringtone',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'ami_port' => 'integer',
        'extension_start' => 'integer',
        'extension_end' => 'integer',
        'max_extensions' => 'integer',
        'is_enabled' => 'boolean',
        'ami_password' => 'encrypted',
        'call_report_api_key' => 'encrypted',

    ];

    public function scopeForCreator($query, int $creatorId)
    {
        return $query->where('creator_id', $creatorId);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function isExtensionInRange(string $extension): bool
    {
        $ext = (int) $extension;

        return $ext >= $this->extension_start && $ext <= $this->extension_end;
    }
}
