<?php

declare(strict_types=1);

namespace App\Modules\Agencies\Services;

use App\Models\Agency;
use App\Modules\Agencies\DTOs\AgencyData;
use App\Modules\Agencies\Repositories\AgencyRepositoryInterface;

final readonly class AgencyService
{
    public function __construct(
        private AgencyRepositoryInterface $agencies,
    ) {
    }

    public function create(AgencyData $data): Agency
    {
        return $this->agencies->create($data->toArray());
    }

    public function update(Agency $agency, AgencyData $data): Agency
    {
        return $this->agencies->update($agency, $data->toArray());
    }
}
