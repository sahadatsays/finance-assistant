<?php

namespace App\Policies\Finance;

use App\Models\Finance\Attachment;
use App\Models\Platform\Tenant;
use App\Models\User;

class AttachmentPolicy
{
    public function upload(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function view(User $user, Attachment $attachment): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($attachment->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($attachment->tenant);
    }
}
