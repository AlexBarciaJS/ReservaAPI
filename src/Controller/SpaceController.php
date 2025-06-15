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

#[Route('/space')]
final class SpaceController extends AbstractController
{
    #[Route(name: 'app_space_index', methods: ['GET'])]
    public function index(SpaceRepository $spaceRepository): Response
    {
        return $this->render('space/index.html.twig', [
            'spaces' => $spaceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_space_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $space = new Space();
        $form = $this->createForm(SpaceForm::class, $space);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($space);
            $entityManager->flush();

            return $this->redirectToRoute('app_space_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('space/new.html.twig', [
            'space' => $space,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_space_show', methods: ['GET'])]
    public function show(Space $space): Response
    {
        return $this->render('space/show.html.twig', [
            'space' => $space,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_space_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Space $space, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SpaceForm::class, $space);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_space_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('space/edit.html.twig', [
            'space' => $space,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_space_delete', methods: ['POST'])]
    public function delete(Request $request, Space $space, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$space->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($space);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_space_index', [], Response::HTTP_SEE_OTHER);
    }

    // ✅ API: Endpoint documentado para frontend Angular
    #[Route('/api/spaces', name: 'get_spaces', methods: ['GET'])]
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
    #[Security(name: 'BearerAuth')]
    public function getSpaces(SpaceRepository $spaceRepository): JsonResponse
    {
        $spaces = $spaceRepository->findAll();

        $data = array_map(function ($space) {
            return [
                'id' => $space->getId(),
                'name' => $space->getName(),
                'description' => $space->getDescription(),
                'capacity' => $space->getCapacity(),
                'photoUrl' => $space->getPhotoUrl(),
                'availableFrom' => $space->getAvailableFrom()->format('H:i'),
                'availableTo' => $space->getAvailableTo()->format('H:i'),
            ];
        }, $spaces);

        return new JsonResponse($data);
    }
}
