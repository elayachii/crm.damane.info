<?php

declare(strict_types=1);

namespace App\Modules\Agencies\Actions;

use App\Models\Agency;
use App\Modules\Agencies\DTOs\AgencyData;
use App\Modules\Agencies\Services\AgencyService;

final readonly class CreateAgencyAction
{
    public function __construct(
        private AgencyService $agencyService,
    ) {
    }

    public function execute(AgencyData $data): Agency
    {
        return $this->agencyService->create($data);
    }
}
