<?php

namespace App\Controller\Api;

use App\Repository\SpaceRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class SpaceApiController extends AbstractController
{
    #[Route('/spaces/list', name: 'api_spaces_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/spaces',
        summary: 'Obtener todos los espacios disponibles con filtros opcionales',
        tags: ['Spaces'],
        parameters: [
            new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'capacity', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'availableFrom', in: 'query', schema: new OA\Schema(type: 'string', format: 'time')),
            new OA\Parameter(name: 'availableTo', in: 'query', schema: new OA\Schema(type: 'string', format: 'time')),
        ],
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
                            new OA\Property(property: 'availableTo', type: 'string', example: '18:00'),
                            new OA\Property(property: 'type', type: 'string', nullable: true)
                        ]
                    )
                )
            )
        ]
    )]
    public function getSpaces(Request $request, SpaceRepository $spaceRepository): JsonResponse
    {
        $type = $request->query->get('type');
        $capacity = $request->query->get('capacity');
        $from = $request->query->get('availableFrom');
        $to = $request->query->get('availableTo');

        $qb = $spaceRepository->createQueryBuilder('s');

        if ($type) {
            $qb->andWhere('s.type = :type')->setParameter('type', $type);
        }

        if ($capacity) {
            $qb->andWhere('s.capacity >= :capacity')->setParameter('capacity', (int) $capacity);
        }

        if ($from) {
            $qb->andWhere('s.availableFrom <= :from')->setParameter('from', new \DateTime($from));
        }

        if ($to) {
            $qb->andWhere('s.availableTo >= :to')->setParameter('to', new \DateTime($to));
        }

        $spaces = $qb->getQuery()->getResult();

        $data = array_map(fn($space) => [
            'id' => $space->getId(),
            'name' => $space->getName(),
            'description' => $space->getDescription(),
            'capacity' => $space->getCapacity(),
            'photoUrl' => $space->getPhotoUrl(),
            'availableFrom' => $space->getAvailableFrom()?->format('H:i'),
            'availableTo' => $space->getAvailableTo()?->format('H:i'),
            'type' => $space->getType(),
        ], $spaces);

        return new JsonResponse($data);
    }

    #[Route('/space-types', name: 'api_space_types', methods: ['GET'])]
    #[OA\Get(
        path: '/api/space-types',
        summary: 'Obtener todos los tipos únicos de espacio',
        tags: ['Spaces'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tipos disponibles',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'string'))
            )
        ]
    )]
    public function getSpaceTypes(SpaceRepository $spaceRepository): JsonResponse
    {
        $conn = $spaceRepository->getEntityManager()->getConnection();
        $types = $conn->executeQuery('SELECT DISTINCT type FROM space WHERE type IS NOT NULL')->fetchFirstColumn();

        return new JsonResponse($types);
    }
}
