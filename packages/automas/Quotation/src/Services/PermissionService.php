<?php

namespace Automas\Quotation\Services;

use Automas\Quotation\Models\SalesQuotation;
use Illuminate\Support\Facades\Auth;

class PermissionService
{
    /**
     * Check if current user has permission and access to a specific quotation action
     *
     * @param string|array $permissions
     * @param SalesQuotation|null $quotation
     * @return bool
     */
    public function canAccessQuotation($permissions, ?SalesQuotation $quotation = null): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Check Spatie permissions (single string or array of permissions)
        $hasPermission = is_array($permissions)
            ? $user->hasAnyPermission($permissions)
            : $user->can($permissions);

        if (!$hasPermission) {
            return false;
        }

        // If quotation model passed, also verify creator / ownership access
        if ($quotation) {
            return $this->hasQuotationAccess($quotation);
        }

        return true;
    }

    /**
     * Check workspace ownership & creator boundary access on a quotation
     */
    public function hasQuotationAccess(SalesQuotation $quotation): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($quotation->created_by != creatorId()) {
            return false;
        }

        if ($user->type === 'superadmin' || $user->type === 'company' || $user->can('manage-any-quotations')) {
            return true;
        }

        if ($user->can('manage-own-quotations')) {
            $isOwnerOrCustomer = ($quotation->creator_id == $user->id || $quotation->customer_id == $user->id);
            if (!$isOwnerOrCustomer) {
                return false;
            }

            if ($quotation->creator_id != $user->id && $user->type === 'client' && $quotation->status === 'draft') {
                return false;
            }

            return true;
        }

        return false;
    }
}
