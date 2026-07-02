<?php

namespace App\Repository;

use DateMalformedStringException;
use DateTime;
use PDO;

final readonly class CachedRepositoryImpl implements CachedRepository {

    function __construct(private PDO $pdo) { }

    /**
     * @throws DateMalformedStringException
     */
    public function get(): DateTime {
        $stmt = $this->pdo->prepare("SELECT * FROM sync_date");
        $stmt->execute();
        return new DateTime($stmt->fetch(PDO::FETCH_ASSOC)["cached"]);
    }

    public function update(DateTime $dateTime): void {
        $stmt = $this->pdo->prepare("UPDATE sync_date SET cached = :date");
        $stmt->execute(["date" => $dateTime->format("Y-m-d H:i:s")]);
    }
}
