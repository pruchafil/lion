<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ParcelRepository;
use JsonException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final readonly class ParcelController
{
    public function __construct(
        private ParcelRepository $parcelRepository
    ) {
    }

    /**
     * @throws JsonException
     */
    public function index(Request $request, Response $response): Response
    {
        $data = $this->parcelRepository->getParcelById(1);

        return $this->json($response, $data);
    }

    /**
     * @throws JsonException
     */
    private function json(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
        ));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}