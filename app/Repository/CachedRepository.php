<?php

namespace App\Repository;

use DateTime;

interface CachedRepository {
    public function get(): DateTime;

    public function update(DateTime $dateTime): void;
}