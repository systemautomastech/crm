<?php

namespace Automas\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class LeadActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lead_id',
        'log_type',
        'remark'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'id');
    }

    public function getLeadRemark(): string
    {
        $remark = json_decode($this->remark, true);
        $userName = $this->user?->name ?? (is_array($remark) ? ($remark['user_name'] ?? '') : '');

        if (is_array($remark)) {
            if ($this->log_type === 'Upload File') {
                return trim($userName . ' ' . __('Upload new file') . ' ' . ($remark['file_name'] ?? $remark['title'] ?? ''));
            }
            if ($this->log_type === 'Add Product') {
                return trim($userName . ' ' . __('Add new Products') . ' ' . ($remark['title'] ?? ''));
            }
            if ($this->log_type === 'Update Sources') {
                return trim($userName . ' ' . __('Update Sources'));
            }
            if ($this->log_type === 'Create Lead Call') {
                return trim($userName . ' ' . __('Create new Lead Call'));
            }
            if ($this->log_type === 'Create Lead Email') {
                return trim($userName . ' ' . __('Create new Lead Email'));
            }
            if ($this->log_type === 'Move') {
                $old = $remark['old_status'] ?? '';
                $new = $remark['new_status'] ?? '';
                return trim($userName . ' ' . __('Moved lead') . ($old && $new ? ' ' . __('from') . ' ' . $old . ' ' . __('to') . ' ' . $new : ''));
            }
            if ($this->log_type === 'Create Task') {
                return trim($userName . ' ' . __('Create new Task') . ' ' . ($remark['title'] ?? ''));
            }
            if ($this->log_type === 'Update Lead' && !empty($remark['changes'])) {
                $changeDetails = [];
                foreach ($remark['changes'] as $c) {
                    $changeDetails[] = $c['field'] . ': ' . $c['old'] . ' → ' . $c['new'];
                }
                return trim($userName . ' ' . __('updated') . ' ' . implode(', ', $changeDetails));
            }
            return trim($userName . ' ' . ($remark['title'] ?? $this->log_type));
        }

        return $this->remark ?: $this->log_type;
    }

    public function logIcon(): string
    {
        $type = $this->log_type;
        $icon = 'fa-history';

        if (!empty($type)) {
            if ($type == 'Move') {
                $icon = 'fa-arrows-alt';
            } elseif ($type == 'Add Product' || $type == 'Remove Product') {
                $icon = 'fa-dolly';
            } elseif ($type == 'Upload File' || $type == 'Delete File') {
                $icon = 'fa-file-alt';
            } elseif ($type == 'Update Sources' || $type == 'Remove Source') {
                $icon = 'fa-globe';
            } elseif ($type == 'Create Lead Call' || $type == 'Update Lead Call' || $type == 'Delete Lead Call') {
                $icon = 'fa-phone';
            } elseif ($type == 'Create Lead Email') {
                $icon = 'fa-envelope';
            } elseif ($type == 'Create Task' || $type == 'Update Task' || $type == 'Delete Task') {
                $icon = 'fa-tasks';
            } elseif ($type == 'Assign User' || $type == 'Remove User') {
                $icon = 'fa-users';
            } elseif ($type == 'Create Lead Discussion') {
                $icon = 'fa-comments';
            } elseif ($type == 'Update Notes') {
                $icon = 'fa-sticky-note';
            } elseif ($type == 'Update Labels') {
                $icon = 'fa-tags';
            } elseif ($type == 'Convert to Deal') {
                $icon = 'fa-briefcase';
            } elseif ($type == 'Create Lead') {
                $icon = 'fa-user-plus';
            }
        }

        return $icon;
    }
}