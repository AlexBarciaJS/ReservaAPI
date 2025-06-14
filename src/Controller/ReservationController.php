<?php

namespace App\Controller;

use App\DTO\CreateReservationDTO;
use App\Entity\Reservation;
use App\Entity\Space;
use App\Repository\ReservationRepository;
use App\Repository\SpaceRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        // Convert times
        $start = new \DateTime($dto->startTime);
        $end = new \DateTime($dto->endTime);

        if ($start >= $end) {
            return $this->json(['error' => 'End time must be after start time'], 400);
        }

        $space = $spaceRepo->find($dto->spaceId);
        if (!$space) {
            return $this->json(['error' => 'Space not found'], 404);
        }

        // Check for overlapping reservations
        $existingReservations = $reservationRepo->findBy(['space' => $space]);
        foreach ($existingReservations as $existing) {
            if (
                $start < $existing->getEndTime() &&
                $end > $existing->getStartTime()
            ) {
                return $this->json(['error' => 'The space is already reserved at this time.'], 409);
            }
        }

        // Create reservation
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
}
