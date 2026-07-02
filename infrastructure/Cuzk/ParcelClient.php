<?php

declare(strict_types=1);

namespace Infrastructure\Cuzk;

interface ParcelClient {
    public function getParcelInBbox(float $minX, float $maxX, float $minY, float $maxY) : string;
    public function getParcelUpdateInBbox(float $minX, float $maxX, float $minY, float $maxY) : string;
    public function getParcelById(string $glmId) : string;
}