<?php

declare(strict_types=1);

namespace App\Modules\Agencies\Repositories;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Builder;

interface AgencyRepositoryInterface
{
    /**
     * @return Builder<Agency>
     */
    public function query(): Builder;

    public function create(array $data): Agency;

    public function update(Agency $agency, array $data): Agency;
}
