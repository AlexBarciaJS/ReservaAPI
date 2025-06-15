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
    #[Security(name: 'BearerAuth')]
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
    #[Security(name: 'BearerAuth')]    
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
    #[Security(name: 'BearerAuth')]
    public function delete(Request $request, Space $space, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$space->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($space);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_space_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/api/spaces', name: 'get_spaces', methods: ['GET'])]
    #[OA\Get(
        path: '/api/spaces',
        summary: 'Listar espacios disponibles con filtros opcionales',
        parameters: [
            new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'capacity', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'availableFrom', in: 'query', schema: new OA\Schema(type: 'string', format: 'time')),
            new OA\Parameter(name: 'availableTo', in: 'query', schema: new OA\Schema(type: 'string', format: 'time')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista de espacios filtrados')
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

        $data = array_map(fn($s) => [
            'id' => $s->getId(),
            'name' => $s->getName(),
            'description' => $s->getDescription(),
            'capacity' => $s->getCapacity(),
            'photoUrl' => $s->getPhotoUrl(),
            'type' => $s->getType(), // si existe
            'availableFrom' => $s->getAvailableFrom()?->format('H:i'),
            'availableTo' => $s->getAvailableTo()?->format('H:i'),
        ], $spaces);

        return new JsonResponse($data);
    }

    #[Route('/api/space-types', name: 'get_space_types', methods: ['GET'])]
    #[Security(name: 'BearerAuth')]  
    public function getSpaceTypes(SpaceRepository $spaceRepository): JsonResponse
    {
        $entityManager = $spaceRepository->getEntityManager();
        $conn = $entityManager->getConnection();

        $results = $conn->executeQuery('SELECT DISTINCT type FROM space WHERE type IS NOT NULL')->fetchFirstColumn();

        return new JsonResponse($results);
    }
}
