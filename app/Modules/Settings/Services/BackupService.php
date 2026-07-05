<?php

declare(strict_types=1);

namespace App\Modules\Settings\Services;

use App\Models\BackupRecord;
use App\Models\User;

final class BackupService
{
    public function createManualBackup(?User $user): BackupRecord
    {
        return BackupRecord::query()->create([
            'created_by' => $user?->id,
            'filename' => 'manual-backup-' . now()->format('Ymd-His') . '.zip',
            'disk' => 'local',
            'size' => 0,
            'status' => 'queued',
            'completed_at' => null,
        ]);
    }

    /**
     * @return list<BackupRecord>
     */
    public function history(): array
    {
        return BackupRecord::query()
            ->with('creator')
            ->latest()
            ->limit(20)
            ->get()
            ->all();
    }
}
