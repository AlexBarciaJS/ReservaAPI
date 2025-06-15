<?php

namespace App\Controller\Api;

use App\Repository\SpaceRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class SpaceApiController extends AbstractController
{
    #[Route('/spaces', name: 'api_spaces_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/spaces',
        summary: 'Obtener todos los espacios disponibles',
        tags: ['Spaces'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de espacios',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'description', type: 'string'),
                            new OA\Property(property: 'capacity', type: 'integer'),
                            new OA\Property(property: 'photoUrl', type: 'string', nullable: true),
                            new OA\Property(property: 'availableFrom', type: 'string', example: '08:00'),
                            new OA\Property(property: 'availableTo', type: 'string', example: '18:00')
                        ]
                    )
                )
            )
        ]
    )]
    public function getSpaces(SpaceRepository $spaceRepository): JsonResponse
    {
        $spaces = $spaceRepository->findAll();

        $data = array_map(fn($space) => [
            'id' => $space->getId(),
            'name' => $space->getName(),
            'description' => $space->getDescription(),
            'capacity' => $space->getCapacity(),
            'photoUrl' => $space->getPhotoUrl(),
            'availableFrom' => $space->getAvailableFrom()?->format('H:i'),
            'availableTo' => $space->getAvailableTo()?->format('H:i'),
        ], $spaces);

        return new JsonResponse($data);
    }
}
