<?php

namespace App\Controller;

use App\DTO\CreateReservationDTO;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\SpaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReservationController extends AbstractController
{
    #[Route('/api/reservations', name: 'create_reservation', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createReservation(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        SpaceRepository $spaceRepo,
        ReservationRepository $reservationRepo
    ): JsonResponse {
        /** @var CreateReservationDTO $dto */
        $dto = $serializer->deserialize($request->getContent(), CreateReservationDTO::class, 'json');

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $start = new \DateTime($dto->startTime);
        $end = new \DateTime($dto->endTime);

        if ($start >= $end) {
            return $this->json(['error' => 'End time must be after start time'], 400);
        }

        $space = $spaceRepo->find($dto->spaceId);
        if (!$space) {
            return $this->json(['error' => 'Space not found'], 404);
        }

        $existingReservations = $reservationRepo->findBy(['space' => $space]);
        foreach ($existingReservations as $existing) {
            if (
                $start < $existing->getEndTime() &&
                $end > $existing->getStartTime()
            ) {
                return $this->json(['error' => 'The space is already reserved at this time.'], 409);
            }
        }

        $reservation = new Reservation();
        $reservation->setEventName($dto->eventName);
        $reservation->setStartTime($start);
        $reservation->setEndTime($end);
        $reservation->setSpace($space);
        $reservation->setUser($this->getUser());

        $em->persist($reservation);
        $em->flush();

        return $this->json(['message' => 'Reservation created successfully'], 201);
    }

    #[Route('/api/reservations', name: 'list_user_reservations', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OA\Get(
        path: '/api/reservations',
        summary: 'Obtener reservas del usuario autenticado con filtros opcionales',
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', description: 'Fecha desde (Y-m-d)', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'to', in: 'query', description: 'Fecha hasta (Y-m-d)', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'spaceId', in: 'query', description: 'ID del espacio', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reservas filtradas',
                content: new OA\JsonContent(type: 'array', items: new OA\Items())
            )
        ]
    )]
    public function listUserReservations(
        Request $request,
        ReservationRepository $reservationRepo
    ): JsonResponse {
        $user = $this->getUser();
        $from = $request->query->get('from');
        $to = $request->query->get('to');
        $spaceId = $request->query->get('spaceId');

        $qb = $reservationRepo->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user);

        if ($from) {
            $fromDate = \DateTime::createFromFormat('Y-m-d', $from);
            if ($fromDate) {
                $qb->andWhere('r.startTime >= :from')->setParameter('from', $fromDate);
            }
        }

        if ($to) {
            $toDate = \DateTime::createFromFormat('Y-m-d', $to);
            if ($toDate) {
                $qb->andWhere('r.endTime <= :to')->setParameter('to', $toDate);
            }
        }

        if ($spaceId) {
            $qb->andWhere('r.space = :spaceId')->setParameter('spaceId', $spaceId);
        }

        $reservations = $qb->getQuery()->getResult();

        $data = array_map(function ($r) {
            return [
                'id' => $r->getId(),
                'eventName' => $r->getEventName(),
                'startTime' => $r->getStartTime()->format('Y-m-d H:i'),
                'endTime' => $r->getEndTime()->format('Y-m-d H:i'),
                'space' => [
                    'id' => $r->getSpace()->getId(),
                    'name' => $r->getSpace()->getName()
                ]
            ];
        }, $reservations);

        return new JsonResponse($data);
    }
}
