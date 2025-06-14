<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateReservationDTO
{
    #[Assert\NotBlank(message: "Event name is required")]
    public string $eventName;

    #[Assert\NotBlank(message: "Start time is required")]
    #[Assert\DateTime(message: "Start time must be a valid datetime")]
    public string $startTime;

    #[Assert\NotBlank(message: "End time is required")]
    #[Assert\DateTime(message: "End time must be a valid datetime")]
    public string $endTime;

    #[Assert\NotBlank(message: "Space ID is required")]
    #[Assert\Positive(message: "Space ID must be a positive number")]
    public int $spaceId;
}
