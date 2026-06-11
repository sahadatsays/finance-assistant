<?php

namespace App\Services\Platform;

use App\Models\Platform\ActivityLog;
use App\Models\Platform\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogService
{
    public function log(
        string $description,
        string $logName = 'platform',
        ?Model $subject = null,
        ?User $causer = null,
        ?Tenant $tenant = null,
        ?array $properties = null,
        ?Request $request = null,
    ): ActivityLog {
        $request ??= request();

        return ActivityLog::query()->create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'causer_type' => $causer?->getMorphClass(),
            'causer_id' => $causer?->getKey(),
            'tenant_id' => $tenant?->id,
            'properties' => $properties,
            'ip_address' => $request?->ip(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, ActivityLog>
     */
    public function paginate(int $perPage = 20, ?string $logName = null): LengthAwarePaginator
    {
        $query = ActivityLog::query()
            ->with(['causer', 'tenant'])
            ->latest();

        if ($logName !== null) {
            $query->where('log_name', $logName);
        }

        return $query->paginate($perPage);
    }
}
