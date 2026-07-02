<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ParcelRepository;
use App\Service\ParcelService;
use JsonException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final readonly class ParcelController
{
    public function __construct(
        private ParcelService $parcelService
    ) {
    }

    /**
     * @throws JsonException
     */
    public function getById(Request $request, Response $response, array $args): Response {
        $data = $this->parcelService->getParcelById((int) $args['id']);

        return $this->json($response, $data);
    }

    /**
     * @throws JsonException
     */
    public function getArea(Request $request, Response $response, array $args): Response {
        $area = $request->getQueryParams()['area'];

        if ($area === null) {
            return $response->withStatus(400);
        }

        $arr = explode(',', $area);

        if (count($arr) !== 4) {
            return $response->withStatus(400);
        }

        $bbox = [];

        foreach ($arr as $value) {
            $bbox[] = (float) $value;
        }

        $areas = $this->parcelService->getArea($bbox[0], $bbox[1], $bbox[2], $bbox[3]);

        $features = [];
        foreach ($areas as $area) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => json_decode($area->geomEwkt, true),
                'properties' => [
                    'id' => (int)$area->id,
                    'cachedAt' => $area->CachedAt
                ]
            ];
        }


        return $this->json($response, ['type' => 'FeatureCollection', 'features' => $features]);
    }

    /**
     * @throws JsonException
     */
    private function json(Response $response, mixed $data, int $status = 200): Response {
        $response->getBody()->write(json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
        ));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}