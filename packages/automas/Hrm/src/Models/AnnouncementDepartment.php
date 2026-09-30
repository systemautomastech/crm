<?php

namespace Automas\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementDepartment extends Model
{
    protected $fillable = [
        'announcement_id',
        'department_id',
        'creator_id',
        'created_by',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}