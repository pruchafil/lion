<?php

declare(strict_types=1);

namespace Infrastructure\Cuzk;

interface ParcelClient {
    public function getParcelInBbox(float $minX, float $maxX, float $minY, float $maxY) : string;
}