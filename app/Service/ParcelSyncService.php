<?php

declare(strict_types=1);

namespace App\Service;

interface ParcelSyncService
{
    public function syncJicinDistrict(): void;
    public function updateGlobalCache(): void;
}