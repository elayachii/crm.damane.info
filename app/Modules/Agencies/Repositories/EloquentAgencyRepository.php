<?php

declare(strict_types=1);

namespace App\Modules\Agencies\Repositories;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Builder;

final class EloquentAgencyRepository implements AgencyRepositoryInterface
{
    /**
     * @return Builder<Agency>
     */
    public function query(): Builder
    {
        return Agency::query();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Agency
    {
        return Agency::create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Agency $agency, array $data): Agency
    {
        $agency->update($data);

        return $agency->refresh();
    }
}
