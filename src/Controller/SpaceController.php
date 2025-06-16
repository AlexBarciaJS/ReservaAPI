<?php

namespace App\Controller;

use App\Entity\Space;
use App\Form\SpaceForm;
use App\Repository\SpaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/spaces')]
#[OA\Tag(name: 'Spaces')]
class SpaceController extends AbstractController
{
    #[Route('', name: 'space_list', methods: ['GET'])]
    #[OA\Get(summary: 'Listar espacios disponibles')]
    public function list(SpaceRepository $repo): JsonResponse
    {
        $spaces = $repo->findAll();

        $data = array_map(fn($s) => [
            'id' => $s->getId(),
            'name' => $s->getName(),
            'description' => $s->getDescription(),
            'capacity' => $s->getCapacity(),
            'photoUrl' => $s->getPhotoUrl(),
            'type' => $s->getType(),
            'availableFrom' => $s->getAvailableFrom()?->format('H:i'),
            'availableTo' => $s->getAvailableTo()?->format('H:i'),
        ], $spaces);

        return new JsonResponse($data);
    }

    #[Route('', name: 'space_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(summary: 'Crear un nuevo espacio')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $space = new Space();
        $space->setName($data['name'] ?? '');
        $space->setDescription($data['description'] ?? '');
        $space->setCapacity($data['capacity'] ?? null);
        $space->setType($data['type'] ?? null);
        $space->setPhotoUrl($data['photoUrl'] ?? null);
        $space->setAvailableFrom(new \DateTime($data['availableFrom'] ?? '08:00'));
        $space->setAvailableTo(new \DateTime($data['availableTo'] ?? '18:00'));

        $em->persist($space);
        $em->flush();

        return new JsonResponse(['id' => $space->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'space_show', methods: ['GET'])]
    #[OA\Get(summary: 'Mostrar detalles de un espacio')]
    public function show(Space $space): JsonResponse
    {
        return new JsonResponse([
            'id' => $space->getId(),
            'name' => $space->getName(),
            'description' => $space->getDescription(),
            'capacity' => $space->getCapacity(),
            'photoUrl' => $space->getPhotoUrl(),
            'type' => $space->getType(),
            'availableFrom' => $space->getAvailableFrom()?->format('H:i'),
            'availableTo' => $space->getAvailableTo()?->format('H:i'),
        ]);
    }

    #[Route('/{id}', name: 'space_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(summary: 'Actualizar un espacio')]
    public function update(Request $request, Space $space, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $space->setName($data['name'] ?? $space->getName());
        $space->setDescription($data['description'] ?? $space->getDescription());
        $space->setCapacity($data['capacity'] ?? $space->getCapacity());
        $space->setType($data['type'] ?? $space->getType());
        $space->setPhotoUrl($data['photoUrl'] ?? $space->getPhotoUrl());
        $space->setAvailableFrom(new \DateTime($data['availableFrom'] ?? $space->getAvailableFrom()->format('H:i')));
        $space->setAvailableTo(new \DateTime($data['availableTo'] ?? $space->getAvailableTo()->format('H:i')));

        $em->flush();

        return new JsonResponse(['status' => 'updated']);
    }

    #[Route('/{id}', name: 'space_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(summary: 'Eliminar un espacio')]
    public function delete(Space $space, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($space);
        $em->flush();

        return new JsonResponse(['status' => 'deleted']);
    }
}
